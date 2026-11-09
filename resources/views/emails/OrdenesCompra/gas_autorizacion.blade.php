<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Autorización - Dispersión de Gasolina #{{ $orden->id }}</title>
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

        .badge-urgente {
            display: inline-block;
            background-color: #ef4444;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
            margin-left: 6px;
        }

        .content {
            padding: 24px 28px;
        }

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
        $isAutorizado = (stripos($estatusNombre, 'Autorizado') !== false && stripos($estatusNombre, 'No Autorizado') === false)
            || stripos($estatusNombre, 'Por dispersar') !== false
            || stripos($estatusNombre, 'Dispersada') !== false;
        $isNoAutorizado = stripos($estatusNombre, 'No Autorizado') !== false || stripos($estatusNombre, 'Rechazado') !== false;

        if ($isAutorizado) {
            $headerBg = 'linear-gradient(135deg, #065f46 0%, #10b981 100%)';
            $cardBg = '#ecfdf5';
            $cardBorder = '#10b981';
            $badgeBg = '#059669';
            $badgeText = '#ffffff';
            $cardTextColor = '#065f46';
            $statusDesc = 'La solicitud de dispersión de gasolina ha sido AUTORIZADA correctamente.';
        } elseif ($isNoAutorizado) {
            $headerBg = 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)';
            $cardBg = '#fef2f2';
            $cardBorder = '#ef4444';
            $badgeBg = '#dc2626';
            $badgeText = '#ffffff';
            $cardTextColor = '#991b1b';
            $statusDesc = 'La solicitud de dispersión de gasolina ha sido marcada como NO AUTORIZADA.';
        } else {
            $headerBg = 'linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%)';
            $cardBg = '#eff6ff';
            $cardBorder = '#3b82f6';
            $badgeBg = '#2563eb';
            $badgeText = '#ffffff';
            $cardTextColor = '#1e40af';
            $statusDesc = 'Se ha actualizado el estatus de la solicitud de dispersión de gasolina.';
        }

        $ultimaBitacora = $orden->bitacora?->sortByDesc('id')->first(function ($b) {
            return !empty($b->comentarios) && !str_contains($b->comentarios, 'creada');
        });

        $kmEvento = $orden->km_evento ?? $orden->vehicle?->km;
        $ltsSolicitados = $orden->lts_solicitados ?? 0;
        $ltsDispersados = $orden->lts_dispersados;
    @endphp

    <div class="container">
        <div class="header" style="background: {{ $headerBg }};">
            <h1>Resolución de Dispersión de Gasolina</h1>
            <p>{{ $statusDesc }}</p>
            <div>
                <span class="badge-folio">
                    Solicitud #{{ $orden->id }} &bull; Gasolina
                </span>
                @if($orden->is_urgente)
                    <span class="badge-urgente">URGENTE</span>
                @endif
            </div>
        </div>

        <div class="content">
            <div class="status-card" style="background-color: {{ $cardBg }}; border-color: {{ $cardBorder }}; color: {{ $cardTextColor }};">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; opacity: 0.85;">
                    Estatus de la Solicitud
                </div>
                <div>
                    <span class="status-badge" style="background-color: {{ $badgeBg }}; color: {{ $badgeText }};">
                        {{ $estatusNombre }}
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
                                Registrado por: {{ $ultimaBitacora->user->empleado->nombreCompleto ?? ($ultimaBitacora->user->empleado->nombre . ' ' . $ultimaBitacora->user->empleado->apellido_paterno) }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Resumen de Combustible -->
            <div class="fuel-summary-box">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="33%">
                            <div class="fuel-summary-label">Litros Solicitados</div>
                            <div class="fuel-summary-value">{{ number_format($ltsSolicitados) }} Lts</div>
                        </td>
                        <td width="33%">
                            <div class="fuel-summary-label">Litros Autorizados</div>
                            <div class="fuel-summary-value" style="color: #059669;">
                                {{ $ltsDispersados !== null ? number_format($ltsDispersados) . ' Lts' : 'N/A' }}
                            </div>
                        </td>
                        <td width="34%">
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
                            {{ $orden->created_at ? \Carbon\Carbon::parse($orden->created_at)->format('d/m/Y h:i A') : 'N/A' }}
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
                        <div class="info-label">Notas / Observaciones</div>
                        <div class="info-value">{{ $orden->notas ?: 'Sin observaciones adicionales' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Este correo es una notificación automática generada por el sistema Corporativo ETBSA.<br>
            Por favor, no responda directamente a esta dirección de correo electrónico.
        </div>
    </div>
</body>

</html>
