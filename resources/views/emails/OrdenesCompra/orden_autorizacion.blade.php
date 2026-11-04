<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resolución de Orden de Compra #{{ $orden->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
            padding: 20px 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .header {
            color: #ffffff;
            padding: 24px 28px;
            text-align: left;
        }

        .header h1 {
            margin: 0 0 6px 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.95;
        }

        .badge-folio {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            color: #ffffff;
            margin-top: 8px;
        }

        .badge-oc-tag {
            display: inline-block;
            background-color: #ffffff;
            color: #065f46;
            padding: 1px 7px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            margin: 0 2px;
        }

        .content {
            padding: 24px 28px;
        }

        /* Callout de Estatus */
        .status-card {
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 22px;
            border-left: 5px solid;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .comments-box {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed rgba(0, 0, 0, 0.15);
            font-size: 13px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a8a;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-top: 24px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 0 -8px 10px -8px;
        }

        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 9px 12px;
            vertical-align: top;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }

        .badge-placa {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* Bloque por Orden de Compra */
        .oc-block {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .oc-header {
            background-color: #f0fdf4;
            padding: 10px 14px;
            border-bottom: 1px solid #dcfce7;
        }

        .oc-title {
            font-size: 14px;
            font-weight: 700;
            color: #166534;
        }

        .badge-aut {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 6px;
        }

        /* Tabla de Conceptos */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .items-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-align: left;
            padding: 9px 10px;
            border-bottom: 1px solid #cbd5e1;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge-type {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-tire {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }

        .fiscal-badge {
            display: inline-block;
            background-color: #f8fafc;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            margin-right: 4px;
            margin-top: 3px;
        }

        /* Totales */
        .totals-container {
            width: 100%;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .totals-table {
            width: 280px;
            margin-left: auto;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .totals-table td {
            padding: 6px 12px;
            font-size: 12px;
        }

        .totals-table tr.grand-total td {
            border-top: 2px solid #10b981;
            font-size: 14px;
            font-weight: bold;
            color: #065f46;
            background-color: #ecfdf5;
        }

        .totals-table tr.rejected-total td {
            border-top: 2px solid #ef4444;
            font-size: 14px;
            font-weight: bold;
            color: #991b1b;
            background-color: #fef2f2;
        }

        /* Resumen Consolidado */
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 8px;
        }

        .summary-table th {
            background-color: #e2e8f0;
            color: #334155;
            padding: 6px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        .summary-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .attachment-banner {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 20px 0 10px 0;
            font-size: 13px;
            color: #1e40af;
        }

        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 18px 28px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    @php
        $ordenesList = isset($ordenes) && count($ordenes) > 0 ? $ordenes : collect([$orden]);
        $totalOrdenes = count($ordenesList);
        $estatusNombre = $orden->estatus?->nombre ?? 'Sin Estatus';
        $isAutorizado = stripos($estatusNombre, 'Autorizado') !== false && stripos($estatusNombre, 'No Autorizado') === false;
        $isNoAutorizado = stripos($estatusNombre, 'No Autorizado') !== false || stripos($estatusNombre, 'Rechazado') !== false;

        $granTotalSubtotal = $ordenesList->sum(fn($o) => $o->detalles ? $o->detalles->sum('subtotal') : 0);
        $granTotalIva = $ordenesList->sum(fn($o) => $o->detalles ? $o->detalles->sum('iva') : 0);
        $granTotalTotal = $ordenesList->sum(fn($o) => $o->detalles ? $o->detalles->sum('total') : 0);
        $totalConceptosAprobados = $ordenesList->sum(fn($o) => $o->detalles ? count($o->detalles) : 0);

        if ($isAutorizado) {
            $headerBg = 'linear-gradient(135deg, #065f46 0%, #10b981 100%)';
            $cardBg = '#ecfdf5';
            $cardBorder = '#10b981';
            $badgeBg = '#059669';
            $badgeText = '#ffffff';
            $cardTextColor = '#065f46';
            $headerTitle = $totalOrdenes > 1 ? 'Órdenes de Compra Autorizadas' : 'Orden de Compra Autorizada';
            $statusDesc = $totalOrdenes > 1 
                ? "Se han AUTORIZADO los conceptos y se generaron {$totalOrdenes} órdenes de compra bajo la autorización {$orden->autorizacion_compras}."
                : 'La orden de compra ha sido AUTORIZADA correctamente.';
        } elseif ($isNoAutorizado) {
            $headerBg = 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)';
            $cardBg = '#fef2f2';
            $cardBorder = '#ef4444';
            $badgeBg = '#dc2626';
            $badgeText = '#ffffff';
            $cardTextColor = '#991b1b';
            $headerTitle = 'Orden de Compra No Autorizada';
            $statusDesc = 'La solicitud de orden de servicio ha sido marcada como NO AUTORIZADA.';
        } else {
            $headerBg = 'linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%)';
            $cardBg = '#eff6ff';
            $cardBorder = '#3b82f6';
            $badgeBg = '#2563eb';
            $badgeText = '#ffffff';
            $cardTextColor = '#1e40af';
            $headerTitle = 'Actualización de Orden de Compra';
            $statusDesc = 'Se ha actualizado el estatus de la orden de servicio.';
        }

        // Obtener la última bitácora con comentarios
        $ultimaBitacora = $orden->bitacora?->sortByDesc('id')->first(function ($b) {
            return !empty($b->comentarios) && $b->comentarios !== 'Orden de Servicio creada';
        });

        // KM del evento
        $kmEvento = $orden->detalles?->firstWhere('km_evento', '!=', null)?->km_evento
            ?? $orden->detalles?->max('km_evento');
    @endphp

    <div class="container">
        <!-- Encabezado con color dependiente del estatus -->
        <div class="header" style="background: {{ $headerBg }};">
            <h1>{{ $headerTitle }}</h1>
            <p>{{ $statusDesc }}</p>
            <div class="badge-folio">
                @if($orden->autorizacion_compras)
                    <strong>Autorización:</strong> {{ $orden->autorizacion_compras }} &bull;
                @endif
                @if($totalOrdenes > 1)
                    {{ $totalOrdenes }} Órdenes generadas:
                    @foreach($ordenesList as $o)
                        <span class="badge-oc-tag">OC #{{ $o->id }}</span>
                    @endforeach
                @else
                    Orden #{{ $orden->id }} &bull; OC: {{ $orden->orden_compra ?? $orden->id }}
                @endif
            </div>
        </div>

        <div class="content">
            <!-- Tarjeta de Estatus de Autorización -->
            <div class="status-card" style="background-color: {{ $cardBg }}; border-color: {{ $cardBorder }}; color: {{ $cardTextColor }};">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; opacity: 0.85;">
                                Estatus de la Resolución
                            </div>
                            <div style="margin-top: 4px;">
                                <span class="status-badge" style="background-color: {{ $badgeBg }}; color: {{ $badgeText }};">
                                    {{ $estatusNombre }}
                                </span>
                                @if($isAutorizado && $orden->autorizacion_compras)
                                    <span style="margin-left: 8px; font-weight: 700; font-size: 13px;">
                                        Folio Autorización: {{ $orden->autorizacion_compras }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        @if($isAutorizado)
                            <td align="right" valign="top">
                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 600; opacity: 0.85;">Total Autorizado</div>
                                <div style="font-size: 16px; font-weight: 800; color: #065f46;">
                                    ${{ number_format($granTotalTotal, 2) }} MXN
                                </div>
                            </td>
                        @endif
                    </tr>
                </table>

                @if($isAutorizado && $totalOrdenes > 1)
                    <div style="margin-top: 12px; padding: 8px 12px; background-color: #ffffff; border-radius: 6px; border: 1px solid #a7f3d0; font-size: 12px; color: #065f46;">
                        <strong>Aviso de separación por proveedor:</strong> Al seleccionarse cotizaciones con diferentes proveedores, el sistema generó automáticamente <strong>{{ $totalOrdenes }} órdenes de compra independientes</strong> bajo el mismo folio de autorización <strong>{{ $orden->autorizacion_compras }}</strong>. A continuación se detalla cada una.
                    </div>
                @endif

                @if($ultimaBitacora && !empty($ultimaBitacora->comentarios))
                    <div class="comments-box">
                        <strong>Comentarios / Observaciones:</strong>
                        <div style="margin-top: 3px; font-style: italic;">
                            "{{ $ultimaBitacora->comentarios }}"
                        </div>
                        @if($ultimaBitacora->user?->empleado)
                            <div style="font-size: 11px; margin-top: 4px; opacity: 0.8;">
                                Registrado por: {{ $ultimaBitacora->user->empleado->nombreCompleto }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Datos de la Unidad / Vehículo -->
            <div class="section-title">Datos de la Unidad</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="25%">
                        <div class="info-label">Placas</div>
                        <div class="info-value">
                            <span class="badge-placa">{{ $orden->vehicle?->placas ?? 'S/P' }}</span>
                        </div>
                    </td>
                    <td class="info-card" width="25%">
                        <div class="info-label">Kilometraje</div>
                        <div class="info-value" style="color: #ea580c; font-weight: 700;">
                            {{ $kmEvento ? number_format($kmEvento) . ' km' : 'N/A' }}
                        </div>
                    </td>
                    <td class="info-card" width="25%">
                        <div class="info-label">Sucursal Unidad</div>
                        <div class="info-value">{{ $orden->vehicle?->sucursal?->nombre ?? 'N/A' }}</div>
                    </td>
                    <td class="info-card" width="25%">
                        <div class="info-label">Línea</div>
                        <div class="info-value">{{ $orden->vehicle?->linea?->nombre ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="info-card">
                        <div class="info-label">Serie (VIN)</div>
                        <div class="info-value" style="font-family: monospace;">{{ $orden->vehicle?->serie ?? 'N/A' }}</div>
                    </td>
                    <td class="info-card">
                        <div class="info-label">Departamento Unidad</div>
                        <div class="info-value">{{ $orden->vehicle?->departamento?->nombre ?? 'N/A' }}</div>
                    </td>
                    <td class="info-card" colspan="2">
                        <div class="info-label">Tipo de Vehículo</div>
                        <div class="info-value">{{ $orden->vehicle?->estatus?->nombre ?? 'No definido' }}</div>
                    </td>
                </tr>
            </table>

            <!-- Datos del Solicitante y Cargo -->
            <div class="section-title">Información de Solicitud y Cargo</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Empleado Solicitante</div>
                        <div class="info-value">
                            {{ $orden->solicitante?->nombreCompleto ?? ($orden->solicitante ? trim($orden->solicitante->nombre . ' ' . $orden->solicitante->apellido_paterno . ' ' . $orden->solicitante->apellido_materno) : 'No especificado') }}
                        </div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Fecha de Registro</div>
                        <div class="info-value">
                            {{ $orden->fecha_elaboracion ? \Carbon\Carbon::parse($orden->fecha_elaboracion)->format('d/m/Y') : 'N/A' }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Sucursal con Cargo</div>
                        <div class="info-value">{{ $orden->sucursalCargo?->nombre ?? 'No especificada' }}</div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Departamento con Cargo</div>
                        <div class="info-value">{{ $orden->departamentoCargo?->nombre ?? 'No especificado' }}</div>
                    </td>
                </tr>
                @if($totalOrdenes === 1 && $orden->proveedor)
                    <tr>
                        <td class="info-card" colspan="2">
                            <div class="info-label">Proveedor / Taller Autorizado</div>
                            <div class="info-value" style="font-weight: 700; color: #065f46;">
                                {{ $orden->proveedor->nombre }}
                            </div>
                        </td>
                    </tr>
                @endif
            </table>

            @if($isAutorizado)
                <!-- Desglose de Órdenes de Compra Aprobadas -->
                <div class="section-title">
                    Órdenes de Compra Aprobadas ({{ $totalOrdenes }} OC{{ $totalOrdenes > 1 ? 's' : '' }} &bull; {{ $totalConceptosAprobados }} concepto{{ $totalConceptosAprobados > 1 ? 's' : '' }})
                </div>

                @foreach($ordenesList as $ocIdx => $oc)
                    <div class="oc-block">
                        <div class="oc-header">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <span class="oc-title">
                                            Orden de Compra #{{ $oc->id }}
                                        </span>
                                        <span style="font-size: 12px; color: #475569; margin-left: 6px;">
                                            (Folio OC: <strong>{{ $oc->orden_compra ?? $oc->id }}</strong>)
                                        </span>
                                        @if($oc->autorizacion_compras)
                                            <span class="badge-aut">Aut: {{ $oc->autorizacion_compras }}</span>
                                        @endif
                                    </td>
                                    <td align="right">
                                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Proveedor:</span>
                                        <strong style="color: #065f46; font-size: 13px;">{{ $oc->proveedor?->nombre ?? 'Sin proveedor' }}</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Tipo</th>
                                    <th width="22%">Concepto</th>
                                    <th width="28%">Descripción</th>
                                    <th width="6%" class="text-center">Cant.</th>
                                    <th width="11%" class="text-right">P. Unitario</th>
                                    <th width="13%" class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($oc->detalles as $detIdx => $det)
                                    <tr>
                                        <td class="text-center"><strong>{{ $detIdx + 1 }}</strong></td>
                                        <td>
                                            <span class="badge-type">
                                                {{ $det->servicio?->tipoServicio?->nombre ?? ($det->servicio?->tipo_servicio?->nombre ?? 'General') }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $det->servicio?->nombre ?? 'Servicio' }}</strong>
                                            @if($det->llanta_anchura || $det->llanta_altura || $det->llanta_rin)
                                                <br>
                                                <span class="badge-tire">
                                                    Medida: {{ $det->llanta_anchura ?? '-' }}/{{ $det->llanta_altura ?? '-' }} R{{ $det->llanta_rin ?? '-' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $det->descripcion }}</div>
                                            @if($det->formaPago || $det->metodoPago || $det->usoCfdi)
                                                <div style="margin-top: 3px;">
                                                    @if($det->formaPago)
                                                        <span class="fiscal-badge" title="Forma de Pago">FP: {{ $det->formaPago->clave }} - {{ $det->formaPago->nombre }}</span>
                                                    @endif
                                                    @if($det->metodoPago)
                                                        <span class="fiscal-badge" title="Método de Pago">MP: {{ $det->metodoPago->clave }}</span>
                                                    @endif
                                                    @if($det->usoCfdi)
                                                        <span class="fiscal-badge" title="Uso CFDI">Uso: {{ $det->usoCfdi->clave }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center font-mono">{{ $det->cantidad ?? 1 }}</td>
                                        <td class="text-right font-mono">
                                            {{ $det->precio_unitario ? '$' . number_format($det->precio_unitario, 2) : '-' }}
                                        </td>
                                        <td class="text-right font-mono" style="font-weight: 700; color: #065f46;">
                                            ${{ number_format($det->total ?? 0, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center" style="padding: 12px; color: #94a3b8;">
                                            No hay conceptos en esta orden de compra.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Subtotales de esta OC -->
                        <div class="totals-container" style="padding: 0 14px 10px 14px;">
                            @php
                                $subOc = $oc->detalles ? $oc->detalles->sum('subtotal') : 0;
                                $ivaOc = $oc->detalles ? $oc->detalles->sum('iva') : 0;
                                $totOc = $oc->detalles ? $oc->detalles->sum('total') : 0;
                            @endphp
                            <table class="totals-table">
                                <tr>
                                    <td class="text-right">Subtotal OC #{{ $oc->id }}:</td>
                                    <td class="text-right font-mono">${{ number_format($subOc, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-right">IVA (16%):</td>
                                    <td class="text-right font-mono">${{ number_format($ivaOc, 2) }}</td>
                                </tr>
                                <tr class="grand-total">
                                    <td class="text-right">Total OC #{{ $oc->id }}:</td>
                                    <td class="text-right font-mono">${{ number_format($totOc, 2) }} MXN</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endforeach

                @if($totalOrdenes > 1)
                    <!-- Resumen Consolidado de todas las Órdenes de Compra -->
                    <div class="summary-box">
                        <div style="font-size: 13px; font-weight: 700; color: #1e3a8a; text-transform: uppercase;">
                            Resumen Consolidado de la Autorización {{ $orden->autorizacion_compras }}
                        </div>
                        <table class="summary-table">
                            <thead>
                                <tr>
                                    <th>Orden de Compra</th>
                                    <th>Folio OC</th>
                                    <th>Proveedor / Taller</th>
                                    <th class="text-center">Conceptos</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ordenesList as $o)
                                    <tr>
                                        <td><strong>OC #{{ $o->id }}</strong></td>
                                        <td class="font-mono">{{ $o->orden_compra ?? $o->id }}</td>
                                        <td>{{ $o->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                                        <td class="text-center">{{ $o->detalles ? count($o->detalles) : 0 }}</td>
                                        <td class="text-right font-mono" style="font-weight: 600;">
                                            ${{ number_format($o->detalles ? $o->detalles->sum('total') : 0, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #ecfdf5; font-weight: 700; color: #065f46;">
                                    <td colspan="4" class="text-right" style="padding: 10px; font-size: 13px;">
                                        GRAN TOTAL AUTORIZADO ({{ $totalOrdenes }} Órdenes):
                                    </td>
                                    <td class="text-right font-mono" style="padding: 10px; font-size: 15px;">
                                        ${{ number_format($granTotalTotal, 2) }} MXN
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

            @else
                <!-- Caso: No Autorizado / Rechazado -->
                <div class="section-title">Conceptos de la Solicitud</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="18%">Tipo</th>
                            <th width="25%">Concepto</th>
                            <th width="32%">Descripción</th>
                            <th width="8%" class="text-center">Cant.</th>
                            <th width="12%" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orden->detalles as $index => $det)
                            <tr>
                                <td class="text-center"><strong>{{ $index + 1 }}</strong></td>
                                <td>
                                    <span class="badge-type">
                                        {{ $det->servicio?->tipoServicio?->nombre ?? ($det->servicio?->tipo_servicio?->nombre ?? 'General') }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $det->servicio?->nombre ?? 'Servicio' }}</strong>
                                    @if($det->llanta_anchura || $det->llanta_altura || $det->llanta_rin)
                                        <br>
                                        <span class="badge-tire">
                                            Medida: {{ $det->llanta_anchura ?? '-' }}/{{ $det->llanta_altura ?? '-' }} R{{ $det->llanta_rin ?? '-' }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $det->descripcion }}</td>
                                <td class="text-center font-mono">{{ $det->cantidad ?? 1 }}</td>
                                <td class="text-right font-mono" style="font-weight: 600; color: #ef4444;">
                                    ${{ number_format($det->total ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 15px; color: #94a3b8;">
                                    No se encontraron conceptos registrados para esta orden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="totals-container">
                    <table class="totals-table">
                        <tr class="rejected-total">
                            <td class="text-right">Resolución:</td>
                            <td class="text-right">NO AUTORIZADO</td>
                        </tr>
                    </table>
                </div>
            @endif

            <!-- Aviso de Cotizaciones Adjuntas -->
            @php
                $hasAdjuntos = false;
                foreach ($ordenesList as $ocItem) {
                    if (!empty($ocItem->cotizacion_url) || ($ocItem->detalles && $ocItem->detalles->whereNotNull('cotizacion_url')->count() > 0)) {
                        $hasAdjuntos = true;
                        break;
                    }
                }
            @endphp
            @if($hasAdjuntos && $isAutorizado)
                <div class="attachment-banner">
                    <strong>Archivos Adjuntos:</strong> Se han adjuntado a este correo los archivos PDF de las cotizaciones autorizadas correspondientes a cada orden de compra para su consulta.
                </div>
            @endif
        </div>

        <!-- Pie de página -->
        <div class="footer">
            Este correo es una notificación automática generada por el sistema Corporativo ETBSA.<br>
            Por favor, no responda directamente a esta dirección de correo electrónico.
        </div>
    </div>
</body>

</html>
