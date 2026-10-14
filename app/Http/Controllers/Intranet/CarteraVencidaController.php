<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Models\Intranet\CarteraVencidaCaratula;
use App\Models\Intranet\CarteraVencidaIngreso;
use App\Models\Intranet\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CarteraVencidaController extends ApiController
{
    const GOOGLE_SHEET_CARATULA_URL = 'https://docs.google.com/spreadsheets/d/1cXCeRIWrvWxzJdubm9PPEbcb996lJq4Q1QT1uUmCMWQ/gviz/tq?tqx=out:csv&sheet=LLenado%20Caratula';
    const GOOGLE_SHEET_INGRESOS_URL = 'https://docs.google.com/spreadsheets/d/1iFDIIFCKl8FPEVaMM0UJyTkor_KBNRwFCS2Vuzs7Vj8/gviz/tq?tqx=out:csv';

    const SYNC_CACHE_KEY = 'cartera_vencida_caratula_last_sync';
    const SYNC_INGRESOS_CACHE_KEY = 'cartera_vencida_ingresos_last_sync';

    /**
     * Obtiene los datos de cartera vencida e ingresos para un cliente específico
     */
    public function getClienteData(Request $request)
    {
        // Si las tablas están vacías, sincronizar automáticamente por primera vez
        if (CarteraVencidaCaratula::count() === 0) {
            $this->syncFromGoogleSheet();
        }
        if (CarteraVencidaIngreso::count() === 0) {
            $this->syncIngresosFromGoogleSheet();
        }

        $clienteId = $request->input('cliente_id');
        $nombre = trim($request->input('nombre', ''));

        $clienteModel = null;
        if (!empty($clienteId)) {
            $clienteModel = Cliente::find($clienteId);
        }

        if ($clienteModel && empty($nombre)) {
            $nombre = trim($clienteModel->nombre);
        }

        $equip = trim($request->input('equip', ''));
        if (empty($equip) && $clienteModel) {
            $equip = trim($clienteModel->equip ?? '');
        }

        $matchingQuery = null;
        $matchedBy = null;
        $matchedClienteName = $nombre;

        // PASO 1: Si el cliente tiene EQUIP, buscar por id_cliente (Columna X / Índice 23)
        if (!empty($equip)) {
            $normEquip = strtoupper(trim($equip));
            $count = CarteraVencidaCaratula::whereRaw("UPPER(TRIM(id_cliente)) = ?", [$normEquip])->count();
            if ($count > 0) {
                $matchingQuery = CarteraVencidaCaratula::whereRaw("UPPER(TRIM(id_cliente)) = ?", [$normEquip]);
                $matchedBy = 'equip';
            }
        }

        // PASO 2: Si no tiene equip o no hubo coincidencias por equip, buscar por nombre (Columna N / Índice 13)
        if (!$matchingQuery && !empty($nombre)) {
            $normalized = $this->normalizeText($nombre);

            // 2A: Coincidencia exacta o contiene
            $existsExact = CarteraVencidaCaratula::whereRaw(
                "UPPER(REPLACE(REPLACE(REPLACE(cliente, '.', ''), ',', ''), '-', '')) = ?",
                [$normalized]
            )->exists();

            if ($existsExact) {
                $matchedBy = 'nombre';
                $matchingQuery = CarteraVencidaCaratula::whereRaw(
                    "UPPER(REPLACE(REPLACE(REPLACE(cliente, '.', ''), ',', ''), '-', '')) = ?",
                    [$normalized]
                );
            } else {
                // 2B: Búsqueda por palabras clave
                $words = array_filter(explode(' ', $normalized), fn($w) => strlen($w) > 2);
                if (!empty($words)) {
                    $q = CarteraVencidaCaratula::query();
                    foreach ($words as $w) {
                        $q->where('cliente', 'like', "%{$w}%");
                    }
                    if ($q->exists()) {
                        $matchedBy = 'nombre';
                        $matchingQuery = $q;
                    }
                }
            }
        }

        $rows = $matchingQuery ? $matchingQuery->get() : collect();

        // Si encontramos filas en carátula, extraer el nombre exacto de la base de datos
        if ($rows->isNotEmpty()) {
            $matchedClienteName = $rows->first()->cliente ?? $nombre;
        }

        // BUSCAR INGRESOS DEL CLIENTE EN LA SEGUNDA TABLA
        $ingresosQuery = null;
        if (!empty($equip)) {
            $normEquip = strtoupper(trim($equip));
            $ingresosQuery = CarteraVencidaIngreso::whereRaw("UPPER(TRIM(id_cliente)) = ?", [$normEquip]);
        } elseif ($rows->isNotEmpty()) {
            $caratulaIdClientes = $rows->pluck('id_cliente')->filter()->unique()->values();
            if ($caratulaIdClientes->isNotEmpty()) {
                $ingresosQuery = CarteraVencidaIngreso::whereIn('id_cliente', $caratulaIdClientes);
            }
        }

        $ingresosRows = $ingresosQuery
            ? $ingresosQuery->orderBy('fecha_orden', 'asc')->orderBy('id', 'asc')->get()
            : collect();

        // Si no se encontró nada ni en carátula ni en ingresos
        if ($rows->isEmpty() && $ingresosRows->isEmpty()) {
            return response()->json([
                'success' => true,
                'encontrado' => false,
                'nombre_buscado' => $nombre,
                'equip_buscado' => $equip,
                'last_sync' => Cache::get(self::SYNC_CACHE_KEY, 'Reciente'),
                'totales' => null,
                'detalles' => [],
                'ingresos' => [
                    'items' => [],
                    'totales' => null,
                ],
            ]);
        }

        // Totales de Carátula
        $sumMas90 = (float) $rows->sum('mas_de_90');
        $sumMas60 = (float) $rows->sum('mas_de_60');
        $sumMas30 = (float) $rows->sum('mas_de_30');
        $sumMas15 = (float) $rows->sum('mas_de_15');
        $sumDe1a15 = (float) $rows->sum('de_1_a_15');
        $sumPorVencer = (float) $rows->sum('por_vencer');
        $sumCarteraTotal = (float) $rows->sum('total');

        $totalVencido = round($sumMas90 + $sumMas60 + $sumMas30 + $sumMas15 + $sumDe1a15, 2);

        $totales = [
            'cliente' => $matchedClienteName,
            'mas_de_90' => round($sumMas90, 2),
            'mas_de_60' => round($sumMas60, 2),
            'mas_de_30' => round($sumMas30, 2),
            'mas_de_15' => round($sumMas15, 2),
            'de_1_a_15' => round($sumDe1a15, 2),
            'por_vencer' => round($sumPorVencer, 2),
            'total_vencido' => $totalVencido,
            'cartera_total' => round($sumCarteraTotal > 0 ? $sumCarteraTotal : ($totalVencido + $sumPorVencer), 2),
            'total_documentos' => $rows->count(),
            'total_folios' => $rows->count(),
        ];

        // Mapear detalles individuales para la tabla expandible de folios
        $detalles = $rows->map(function ($r) {
            return [
                'id' => $r->id,
                'folio' => $r->folio,
                'depto' => $r->depto,
                'fecha_vencimiento' => $r->fecha_vencimiento,
                'sucursal' => $r->sucursal,
                'concepto' => $r->concepto,
                'mas_de_90' => (float)$r->mas_de_90,
                'mas_de_60' => (float)$r->mas_de_60,
                'mas_de_30' => (float)$r->mas_de_30,
                'mas_de_15' => (float)$r->mas_de_15,
                'de_1_a_15' => (float)$r->de_1_a_15,
                'por_vencer' => (float)$r->por_vencer,
                'total_vencido' => round((float)$r->mas_de_90 + (float)$r->mas_de_60 + (float)$r->mas_de_30 + (float)$r->mas_de_15 + (float)$r->de_1_a_15, 2),
                'cartera_total' => (float)$r->total,
            ];
        });

        // Agrupación por Sucursal
        $porSucursal = $rows->groupBy('sucursal')->map(function ($items, $suc) {
            $m90 = round((float)$items->sum('mas_de_90'), 2);
            $m60 = round((float)$items->sum('mas_de_60'), 2);
            $m30 = round((float)$items->sum('mas_de_30'), 2);
            $m15 = round((float)$items->sum('mas_de_15'), 2);
            $m1a15 = round((float)$items->sum('de_1_a_15'), 2);
            $pVencer = round((float)$items->sum('por_vencer'), 2);
            $cTotal = round((float)$items->sum('total'), 2);
            $vencido = round($m90 + $m60 + $m30 + $m15 + $m1a15, 2);

            return [
                'sucursal' => $suc ?: 'Sin Sucursal',
                'mas_de_90' => $m90,
                'mas_de_60' => $m60,
                'mas_de_30' => $m30,
                'mas_de_15' => $m15,
                'de_1_a_15' => $m1a15,
                'por_vencer' => $pVencer,
                'total_vencido' => $vencido,
                'total' => $cTotal > 0 ? $cTotal : round($vencido + $pVencer, 2),
                'total_documentos' => $items->count(),
            ];
        })->values();

        // Agrupación por Departamento
        $porDepto = $rows->groupBy('depto')->map(function ($items, $dep) {
            $m90 = round((float)$items->sum('mas_de_90'), 2);
            $m60 = round((float)$items->sum('mas_de_60'), 2);
            $m30 = round((float)$items->sum('mas_de_30'), 2);
            $m15 = round((float)$items->sum('mas_de_15'), 2);
            $m1a15 = round((float)$items->sum('de_1_a_15'), 2);
            $pVencer = round((float)$items->sum('por_vencer'), 2);
            $cTotal = round((float)$items->sum('total'), 2);
            $vencido = round($m90 + $m60 + $m30 + $m15 + $m1a15, 2);

            return [
                'depto' => $dep ?: 'Sin Departamento',
                'mas_de_90' => $m90,
                'mas_de_60' => $m60,
                'mas_de_30' => $m30,
                'mas_de_15' => $m15,
                'de_1_a_15' => $m1a15,
                'por_vencer' => $pVencer,
                'total_vencido' => $vencido,
                'total' => $cTotal > 0 ? $cTotal : round($vencido + $pVencer, 2),
                'total_documentos' => $items->count(),
            ];
        })->values();

        // Extraer folios de cartera vencida para identificar créditos saldados vs pendientes
        $caratulaFolios = $rows->pluck('folio')->filter()->unique()->toArray();

        // MAPEO Y TOTALES DE INGRESOS (DETALLE INGRESOS)
        $ingresosItems = $ingresosRows->map(function ($r) use ($matchedClienteName, $caratulaFolios) {
            $saldado = !in_array($r->folio, $caratulaFolios);
            return [
                'id'              => $r->id,
                'id_cliente'      => $r->id_cliente,
                'cliente'         => $matchedClienteName,
                'ano'             => $r->ano,
                'mes'             => $r->mes,
                'dia'             => $r->dia,
                'folio'           => $r->folio,
                'descripcion'     => $r->descripcion,
                'credito'         => (float)$r->credito,
                'contado'         => (float)$r->contado,
                'nota_debito'     => (float)$r->nota_debito,
                'nota_credito'    => (float)$r->nota_credito,
                'pagos_cobranza'  => (float)$r->pagos_cobranza,
                'pagos_mostrador' => (float)$r->pagos_mostrador,
                'sucursal'        => $r->sucursal,
                'depto'           => $r->depto ?: 'Sin Departamento',
                'esta_saldado'    => $saldado,
                'estado'          => $saldado ? 'Saldado' : 'Pendiente',
            ];
        });

        $ingresosTotales = [
            'credito'         => round((float)$ingresosRows->sum('credito'), 2),
            'contado'         => round((float)$ingresosRows->sum('contado'), 2),
            'nota_debito'     => round((float)$ingresosRows->sum('nota_debito'), 2),
            'nota_credito'    => round((float)$ingresosRows->sum('nota_credito'), 2),
            'pagos_cobranza'  => round((float)$ingresosRows->sum('pagos_cobranza'), 2),
            'pagos_mostrador' => round((float)$ingresosRows->sum('pagos_mostrador'), 2),
            'total_filas'     => $ingresosRows->count(),
        ];

        // ANÁLISIS DE CRÉDITO OTORGADO VS PAGOS (POR MES Y POR AÑO)
        // Regla de negocio: Crédito Total Otorgado = Pagos Cobranza (Hoja Ingresos) + Total Cartera Vencida (Hoja Carátula Columna U)
        $pagosTotal = round((float)$ingresosRows->sum('pagos_cobranza'), 2);
        $carteraPendienteTotal = round($sumCarteraTotal > 0 ? $sumCarteraTotal : ($totalVencido + $sumPorVencer), 2);
        $creditoTotalOtorgado = round($pagosTotal + $carteraPendienteTotal, 2);

        $saldadosRows = $ingresosRows->filter(fn($r) => !in_array($r->folio, $caratulaFolios));
        $pendientesRows = $ingresosRows->filter(fn($r) => in_array($r->folio, $caratulaFolios));

        $porcentajePagado = $creditoTotalOtorgado > 0 ? round(($pagosTotal / $creditoTotalOtorgado) * 100, 1) : 0;
        $porcentajePendiente = $creditoTotalOtorgado > 0 ? round(($carteraPendienteTotal / $creditoTotalOtorgado) * 100, 1) : 0;

        $pagosRows = $ingresosRows->filter(fn($r) => (float)$r->pagos_cobranza > 0);

        $nombresMeses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $mesesMap = [
            'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6,
            'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12,
        ];

        $parseFechaVen = function ($str) {
            if (empty($str)) return null;
            $s = trim(explode(' ', (string)$str)[0]);
            if (empty($s)) return null;
            if (str_contains($s, '/')) {
                $parts = explode('/', $s);
                if (count($parts) >= 3) {
                    if (strlen($parts[0]) === 4) {
                        $ano = (int)$parts[0];
                        $mes = (int)$parts[1];
                    } else {
                        $ano = (int)$parts[2];
                        $mes = (int)$parts[1];
                    }
                    if ($ano < 100) $ano += 2000;
                    return ['ano' => $ano, 'mes' => $mes];
                }
            }
            if (str_contains($s, '-')) {
                $parts = explode('-', $s);
                if (count($parts) >= 3) {
                    if (strlen($parts[0]) === 4) {
                        $ano = (int)$parts[0];
                        $mes = (int)$parts[1];
                    } else {
                        $ano = (int)$parts[2];
                        $mes = (int)$parts[1];
                    }
                    if ($ano < 100) $ano += 2000;
                    return ['ano' => $ano, 'mes' => $mes];
                }
            }
            return null;
        };

        // 1. Agrupación por Año (Pagos de Cobranza + Monto Vencido de Cartera)
        $mapAno = [];
        foreach ($pagosRows as $r) {
            $a = (string)($r->ano ?: 'Sin Año');
            if (!isset($mapAno[$a])) {
                $mapAno[$a] = ['ano' => $a, 'pagos' => 0.0, 'vencido' => 0.0];
            }
            $mapAno[$a]['pagos'] += (float)$r->pagos_cobranza;
        }
        foreach ($rows as $r) {
            $tot = (float)$r->total;
            if ($tot <= 0) continue;
            $parsed = $parseFechaVen($r->fecha_vencimiento);
            $a = $parsed ? (string)$parsed['ano'] : 'Sin Año';
            if (!isset($mapAno[$a])) {
                $mapAno[$a] = ['ano' => $a, 'pagos' => 0.0, 'vencido' => 0.0];
            }
            $mapAno[$a]['vencido'] += $tot;
        }
        ksort($mapAno);
        $porAno = collect(array_values($mapAno))->map(fn($item) => [
            'ano'     => $item['ano'],
            'pagos'   => round($item['pagos'], 2),
            'vencido' => round($item['vencido'], 2),
        ]);

        // 2. Agrupación por Mes (Pagos de Cobranza + Monto Vencido de Cartera)
        $mapMes = [];
        foreach ($pagosRows as $r) {
            $ano = $r->ano ?: 2026;
            $mStr = strtolower(trim((string)$r->mes));
            $mNum = $mesesMap[$mStr] ?? 1;
            if ($r->fecha_movimiento && str_contains($r->fecha_movimiento, '/')) {
                $parts = explode('/', $r->fecha_movimiento);
                if (count($parts) === 3 && is_numeric($parts[0])) $mNum = (int)$parts[0];
            }
            $key = sprintf('%04d-%02d', $ano, $mNum);
            $label = ($nombresMeses[$mNum] ?? 'Mes ' . $mNum) . " {$ano}";
            if (!isset($mapMes[$key])) {
                $mapMes[$key] = [
                    'periodo' => $key,
                    'label'   => $label,
                    'mes'     => $mNum,
                    'ano'     => $ano,
                    'pagos'   => 0.0,
                    'vencido' => 0.0,
                ];
            }
            $mapMes[$key]['pagos'] += (float)$r->pagos_cobranza;
        }
        foreach ($rows as $r) {
            $tot = (float)$r->total;
            if ($tot <= 0) continue;
            $parsed = $parseFechaVen($r->fecha_vencimiento);
            if ($parsed) {
                $ano = $parsed['ano'];
                $mNum = $parsed['mes'];
                $key = sprintf('%04d-%02d', $ano, $mNum);
                $label = ($nombresMeses[$mNum] ?? 'Mes ' . $mNum) . " {$ano}";
                if (!isset($mapMes[$key])) {
                    $mapMes[$key] = [
                        'periodo' => $key,
                        'label'   => $label,
                        'mes'     => $mNum,
                        'ano'     => $ano,
                        'pagos'   => 0.0,
                        'vencido' => 0.0,
                    ];
                }
                $mapMes[$key]['vencido'] += $tot;
            }
        }
        ksort($mapMes);
        $porMes = collect(array_values($mapMes))->map(fn($item) => [
            'periodo' => $item['periodo'],
            'label'   => $item['label'],
            'mes'     => $item['mes'],
            'ano'     => $item['ano'],
            'pagos'   => round($item['pagos'], 2),
            'vencido' => round($item['vencido'], 2),
        ]);

        // Agrupación de ingresos y pagos por departamento de cartera_vencida_ingresos
        $porDeptoAnalisis = $ingresosRows->groupBy('depto')->map(function ($items, $dep) {
            $p = round((float)$items->sum('pagos_cobranza'), 2);
            $c = round((float)$items->sum('credito'), 2);
            return [
                'depto'        => $dep ?: 'Sin Departamento',
                'pagos'        => $p,
                'credito'      => $c,
                'folios_count' => $items->pluck('folio')->unique()->count(),
            ];
        })->values()->sortByDesc('pagos')->values();

        $analisisCreditoPagos = [
            'credito_total_otorgado' => $creditoTotalOtorgado,
            'credito_total'          => $creditoTotalOtorgado,
            'pagos_total'            => $pagosTotal,
            'cartera_pendiente'      => $carteraPendienteTotal,
            'porcentaje_pagado'      => $porcentajePagado,
            'porcentaje_pendiente'   => $porcentajePendiente,
            'nota_credito_total'     => round((float)$ingresosRows->sum('nota_credito'), 2),
            'folios_totales'         => $ingresosRows->pluck('folio')->unique()->count(),
            'por_ano'                => $porAno,
            'por_mes'                => $porMes,
            'por_depto'              => $porDeptoAnalisis,
        ];

        return response()->json([
            'success'        => true,
            'encontrado'     => true,
            'matched_by'     => $matchedBy,
            'cliente_nombre' => $matchedClienteName,
            'equip'          => $equip,
            'last_sync'      => Cache::get(self::SYNC_CACHE_KEY, date('d/m/Y H:i')),
            'totales'        => $totales,
            'por_sucursal'   => $porSucursal,
            'por_depto'      => $porDepto,
            'detalles'       => $detalles,
            'ingresos'       => [
                'items'    => $ingresosItems,
                'totales'  => $ingresosTotales,
                'analisis' => $analisisCreditoPagos,
            ],
        ]);
    }

    /**
     * Endpoint para sincronizar forzadamente ambas bases de datos desde Google Sheets
     */
    public function sync(Request $request)
    {
        $resCaratula = $this->syncFromGoogleSheet();
        $resIngresos = $this->syncIngresosFromGoogleSheet();

        Cache::forget('cartera_vencida_global_comparativa_data');

        $success = $resCaratula['success'] && $resIngresos['success'];
        $message = "Sincronización completada. Carátula: " . ($resCaratula['count'] ?? 0) . " registros. Ingresos: " . ($resIngresos['count'] ?? 0) . " registros.";

        return response()->json([
            'success' => $success,
            'message' => $message,
            'caratula_count' => $resCaratula['count'] ?? 0,
            'ingresos_count' => $resIngresos['count'] ?? 0,
            'last_sync' => Cache::get(self::SYNC_CACHE_KEY, date('d/m/Y H:i')),
        ]);
    }

    /**
     * Obtiene la comparativa mensual de deuda vs abonos para los folios presentes en ambas tablas
     */
    public function getGlobalComparativa(Request $request)
    {
        $cacheKey = 'cartera_vencida_global_comparativa_data';
        $data = Cache::remember($cacheKey, 1800, function () {
            $nombresMeses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];

            $mesesMap = [
                'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6,
                'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12,
            ];

            $parseFechaVen = function ($str) {
                if (empty($str)) return null;
                $s = trim(explode(' ', (string)$str)[0]);
                if (empty($s)) return null;
                if (str_contains($s, '/')) {
                    $parts = explode('/', $s);
                    if (count($parts) >= 3) {
                        $ano = strlen($parts[0]) === 4 ? (int)$parts[0] : (int)$parts[2];
                        $mes = (int)$parts[1];
                        if ($ano < 100) $ano += 2000;
                        return ['ano' => $ano, 'mes' => $mes];
                    }
                }
                if (str_contains($s, '-')) {
                    $parts = explode('-', $s);
                    if (count($parts) >= 3) {
                        $ano = strlen($parts[0]) === 4 ? (int)$parts[0] : (int)$parts[2];
                        $mes = (int)$parts[1];
                        if ($ano < 100) $ano += 2000;
                        return ['ano' => $ano, 'mes' => $mes];
                    }
                }
                return null;
            };

            $foliosCaratula = DB::table('cartera_vencida_caratula')
                ->whereNotNull('folio')
                ->whereRaw("TRIM(folio) != ''")
                ->selectRaw("DISTINCT TRIM(folio) as folio")
                ->pluck('folio')
                ->toArray();

            $foliosIngresos = DB::table('cartera_vencida_ingresos')
                ->whereNotNull('folio')
                ->whereRaw("TRIM(folio) != ''")
                ->selectRaw("DISTINCT TRIM(folio) as folio")
                ->pluck('folio')
                ->toArray();

            $interseccion = array_intersect_key(array_flip($foliosCaratula), array_flip($foliosIngresos));
            $foliosCompartidos = array_keys($interseccion);

            $caratulaRows = DB::table('cartera_vencida_caratula')
                ->whereIn('folio', $foliosCompartidos)
                ->select('folio', 'fecha_vencimiento', 'total', 'depto')
                ->get();

            $ingresosRows = DB::table('cartera_vencida_ingresos')
                ->whereIn('folio', $foliosCompartidos)
                ->where('pagos_cobranza', '>', 0)
                ->select('folio', 'ano', 'mes', 'fecha_movimiento', 'pagos_cobranza', 'depto')
                ->get();

            $folioCaratulaDepto = [];
            foreach ($caratulaRows as $r) {
                if (!isset($folioCaratulaDepto[$r->folio])) {
                    $folioCaratulaDepto[$r->folio] = trim((string)$r->depto);
                }
            }

            $rawCaratula = [];
            $deptosCaratula = [];
            $mesesData = [];

            foreach ($caratulaRows as $r) {
                $tot = (float)$r->total;
                if ($tot <= 0) continue;
                $parsed = $parseFechaVen($r->fecha_vencimiento);
                if ($parsed && $parsed['ano'] >= 2020 && $parsed['ano'] <= 2030) {
                    $ano = $parsed['ano'];
                    $mNum = $parsed['mes'];
                    $key = sprintf('%04d-%02d', $ano, $mNum);
                    $label = ($nombresMeses[$mNum] ?? 'Mes ' . $mNum) . " {$ano}";
                    $d = trim((string)($r->depto ?: 'Sin Departamento'));
                    $deptosCaratula[$d] = true;

                    $rawCaratula[] = [
                        'folio'   => $r->folio,
                        'periodo' => $key,
                        'label'   => $label,
                        'ano'     => $ano,
                        'mes'     => $mNum,
                        'depto'   => $d,
                        'total'   => $tot,
                    ];

                    if (!isset($mesesData[$key])) {
                        $mesesData[$key] = [
                            'periodo' => $key,
                            'label'   => $label,
                            'ano'     => $ano,
                            'mes'     => $mNum,
                            'deuda'   => 0.0,
                            'abonos'  => 0.0,
                        ];
                    }
                    $mesesData[$key]['deuda'] += $tot;
                }
            }

            $rawIngresos = [];
            $deptosIngresos = [];

            foreach ($ingresosRows as $r) {
                $p = (float)$r->pagos_cobranza;
                if ($p <= 0) continue;
                $ano = (int)($r->ano ?: 2026);
                $mStr = strtolower(trim((string)$r->mes));
                $mNum = $mesesMap[$mStr] ?? 1;
                if ($r->fecha_movimiento && str_contains($r->fecha_movimiento, '/')) {
                    $parts = explode('/', $r->fecha_movimiento);
                    if (count($parts) === 3 && is_numeric($parts[0])) $mNum = (int)$parts[0];
                }
                if ($ano >= 2020 && $ano <= 2030) {
                    $key = sprintf('%04d-%02d', $ano, $mNum);
                    $label = ($nombresMeses[$mNum] ?? 'Mes ' . $mNum) . " {$ano}";
                    $d = trim((string)($r->depto ?: 'Sin Departamento'));
                    $cDepto = $folioCaratulaDepto[$r->folio] ?? $d;
                    $deptosIngresos[$d] = true;

                    $rawIngresos[] = [
                        'folio'          => $r->folio,
                        'periodo'        => $key,
                        'label'          => $label,
                        'ano'            => $ano,
                        'mes'            => $mNum,
                        'depto'          => $d,
                        'caratula_depto' => $cDepto,
                        'pagos_cobranza' => $p,
                    ];

                    if (!isset($mesesData[$key])) {
                        $mesesData[$key] = [
                            'periodo' => $key,
                            'label'   => $label,
                            'ano'     => $ano,
                            'mes'     => $mNum,
                            'deuda'   => 0.0,
                            'abonos'  => 0.0,
                        ];
                    }
                    $mesesData[$key]['abonos'] += $p;
                }
            }

            ksort($mesesData);

            $mensual = array_values(array_map(function($item) {
                return [
                    'periodo' => $item['periodo'],
                    'label'   => $item['label'],
                    'ano'     => $item['ano'],
                    'mes'     => $item['mes'],
                    'deuda'   => round($item['deuda'], 2),
                    'abonos'  => round($item['abonos'], 2),
                ];
            }, $mesesData));

            $totalDeuda = array_sum(array_column($mensual, 'deuda'));
            $totalAbonos = array_sum(array_column($mensual, 'abonos'));

            // Lista consolidada de departamentos ordenada alfabéticamente
            $allDeptos = array_values(array_unique(array_merge(array_keys($deptosCaratula), array_keys($deptosIngresos))));
            sort($allDeptos);

            return [
                'total_folios_compartidos' => count($foliosCompartidos),
                'total_folios_caratula'    => count($foliosCaratula),
                'total_folios_ingresos'    => count($foliosIngresos),
                'deptos'                   => $allDeptos,
                'totales' => [
                    'deuda'   => round($totalDeuda, 2),
                    'abonos'  => round($totalAbonos, 2),
                    'saldo'   => round($totalDeuda - $totalAbonos, 2),
                    'pct'     => $totalDeuda > 0 ? round(($totalAbonos / $totalDeuda) * 100, 1) : 0,
                ],
                'mensual'      => $mensual,
                'raw_caratula' => $rawCaratula,
                'raw_ingresos' => $rawIngresos,
            ];
        });

        return response()->json($data);
    }

    /**
     * Descarga la hoja y guarda todos los registros en la tabla MySQL cartera_vencida_caratula
     */
    public function syncFromGoogleSheet(): array
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');

        try {
            $response = Http::timeout(60)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get(self::GOOGLE_SHEET_CARATULA_URL);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Error al descargar la hoja de carátula. Código HTTP: ' . $response->status(),
                ];
            }

            $csv = $response->body();
            $lines = explode("\n", $csv);
            if (count($lines) < 2) {
                return [
                    'success' => false,
                    'message' => 'El archivo CSV descargado está vacío.',
                ];
            }

            // Quitar encabezados
            $header = str_getcsv(array_shift($lines));

            $cleanNumber = function ($val) {
                if ($val === null || $val === '') return 0.0;
                $clean = str_replace([',', '$', ' '], '', (string)$val);
                return is_numeric($clean) ? floatval($clean) : 0.0;
            };

            $records = [];
            $now = now();

            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $row = str_getcsv($line);

                $cliente = trim($row[13] ?? '');
                $idCliente = trim($row[23] ?? '');

                // Si no tiene cliente ni id_cliente, ignorar fila vacía
                if ($cliente === '' && $idCliente === '') continue;

                $records[] = [
                    'id_sucursal'        => trim($row[0] ?? ''),
                    'sucursal'           => trim($row[1] ?? ''),
                    'linea'              => trim($row[2] ?? ''),
                    'concepto'           => trim($row[3] ?? ''),
                    'depto'              => trim($row[7] ?? ''),  // Col H (Departamento / Modulo)
                    'serie_fiscal'       => trim($row[8] ?? ''),
                    'cliente'            => $cliente,             // Col N (Cliente)
                    'mas_de_90'          => $cleanNumber($row[14] ?? 0),
                    'mas_de_60'          => $cleanNumber($row[15] ?? 0),
                    'mas_de_30'          => $cleanNumber($row[16] ?? 0),
                    'mas_de_15'          => $cleanNumber($row[17] ?? 0),
                    'de_1_a_15'          => $cleanNumber($row[18] ?? 0),
                    'por_vencer'         => $cleanNumber($row[19] ?? 0),
                    'total'              => $cleanNumber($row[20] ?? 0),
                    'id_cliente'         => $idCliente,           // Col X (ID_Cliente / equip)
                    'fecha_vencimiento'  => trim($row[24] ?? ''), // Col Y (FECHA VENCIMIENTO)
                    'folio'              => trim($row[29] ?? ''), // Col AD (FOLIO)
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            CarteraVencidaCaratula::truncate();
            DB::beginTransaction();

            // Insertar por bloques de 500 registros
            foreach (array_chunk($records, 500) as $chunk) {
                CarteraVencidaCaratula::insert($chunk);
            }
            DB::commit();

            $syncTime = date('d/m/Y H:i');
            Cache::put(self::SYNC_CACHE_KEY, $syncTime, 86400 * 30);

            return [
                'success' => true,
                'count' => count($records),
                'message' => 'Se guardaron ' . count($records) . ' registros en la base de datos de Cartera Vencida.',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error durante la sincronización: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Descarga la segunda hoja y guarda todos los registros en la tabla MySQL cartera_vencida_ingresos
     */
    public function syncIngresosFromGoogleSheet(): array
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $tempFile = null;
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'sheet_ingresos_');
            $response = Http::timeout(180)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->sink($tempFile)
                ->get(self::GOOGLE_SHEET_INGRESOS_URL);

            if (!$response->successful()) {
                if ($tempFile && file_exists($tempFile)) @unlink($tempFile);
                return [
                    'success' => false,
                    'message' => 'Error al descargar la hoja de ingresos. HTTP: ' . $response->status(),
                ];
            }

            $handle = fopen($tempFile, 'r');
            if (!$handle) {
                if ($tempFile && file_exists($tempFile)) @unlink($tempFile);
                return [
                    'success' => false,
                    'message' => 'Error al abrir el archivo descargado.',
                ];
            }

            // Quitar encabezados
            $header = fgetcsv($handle);

            $cleanNumber = function ($val) {
                if ($val === null || $val === '') return 0.0;
                $clean = str_replace([',', '$', ' '], '', (string)$val);
                return is_numeric($clean) ? floatval($clean) : 0.0;
            };

            $records = [];
            $total = 0;
            $now = now()->toDateTimeString();

            CarteraVencidaIngreso::truncate();
            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                $idCliente = trim($row[1] ?? '');
                $folio = trim($row[2] ?? '');

                if ($idCliente === '' && $folio === '') continue;

                $fecha = trim($row[3] ?? '');
                $dia = null;
                $mesNum = null;
                $ano = (int)trim($row[50] ?? 0);
                if ($fecha) {
                    $parts = explode('/', $fecha);
                    if (count($parts) === 3) {
                        $mesNum = (int)$parts[0];
                        $dia = (int)$parts[1];
                        if (!$ano) $ano = (int)$parts[2];
                    }
                }
                $mes = strtolower(trim($row[49] ?? ''));
                $fechaOrden = null;
                if ($ano > 1900 && $mesNum >= 1 && $mesNum <= 12 && $dia >= 1 && $dia <= 31) {
                    $fechaOrden = sprintf('%04d-%02d-%02d', $ano, $mesNum, $dia);
                }

                $records[] = [
                    'id_cliente'        => $idCliente,
                    'v_rec_no'          => trim($row[0] ?? ''),
                    'folio'             => $folio,
                    'fecha_movimiento'  => $fecha,
                    'ano'               => $ano ?: null,
                    'mes'               => $mes ?: null,
                    'dia'               => $dia ?: null,
                    'fecha_orden'       => $fechaOrden,
                    'descripcion'       => trim($row[20] ?? ''),
                    'depto'             => trim($row[47] ?? ''), // Col 47 (Departamento / Modulo)
                    'credito'           => $cleanNumber($row[35] ?? 0),
                    'contado'           => $cleanNumber($row[36] ?? 0),
                    'nota_debito'       => $cleanNumber($row[37] ?? 0),
                    'nota_credito'      => $cleanNumber($row[38] ?? 0),
                    'pagos_cobranza'    => $cleanNumber($row[39] ?? 0),
                    'pagos_mostrador'   => $cleanNumber($row[40] ?? 0),
                    'sucursal'          => trim($row[46] ?? ''),
                    'condiciones'       => trim($row[15] ?? ''),
                    'tipo_moneda'       => trim($row[22] ?? ''),
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];

                $total++;

                if (count($records) >= 1000) {
                    DB::table('cartera_vencida_ingresos')->insert($records);
                    $records = [];
                }
            }

            if (!empty($records)) {
                DB::table('cartera_vencida_ingresos')->insert($records);
            }

            DB::commit();
            fclose($handle);
            if ($tempFile && file_exists($tempFile)) @unlink($tempFile);

            $syncTime = date('d/m/Y H:i');
            Cache::put(self::SYNC_INGRESOS_CACHE_KEY, $syncTime, 86400 * 30);

            return [
                'success' => true,
                'count' => $total,
                'message' => 'Se guardaron ' . $total . ' registros en la base de datos de Ingresos.',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($tempFile && file_exists($tempFile)) @unlink($tempFile);
            return [
                'success' => false,
                'message' => 'Error durante la sincronización de ingresos: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Normaliza un texto para comparaciones sin acentos, mayúsculas y caracteres especiales
     */
    private function normalizeText(string $text): string
    {
        $text = strtoupper(trim($text));
        $text = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ', '.', ',', '-', '/', '_'],
            ['A', 'E', 'I', 'O', 'U', 'U', 'N', ' ', ' ', ' ', ' ', ' '],
            $text
        );
        return preg_replace('/\s+/', ' ', trim($text));
    }
}
