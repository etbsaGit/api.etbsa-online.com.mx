<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if (strtolower($estatusNombre) === 'validado')
            Documento Validado: {{ $documento->nombre_doc }}
        @elseif (strtolower($estatusNombre) === 'corregir')
            Documento Requiere Corrección: {{ $documento->nombre_doc }}
        @else
            Estatus de Documento: {{ $documento->nombre_doc }}
        @endif
    </title>
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
            color: #ffffff;
            padding: 26px 30px;
            text-align: left;
        }

        .header-validado {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
        }

        .header-corregir {
            background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%);
        }

        .header-default {
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
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
            margin-top: 10px;
        }

        .badge-status-header {
            display: inline-block;
            background-color: #ffffff;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            margin-left: 6px;
        }

        .badge-status-header.status-validado {
            color: #15803d;
        }

        .badge-status-header.status-corregir {
            color: #b91c1c;
        }

        .content {
            padding: 26px 30px;
        }

        .intro {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 22px;
            padding: 14px 18px;
            border-radius: 6px;
        }

        .intro-validado {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            color: #166534;
        }

        .intro-corregir {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }

        .intro-default {
            background-color: #f8fafc;
            border-left: 4px solid #0284c7;
            color: #334155;
        }

        .observaciones-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }

        .observaciones-title {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
            color: #92400e;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .observaciones-text {
            font-size: 14px;
            color: #78350f;
            line-height: 1.5;
            white-space: pre-line;
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

        .badge-pill-validado {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-pill-corregir {
            display: inline-block;
            background-color: #fee2e2;
            color: #991b1b;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-pill-default {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
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
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 8px;
        }

        .btn-action-green {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
            box-shadow: 0 2px 4px rgba(22, 163, 74, 0.3);
        }

        .btn-action-red {
            background: linear-gradient(135deg, #b91c1c 0%, #ea580c 100%);
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
        }

        .btn-action-blue {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3);
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
    @php
        $estatusKey = strtolower($estatusNombre);
        $headerClass = 'header-default';
        $introClass = 'intro-default';
        $badgeClass = 'badge-pill-default';
        $btnClass = 'btn-action-blue';

        if ($estatusKey === 'validado') {
            $headerClass = 'header-validado';
            $introClass = 'intro-validado';
            $badgeClass = 'badge-pill-validado';
            $btnClass = 'btn-action-green';
        } elseif ($estatusKey === 'corregir') {
            $headerClass = 'header-corregir';
            $introClass = 'intro-corregir';
            $badgeClass = 'badge-pill-corregir';
            $btnClass = 'btn-action-red';
        }
    @endphp

    <div class="container">
        <!-- Encabezado -->
        <div class="header {{ $headerClass }}">
            <h1>
                @if ($estatusKey === 'validado')
                    Documento Validado Exitosamente
                @elseif ($estatusKey === 'corregir')
                    Documento Requiere Corrección
                @else
                    Actualización de Documento
                @endif
            </h1>
            <p>Notificación automática del Sistema Corporativo ETBSA</p>
            <div>
                <span class="badge-folio">Proveedor #{{ $proveedor?->id }}</span>
                <span class="badge-status-header status-{{ $estatusKey }}">{{ $estatusNombre }}</span>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <div class="intro {{ $introClass }}">
                @if ($estatusKey === 'validado')
                    El documento <strong>{{ $documento->nombre_doc }}</strong> para el proveedor <strong>{{ $proveedor?->razon_social }}</strong> ha sido <strong>validado correctamente</strong> por el equipo de Proveedores y cumple con los requerimientos establecidos.
                @elseif ($estatusKey === 'corregir')
                    El documento <strong>{{ $documento->nombre_doc }}</strong> para el proveedor <strong>{{ $proveedor?->razon_social }}</strong> ha sido marcado como <strong>"A Corregir"</strong>. Por favor revisa las observaciones del validador y sube el archivo corregido en el portal.
                @else
                    Se ha actualizado el estatus del documento <strong>{{ $documento->nombre_doc }}</strong> para el proveedor <strong>{{ $proveedor?->razon_social }}</strong> a <strong>"{{ $estatusNombre }}"</strong>.
                @endif
            </div>

            <!-- Observaciones / Motivo si aplica -->
            @if (!empty($documento->notas))
                <div class="observaciones-box">
                    <div class="observaciones-title">Observaciones / Motivo de Revisión</div>
                    <div class="observaciones-text">{{ $documento->notas }}</div>
                </div>
            @endif

            <!-- Datos del Documento -->
            <div class="section-title">Datos del Documento Revisado</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Tipo de Documento</div>
                        <div class="info-value">{{ $documento->nombre_doc }}</div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Nuevo Estatus</div>
                        <div class="info-value">
                            <span class="{{ $badgeClass }}">{{ $estatusNombre }}</span>
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
                        <div class="info-label">Fecha de Revisión</div>
                        <div class="info-value">
                            {{ $documento->updated_at ? \Carbon\Carbon::parse($documento->updated_at)->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A') }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Datos del Proveedor -->
            <div class="section-title">Información del Proveedor</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="60%">
                        <div class="info-label">Razón Social</div>
                        <div class="info-value">{{ $proveedor?->razon_social }}</div>
                    </td>
                    <td class="info-card" width="40%">
                        <div class="info-label">RFC</div>
                        <div class="info-value">
                            @if (!empty($proveedor?->rfc))
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
                        <div class="info-value">{{ $proveedor?->tipo_persona ?? 'No especificado' }}</div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Contacto Registrado</div>
                        <div class="info-value">
                            {{ $proveedor?->correo ?: $proveedor?->telefono ?: 'No especificado' }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Información del Validador -->
            <div class="section-title">Información del Revisor / Validador</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Revisado Por</div>
                        <div class="info-value">
                            @php
                                $nombreValidador = 'Equipo de Proveedores';
                                $emailValidador = '';
                                if ($validador) {
                                    $nombreValidador = optional($validador->empleado)->nombreCompleto
                                        ?? $validador->name;
                                    $emailValidador = $validador->email
                                        ?? optional($validador->empleado)->correo_institucional;
                                } elseif ($documento->validator) {
                                    $nombreValidador = optional($documento->validator->empleado)->nombreCompleto
                                        ?? $documento->validator->name;
                                    $emailValidador = $documento->validator->email
                                        ?? optional($documento->validator->empleado)->correo_institucional;
                                }
                            @endphp
                            {{ $nombreValidador }}
                            @if (!empty($emailValidador))
                                <br><small style="color: #64748b; font-weight: normal;">{{ $emailValidador }}</small>
                            @endif
                        </div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Área / Rol</div>
                        <div class="info-value">Gestión de Proveedores</div>
                    </td>
                </tr>
            </table>

            <!-- Botón de Acción -->
            <div class="action-container">
                <a href="{{ $urlPortal }}" class="btn-action {{ $btnClass }}" target="_blank">
                    @if ($estatusKey === 'corregir')
                        Ver Expediente y Subir Documento Corregido
                    @else
                        Ver Expediente en el Portal Corporativo
                    @endif
                </a>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <strong>Corporativo ETBSA</strong> &bull; Sistema de Coordinación de Compras / Proveedores<br>
            Este correo es una notificación automática. Por favor no responda directamente a esta dirección electrónica.
        </div>
    </div>
</body>

</html>
