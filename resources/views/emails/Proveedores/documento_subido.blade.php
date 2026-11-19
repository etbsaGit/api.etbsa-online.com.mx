<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $esReemplazo ? 'Documento Actualizado' : 'Nuevo Documento Subido' }}: {{ $documento->nombre_doc }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
            padding: 24px 0;
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
            padding: 26px 30px;
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
            margin-top: 10px;
        }

        .badge-tipo {
            display: inline-block;
            background-color: #38bdf8;
            color: #0c4a6e;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            margin-left: 6px;
        }

        .content {
            padding: 26px 30px;
        }

        .intro {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 22px;
            background-color: #f8fafc;
            border-left: 4px solid #0284c7;
            padding: 12px 16px;
            border-radius: 0 6px 6px 0;
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
            margin: 0 -8px 12px -8px;
        }

        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            vertical-align: top;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        .info-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 600;
            word-break: break-word;
        }

        .badge-status {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-rfc {
            font-family: 'Courier New', Courier, monospace;
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
        }

        .btn-action {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3);
            margin-top: 8px;
        }

        .action-container {
            text-align: center;
            padding: 20px 0 10px 0;
        }

        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 30px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            text-align: center;
        }

        .footer strong {
            color: #334155;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <h1>{{ $esReemplazo ? 'Documento de Proveedor Actualizado' : 'Nuevo Documento Subido' }}</h1>
            <p>Notificación automática del Sistema Corporativo ETBSA</p>
            <div>
                <span class="badge-folio">Proveedor #{{ $proveedor->id }}</span>
                <span class="badge-tipo">{{ $documento->nombre_doc }}</span>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <div class="intro">
                @if ($esReemplazo)
                    Se ha actualizado / sustituido el documento <strong>{{ $documento->nombre_doc }}</strong> para el proveedor <strong>{{ $proveedor->razon_social }}</strong>. El documento ha quedado en estatus <strong>"Por Validar"</strong> pendiente de revisión por el equipo autorizado.
                @else
                    Se ha subido un nuevo documento (<strong>{{ $documento->nombre_doc }}</strong>) para el proveedor <strong>{{ $proveedor->razon_social }}</strong> y se encuentra en estatus <strong>"Por Validar"</strong> para su revisión.
                @endif
            </div>

            <!-- Datos del Documento Subido -->
            <div class="section-title">Datos del Documento Subido</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Tipo de Documento</div>
                        <div class="info-value">{{ $documento->nombre_doc }}</div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Estatus Asignado</div>
                        <div class="info-value">
                            <span class="badge-status">{{ optional($documento->estatus)->nombre ?? 'Por Validar' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Fecha de Vencimiento</div>
                        <div class="info-value">
                            @if (!empty($documento->fecha_vencimiento))
                                {{ \Carbon\Carbon::parse($documento->fecha_vencimiento)->format('d/m/Y') }}
                            @else
                                <span style="color: #94a3b8; font-weight: normal;">No especificada</span>
                            @endif
                        </div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Tipo de Acción</div>
                        <div class="info-value">
                            {{ $esReemplazo ? 'Actualización / Reemplazo' : 'Carga de Documento' }}
                        </div>
                    </td>
                </tr>
                @if (!empty($documento->notas))
                <tr>
                    <td class="info-card" colspan="2">
                        <div class="info-label">Notas / Observaciones del Documento</div>
                        <div class="info-value" style="font-weight: normal;">
                            {{ $documento->notas }}
                        </div>
                    </td>
                </tr>
                @endif
            </table>

            <!-- Datos del Proveedor -->
            <div class="section-title">Información del Proveedor</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="60%">
                        <div class="info-label">Razón Social</div>
                        <div class="info-value">{{ $proveedor->razon_social }}</div>
                    </td>
                    <td class="info-card" width="40%">
                        <div class="info-label">RFC</div>
                        <div class="info-value">
                            @if (!empty($proveedor->rfc))
                                <span class="badge-rfc">{{ $proveedor->rfc }}</span>
                            @else
                                <span style="color: #94a3b8; font-weight: normal;">No especificado</span>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Tipo de Persona</div>
                        <div class="info-value">{{ $proveedor->tipo_persona }}</div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Contacto</div>
                        <div class="info-value">
                            {{ $proveedor->correo ?: $proveedor->telefono ?: 'No especificado' }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Información de Carga -->
            <div class="section-title">Información de Carga</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Subido Por</div>
                        <div class="info-value">
                            @php
                                $nombreUploader = 'Usuario del Sistema';
                                $emailUploader = '';
                                if ($uploader) {
                                    $nombreUploader = optional($uploader->empleado)->nombreCompleto
                                        ?? $uploader->name;
                                    $emailUploader = $uploader->email
                                        ?? optional($uploader->empleado)->correo_institucional;
                                } elseif ($documento->uploader) {
                                    $nombreUploader = optional($documento->uploader->empleado)->nombreCompleto
                                        ?? $documento->uploader->name;
                                    $emailUploader = $documento->uploader->email
                                        ?? optional($documento->uploader->empleado)->correo_institucional;
                                }
                            @endphp
                            {{ $nombreUploader }}
                            @if (!empty($emailUploader))
                                <br><small style="color: #64748b; font-weight: normal;">{{ $emailUploader }}</small>
                            @endif
                        </div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Fecha y Hora de Carga</div>
                        <div class="info-value">
                            {{ $documento->updated_at ? \Carbon\Carbon::parse($documento->updated_at)->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A') }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Botón de Acción -->
            <div class="action-container">
                <a href="{{ $urlPortal }}" class="btn-action" target="_blank">
                    Ver Expediente en el Portal Corporativo
                </a>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <strong>Corporativo ETBSA</strong> &bull; Sistema de Coordinación de Compras<br>
            Este correo es una notificación automática. Por favor no responda directamente a esta dirección electrónica.
        </div>
    </div>
</body>

</html>
