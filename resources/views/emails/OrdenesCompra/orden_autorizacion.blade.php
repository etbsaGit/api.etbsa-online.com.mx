<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Autorización - Orden #{{ $orden->id }}</title>
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
            max-width: 720px;
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
            opacity: 0.9;
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
            margin-top: 4px;
        }

        .comments-box {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed rgba(0, 0, 0, 0.15);
            font-size: 13px;
        }

        .section-title {
            font-size: 14px;
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
            border-spacing: 10px;
            margin: 0 -10px 10px -10px;
        }

        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
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
            font-size: 14px;
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

        /* Tabla de Conceptos */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 16px;
            font-size: 12px;
        }

        .items-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            padding: 10px 8px;
            border-bottom: 2px solid #cbd5e1;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
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

        /* Totales */
        .totals-container {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .totals-table {
            width: 280px;
            margin-left: auto;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .totals-table td {
            padding: 8px 14px;
            font-size: 13px;
        }

        .totals-table tr.grand-total td {
            border-top: 2px solid #10b981;
            font-size: 15px;
            font-weight: bold;
            color: #065f46;
            background-color: #ecfdf5;
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
    $estatusNombre = $orden->estatus?->nombre ?? 'Sin Estatus';
    $isAutorizado = stripos($estatusNombre, 'Autorizado') !== false && stripos($estatusNombre, 'No Autorizado') === false;
    $isNoAutorizado = stripos($estatusNombre, 'No Autorizado') !== false || stripos($estatusNombre, 'Rechazado') !== false;

    // Estilos dependientes del estatus
    if ($isAutorizado) {
    $headerBg = 'linear-gradient(135deg, #065f46 0%, #10b981 100%)';
    $cardBg = '#ecfdf5';
    $cardBorder = '#10b981';
    $badgeBg = '#059669';
    $badgeText = '#ffffff';
    $cardTextColor = '#065f46';
    $statusDesc = 'La solicitud de orden de servicio ha sido AUTORIZADA correctamente.';
    } elseif ($isNoAutorizado) {
    $headerBg = 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)';
    $cardBg = '#fef2f2';
    $cardBorder = '#ef4444';
    $badgeBg = '#dc2626';
    $badgeText = '#ffffff';
    $cardTextColor = '#991b1b';
    $statusDesc = 'La solicitud de orden de servicio ha sido marcada como NO AUTORIZADA.';
    } else {
    $headerBg = 'linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%)';
    $cardBg = '#eff6ff';
    $cardBorder = '#3b82f6';
    $badgeBg = '#2563eb';
    $badgeText = '#ffffff';
    $cardTextColor = '#1e40af';
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
            <h1>Resolución de Orden de Compra</h1>
            <p>{{ $statusDesc }}</p>
            <div class="badge-folio">
                Orden #{{ $orden->id }} &bull; OC: {{ $orden->orden_compra ?? $orden->id }}
            </div>
        </div>

        <div class="content">
            <!-- Tarjeta de Estatus de Autorización -->
            <div class="status-card" style="background-color: {{ $cardBg }}; border-color: {{ $cardBorder }}; color: {{ $cardTextColor }};">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; opacity: 0.85;">
                    Estatus de la Autorización
                </div>
                <div>
                    <span class="status-badge" style="background-color: {{ $badgeBg }}; color: {{ $badgeText }};">
                        {{ $estatusNombre }} @php if($orden->autorizacion_compras != null) echo "-> Autorización ".$orden->autorizacion_compras @endphp
                    </span>
                </div>

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
                <tr>
                    <td class="info-card" colspan="2">
                        <div class="info-label">Proveedor / Taller</div>
                        <div class="info-value">{{ $orden->proveedor?->nombre ?? 'No asignado' }}</div>
                    </td>
                </tr>
            </table>

            <!-- Desglose de Conceptos -->
            <div class="section-title">
                Desglose de Conceptos ({{ $orden->detalles ? count($orden->detalles) : 0 }})
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="18%">Tipo</th>
                        <th width="22%">Concepto</th>
                        <th width="30%">Descripción</th>
                        <th width="7%" class="text-center">Cant.</th>
                        <th width="9%" class="text-right">P. Unitario</th>
                        <th width="9%" class="text-right">Total</th>
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
                                R: {{ $det->llanta_anchura ?? '-' }}/{{ $det->llanta_altura ?? '-' }} R{{ $det->llanta_rin ?? '-' }}
                            </span>
                            @endif
                        </td>
                        <td>{{ $det->descripcion }}</td>
                        <td class="text-center">{{ $det->cantidad ?? 1 }}</td>
                        <td class="text-right">
                            {{ $det->precio_unitario ? '$' . number_format($det->precio_unitario, 2) : '-' }}
                        </td>
                        <td class="text-right" style="font-weight: 600;">
                            {{ $det->total ? '$' . number_format($det->total, 2) : '$0.00' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 15px; color: #94a3b8;">
                            No se encontraron conceptos registrados para esta orden.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Resumen de Totales -->
            <div class="totals-container">
                <table class="totals-table">
                    @php
                    $subtotalAcumulado = $orden->detalles ? $orden->detalles->sum('subtotal') : 0;
                    $ivaAcumulado = $orden->detalles ? $orden->detalles->sum('iva') : 0;
                    $totalAcumulado = $orden->detalles ? $orden->detalles->sum('total') : 0;
                    @endphp
                    <tr>
                        <td class="text-right"><strong>Subtotal:</strong></td>
                        <td class="text-right">${{ number_format($subtotalAcumulado, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>IVA (16%):</strong></td>
                        <td class="text-right">${{ number_format($ivaAcumulado, 2) }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td class="text-right">Total:</td>
                        <td class="text-right">${{ number_format($totalAcumulado, 2) }} MXN</td>
                    </tr>
                </table>
            </div>

            <!-- Aviso de Cotización Adjunta -->
            @if(!empty($orden->cotizacion_url))
            <div class="attachment-banner">
                <strong>Documento adjunto:</strong> Se ha adjuntado la cotización en formato PDF correspondiente a esta orden de compra para su consulta.
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