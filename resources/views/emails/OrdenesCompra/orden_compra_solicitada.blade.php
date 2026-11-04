<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Orden de Compra #{{ $orden->id }}</title>
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
            font-size: 14px;
            line-height: 1.5;
            color: #475569;
            margin-bottom: 20px;
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

        /* Bloque de Concepto */
        .concept-block {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0284c7;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .concept-header {
            background-color: #f1f5f9;
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .concept-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }

        .concept-desc {
            font-size: 12px;
            color: #475569;
            margin-top: 3px;
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
        }

        .badge-quote-count {
            display: inline-block;
            background-color: #ccfbf1;
            color: #0f766e;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        /* Tabla de Cotizaciones Comparativas */
        .quotes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .quotes-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid #cbd5e1;
            font-size: 11px;
            text-transform: uppercase;
        }

        .quotes-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .quotes-table tr:hover {
            background-color: #f8fafc;
        }

        .badge-quote-num {
            display: inline-block;
            background-color: #0f766e;
            color: #ffffff;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .fiscal-badge {
            display: inline-block;
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 10px;
            margin-right: 2px;
            margin-top: 2px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Callouts / Banners */
        .status-callout {
            background-color: #fefce8;
            border-left: 4px solid #ca8a04;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 16px 0;
            font-size: 13px;
            color: #854d0e;
        }

        .info-callout {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 6px;
            margin: 16px 0;
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
        $kmEvento = $orden->detalles?->firstWhere('km_evento', '!=', null)?->km_evento 
            ?? $orden->detalles?->max('km_evento');

        $conceptosAgrupados = $orden->detalles ? $orden->detalles->groupBy(function ($d) {
            $srvId = $d->servicio_id ?? 'sin_srv';
            $desc = trim($d->descripcion ?? '');
            $anchura = $d->llanta_anchura ?? '';
            $altura = $d->llanta_altura ?? '';
            $rin = $d->llanta_rin ?? '';
            return "{$srvId}_{$desc}_{$anchura}_{$altura}_{$rin}";
        }) : collect();
    @endphp

    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <h1>Nueva Solicitud de Orden de Compra</h1>
            <p>Se ha registrado una nueva orden de servicio con cotizaciones comparativas para su revisión y autorización.</p>
            <div class="badge-folio">
                Orden #{{ $orden->id }} &bull; Unidad: {{ $orden->vehicle?->placas ?? 'S/P' }} &bull; Estatus: Solicitado
            </div>
        </div>

        <div class="content">
            <p class="intro">
                Estimado(a), se ha generado una nueva solicitud de compra/servicio en el sistema. A continuación se presentan los datos de la unidad y el desglose de los conceptos con sus cotizaciones comparativas:
            </p>

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
                        <div class="info-value" style="color: #64748b; font-style: italic;">
                            Pendiente de selección al momento de autorizar (múltiples cotizaciones comparativas)
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Desglose de Conceptos y Cotizaciones -->
            <div class="section-title">
                Conceptos y Cotizaciones Comparativas ({{ $conceptosAgrupados->count() }} concepto{{ $conceptosAgrupados->count() > 1 ? 's' : '' }} &bull; {{ $orden->detalles ? count($orden->detalles) : 0 }} cotizaciones en total)
            </div>

            @forelse($conceptosAgrupados as $grupo)
                @php
                    $primerDet = $grupo->first();
                @endphp
                <div class="concept-block">
                    <div class="concept-header">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <div class="concept-title">
                                        Concepto #{{ $loop->iteration }}: {{ $primerDet->servicio?->nombre ?? 'Servicio' }}
                                        <span class="badge-type">
                                            {{ $primerDet->servicio?->tipoServicio?->nombre ?? ($primerDet->servicio?->tipo_servicio?->nombre ?? 'General') }}
                                        </span>
                                        @if($primerDet->llanta_anchura || $primerDet->llanta_altura || $primerDet->llanta_rin)
                                            <span class="badge-tire">
                                                Medida: {{ $primerDet->llanta_anchura ?? '-' }}/{{ $primerDet->llanta_altura ?? '-' }} R{{ $primerDet->llanta_rin ?? '-' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="concept-desc">
                                        <strong>Descripción:</strong> {{ $primerDet->descripcion ?: 'Sin descripción' }}
                                    </div>
                                </td>
                                <td align="right" valign="top" style="width: 130px;">
                                    <span class="badge-quote-count">
                                        {{ $grupo->count() }} {{ $grupo->count() === 1 ? 'cotización' : 'cotizaciones' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table class="quotes-table">
                        <thead>
                            <tr>
                                <th width="10%">Opción</th>
                                <th width="30%">Proveedor / Taller</th>
                                <th width="7%" class="text-center">Cant.</th>
                                <th width="12%" class="text-right">P. Unitario</th>
                                <th width="12%" class="text-right">Subtotal</th>
                                <th width="12%" class="text-right">IVA</th>
                                <th width="17%" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grupo as $qIdx => $cot)
                                <tr>
                                    <td>
                                        <span class="badge-quote-num">Opción #{{ $qIdx + 1 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $cot->proveedor?->nombre ?? 'Sin proveedor' }}</strong>
                                        <div style="margin-top: 2px;">
                                            @if($cot->formaPago)
                                                <span class="fiscal-badge" title="Forma de Pago">FP: {{ $cot->formaPago->clave }} - {{ $cot->formaPago->nombre }}</span>
                                            @endif
                                            @if($cot->metodoPago)
                                                <span class="fiscal-badge" title="Método de Pago">MP: {{ $cot->metodoPago->clave }}</span>
                                            @endif
                                            @if($cot->usoCfdi)
                                                <span class="fiscal-badge" title="Uso CFDI">Uso: {{ $cot->usoCfdi->clave }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center font-mono">{{ $cot->cantidad ?? 1 }}</td>
                                    <td class="text-right">
                                        {{ $cot->precio_unitario ? '$' . number_format($cot->precio_unitario, 2) : '-' }}
                                    </td>
                                    <td class="text-right">${{ number_format($cot->subtotal ?? 0, 2) }}</td>
                                    <td class="text-right">${{ number_format($cot->iva ?? 0, 2) }}</td>
                                    <td class="text-right" style="font-weight: 700; color: #0f766e;">
                                        ${{ number_format($cot->total ?? 0, 2) }} MXN
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div style="padding: 16px; background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; text-align: center; color: #94a3b8;">
                    No se encontraron conceptos registrados para esta orden.
                </div>
            @endforelse

            <!-- Banner Informativo del Estado de la Orden -->
            <div class="status-callout">
                <strong>Estatus actual:</strong> Pendiente de Autorización.<br>
                <span style="font-size: 12px;">
                    El monto definitivo y el/los proveedores asignados se confirmarán al momento de la autorización en el sistema. Si se seleccionan cotizaciones de distintos proveedores, el sistema generará automáticamente las órdenes de compra independientes correspondientes bajo el mismo folio de autorización.
                </span>
            </div>

            <!-- Aviso de Documentos Adjuntos -->
            @php
                $hasCotizaciones = $orden->detalles?->whereNotNull('cotizacion_url')->count() > 0 || !empty($orden->cotizacion_url);
            @endphp
            @if($hasCotizaciones)
                <div class="info-callout">
                    <strong>Archivos Adjuntos:</strong> Se han adjuntado a este correo los archivos PDF de las cotizaciones registradas para su consulta y comparativa directa.
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
