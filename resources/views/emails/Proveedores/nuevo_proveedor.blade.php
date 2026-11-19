<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Proveedor Registrado: {{ $proveedor->razon_social }}</title>
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

        .docs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .docs-table th {
            background-color: #f1f5f9;
            color: #475569;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #e2e8f0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .docs-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        .docs-table tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .badge-status {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
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
            <h1>Nuevo Proveedor Registrado</h1>
            <p>Notificación automática del Sistema Corporativo ETBSA</p>
            <div>
                <span class="badge-folio">Folio #{{ $proveedor->id }}</span>
                <span class="badge-tipo">{{ $proveedor->tipo_persona ?? 'Persona Moral' }}</span>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <div class="intro">
                Se ha registrado un nuevo proveedor en el sistema. Los documentos adjuntos están pendientes de revisión y validación por parte del equipo autorizado.
            </div>

            <!-- Datos del Proveedor -->
            <div class="section-title">Datos del Proveedor</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="60%">
                        <div class="info-label">Razón Social / Nombre</div>
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
                        <div class="info-label">Días de Crédito</div>
                        <div class="info-value">{{ $proveedor->dias_credito ?: 'Contado / No especificado' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Correo Electrónico</div>
                        <div class="info-value">
                            {{ $proveedor->correo ?: 'No especificado' }}
                        </div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Teléfono de Contacto</div>
                        <div class="info-value">{{ $proveedor->telefono ?: 'No especificado' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="info-card" colspan="2">
                        <div class="info-label">Domicilio Fiscal</div>
                        <div class="info-value" style="font-weight: normal;">
                            {{ $proveedor->domicilio ?: 'No especificado' }}
                        </div>
                    </td>
                </tr>
                @if (!empty($proveedor->descripcion))
                <tr>
                    <td class="info-card" colspan="2">
                        <div class="info-label">Descripción / Giro Comercial</div>
                        <div class="info-value" style="font-weight: normal;">
                            {{ $proveedor->descripcion }}
                        </div>
                    </td>
                </tr>
                @endif
            </table>

            <!-- Información de Registro -->
            <div class="section-title">Información de Registro</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Registrado Por</div>
                        <div class="info-value">
                            @php
                                $nombreRegistrador = 'Usuario del Sistema';
                                $emailRegistrador = '';
                                if ($registrador) {
                                    $nombreRegistrador = optional($registrador->empleado)->nombreCompleto
                                        ?? $registrador->name;
                                    $emailRegistrador = $registrador->email
                                        ?? optional($registrador->empleado)->correo_institucional;
                                } elseif ($proveedor->registrador) {
                                    $nombreRegistrador = optional($proveedor->registrador->empleado)->nombreCompleto
                                        ?? $proveedor->registrador->name;
                                    $emailRegistrador = $proveedor->registrador->email
                                        ?? optional($proveedor->registrador->empleado)->correo_institucional;
                                }
                            @endphp
                            {{ $nombreRegistrador }}
                            @if (!empty($emailRegistrador))
                                <br><small style="color: #64748b; font-weight: normal;">{{ $emailRegistrador }}</small>
                            @endif
                        </div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Fecha y Hora de Registro</div>
                        <div class="info-value">
                            {{ $proveedor->created_at ? \Carbon\Carbon::parse($proveedor->created_at)->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A') }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Expediente Digital Adjunto -->
            <div class="section-title">Expediente Digital Inicial</div>
            @if ($proveedor->docs && count($proveedor->docs) > 0)
                <table class="docs-table">
                    <thead>
                        <tr>
                            <th width="50%">Documento</th>
                            <th width="25%">Estatus</th>
                            <th width="25%">Vencimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proveedor->docs as $doc)
                            <tr>
                                <td>
                                    <strong>{{ $doc->nombre_doc }}</strong>
                                </td>
                                <td>
                                    <span class="badge-status">{{ optional($doc->estatus)->nombre ?? 'Por Validar' }}</span>
                                </td>
                                <td>
                                    @if (!empty($doc->fecha_vencimiento))
                                        {{ \Carbon\Carbon::parse($doc->fecha_vencimiento)->format('d/m/Y') }}
                                    @else
                                        <span style="color: #94a3b8;">No especificada</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-size: 13px; color: #64748b; font-style: italic; margin-top: 6px;">
                    No se adjuntaron documentos iniciales al momento del registro.
                </p>
            @endif

            <!-- Botón de Acción -->
            <div class="action-container">
                <a href="{{ $urlPortal }}" class="btn-action" target="_blank">
                    Ver Proveedores en el Portal Corporativo
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
