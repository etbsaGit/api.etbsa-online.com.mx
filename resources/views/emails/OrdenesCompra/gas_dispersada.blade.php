<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Dispersión de Gasolina</title>
</head>
@php
    $estatusNombre = strtolower(optional($orden->estatus)->nombre ?? '');
    $esDispersada = $estatusNombre === 'dispersada';

    $colorPrimario = $esDispersada ? '#2e7d32' : '#c62828';
    $colorFondoBadge = $esDispersada ? '#e8f5e9' : '#ffebee';
    $colorBordeCaja = $esDispersada ? '#a5d6a7' : '#ef9a9a';

    $ultimaBitacora = $orden->bitacora ? $orden->bitacora->sortByDesc('id')->first() : null;
    $comentarioBitacora = optional($ultimaBitacora)->comentarios;
@endphp
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; color: #333333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f6f9; padding: 24px 0;">
        <tr>
            <td align="center">
                <table width="640" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Encabezado -->
                    <tr>
                        <td style="background-color: {{ $colorPrimario }}; color: #ffffff; padding: 20px 28px;">
                            <h2 style="margin: 0; font-size: 20px; font-weight: 700;">
                                @if($esDispersada)
                                    Gasolina Dispersada - Solicitud #{{ $orden->id }}
                                @else
                                    Solicitud de Dispersión de Gasolina No Autorizada #{{ $orden->id }}
                                @endif
                            </h2>
                            <p style="margin: 4px 0 0 0; font-size: 13px; color: #f1f5f9;">
                                Estatus actual: <strong>{{ optional($orden->estatus)->nombre ?? 'Actualizado' }}</strong>
                                @if($orden->is_urgente)
                                    &bull; <strong>URGENTE</strong>
                                @endif
                            </p>
                        </td>
                    </tr>

                    <!-- Cuerpo -->
                    <tr>
                        <td style="padding: 24px 28px;">
                            <p style="margin: 0 0 12px 0; font-size: 14px;">
                                Hola <strong>{{ optional($orden->solicitante)->nombreCompleto ?? 'Solicitante' }}</strong>:
                            </p>

                            @if($esDispersada)
                                <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.5;">
                                    Te informamos que tu solicitud de dispersión de gasolina con folio <strong>#{{ $orden->id }}</strong> ha sido <strong>dispersada</strong> exitosamente.
                                </p>
                            @else
                                <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.5;">
                                    Te informamos que tu solicitud de dispersión de gasolina con folio <strong>#{{ $orden->id }}</strong> <strong>no fue autorizada</strong>.
                                </p>
                            @endif

                            <!-- Resumen de Litros / Motivo -->
                            <div style="background-color: {{ $colorFondoBadge }}; border: 1px solid {{ $colorBordeCaja }}; border-radius: 6px; padding: 14px 18px; margin-bottom: 18px;">
                                @if($esDispersada)
                                    <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 14px;">
                                        <tr>
                                            <td width="50%" style="color: #555;">
                                                <strong>Litros Solicitados:</strong> {{ number_format((int) $orden->lts_solicitados) }} Lts
                                            </td>
                                            <td width="50%" style="color: {{ $colorPrimario }}; font-size: 16px;">
                                                <strong>Litros Dispersados: {{ number_format((int) ($orden->lts_dispersados ?? $orden->lts_solicitados)) }} Lts</strong>
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <div style="font-size: 13px; color: {{ $colorPrimario }};">
                                        <strong>Motivo / Comentarios de rechazo:</strong><br>
                                        <span style="color: #333333;">{{ $comentarioBitacora ?: 'Sin comentarios adicionales.' }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Datos de la Unidad y Solicitud -->
                            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-size: 13px; margin-bottom: 16px; border: 1px solid #e0e0e0;">
                                <tr style="background-color: #f8fafc;">
                                    <td width="35%" style="border-bottom: 1px solid #e0e0e0;"><strong>Folio de Solicitud:</strong></td>
                                    <td style="border-bottom: 1px solid #e0e0e0;">#{{ $orden->id }}</td>
                                </tr>
                                <tr>
                                    <td style="border-bottom: 1px solid #e0e0e0;"><strong>Fecha de Solicitud:</strong></td>
                                    <td style="border-bottom: 1px solid #e0e0e0;">{{ optional($orden->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr style="background-color: #f8fafc;">
                                    <td style="border-bottom: 1px solid #e0e0e0;"><strong>Unidad / Vehículo:</strong></td>
                                    <td style="border-bottom: 1px solid #e0e0e0;">
                                        <strong>{{ optional($orden->vehicle)->placas ?? 'S/P' }}</strong>
                                        @if(optional($orden->vehicle)->serie)
                                            (Serie: {{ $orden->vehicle->serie }})
                                        @endif
                                        @if(optional(optional($orden->vehicle)->estatus)->nombre)
                                            - {{ $orden->vehicle->estatus->nombre }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border-bottom: 1px solid #e0e0e0;"><strong>Kilometraje Registrado:</strong></td>
                                    <td style="border-bottom: 1px solid #e0e0e0;">{{ number_format((int) $orden->km_evento) }} km</td>
                                </tr>
                                <tr style="background-color: #f8fafc;">
                                    <td style="border-bottom: 1px solid #e0e0e0;"><strong>Litros Solicitados:</strong></td>
                                    <td style="border-bottom: 1px solid #e0e0e0;">{{ number_format((int) $orden->lts_solicitados) }} Lts</td>
                                </tr>
                                @if($esDispersada || $orden->lts_dispersados !== null)
                                    <tr>
                                        <td style="border-bottom: 1px solid #e0e0e0;"><strong>Litros Dispersados:</strong></td>
                                        <td style="border-bottom: 1px solid #e0e0e0; font-weight: bold; color: #2e7d32;">
                                            {{ number_format((int) ($orden->lts_dispersados ?? $orden->lts_solicitados)) }} Lts
                                        </td>
                                    </tr>
                                @endif
                                <tr style="background-color: #f8fafc;">
                                    <td style="border-bottom: 1px solid #e0e0e0;"><strong>Cargo a:</strong></td>
                                    <td style="border-bottom: 1px solid #e0e0e0;">
                                        {{ optional($orden->sucursalCargo)->nombre ?? 'N/A' }} / {{ optional($orden->departamentoCargo)->nombre ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Notas de la Solicitud:</strong></td>
                                    <td>{{ $orden->notas ?: 'Sin notas' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Pie -->
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 14px 28px; text-align: center; font-size: 12px; color: #64748b;">
                            Este es un mensaje automático generado por el Sistema Corporativo ETBSA.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
