<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Revisión de Flotilla #{{ $checklist->id }}</title>
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
            background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%);
            color: #ffffff;
            padding: 26px 30px;
            text-align: left;
        }

        .header h1 {
            margin: 0 0 6px 0;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .header p {
            margin: 0;
            font-size: 14px;
            color: #fecaca;
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

        .badge-trimestre {
            display: inline-block;
            background-color: #fef08a;
            color: #854d0e;
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

        .alert-box {
            font-size: 14px;
            line-height: 1.6;
            color: #7f1d1d;
            margin-bottom: 22px;
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
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

        .badge-placas {
            font-family: 'Courier New', Courier, monospace;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 14px;
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

        .badge-present {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-missing {
            display: inline-block;
            background-color: #fee2e2;
            color: #991b1b;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .notas-card {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #d97706;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 14px;
            color: #78350f;
            margin-top: 8px;
        }

        .btn-action {
            display: inline-block;
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
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
            <h1>Alerta: Documentación Faltante en Revisión de Flotilla</h1>
            <p>Notificación automática del Módulo de Revisión de Flotillas &bull; Corporativo ETBSA</p>
            <div>
                <span class="badge-folio">Revisión #{{ $checklist->id }}</span>
                <span class="badge-trimestre">Trimestre {{ $checklist->trimestre_revision }}</span>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <div class="alert-box">
                Se ha registrado una revisión física para la unidad <strong>{{ $vehicle?->placas ?? 'S/P' }}</strong> ({{ $vehicle?->nombre_unidad ?? 'Vehículo' }}) en la que se reporta que <strong>{{ $faltantes->count() }} documento(s) o placa(s) NO se encuentran en físico</strong>.
            </div>

            <!-- Datos del Vehículo -->
            <div class="section-title">Información de la Unidad Vehicular</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="40%">
                        <div class="info-label">Placas</div>
                        <div class="info-value">
                            <span class="badge-placas">{{ $vehicle?->placas ?: 'S/P' }}</span>
                        </div>
                    </td>
                    <td class="info-card" width="60%">
                        <div class="info-label">Vehículo (Marca / Modelo / Tipo)</div>
                        <div class="info-value">{{ $vehicle?->nombre_unidad ?: 'Unidad' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="info-card" width="40%">
                        <div class="info-label">Número de Serie</div>
                        <div class="info-value">{{ $vehicle?->serie ?: 'No especificado' }}</div>
                    </td>
                    <td class="info-card" width="60%">
                        <div class="info-label">Sucursal / Departamento</div>
                        <div class="info-value">
                            {{ optional($vehicle?->sucursal)->nombre ?: 'N/A' }}
                            @if (optional($vehicle?->departamento)->nombre)
                                / {{ $vehicle->departamento->nombre }}
                            @endif
                        </div>
                    </td>
                </tr>
                @if ($vehicle?->responsable)
                <tr>
                    <td class="info-card" colspan="2">
                        <div class="info-label">Responsable Asignado de la Unidad</div>
                        <div class="info-value">{{ $vehicle->responsable->nombreCompleto }}</div>
                    </td>
                </tr>
                @endif
            </table>

            <!-- Datos de la Revisión -->
            <div class="section-title">Información de la Revisión</div>
            <table class="grid-table">
                <tr>
                    <td class="info-card" width="50%">
                        <div class="info-label">Verificado Por</div>
                        <div class="info-value">
                            {{ optional($verificador?->empleado)->nombreCompleto ?: ($verificador?->name ?: 'Usuario del Sistema') }}
                        </div>
                    </td>
                    <td class="info-card" width="50%">
                        <div class="info-label">Fecha y Trimestre de Revisión</div>
                        <div class="info-value">
                            {{ $checklist->fecha_revision ? \Carbon\Carbon::parse($checklist->fecha_revision)->format('d/m/Y') : now()->format('d/m/Y') }}
                            &bull; Trimestre {{ $checklist->trimestre_revision }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Resultado del Checklist -->
            <div class="section-title">Resultado del Checklist Físico</div>
            <table class="docs-table">
                <thead>
                    <tr>
                        <th width="65%">Documento / Placa</th>
                        <th width="35%">Existencia en Físico</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalles as $det)
                        <tr>
                            <td>
                                <strong>{{ $det->documento }}</strong>
                            </td>
                            <td>
                                @if ($det->existencia)
                                    <span class="badge-present">Sí cuenta en físico</span>
                                @else
                                    <span class="badge-missing">FALTANTE (No tiene)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Motivo / Notas del Verificador -->
            <div class="section-title">Motivo / Observaciones del Verificador</div>
            <div class="notas-card">
                {{ $checklist->notas ?: 'Sin comentarios adicionales.' }}
            </div>

            <!-- Botón de Acción -->
            <div class="action-container">
                <a href="{{ $urlPortal }}" class="btn-action" target="_blank">
                    Ir al Módulo de Revisión de Flotillas
                </a>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <strong>Corporativo ETBSA</strong> &bull; Módulo de Revisión de Flotillas<br>
            Este correo es una notificación automática. Por favor no responda directamente a esta dirección electrónica.
        </div>
    </div>
</body>

</html>
