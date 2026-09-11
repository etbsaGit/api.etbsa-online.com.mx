<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Models\Intranet\CreditoInterno\CreditoHistorialPagos;
use App\Models\Intranet\CreditoInterno\CreditoLineas;
use App\Models\Intranet\CreditoInterno\CreditoSolicitud;
use App\Models\Sucursal;
use App\Models\Estatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditoInternoDashboardController extends ApiController
{
    /**
     * Devuelve el consolidado de métricas ejecutivas y datos para gráficas ApexCharts.
     * Acceso exclusivo para roles 'Admin' y 'Credito'.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || (!$user->hasRole('Admin') && !$user->hasRole('Credito'))) {
                return $this->respondError('No tiene permisos para acceder al Dashboard Ejecutivo de Crédito', 403);
            }

            $year = $request->input('year', Carbon::now()->year);
            $sucursalId = $request->input('sucursal_id');
            $lineaId = $request->input('linea_id');
            $asesorId = $request->input('asesor_id');
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            // 1. Base Query para Solicitudes con filtros (columnas calificadas con credito_solicitud)
            $solicitudesQuery = CreditoSolicitud::query()
                ->when($year && !$fechaInicio, function ($q) use ($year) {
                    $q->whereYear('credito_solicitud.created_at', $year);
                })
                ->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('credito_solicitud.created_at', [$fechaInicio, $fechaFin]);
                })
                ->when($sucursalId, function ($q) use ($sucursalId) {
                    $q->where('credito_solicitud.sucursal_id', $sucursalId);
                })
                ->when($lineaId, function ($q) use ($lineaId) {
                    $q->where('credito_solicitud.linea_id', $lineaId);
                })
                ->when($asesorId, function ($q) use ($asesorId) {
                    $q->where('credito_solicitud.asesor_id', $asesorId);
                });

            $solicitudesIds = (clone $solicitudesQuery)->pluck('credito_solicitud.id')->toArray();

            // 2. Base Query para Pagos asociados a esas solicitudes
            $pagosQuery = CreditoHistorialPagos::query()
                ->whereIn('credito_historial_pagos.solicitud_id', $solicitudesIds);

            // 3. Obtener cada bloque analítico
            $kpis = $this->getKpis($solicitudesQuery, $pagosQuery);
            $flujoMensual = $this->getFlujoMensual($solicitudesIds, (int)$year);
            $carteraPorLinea = $this->getCarteraPorLinea($solicitudesIds);
            $desempenoPorSucursal = $this->getDesempenoPorSucursal($solicitudesIds);
            $antiguedadSaldos = $this->getAntiguedadSaldos($solicitudesIds);
            $distribucionEstatus = $this->getDistribucionEstatus($solicitudesQuery);
            $topClientesMora = $this->getTopClientesMora($solicitudesIds);
            $detallesKpis = $this->getDetallesKpis($solicitudesIds, $solicitudesQuery);

            $data = [
                'kpis'                   => $kpis,
                'detalles_kpis'          => $detallesKpis,
                'flujo_mensual'          => $flujoMensual,
                'cartera_por_linea'      => $carteraPorLinea,
                'desempeno_por_sucursal' => $desempenoPorSucursal,
                'antiguedad_saldos'      => $antiguedadSaldos,
                'distribucion_estatus'   => $distribucionEstatus,
                'top_clientes_mora'      => $topClientesMora,
            ];

            return $this->respond($data, 'Métricas ejecutivas de crédito obtenidas correctamente');
        } catch (\Throwable $e) {
            return $this->respondError('Error al calcular métricas de crédito: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 1. KPIs Globales de Dirección
     */
    private function getKpis($solicitudesQuery, $pagosQuery)
    {
        $hoy = Carbon::now()->toDateString();

        // Total colocado (Monto solicitado de créditos autorizados/activos)
        $totalColocado = (float) (clone $solicitudesQuery)
            ->whereHas('estatus', function ($q) {
                $q->whereNotIn('nombre', ['Crédito Rechazado', 'Cancelado']);
            })
            ->sum('credito_solicitud.monto_solicitado');

        // Total cobrado / recuperado
        $totalCobrado = (float) (clone $pagosQuery)
            ->sum('credito_historial_pagos.monto_pagado');

        // Saldo total pendiente activo
        $saldoPendienteTotal = (float) (clone $pagosQuery)
            ->whereNull('credito_historial_pagos.fecha_liquidado')
            ->whereNotNull('credito_historial_pagos.saldo_pendiente')
            ->sum('credito_historial_pagos.saldo_pendiente');

        // Saldo vencido (pagos cuya fecha_a_pagar < hoy y no liquidados)
        $saldoVencido = (float) (clone $pagosQuery)
            ->whereNull('credito_historial_pagos.fecha_liquidado')
            ->where('credito_historial_pagos.fecha_a_pagar', '<', $hoy)
            ->where(function ($q) {
                $q->where('credito_historial_pagos.saldo_pendiente', '>', 0)
                  ->orWhereNull('credito_historial_pagos.monto_pagado');
            })
            ->sum(DB::raw('COALESCE(credito_historial_pagos.saldo_pendiente, 0)'));

        // Pagos validados por Dirección/Crédito
        $pagosValidados = (clone $pagosQuery)
            ->whereNotNull('credito_historial_pagos.validated_by')
            ->where('credito_historial_pagos.monto_pagado', '>', 0)
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(credito_historial_pagos.monto_pagado), 0) as total_monto')
            ->first();

        // Pagos pendientes de validación por Dirección/Crédito
        $pagosPendientesValidar = (clone $pagosQuery)
            ->whereNull('credito_historial_pagos.validated_by')
            ->where(function ($q) {
                $q->whereNotNull('credito_historial_pagos.document_id')
                  ->orWhere('credito_historial_pagos.monto_pagado', '>', 0);
            })
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(credito_historial_pagos.monto_pagado), 0) as total_monto')
            ->first();

        // Conteo de solicitudes
        $totalSolicitudes = (clone $solicitudesQuery)->count('credito_solicitud.id');
        $solicitudesActivas = (clone $solicitudesQuery)
            ->whereHas('estatus', function ($q) {
                $q->whereNotIn('nombre', ['Crédito Liquidado', 'Crédito Rechazado', 'Cancelado']);
            })->count('credito_solicitud.id');

        // Índices de gestión
        $tasaRecuperacion = ($totalColocado > 0) ? round(($totalCobrado / $totalColocado) * 100, 2) : 0;
        $tasaMorosidad = ($totalColocado > 0) ? round(($saldoVencido / $totalColocado) * 100, 2) : 0;
        $ticketPromedio = ($totalSolicitudes > 0) ? round($totalColocado / $totalSolicitudes, 2) : 0;

        return [
            'total_colocado'         => $totalColocado,
            'total_cobrado'          => $totalCobrado,
            'saldo_pendiente_total'  => $saldoPendienteTotal,
            'saldo_vencido'          => $saldoVencido,
            'tasa_recuperacion_pct'  => $tasaRecuperacion,
            'tasa_morosidad_pct'     => $tasaMorosidad,
            'ticket_promedio'        => $ticketPromedio,
            'total_solicitudes'      => $totalSolicitudes,
            'solicitudes_activas'    => $solicitudesActivas,
            'pagos_validados'        => [
                'conteo' => (int) ($pagosValidados->total_count ?? 0),
                'monto'  => (float) ($pagosValidados->total_monto ?? 0)
            ],
            'pagos_por_validar'      => [
                'conteo' => (int) ($pagosPendientesValidar->total_count ?? 0),
                'monto'  => (float) ($pagosPendientesValidar->total_monto ?? 0)
            ],
        ];
    }

    /**
     * 2. Flujo Mensual (Cobranza Esperada vs Cobranza Real) -> ApexCharts Doble Área/Línea
     */
    private function getFlujoMensual(array $solicitudesIds, int $year)
    {
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $esperado = array_fill(1, 12, 0);
        $recaudado = array_fill(1, 12, 0);

        if (empty($solicitudesIds)) {
            return [
                'categories' => $meses,
                'series'     => [
                    ['name' => 'Recaudado Real', 'data' => array_values($recaudado)],
                    ['name' => 'Cobranza Esperada / Programada', 'data' => array_values($esperado)]
                ]
            ];
        }

        // Recaudado real agrupado por mes de liquidación
        $pagosReales = CreditoHistorialPagos::whereIn('solicitud_id', $solicitudesIds)
            ->whereYear('fecha_liquidado', $year)
            ->whereNotNull('fecha_liquidado')
            ->selectRaw('MONTH(fecha_liquidado) as mes, SUM(monto_pagado) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        foreach ($pagosReales as $m => $total) {
            $recaudado[(int)$m] = (float) $total;
        }

        // Programado / Esperado agrupado por mes de vencimiento
        $pagosProgramados = CreditoHistorialPagos::whereIn('solicitud_id', $solicitudesIds)
            ->whereYear('fecha_a_pagar', $year)
            ->selectRaw('MONTH(fecha_a_pagar) as mes, SUM(COALESCE(monto_pagado, saldo_pendiente, 0)) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        foreach ($pagosProgramados as $m => $total) {
            $esperado[(int)$m] = (float) $total;
        }

        return [
            'categories' => $meses,
            'series'     => [
                [
                    'name' => 'Recaudado Real',
                    'data' => array_values($recaudado),
                ],
                [
                    'name' => 'Cobranza Esperada / Programada',
                    'data' => array_values($esperado),
                ]
            ]
        ];
    }

    /**
     * 3. Colocación y Cartera por Línea de Crédito -> ApexCharts Barras Horizontales
     */
    private function getCarteraPorLinea(array $solicitudesIds)
    {
        $lineas = CreditoLineas::select('id', 'name')->get();

        $nombres = [];
        $colocados = [];
        $cobrados = [];

        if (!empty($solicitudesIds)) {
            foreach ($lineas as $linea) {
                $solicitudesLinea = CreditoSolicitud::whereIn('id', $solicitudesIds)
                    ->where('linea_id', $linea->id);

                $montoColocado = (float) (clone $solicitudesLinea)->sum('monto_solicitado');
                $idsLinea = (clone $solicitudesLinea)->pluck('id');

                $montoCobrado = (float) CreditoHistorialPagos::whereIn('solicitud_id', $idsLinea)->sum('monto_pagado');

                if ($montoColocado > 0 || $montoCobrado > 0) {
                    $nombres[] = $linea->name;
                    $colocados[] = $montoColocado;
                    $cobrados[] = $montoCobrado;
                }
            }
        }

        return [
            'categories' => $nombres,
            'series'     => [
                [
                    'name' => 'Monto Colocado',
                    'data' => $colocados
                ],
                [
                    'name' => 'Monto Recuperado',
                    'data' => $cobrados
                ]
            ]
        ];
    }

    /**
     * 4. Desempeño por Sucursal (Colocado vs Cobrado vs Saldo Vencido) -> ApexCharts Barras Apiladas / Columnas
     */
    private function getDesempenoPorSucursal(array $solicitudesIds)
    {
        $hoy = Carbon::now()->toDateString();
        $sucursales = Sucursal::select('id', 'nombre')->get();

        $nombres = [];
        $colocado = [];
        $cobrado = [];
        $vencido = [];

        if (!empty($solicitudesIds)) {
            foreach ($sucursales as $suc) {
                $solicitudesSuc = CreditoSolicitud::whereIn('id', $solicitudesIds)
                    ->where('sucursal_id', $suc->id);

                $montoColocado = (float) (clone $solicitudesSuc)->sum('monto_solicitado');
                $idsSuc = (clone $solicitudesSuc)->pluck('id');

                if (count($idsSuc) === 0) continue;

                $montoCobrado = (float) CreditoHistorialPagos::whereIn('solicitud_id', $idsSuc)->sum('monto_pagado');

                $montoVencido = (float) CreditoHistorialPagos::whereIn('solicitud_id', $idsSuc)
                    ->whereNull('fecha_liquidado')
                    ->where('fecha_a_pagar', '<', $hoy)
                    ->sum('saldo_pendiente');

                $nombres[] = $suc->nombre;
                $colocado[] = $montoColocado;
                $cobrado[] = $montoCobrado;
                $vencido[] = $montoVencido;
            }
        }

        return [
            'categories' => $nombres,
            'series'     => [
                ['name' => 'Colocado', 'data' => $colocado],
                ['name' => 'Cobrado', 'data' => $cobrado],
                ['name' => 'En Mora (Vencido)', 'data' => $vencido],
            ]
        ];
    }

    /**
     * 5. Antigüedad de Saldos / Semáforo de Cartera Vencida (Aging) -> ApexCharts Dona / Barra
     */
    private function getAntiguedadSaldos(array $solicitudesIds)
    {
        $hoy = Carbon::now();

        $aging = [
            'Al Corriente'       => 0,
            'Vencido 1-30 días'  => 0,
            'Vencido 31-60 días' => 0,
            'Vencido 61-90 días' => 0,
            'Vencido +90 días'   => 0,
        ];

        if (!empty($solicitudesIds)) {
            $pagosPendientes = CreditoHistorialPagos::whereIn('solicitud_id', $solicitudesIds)
                ->whereNull('fecha_liquidado')
                ->whereNotNull('fecha_a_pagar')
                ->get();

            foreach ($pagosPendientes as $pago) {
                $monto = (float) ($pago->saldo_pendiente ?? 0);
                if ($monto <= 0) continue;

                $fechaPago = Carbon::parse($pago->fecha_a_pagar);
                if ($fechaPago->gte($hoy)) {
                    $aging['Al Corriente'] += $monto;
                } else {
                    $diasVencido = $fechaPago->diffInDays($hoy);
                    if ($diasVencido <= 30) {
                        $aging['Vencido 1-30 días'] += $monto;
                    } elseif ($diasVencido <= 60) {
                        $aging['Vencido 31-60 días'] += $monto;
                    } elseif ($diasVencido <= 90) {
                        $aging['Vencido 61-90 días'] += $monto;
                    } else {
                        $aging['Vencido +90 días'] += $monto;
                    }
                }
            }
        }

        return [
            'labels' => array_keys($aging),
            'series' => array_values($aging),
        ];
    }

    /**
     * 6. Distribución de Solicitudes por Estatus -> ApexCharts Pie / Donut
     */
    private function getDistribucionEstatus($solicitudesQuery)
    {
        $distribucion = (clone $solicitudesQuery)
            ->join('estatus', 'credito_solicitud.estatus_id', '=', 'estatus.id')
            ->selectRaw('estatus.nombre, estatus.color, COUNT(credito_solicitud.id) as total')
            ->groupBy('estatus.nombre', 'estatus.color')
            ->get();

        return [
            'labels' => $distribucion->pluck('nombre'),
            'colors' => $distribucion->pluck('color'),
            'series' => $distribucion->pluck('total'),
        ];
    }

    /**
     * 7. Top 5 Clientes con Mayor Saldo en Mora (Para Acción Ejecutiva Inmediata)
     */
    private function getTopClientesMora(array $solicitudesIds)
    {
        if (empty($solicitudesIds)) {
            return [];
        }

        $hoy = Carbon::now()->toDateString();

        return CreditoHistorialPagos::whereIn('credito_historial_pagos.solicitud_id', $solicitudesIds)
            ->whereNull('credito_historial_pagos.fecha_liquidado')
            ->where('credito_historial_pagos.fecha_a_pagar', '<', $hoy)
            ->join('credito_solicitud', 'credito_historial_pagos.solicitud_id', '=', 'credito_solicitud.id')
            ->join('clientes', 'credito_solicitud.cliente_id', '=', 'clientes.id')
            ->leftJoin('sucursales', 'credito_solicitud.sucursal_id', '=', 'sucursales.id')
            ->selectRaw('
                clientes.nombre as cliente,
                clientes.telefono,
                credito_solicitud.folio,
                sucursales.nombre as sucursal,
                SUM(COALESCE(credito_historial_pagos.saldo_pendiente, 0)) as total_mora,
                MIN(credito_historial_pagos.fecha_a_pagar) as fecha_mas_antigua
            ')
            ->groupBy('clientes.nombre', 'clientes.telefono', 'credito_solicitud.folio', 'sucursales.nombre')
            ->orderByDesc('total_mora')
            ->limit(5)
            ->get();
    }

    /**
     * 7b. Detalle de Registros para Despliegue de KPIs Ejecutivos
     */
    private function getDetallesKpis(array $solicitudesIds, $solicitudesQuery)
    {
        $hoy = Carbon::now();
        $hoyStr = $hoy->toDateString();

        if (empty($solicitudesIds)) {
            return [
                'colocado'        => [],
                'cobrado'         => [],
                'vencido'         => [],
                'pendiente'       => [],
                'por_validar'     => [],
                'ticket_promedio' => [],
            ];
        }

        // 1. Solicitudes con sus relaciones
        $solicitudes = (clone $solicitudesQuery)
            ->with([
                'cliente',
                'sucursal',
                'linea',
                'asesor',
                'estatus'
            ])
            ->orderBy('credito_solicitud.created_at', 'desc')
            ->get()
            ->map(function ($s) {
                return [
                    'id'               => $s->id,
                    'folio'            => $s->folio ?? (string)$s->id,
                    'cliente'          => $s->cliente?->nombre ?? 'Sin cliente',
                    'telefono'         => $s->cliente?->telefono ?? '',
                    'rfc'              => $s->cliente?->rfc ?? '',
                    'sucursal'         => $s->sucursal?->nombre ?? 'General',
                    'linea'            => $s->linea?->name ?? 'General',
                    'asesor'           => $s->asesor?->nombreCompleto ?? 'Sin asesor',
                    'monto_solicitado' => (float) ($s->monto_solicitado ?? 0),
                    'numero_pagos'     => $s->numero_pagos,
                    'estatus'          => $s->estatus?->nombre ?? 'Sin estatus',
                    'estatus_color'    => $s->estatus?->color ?? '#1976d2',
                    'created_at'       => $s->created_at?->toIso8601String() ?? $s->created_at,
                    'resumen_pagos'    => $s->resumen_pagos,
                ];
            });

        // 2. Todos los pagos asociados a las solicitudes
        $pagos = CreditoHistorialPagos::whereIn('credito_historial_pagos.solicitud_id', $solicitudesIds)
            ->with([
                'solicitud.cliente',
                'solicitud.sucursal',
                'solicitud.linea',
                'solicitud.asesor',
                'estatus',
                'documento',
                'validadoPor',
            ])
            ->orderBy('credito_historial_pagos.fecha_a_pagar', 'asc')
            ->get();

        $cobrado = [];
        $validado = [];
        $porValidar = [];
        $vencido = [];
        $pendiente = [];

        foreach ($pagos as $pago) {
            $montoPagado = (float) ($pago->monto_pagado ?? 0);
            $saldoPendiente = (float) ($pago->saldo_pendiente ?? 0);
            $liquidado = (bool) ($pago->fecha_liquidado || ($pago->estatus && $pago->estatus->nombre === 'Pago Realizado'));
            $fechaPagoStr = substr($pago->fecha_a_pagar ?? '', 0, 10);
            $montoEsperado = (float) ($saldoPendiente > 0 ? $saldoPendiente : ($montoPagado > 0 ? $montoPagado : 0));
            $estaValidado = ($pago->validated_by !== null);
            $tienePagoRegistrado = ($liquidado || ($montoPagado > 0) || ($pago->document_id !== null));
            $estadoValidacion = $estaValidado ? 'validado' : ($tienePagoRegistrado ? 'por_validar' : 'sin_pago');

            $diasVencido = 0;
            if (!$liquidado && $fechaPagoStr && $fechaPagoStr < $hoyStr) {
                $fechaLim = Carbon::parse($pago->fecha_a_pagar);
                $diasVencido = (int) $fechaLim->diffInDays($hoy);
            }

            $item = [
                'id'                => $pago->id,
                'solicitud_id'      => $pago->solicitud_id,
                'folio'             => $pago->solicitud?->folio ?? (string)$pago->solicitud_id,
                'cliente'           => $pago->solicitud?->cliente?->nombre ?? 'Sin cliente',
                'telefono'          => $pago->solicitud?->cliente?->telefono ?? '',
                'rfc'               => $pago->solicitud?->cliente?->rfc ?? '',
                'sucursal'          => $pago->solicitud?->sucursal?->nombre ?? 'General',
                'linea'             => $pago->solicitud?->linea?->name ?? 'General',
                'asesor'            => $pago->solicitud?->asesor?->nombreCompleto ?? 'Sin asesor',
                'n_pago'            => $pago->n_pago,
                'etiqueta'          => $pago->etiqueta ?? "Pago #{$pago->n_pago}",
                'monto_esperado'    => $montoEsperado,
                'monto_pagado'      => $montoPagado,
                'saldo_pendiente'   => $saldoPendiente,
                'fecha_a_pagar'     => $pago->fecha_a_pagar,
                'fecha_liquidado'   => $pago->fecha_liquidado,
                'dias_vencido'      => $diasVencido,
                'esta_validado'     => $estaValidado,
                'estado_validacion' => $estadoValidacion,
                'validado_por'      => $pago->validadoPor?->nombreCompleto,
                'estatus'           => $pago->estatus?->nombre ?? ($liquidado ? 'Pago Realizado' : 'Pendiente'),
                'estatus_color'     => $pago->estatus?->color ?? ($liquidado ? '#2e7d32' : ($diasVencido > 0 ? '#d32f2f' : '#ff9800')),
                'comprobante_url'   => $pago->documento?->realpath ?? $pago->documento?->path ?? null,
            ];

            // Dinero Recaudado / Cobranza Total
            if ($liquidado || $montoPagado > 0) {
                $cobrado[] = $item;
            }

            // Pagos Validados por Crédito
            if ($estaValidado && ($liquidado || $montoPagado > 0)) {
                $validado[] = $item;
            }

            // Pagos por Validar
            if (!$estaValidado && $tienePagoRegistrado) {
                $porValidar[] = $item;
            }

            // Cartera Vencida (en mora)
            if (!$liquidado && $fechaPagoStr && $fechaPagoStr < $hoyStr && ($saldoPendiente > 0 || $montoPagado == 0)) {
                $vencido[] = $item;
            }

            // Saldo Pendiente Activo
            if (!$liquidado && $saldoPendiente > 0) {
                $pendiente[] = $item;
            }
        }

        return [
            'colocado'        => $solicitudes,
            'cobrado'         => $cobrado,
            'validado'        => $validado,
            'por_validar'     => $porValidar,
            'vencido'         => $vencido,
            'pendiente'       => $pendiente,
            'ticket_promedio' => $solicitudes,
        ];
    }

    /**
     * 8. Calendario de Pagos y Cobranza Mensual
     * Devuelve el resumen financiero del mes (Pagado, Prospectado, Vencido, Por Vencer)
     * y el desglose de pagos por día del mes.
     */
    public function calendario(Request $request)
    {
        try {
            $year = (int) $request->input('year', Carbon::now()->year);
            $month = (int) $request->input('month', Carbon::now()->month);
            $sucursalId = $request->input('sucursal_id');
            $lineaId = $request->input('linea_id');
            $asesorId = $request->input('asesor_id');

            $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $hoy = Carbon::now()->toDateString();
            $diasEnMes = $endOfMonth->day;

            $nombresMeses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];

            $user = Auth::user();
            $isAdminOrCredito = $user && ($user->hasRole('Admin') || $user->hasRole('Credito'));

            // 1. Filtrar solicitudes: Si no es Admin/Crédito, solo ve sus propios registros
            $solicitudesQuery = CreditoSolicitud::query()
                ->when(!$isAdminOrCredito, function ($q) use ($user) {
                    $empleadoId = $user?->empleado?->id;
                    if ($empleadoId) {
                        $q->where(function ($sub) use ($empleadoId) {
                            $sub->where('credito_solicitud.asesor_id', $empleadoId)
                                ->orWhere('credito_solicitud.notificado_id', $empleadoId);
                        });
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when($isAdminOrCredito && $sucursalId, function ($q) use ($sucursalId) {
                    $q->where('credito_solicitud.sucursal_id', $sucursalId);
                })
                ->when($lineaId, function ($q) use ($lineaId) {
                    $q->where('credito_solicitud.linea_id', $lineaId);
                })
                ->when($isAdminOrCredito && $asesorId, function ($q) use ($asesorId) {
                    $q->where('credito_solicitud.asesor_id', $asesorId);
                });

            $solicitudesIds = $solicitudesQuery->pluck('credito_solicitud.id')->toArray();

            // 2. Consultar pagos programados en el mes o liquidados en el mes
            $pagos = CreditoHistorialPagos::whereIn('credito_historial_pagos.solicitud_id', $solicitudesIds)
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('credito_historial_pagos.fecha_a_pagar', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                      ->orWhereBetween('credito_historial_pagos.fecha_liquidado', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
                })
                ->with([
                    'solicitud.cliente',
                    'solicitud.sucursal',
                    'solicitud.linea',
                    'solicitud.asesor',
                    'estatus',
                    'documento',
                    'validadoPor',
                ])
                ->orderBy('credito_historial_pagos.fecha_a_pagar', 'asc')
                ->get();

            // 3. Estructurar días del mes
            $dias = [];
            for ($d = 1; $d <= $diasEnMes; $d++) {
                $fechaStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $carbonFecha = Carbon::createFromDate($year, $month, $d);

                $dias[$fechaStr] = [
                    'fecha'             => $fechaStr,
                    'dia'               => $d,
                    'dia_semana'        => $carbonFecha->dayOfWeekIso, // 1 (Lun) a 7 (Dom)
                    'es_hoy'            => ($fechaStr === $hoy),
                    'es_pasado'         => ($fechaStr < $hoy),
                    'es_futuro'         => ($fechaStr > $hoy),
                    'total_prospectado' => 0.0,
                    'total_pagado'      => 0.0,
                    'total_validado'    => 0.0,
                    'total_por_validar' => 0.0,
                    'total_vencido'     => 0.0,
                    'conteo_pagos'      => 0,
                    'conteo_pendientes' => 0,
                    'conteo_vencidos'   => 0,
                    'conteo_liquidados' => 0,
                    'conteo_validados'  => 0,
                    'conteo_por_validar'=> 0,
                    'pagos'             => [],
                ];
            }

            // 4. Variables de Resumen Mensual
            $montoPagadoMes = 0.0;
            $montoValidadoMes = 0.0;
            $montoPorValidarMes = 0.0;
            $montoVencidoMes = 0.0;
            $conteoLiquidados = 0;
            $conteoValidados = 0;
            $conteoPorValidar = 0;
            $conteoVencidos = 0;
            $conteoPendientes = 0;

            foreach ($pagos as $pago) {
                $fechaPago = $pago->fecha_a_pagar ?? $pago->fecha_liquidado;
                if (!$fechaPago) continue;

                $fechaPagoStr = substr($fechaPago, 0, 10);
                $montoEsperado = (float) ($pago->saldo_pendiente !== null && (float)$pago->saldo_pendiente > 0 ? $pago->saldo_pendiente : ($pago->monto_pagado ?? 0));
                if ($pago->monto_pagado && (float)$pago->monto_pagado > 0) {
                    $montoEsperado = (float) $pago->monto_pagado;
                }

                $montoPagado = (float) ($pago->monto_pagado ?? 0);
                $saldoPendiente = (float) ($pago->saldo_pendiente ?? 0);
                $liquidado = (bool) ($pago->fecha_liquidado || ($pago->estatus && $pago->estatus->nombre === 'Pago Realizado'));
                $estaValidado = ($pago->validated_by !== null);
                $tienePagoRegistrado = ($liquidado || ($montoPagado > 0) || ($pago->document_id !== null));

                // Determinar estado de cobro
                $estadoCobro = 'pendiente';
                if ($liquidado || ($saldoPendiente === 0.0 && $montoPagado > 0)) {
                    $estadoCobro = 'liquidado';
                    $conteoLiquidados++;
                    $montoPagadoMes += $montoPagado;

                    if ($estaValidado) {
                        $conteoValidados++;
                        $montoValidadoMes += $montoPagado;
                    } else {
                        $conteoPorValidar++;
                        $montoPorValidarMes += $montoPagado;
                    }
                } elseif ($tienePagoRegistrado && !$estaValidado) {
                    $conteoPorValidar++;
                    $montoPorValidarMes += $montoPagado;
                } elseif ($fechaPagoStr < $hoy) {
                    $estadoCobro = 'vencido';
                    $conteoVencidos++;
                    $montoVencidoMes += ($saldoPendiente > 0 ? $saldoPendiente : $montoEsperado);
                } else {
                    $estadoCobro = 'pendiente';
                    $conteoPendientes++;
                }

                $estadoValidacion = $estaValidado ? 'validado' : ($tienePagoRegistrado ? 'por_validar' : 'sin_pago');

                $itemPago = [
                    'id'                => $pago->id,
                    'solicitud_id'      => $pago->solicitud_id,
                    'folio'             => $pago->solicitud?->folio ?? (string)$pago->solicitud_id,
                    'cliente'           => $pago->solicitud?->cliente?->nombre ?? 'Sin cliente',
                    'telefono'          => $pago->solicitud?->cliente?->telefono ?? '',
                    'rfc'               => $pago->solicitud?->cliente?->rfc ?? '',
                    'sucursal'          => $pago->solicitud?->sucursal?->nombre ?? 'General',
                    'linea'             => $pago->solicitud?->linea?->name ?? 'General',
                    'asesor'            => $pago->solicitud?->asesor?->nombreCompleto ?? 'Sin asesor',
                    'n_pago'            => $pago->n_pago,
                    'etiqueta'          => $pago->etiqueta ?? "Pago #{$pago->n_pago}",
                    'monto_esperado'    => $montoEsperado,
                    'monto_pagado'      => $montoPagado,
                    'saldo_pendiente'   => $saldoPendiente,
                    'fecha_a_pagar'     => $pago->fecha_a_pagar,
                    'fecha_liquidado'   => $pago->fecha_liquidado,
                    'estatus'           => $pago->estatus?->nombre ?? ($liquidado ? 'Pago Realizado' : 'Pendiente'),
                    'estatus_color'     => $pago->estatus?->color ?? ($liquidado ? '#2e7d32' : ($estadoCobro === 'vencido' ? '#d32f2f' : '#ff9800')),
                    'estado_cobro'      => $estadoCobro,
                    'esta_validado'     => $estaValidado,
                    'estado_validacion' => $estadoValidacion,
                    'validado_por'      => $pago->validadoPor?->nombreCompleto,
                    'comprobante_url'   => $pago->documento?->realpath ?? $pago->documento?->path ?? null,
                ];

                // Si la fecha cae en el grid del mes actual
                if (isset($dias[$fechaPagoStr])) {
                    $dias[$fechaPagoStr]['pagos'][] = $itemPago;
                    $dias[$fechaPagoStr]['conteo_pagos']++;

                    if ($estadoCobro === 'liquidado') {
                        $dias[$fechaPagoStr]['conteo_liquidados']++;
                        $dias[$fechaPagoStr]['total_pagado'] += $montoPagado;
                        if ($estaValidado) {
                            $dias[$fechaPagoStr]['conteo_validados']++;
                            $dias[$fechaPagoStr]['total_validado'] += $montoPagado;
                        } else {
                            $dias[$fechaPagoStr]['conteo_por_validar']++;
                            $dias[$fechaPagoStr]['total_por_validar'] += $montoPagado;
                        }
                    } elseif ($estadoCobro === 'vencido') {
                        $dias[$fechaPagoStr]['conteo_vencidos']++;
                        $dias[$fechaPagoStr]['total_vencido'] += ($saldoPendiente > 0 ? $saldoPendiente : $montoEsperado);
                    } else {
                        $dias[$fechaPagoStr]['conteo_pendientes']++;
                    }
                }
            }

            $totalPagosMes = count($pagos);
            $tasaCumplimiento = ($totalPagosMes > 0)
                ? round(($conteoLiquidados / $totalPagosMes) * 100, 2)
                : 0;

            $resumenMes = [
                'year'                  => $year,
                'month'                 => $month,
                'nombre_mes'            => ($nombresMeses[$month] ?? "Mes {$month}") . " {$year}",
                'monto_pagado'          => round($montoPagadoMes, 2),
                'monto_validado'        => round($montoValidadoMes, 2),
                'pagos_validados'       => $conteoValidados,
                'monto_por_validar'     => round($montoPorValidarMes, 2),
                'pagos_por_validar'     => $conteoPorValidar,
                'monto_vencido'         => round($montoVencidoMes, 2),
                'pagos_vencidos'        => $conteoVencidos,
                'pagos_pendientes'      => $conteoPendientes,
                'total_pagos_mes'       => $totalPagosMes,
                'pagos_liquidados'      => $conteoLiquidados,
                'tasa_cumplimiento_pct' => $tasaCumplimiento,
            ];

            return $this->respond([
                'resumen_mes' => $resumenMes,
                'dias'        => array_values($dias),
                'todos_pagos' => $pagos->map(function ($p) use ($hoy) {
                    $liquidado = (bool) ($p->fecha_liquidado || ($p->estatus && $p->estatus->nombre === 'Pago Realizado'));
                    $fecha = substr($p->fecha_a_pagar ?? '', 0, 10);
                    $estadoCobro = $liquidado ? 'liquidado' : ($fecha && $fecha < $hoy ? 'vencido' : 'pendiente');
                    $montoEsperado = (float) ($p->saldo_pendiente !== null && (float)$p->saldo_pendiente > 0 ? $p->saldo_pendiente : ($p->monto_pagado ?? 0));
                    if ($p->monto_pagado && (float)$p->monto_pagado > 0) {
                        $montoEsperado = (float) $p->monto_pagado;
                    }
                    $montoPagado = (float)($p->monto_pagado ?? 0);
                    $estaValidado = ($p->validated_by !== null);
                    $tienePagoRegistrado = ($liquidado || ($montoPagado > 0) || ($p->document_id !== null));
                    $estadoValidacion = $estaValidado ? 'validado' : ($tienePagoRegistrado ? 'por_validar' : 'sin_pago');

                    return [
                        'id'                => $p->id,
                        'solicitud_id'      => $p->solicitud_id,
                        'folio'             => $p->solicitud?->folio ?? (string)$pago->solicitud_id,
                        'cliente'           => $p->solicitud?->cliente?->nombre ?? 'Sin cliente',
                        'telefono'          => $p->solicitud?->cliente?->telefono ?? '',
                        'sucursal'          => $p->solicitud?->sucursal?->nombre ?? 'General',
                        'linea'             => $p->solicitud?->linea?->name ?? 'General',
                        'asesor'            => $p->solicitud?->asesor?->nombreCompleto ?? 'Sin asesor',
                        'n_pago'            => $p->n_pago,
                        'etiqueta'          => $p->etiqueta ?? "Pago #{$p->n_pago}",
                        'monto_esperado'    => $montoEsperado,
                        'monto_pagado'      => $montoPagado,
                        'saldo_pendiente'   => (float)($p->saldo_pendiente ?? 0),
                        'fecha_a_pagar'     => $p->fecha_a_pagar,
                        'fecha_liquidado'   => $p->fecha_liquidado,
                        'estatus'           => $p->estatus?->nombre ?? ($liquidado ? 'Pago Realizado' : 'Pendiente'),
                        'estatus_color'     => $p->estatus?->color ?? ($liquidado ? '#2e7d32' : ($estadoCobro === 'vencido' ? '#d32f2f' : '#ff9800')),
                        'estado_cobro'      => $estadoCobro,
                        'esta_validado'     => $estaValidado,
                        'estado_validacion' => $estadoValidacion,
                        'validado_por'      => $p->validadoPor?->nombreCompleto,
                        'comprobante_url'   => $p->documento?->realpath ?? $p->documento?->path ?? null,
                    ];
                })
            ], 'Datos del calendario de pagos obtenidos correctamente');
        } catch (\Throwable $e) {
            return $this->respondError('Error al obtener datos del calendario de pagos: ' . $e->getMessage(), 500);
        }
    }
}
