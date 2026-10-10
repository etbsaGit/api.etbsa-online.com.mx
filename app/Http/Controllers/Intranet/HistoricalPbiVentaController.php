<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Models\Intranet\HistoricalPbiVenta;
use App\Models\Intranet\HistoricalPbiAnual;
use App\Models\Intranet\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HistoricalPbiVentaController extends ApiController
{
    /**
     * Obtiene el análisis comercial / histórico de ventas de un cliente
     */
    public function getClienteData(Request $request)
    {
        $nombre = trim($request->input('nombre', ''));
        $clienteId = $request->input('cliente_id');

        if (empty($nombre) && !empty($clienteId)) {
            $cliente = Cliente::find($clienteId);
            if ($cliente) {
                $nombre = trim($cliente->nombre);
            }
        }

        if (empty($nombre)) {
            return response()->json([
                'success' => false,
                'message' => 'No se especificó un nombre de cliente.',
                'kpis' => null,
            ]);
        }

        // 1. Normalizar nombre (quitar puntos, comas y espacios dobles)
        $normalized = trim(strtoupper(preg_replace('/[.,]/', '', $nombre)));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        // 2. Buscar Cliente en base de datos intranet (para perfil, RFC, clasificaciones y solicitudes de crédito)
        $clienteModel = null;
        if (!empty($clienteId)) {
            $clienteModel = Cliente::with(['classification', 'creditoClassification', 'segmentation', 'tactic'])->find($clienteId);
        }
        if (!$clienteModel) {
            $clienteModel = Cliente::with(['classification', 'creditoClassification', 'segmentation', 'tactic'])
                ->whereRaw("UPPER(REPLACE(REPLACE(nombre, '.', ''), ',', '')) = ?", [$normalized])
                ->first();
        }
        if (!$clienteModel) {
            $cleaned = preg_replace('/[.,]/', ' ', $nombre);
            $words = array_filter(explode(' ', $cleaned), fn($w) => strlen($w) > 2);
            if (!empty($words)) {
                $qCli = Cliente::with(['classification', 'creditoClassification', 'segmentation', 'tactic']);
                foreach ($words as $w) {
                    $qCli->where('nombre', 'like', "%{$w}%");
                }
                $clienteModel = $qCli->first();
            }
        }

        // 3. Buscar en HistoricalPbiVenta (Equipos y Maquinaria)
        $rows = HistoricalPbiVenta::whereRaw(
            "UPPER(REPLACE(REPLACE(nombre_cliente, '.', ''), ',', '')) = ?",
            [$normalized]
        )->get();

        $nombreMatch = $clienteModel ? $clienteModel->nombre : $nombre;
        if ($rows->isNotEmpty()) {
            $nombreMatch = $rows->first()->nombre_cliente;
        } else {
            $cleaned = preg_replace('/[.,]/', ' ', $nombre);
            $words = array_filter(explode(' ', $cleaned), fn($w) => strlen($w) > 2);

            if (!empty($words)) {
                $queryLike = HistoricalPbiVenta::query();
                foreach ($words as $word) {
                    $queryLike->where('nombre_cliente', 'like', "%{$word}%");
                }
                $rows = $queryLike->get();
            }

            if ($rows->isNotEmpty()) {
                $nombreMatch = $rows->first()->nombre_cliente;
            } else {
                $firstPart = substr($nombre, 0, min(10, strlen($nombre)));
                if (strlen($firstPart) >= 3) {
                    $rows = HistoricalPbiVenta::where('nombre_cliente', 'like', "%{$firstPart}%")->get();
                    if ($rows->isNotEmpty()) {
                        $nombreMatch = $rows->first()->nombre_cliente;
                    }
                }
            }
        }

        // 4. Buscar en HistoricalPbiAnual (Líneas y Tendencias 2012-2026)
        $anualRows = HistoricalPbiAnual::whereRaw(
            "UPPER(REPLACE(REPLACE(nombre_cliente, '.', ''), ',', '')) = ?",
            [$normalized]
        )->orWhereRaw(
            "UPPER(REPLACE(REPLACE(nom_clie_equip, '.', ''), ',', '')) = ?",
            [$normalized]
        )->get();

        if ($anualRows->isEmpty()) {
            $cleaned = preg_replace('/[.,]/', ' ', $nombreMatch);
            $words = array_filter(explode(' ', $cleaned), fn($w) => strlen($w) > 2);
            if (!empty($words)) {
                $qAnual = HistoricalPbiAnual::query();
                foreach ($words as $w) {
                    $qAnual->where(function ($sub) use ($w) {
                        $sub->where('nombre_cliente', 'like', "%{$w}%")
                            ->orWhere('nom_clie_equip', 'like', "%{$w}%");
                    });
                }
                $anualRows = $qAnual->get();
            }
        }

        if ($anualRows->isEmpty()) {
            $claveCliente = $clienteModel?->equip ?: $rows->first()?->clave_cliente;
            if (!empty($claveCliente)) {
                $anualRows = HistoricalPbiAnual::where('clave_cliente', $claveCliente)->get();
            }
        }

        if ($rows->isEmpty() && $anualRows->isEmpty() && !$clienteModel) {
            // Buscar posibles sugerencias de nombres parecidos
            $words = array_filter(explode(' ', $nombre), fn($w) => strlen($w) > 3);
            $sugerencias = [];
            if (!empty($words)) {
                $sugQuery = HistoricalPbiVenta::select('nombre_cliente')->distinct();
                foreach (array_slice($words, 0, 2) as $w) {
                    $sugQuery->where('nombre_cliente', 'like', "%{$w}%");
                }
                $sugerencias = $sugQuery->limit(5)->pluck('nombre_cliente');
            }

            return response()->json([
                'success' => true,
                'encontrado' => false,
                'nombre_buscado' => $nombre,
                'sugerencias' => $sugerencias,
                'kpis' => null,
                'last_sync' => Cache::get('historical_pbi_last_sync'),
            ]);
        }

        // Calcular Métricas / KPIs de Maquinaria
        $totalVenta = (float) $rows->sum('precio_venta');
        $totalCosto = (float) $rows->sum('total_costo');
        $totalMargen = (float) $rows->sum('margen');
        $margenPct = $totalVenta > 0 ? round(($totalMargen / $totalVenta) * 100, 2) : 0;
        $totalUnidades = $rows->count();

        // Años de compra en maquinaria
        $aniosValidos = $rows->pluck('anio')->filter(fn($a) => $a && $a > 2000)->sort()->values();
        $primeraCompra = $aniosValidos->first();
        $ultimaCompra = $rows->pluck('ultima_compra')->filter()->last() ?? $aniosValidos->last();

        // Desglose por Departamento / Línea
        $porDepartamento = $rows->groupBy(function($item) {
            return $item->departamento ?: 'GENERAL';
        })->map(function ($items, $dept) {
            $venta = (float) $items->sum('precio_venta');
            $margen = (float) $items->sum('margen');
            return [
                'departamento' => $dept,
                'unidades' => $items->count(),
                'venta' => $venta,
                'margen' => $margen,
                'margen_pct' => $venta > 0 ? round(($margen / $venta) * 100, 2) : 0,
            ];
        })->values()->sortByDesc('venta')->values();

        // Desglose por Sucursal (para las barras horizontales)
        $porSucursal = $rows->groupBy(function($item) {
            return $item->sucursal ?: 'SIN SUCURSAL';
        })->map(function ($items, $suc) {
            $venta = (float) $items->sum('precio_venta');
            return [
                'sucursal' => $suc,
                'unidades' => $items->count(),
                'venta' => $venta,
                'margen' => (float) $items->sum('margen'),
            ];
        })->values()->sortByDesc('venta')->values();

        // 1. Venta por Categoría
        $ventaCategoria = $rows->groupBy(function($item) {
            return $item->categoria ?: 'OTRAS';
        })->map(function ($items, $cat) {
            $venta = (float) $items->sum('precio_venta');
            $costo = (float) $items->sum('total_costo');
            $margen = (float) $items->sum('margen');
            return [
                'categoria' => $cat,
                'unidades' => $items->count(),
                'precio_venta' => $venta,
                'margen' => $margen,
                'margen_pct' => $venta > 0 ? round(($margen / $venta) * 100, 2) : 0,
            ];
        })->values()->sortByDesc('precio_venta')->values();

        // Helper para agrupar por columna con filtro de categoría
        $agrupar = function ($coleccion, $columnaClave, $columnaTexto = null) {
            return $coleccion->groupBy(function ($item) use ($columnaClave) {
                return $item->{$columnaClave} ?: 'SIN ESPECIFICAR';
            })->map(function ($items, $clave) use ($columnaTexto) {
                $venta = (float) $items->sum('precio_venta');
                return [
                    'clave' => $clave,
                    'unidades' => $items->count(),
                    'precio_venta' => $venta,
                    'margen' => (float) $items->sum('margen'),
                ];
            })->values()->sortByDesc('precio_venta')->values();
        };

        // 2. Tractores por Familia (cuando categoria == 'TRACTORES')
        $tractores = $rows->filter(fn($r) => strtoupper(trim($r->categoria)) === 'TRACTORES');
        $tractoresFamilia = $agrupar($tractores, 'clasificacion');

        // 3. Tractores por Modelo
        $tractoresModelo = $agrupar($tractores, 'modelo');

        // 4. Seminuevos
        $seminuevos = $rows->filter(function($r) {
            $cat = strtoupper(trim($r->categoria));
            $depto = strtoupper(trim($r->departamento));
            return in_array($cat, ['AGRICOLA USADOS', 'CONSTRUCCION USADOS']) ||
                   str_contains($depto, 'SEMINUEVOS') ||
                   !empty($r->cat_seminuevos);
        });
        $seminuevosClasif = $agrupar($seminuevos, 'clasificacion');

        // 5. Jardinería y Golf
        $jardineria = $rows->filter(fn($r) => strtoupper(trim($r->categoria)) === 'JARDINERIA Y GOLF');
        $jardineriaClasif = $agrupar($jardineria, 'clasificacion');

        // 6. Implementos JD
        $implementosJd = $rows->filter(fn($r) => strtoupper(trim($r->categoria)) === 'IMPLEMENTO JD');
        $implementosJdClasif = $agrupar($implementosJd, 'clasificacion');

        // 7. Implementos Diversa
        $implementosDiv = $rows->filter(fn($r) => strtoupper(trim($r->categoria)) === 'IMPLEMENTO DIVERSA');
        $implementosDivClasif = $agrupar($implementosDiv, 'clasificacion');

        // 8. Nuevas Tecnologías
        $nuevasTec = $rows->filter(function($r) {
            $cat = strtoupper(trim($r->categoria));
            $depto = strtoupper(trim($r->departamento));
            return $cat === 'NUEVAS TECNOLOGIAS' || str_contains($depto, 'TECNOLOGIA');
        });
        $nuevasTecClasif = $agrupar($nuevasTec, 'clasificacion');

        // Historial completo de facturas/compras
        $facturas = $rows->sortByDesc(function ($item) {
            return ($item->anio ?: 0) * 10000 + ($item->mes_no ?: 0) * 100 + $item->id;
        })->map(function ($item) {
            return [
                'id' => $item->id,
                'fecha_factura' => $item->fecha_factura,
                'sucursal' => $item->sucursal,
                'departamento' => $item->departamento,
                'categoria' => $item->categoria,
                'clasificacion' => $item->clasificacion,
                'modelo' => $item->modelo,
                'descripcion_producto' => $item->descripcion_producto,
                'nip' => $item->nip,
                'precio_venta' => (float) $item->precio_venta,
                'total_costo' => (float) $item->total_costo,
                'margen' => (float) $item->margen,
                'margen_pct' => (float) $item->margen_pct ?: ($item->precio_venta > 0 ? round(($item->margen / $item->precio_venta) * 100, 2) : 0),
                'nombre_vendedor' => $item->nombre_vendedor,
                'anio' => $item->anio,
                'mes' => $item->mes,
            ];
        })->values();

        // Tendencias Anuales (desde historical_pbi_anual)
        $tendenciasAnuales = $anualRows->groupBy('anio')
            ->map(function ($items, $anio) {
                return [
                    'anio' => (int) $anio,
                    'venta_maquinaria' => (float) $items->sum('venta_maquinaria'),
                    'venta_refacciones' => (float) $items->sum('venta_refacciones'),
                    'venta_servicio' => (float) $items->sum('venta_servicio'),
                    'venta_riego' => (float) $items->sum('venta_riego'),
                    'venta_chevron' => (float) $items->sum('venta_chevron'),
                    'venta_nuevas_tecnologias' => (float) $items->sum('venta_nuevas_tecnologias'),
                    'total_venta' => (float) $items->sum('total_venta'),

                    'margen_maquinaria' => (float) $items->sum('margen_maquinaria'),
                    'margen_refacciones' => (float) $items->sum('margen_refacciones'),
                    'margen_servicio' => (float) $items->sum('margen_servicio'),
                    'margen_riego' => (float) $items->sum('margen_riego'),
                    'margen_chevron' => (float) $items->sum('margen_chevron'),
                    'margen_nuevas_tecnologias' => (float) $items->sum('margen_nuevas_tecnologias'),
                    'total_margen' => (float) $items->sum('margen_total'),
                ];
            })
            ->filter(fn($item) => $item['anio'] && $item['anio'] >= 2012)
            ->values()
            ->sortBy('anio')
            ->values();

        // ==========================================
        // KPIS ESTRATÉGICOS PARA TOMA DE DECISIONES
        // ==========================================
        $solicitudes = collect();
        $creditosAprobados = collect();
        if ($clienteModel) {
            $solicitudes = DB::table('credito_solicitud')
                ->leftJoin('estatus', 'estatus.id', '=', 'credito_solicitud.estatus_id')
                ->leftJoin('credito_lineas', 'credito_lineas.id', '=', 'credito_solicitud.linea_id')
                ->where('credito_solicitud.cliente_id', $clienteModel->id)
                ->select(
                    'credito_solicitud.*',
                    'estatus.nombre as estatus_nombre',
                    'estatus.color as estatus_color',
                    'estatus.clave as estatus_clave',
                    'credito_lineas.name as linea_nombre'
                )
                ->orderByDesc('credito_solicitud.id')
                ->get();

            $creditosAprobados = DB::table('credito_aprobado_cliente')
                ->leftJoin('credito_lineas', 'credito_lineas.id', '=', 'credito_aprobado_cliente.linea_id')
                ->where('credito_aprobado_cliente.cliente_id', $clienteModel->id)
                ->select('credito_aprobado_cliente.*', 'credito_lineas.name as linea_nombre')
                ->get();
        }

        $solicitudActiva = $solicitudes->first(fn($s) => in_array($s->estatus_clave, ['credito-en-proceso', 'credito-solicitado']));

        // Consolidar totales históricos de compras
        $totalVentaMaquinaria = (float) $rows->sum('precio_venta');
        $totalMargenMaquinaria = (float) $rows->sum('margen');
        $unidadesMaquinaria = $rows->count();

        $totalVentaAnual = (float) $anualRows->sum('total_venta');
        $totalMargenAnual = (float) $anualRows->sum('margen_total');

        // Sumas por departamento de la hoja RADIOGRAFIA CLIENTES PARA CREDITO (HistoricalPbiAnual)
        $ventasRadiografia = [
            'maquinaria' => (float) $anualRows->sum('venta_maquinaria'),
            'refacciones' => (float) $anualRows->sum('venta_refacciones'),
            'servicio' => (float) $anualRows->sum('venta_servicio'),
            'riego' => (float) $anualRows->sum('venta_riego'),
            'chevron' => (float) $anualRows->sum('venta_chevron'),
            'nuevas_tecnologias' => (float) $anualRows->sum('venta_nuevas_tecnologias'),
            'total_venta' => (float) $anualRows->sum('total_venta'),
        ];
        if ($ventasRadiografia['total_venta'] <= 0) {
            $ventasRadiografia['total_venta'] = (float) array_sum([
                $ventasRadiografia['maquinaria'],
                $ventasRadiografia['refacciones'],
                $ventasRadiografia['servicio'],
                $ventasRadiografia['riego'],
                $ventasRadiografia['chevron'],
                $ventasRadiografia['nuevas_tecnologias'],
            ]);
        }

        $margenRadiografia = [
            'maquinaria' => (float) $anualRows->sum('margen_maquinaria'),
            'refacciones' => (float) $anualRows->sum('margen_refacciones'),
            'servicio' => (float) $anualRows->sum('margen_servicio'),
            'riego' => (float) $anualRows->sum('margen_riego'),
            'chevron' => (float) $anualRows->sum('margen_chevron'),
            'nuevas_tecnologias' => (float) $anualRows->sum('margen_nuevas_tecnologias'),
            'total_margen' => (float) $anualRows->sum('margen_total'),
        ];

        // Desglose de Ventas por Sucursal y Departamento (desde historical_pbi_anual)
        $desgloseSucursalDepto = $anualRows->groupBy(function($item) {
            return $item->sucursal ?: 'SIN SUCURSAL';
        })->map(function ($items, $suc) {
            $maq = (float) $items->sum('venta_maquinaria');
            $ref = (float) $items->sum('venta_refacciones');
            $serv = (float) $items->sum('venta_servicio');
            $riego = (float) $items->sum('venta_riego');
            $chev = (float) $items->sum('venta_chevron');
            $nt = (float) $items->sum('venta_nuevas_tecnologias');
            $total = (float) $items->sum('total_venta');
            if ($total <= 0) {
                $total = $maq + $ref + $serv + $riego + $chev + $nt;
            }
            return [
                'sucursal' => $suc,
                'venta_maquinaria' => $maq,
                'venta_refacciones' => $ref,
                'venta_servicio' => $serv,
                'venta_riego' => $riego,
                'venta_chevron' => $chev,
                'venta_nuevas_tecnologias' => $nt,
                'total_venta' => $total,
            ];
        })->values()->sortByDesc('total_venta')->values();

        $totalVentaPostventa = (float) $anualRows->sum(fn($r) => (float)$r->venta_refacciones + (float)$r->venta_servicio + (float)$r->venta_chevron);
        $totalVentaRiegoTec = (float) $anualRows->sum(fn($r) => (float)$r->venta_riego + (float)$r->venta_nuevas_tecnologias);

        $totalCompradoGlobal = max($totalVentaAnual, $totalVentaMaquinaria);
        $totalMargenGlobal = max($totalMargenAnual, $totalMargenMaquinaria);
        $margenPctGlobal = $totalCompradoGlobal > 0 ? round(($totalMargenGlobal / $totalCompradoGlobal) * 100, 1) : 0;

        // Compras recientes (2024, 2025, 2026)
        $recientesRows = $anualRows->filter(fn($r) => in_array((int)$r->anio, [2024, 2025, 2026]));
        $comprasRecientes3Y = (float) $recientesRows->sum('total_venta');
        $comproEn2026 = $anualRows->contains(fn($r) => (int)$r->anio === 2026 && $r->total_venta > 0);

        // Años de relación comercial
        $aniosConCompra = $anualRows->where('total_venta', '>', 0)->pluck('anio')->map(fn($a) => (int)$a)->unique()->sort()->values();
        if ($aniosConCompra->isEmpty() && $rows->isNotEmpty()) {
            $aniosConCompra = $rows->pluck('anio')->filter(fn($a) => $a && $a > 2000)->map(fn($a) => (int)$a)->unique()->sort()->values();
        }

        $primeraCompraAnio = $aniosConCompra->first() ?: null;
        $ultimaCompraAnio = $aniosConCompra->last() ?: null;
        $currentYear = (int) date('Y');
        $antiguedadAnios = $primeraCompraAnio ? max(1, ($currentYear - $primeraCompraAnio) + 1) : 0;
        $aniosActivo = $aniosConCompra->count();
        $promedioAnual = $aniosActivo > 0 ? round($totalCompradoGlobal / $aniosActivo, 2) : 0;

        // Dictamen / Recomendación para toma de decisiones
        $creditoClasif = $clienteModel?->creditoClassification?->name ?? 'Sin clasificar';
        $isListaNegra = stripos($creditoClasif, 'Negra') !== false;

        if ($isListaNegra) {
            $dictamen = [
                'nivel' => 'ALTO RIESGO',
                'color' => '#ef4444',
                'badge_color' => 'negative',
                'icono' => 'dangerous',
                'recomendacion' => 'CRÉDITO RESTRINGIDO / BLOQUEADO',
                'detalle' => 'Cliente catalogado en Lista Negra institucional. Requiere autorización excepcional de Dirección General y condiciones estrictas.',
            ];
        } elseif ($totalCompradoGlobal >= 10000000 || in_array($creditoClasif, ['AAA', 'AA'])) {
            $mdpText = number_format($totalCompradoGlobal / 1000000, 1);
            $dictamen = [
                'nivel' => 'RIESGO BAJO (PREFERENTE)',
                'color' => '#10b981',
                'badge_color' => 'positive',
                'icono' => 'verified_user',
                'recomendacion' => 'APTO PARA CRÉDITO PREFERENCIAL',
                'detalle' => $totalCompradoGlobal > 0
                    ? "Excelente solvencia con {$mdpText} MDP facturados y {$antiguedadAnios} años de relación comercial."
                    : "Clasificado como {$creditoClasif} en catálogo. Alta capacidad y solvencia proyectada.",
            ];
        } elseif ($totalCompradoGlobal >= 2000000 || $antiguedadAnios >= 4) {
            $mdpText = number_format($totalCompradoGlobal / 1000000, 1);
            $dictamen = [
                'nivel' => 'RIESGO MODERADO (CONFIABLE)',
                'color' => '#f59e0b',
                'badge_color' => 'warning',
                'icono' => 'check_circle',
                'recomendacion' => 'APTO CON CONDICIONES ESTÁNDAR',
                'detalle' => "Cliente consolidado con {$antiguedadAnios} años de antigüedad y {$mdpText} MDP facturados. Se sugiere enganche mínimo del 25-30%.",
            ];
        } elseif ($totalCompradoGlobal > 0) {
            $milText = number_format($totalCompradoGlobal / 1000, 0);
            $dictamen = [
                'nivel' => 'RIESGO A EVALUAR (DESARROLLO)',
                'color' => '#3b82f6',
                'badge_color' => 'info',
                'icono' => 'info',
                'recomendacion' => 'EVALUAR CON GARANTÍAS ADICIONALES',
                'detalle' => "Volumen histórico moderado ({$milText} mil pesos). Se recomienda aval o enganche mayor al 35%.",
            ];
        } else {
            $dictamen = [
                'nivel' => 'SIN HISTORIAL DE COMPRAS',
                'color' => '#94a3b8',
                'badge_color' => 'grey-7',
                'icono' => 'help_outline',
                'recomendacion' => 'SOLICITAR EXPEDIENTE FINANCIERO COMPLETO',
                'detalle' => 'No registra compras históricas previas en bases de datos. Se requiere validación de buró de crédito, estados de cuenta y garantías.',
            ];
        }

        $decisionKpis = [
            'cliente_id' => $clienteModel?->id,
            'nombre' => $clienteModel?->nombre ?? $nombreMatch,
            'tipo_persona' => $clienteModel?->tipo ? ucfirst($clienteModel->tipo) : 'No especificado',
            'rfc' => $clienteModel?->rfc ?? 'N/A',
            'equip' => $clienteModel?->equip ?? ($rows->first()?->clave_cliente ?? 'N/A'),
            'clasificacion_comercial' => $clienteModel?->classification?->name ?? 'Sin clasificar',
            'clasificacion_credito' => $clienteModel?->creditoClassification?->name ?? 'Sin clasificar',
            'segmentacion' => $clienteModel?->segmentation?->name ?? 'Sin especificar',
            'tactica' => $clienteModel?->tactic?->name ?? 'Sin táctica',

            // Cuánto nos han comprado
            'total_comprado_global' => $totalCompradoGlobal,
            'total_venta_maquinaria' => $totalVentaMaquinaria,
            'total_venta_postventa' => $totalVentaPostventa,
            'total_venta_riego_tec' => $totalVentaRiegoTec,
            'compras_recientes_3y' => $comprasRecientes3Y,
            'compro_en_2026' => $comproEn2026,
            'promedio_anual' => $promedioAnual,
            'anios_activo' => $aniosActivo,
            'antiguedad_anios' => $antiguedadAnios,
            'primera_compra_anio' => $primeraCompraAnio,
            'ultima_compra_anio' => $ultimaCompraAnio,
            'total_margen_global' => $totalMargenGlobal,
            'margen_promedio_pct' => $margenPctGlobal,
            'unidades_maquinaria' => $unidadesMaquinaria,

            // Qué tipo de cliente está pidiendo crédito
            'credito_info' => [
                'tiene_solicitudes' => $solicitudes->isNotEmpty(),
                'solicitud_activa' => $solicitudActiva ? [
                    'id' => $solicitudActiva->id,
                    'folio' => $solicitudActiva->folio,
                    'linea' => $solicitudActiva->linea_nombre,
                    'monto' => (float) $solicitudActiva->monto_solicitado,
                    'enganche' => (float) $solicitudActiva->valor_enganche,
                    'numero_pagos' => $solicitudActiva->numero_pagos,
                    'estatus' => $solicitudActiva->estatus_nombre,
                    'estatus_clave' => $solicitudActiva->estatus_clave,
                    'estatus_color' => $solicitudActiva->estatus_color,
                    'fecha' => $solicitudActiva->created_at,
                ] : null,
                'total_solicitudes' => $solicitudes->count(),
                'solicitudes_pagadas' => $solicitudes->where('estatus_clave', 'credito-pagado')->count(),
                'solicitudes_en_proceso' => $solicitudes->where('estatus_clave', 'credito-en-proceso')->count(),
                'solicitudes_rechazadas' => $solicitudes->where('estatus_clave', 'credito-rechazado')->count(),
                'monto_total_solicitado' => (float) $solicitudes->sum('monto_solicitado'),
                'lineas_aprobadas_monto' => (float) $creditosAprobados->sum('monto'),
                'solicitudes_list' => $solicitudes->take(5)->map(fn($s) => [
                    'folio' => $s->folio,
                    'linea' => $s->linea_nombre,
                    'monto' => (float) $s->monto_solicitado,
                    'enganche' => (float) $s->valor_enganche,
                    'estatus' => $s->estatus_nombre,
                    'estatus_color' => $s->estatus_color,
                    'fecha' => substr($s->created_at, 0, 10),
                ])->values(),
            ],

            'dictamen' => $dictamen,
        ];

        return response()->json([
            'success' => true,
            'encontrado' => true,
            'nombre_buscado' => $nombre,
            'nombre_cliente' => $nombreMatch,
            'clave_cliente' => $rows->first()?->clave_cliente ?? ($clienteModel?->equip ?? null),
            'decision_kpis' => $decisionKpis,
            'ventas_radiografia' => $ventasRadiografia,
            'margen_radiografia' => $margenRadiografia,
            'desglose_sucursal_depto' => $desgloseSucursalDepto,
            'kpis' => [
                'total_venta' => $totalVenta,
                'total_costo' => $totalCosto,
                'total_margen' => $totalMargen,
                'margen_pct' => $margenPct,
                'total_unidades' => $totalUnidades,
                'primera_compra' => $primeraCompra,
                'ultima_compra' => $ultimaCompra,
            ],
            'por_departamento' => $porDepartamento,
            'por_sucursal' => $porSucursal,
            'venta_categoria' => $ventaCategoria,
            'tractores_familia' => $tractoresFamilia,
            'tractores_modelo' => $tractoresModelo,
            'seminuevos' => $seminuevosClasif,
            'jardineria' => $jardineriaClasif,
            'implementos_jd' => $implementosJdClasif,
            'implementos_diversa' => $implementosDivClasif,
            'nuevas_tecnologias' => $nuevasTecClasif,
            'facturas' => $facturas,
            'tendencias_anuales' => $tendenciasAnuales,
            'last_sync' => Cache::get('historical_pbi_last_sync'),
            'last_sync_anual' => Cache::get('historical_pbi_anual_last_sync'),
        ]);
    }

    /**
     * Búsqueda autocompletada de clientes en la base histórica
     */
    public function searchClientes(Request $request)
    {
        $term = trim($request->input('term', ''));
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $clientes = HistoricalPbiVenta::select('nombre_cliente', DB::raw('COUNT(*) as total_compras'), DB::raw('SUM(precio_venta) as total_venta'))
            ->where('nombre_cliente', 'like', "%{$term}%")
            ->groupBy('nombre_cliente')
            ->orderByDesc('total_venta')
            ->limit(15)
            ->get();

        return response()->json($clientes);
    }

    /**
     * Dispara la sincronización con Google Sheets bajo demanda
     */
    public function sync(Request $request)
    {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');

            $exitCode1 = Artisan::call('historico:sync-sheet');
            $output1 = Artisan::output();

            $exitCode2 = Artisan::call('historico:sync-anual');
            $output2 = Artisan::output();

            return response()->json([
                'success' => $exitCode1 === 0 && $exitCode2 === 0,
                'message' => 'Sincronización completada con éxito.',
                'output' => $output1 . "\n" . $output2,
                'last_sync' => Cache::get('historical_pbi_last_sync'),
                'last_sync_anual' => Cache::get('historical_pbi_anual_last_sync'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
