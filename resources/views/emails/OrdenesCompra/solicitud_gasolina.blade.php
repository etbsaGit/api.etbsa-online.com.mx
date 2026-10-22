<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Dispersión de Gasolina #{{ $orden->id }}</title>
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
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
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
            color: #bae6fd;
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

        .intro {
            font-size: 15px;
            line-height: 1.5;
            color: #475569;
            margin-bottom: 20px;
        }

        .fuel-summary-box {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            border-left: 4px solid #ea580c;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }

        .fuel-summary-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .fuel-summary-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #9a3412;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .fuel-summary-value {
            font-size: 20px;
            font-weight: 800;
            color: #c2410c;
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

        .badge-litros {
            display: inline-block;
            background-color: #ffedd5;
            color: #c2410c;
            border: 1px solid #fed7aa;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
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
            border-top: 2px solid #ea580c;
            font-size: 15px;
            font-weight: bold;
            color: #9a3412;
            background-color: #fff7ed;
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
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <h1>Nueva Solicitud de Dispersión de Gasolina</h1>
            <p>Se ha registrado una nueva solicitud de dispersión de gasolina para su revisión y autorización.</p>
            <div class="badge-folio">
                Solicitud #{{ $orden->id }} &bull; Gasolina
            </div>
        </div>

        <div class="content">
            <p class="intro">
                Estimado(a), se ha generado una nueva solicitud de dispersión de gasolina en el sistema. A continuación se presentan los detalles del vehículo, el kilometraje reportado y la cantidad de combustible solicitada:
            </p>

            @php
                $kmEvento = $orden->detalles?->firstWhere('km_evento', '!=', null)?->km_evento 
                    ?? $orden->detalles?->max('km_evento')
                    ?? $orden->vehicle?->km;
                $totalLitros = $orden->detalles ? $orden->detalles->sum('cantidad') : 0;
                $hasAmounts = $orden->detalles && $orden->detalles->sum('total') > 0;
            @endphp

            <!-- Resumen de Combustible -->
            <div class="fuel-summary-box">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="50%">
                            <div class="fuel-summary-label">Litros Solicitados</div>
                            <div class="fuel-summary-value">{{ number_format($totalLitros, 2) }} Lts</div>
                        </td>
                        <td width="50%">
                            <div class="fuel-summary-label">Kilometraje Reportado</div>
                            <div class="fuel-summary-value" style="color: #ea580c;">
                                {{ $kmEvento ? number_format($kmEvento) . ' km' : 'N/A' }}
                            </div>
                        </td>
                    </tr>
                </table>
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
                @if(!empty($orden->proveedor))
                <tr>
                    <td class="info-card" colspan="2">
                        <div class="info-label">Proveedor / Estación</div>
                        <div class="info-value">{{ $orden->proveedor->nombre }}</div>
                    </td>
                </tr>
                @endif
            </table>

            <!-- Desglose de Dispersión -->
            <div class="section-title">
                Desglose de Combustible
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="{{ $hasAmounts ? '20%' : '26%' }}">Concepto</th>
                        <th width="16%" class="text-center">Litros</th>
                        <th width="16%" class="text-center">KM Evento</th>
                        <th width="{{ $hasAmounts ? '23%' : '37%' }}">Descripción / Observaciones</th>
                        @if($hasAmounts)
                            <th width="10%" class="text-right">P. Unitario</th>
                            <th width="10%" class="text-right">Total</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($orden->detalles as $index => $det)
                        <tr>
                            <td class="text-center"><strong>{{ $index + 1 }}</strong></td>
                            <td>
                                <strong>{{ $det->servicio?->nombre ?? 'Dispersión de gasolina' }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge-litros">{{ $det->cantidad ?? 0 }} Lts</span>
                            </td>
                            <td class="text-center" style="color: #ea580c; font-weight: 700;">
                                {{ $det->km_evento ? number_format($det->km_evento) . ' km' : 'N/A' }}
                            </td>
                            <td>{{ $det->descripcion ?: 'Dispersión de gasolina' }}</td>
                            @if($hasAmounts)
                                <td class="text-right">
                                    {{ $det->precio_unitario ? '$' . number_format($det->precio_unitario, 2) : '-' }}
                                </td>
                                <td class="text-right" style="font-weight: 600;">
                                    {{ $det->total ? '$' . number_format($det->total, 2) : '$0.00' }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $hasAmounts ? 7 : 5 }}" class="text-center" style="padding: 15px; color: #94a3b8;">
                                No se encontraron conceptos registrados para esta solicitud.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Resumen de Totales (si existen importes asignados) -->
            @if($hasAmounts)
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
            @endif

            <!-- Aviso de Cotización / Comprobante Adjunto -->
            @if(!empty($orden->cotizacion_url))
                <div class="attachment-banner">
                    <strong>Documento adjunto:</strong> Se ha adjuntado el documento en formato PDF correspondiente a esta solicitud para su validación.
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
