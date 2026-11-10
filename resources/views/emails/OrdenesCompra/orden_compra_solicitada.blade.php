<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra #{{ $orden->id }}</title>
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
            max-width: 780px;
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

        .header.header-revision {
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
        }

        .header.header-correccion {
            background: linear-gradient(135deg, #9f1239 0%, #e11d48 100%);
        }

        .header.header-rechazo {
            background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%);
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
            color: #f8fafc;
            opacity: 0.95;
        }

        .badge-folio {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.22);
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

        .badge-valido {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-vigente {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .badge-caducada {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
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
        $tipo = $tipo ?? 'solicitado';
        $comentarios = $comentarios ?? null;

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

        // Configuración de textos por tipo de notificación
        $headerClass = '';
        $tituloEncabezado = 'Nueva Solicitud de Orden de Compra';
        $subtituloEncabezado = 'Se ha registrado una nueva orden de servicio con cotizaciones comparativas para su revisión y autorización.';
        $estatusLabel = $orden->estatus?->nombre ?? 'En Proceso';

        switch ($tipo) {
            case 'revision':
                $headerClass = 'header-revision';
                $tituloEncabezado = 'Nueva Solicitud de Orden de Compra para Revisión';
                $subtituloEncabezado = 'Se ha registrado una nueva orden de compra para servicio de unidad y requiere su revisión en Coordinación de Compras.';
                $estatusLabel = 'En Revisión';
                break;
            case 'corregida':
                $headerClass = 'header-revision';
                $tituloEncabezado = 'Orden de Compra Corregida para Revisión';
                $subtituloEncabezado = 'El solicitante ha actualizado los datos y/o cotizaciones de la orden de compra y se encuentra nuevamente lista para su revisión en Coordinación de Compras.';
                $estatusLabel = 'En Revisión (Corregida)';
                break;
            case 'correccion':
                $headerClass = 'header-correccion';
                $tituloEncabezado = 'Orden de Compra Requiere Corrección';
                $subtituloEncabezado = 'La Coordinación de Compras ha revisado la orden de compra y solicita correcciones antes de proceder con su autorización.';
                $estatusLabel = 'A Corregir';
                break;
            case 'rechazo_revision':
                $headerClass = 'header-rechazo';
                $tituloEncabezado = 'Orden de Compra No Aprobada en Revisión';
                $subtituloEncabezado = 'La Coordinación de Compras ha determinado no aprobar la orden de compra en su etapa de revisión.';
                $estatusLabel = 'No Autorizado (Rechazado en Revisión)';
                break;
            case 'solicitado':
            default:
                $headerClass = '';
                $tituloEncabezado = 'Solicitud de Orden de Compra para Autorización';
                $subtituloEncabezado = 'La Coordinación de Compras ha revisado y pre-autorizado la orden de compra. Se encuentra lista para su autorización definitiva por la Dirección Administrativa.';
                $estatusLabel = 'Solicitado (Pendiente de Autorización)';
                break;
        }
    @endphp

    <div class="container">
        <!-- Encabezado -->
        <div class="header {{ $headerClass }}">
            <h1>{{ $tituloEncabezado }}</h1>
            <p>{{ $subtituloEncabezado }}</p>
            <div class="badge-folio">
                Orden #{{ $orden->id }} &bull; Unidad: {{ $orden->vehicle?->placas ?? 'S/P' }} &bull; Estatus: {{ $estatusLabel }}
            </div>
        </div>

        <div class="content">
            <!-- Llamado de Comentarios u Observaciones si existen -->
            @if(!empty($comentarios))
                @if($tipo === 'correccion')
                    <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-left: 5px solid #e11d48; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
                        <div style="font-size: 13px; font-weight: 700; color: #9f1239; margin-bottom: 6px; text-transform: uppercase;">
                            ⚠️ Observaciones y Motivo de Corrección (Coordinación de Compras):
                        </div>
                        <div style="font-size: 14px; color: #881337; line-height: 1.5; font-weight: 600;">
                            {{ $comentarios }}
                        </div>
                        <div style="font-size: 12px; color: #9f1239; margin-top: 8px;">
                            Por favor ingresa al sistema corporativo, realiza las modificaciones indicadas en la orden y guárdala para que pase nuevamente a revisión.
                        </div>
                    </div>
                @elseif($tipo === 'rechazo_revision')
                    <div style="background-color: #fef2f2; border: 1px solid #fee2e2; border-left: 5px solid #dc2626; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
                        <div style="font-size: 13px; font-weight: 700; color: #991b1b; margin-bottom: 6px; text-transform: uppercase;">
                            ❌ Motivo del Rechazo en Revisión (Coordinación de Compras):
                        </div>
                        <div style="font-size: 14px; color: #7f1d1d; line-height: 1.5; font-weight: 600;">
                            {{ $comentarios }}
                        </div>
                    </div>
                @elseif($tipo === 'solicitado')
                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-left: 5px solid #16a34a; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
                        <div style="font-size: 13px; font-weight: 700; color: #166534; margin-bottom: 6px; text-transform: uppercase;">
                            ✅ Observaciones de Pre-autorización (Coordinación de Compras):
                        </div>
                        <div style="font-size: 14px; color: #14532d; line-height: 1.5; font-weight: 600;">
                            {{ $comentarios }}
                        </div>
                    </div>
                @endif
            @endif

            <p class="intro">
                Estimado(a), a continuación se presentan los datos de la unidad vehicular y el desglose de los conceptos solicitados con sus cotizaciones comparativas:
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
                                <th width="8%">Opción</th>
                                <th width="28%">Proveedor / Taller</th>
                                <th width="14%">Válido Hasta</th>
                                <th width="6%" class="text-center">Cant.</th>
                                <th width="11%" class="text-right">P. Unitario</th>
                                <th width="11%" class="text-right">Subtotal</th>
                                <th width="10%" class="text-right">IVA</th>
                                <th width="12%" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grupo as $qIdx => $cot)
                                @php
                                    $isVigente = true;
                                    $validoHastaFormatted = null;
                                    if (!empty($cot->valido_hasta)) {
                                        $validoHastaFormatted = \Carbon\Carbon::parse($cot->valido_hasta)->format('d/m/Y');
                                        $isVigente = \Carbon\Carbon::parse($cot->valido_hasta)->endOfDay()->isFuture();
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge-quote-num">#{{ $qIdx + 1 }}</span>
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
                                    <td>
                                        @if($validoHastaFormatted)
                                            <span class="badge-valido {{ $isVigente ? 'badge-vigente' : 'badge-caducada' }}">
                                                {{ $validoHastaFormatted }} ({{ $isVigente ? 'Vigente' : 'Caducada' }})
                                            </span>
                                        @else
                                            <span style="color: #94a3b8; font-size: 11px;">No especificado</span>
                                        @endif
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
                <strong>Estatus actual:</strong> {{ $estatusLabel }}.<br>
                <span style="font-size: 12px;">
                    @if($tipo === 'revision' || $tipo === 'corregida')
                        La orden se encuentra en revisión por Coordinación de Compras. Una vez verificada, pasará a pre-autorización para su autorización final por la Dirección Administrativa.
                    @elseif($tipo === 'correccion')
                        La orden requiere modificaciones por parte del solicitante antes de poder ser autorizada.
                    @elseif($tipo === 'rechazo_revision')
                        La orden no fue aprobada en su proceso de revisión.
                    @else
                        El monto definitivo y el/los proveedores asignados se confirmarán al momento de la autorización final en el sistema por la Dirección Administrativa.
                    @endif
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
