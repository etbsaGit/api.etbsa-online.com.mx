<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Estado de la Requisición</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0"
        style="max-width: 700px; margin: auto; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <tr>
            <td style="background-color: #4CAF50; padding: 20px; color: #fff;">
                <h2 style="margin: 0;">Requisición de Personal #{{ $requisicion->id }}</h2>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px;">
                <p>
                    La requisición ha sido
                    @if ($requisicion->autorizacion == 1)
                        <strong style="color: green;">AUTORIZADA</strong>.
                    @elseif($requisicion->autorizacion == 2)
                        <strong style="color: red;">RECHAZADA</strong>.
                    @else
                        <strong>Solicitada</strong>.
                    @endif
                </p>

                <h3 style="border-bottom: 1px solid #ddd; padding-bottom: 5px;">📋 Detalles de la Requisición</h3>
                <ul style="line-height: 1.6;">
                    <li><strong>Sexo requerido:</strong> {{ $requisicion->sexo }}</li>
                    <li><strong>Rango de edad:</strong> {{ $requisicion->rango_edad }}</li>
                    <li><strong>Escolaridad ID:</strong> {{ $requisicion->escolaridad_id }}</li>
                    <li><strong>Experiencia / conocimientos:</strong> {{ $requisicion->experiencia_conocimientos }}</li>
                    <li><strong>Habilidades requeridas:</strong> {{ $requisicion->habilidades }}</li>
                    <li><strong>Actividades a desempeñar:</strong> {{ $requisicion->actividades_desempenar }}</li>
                    <li><strong>Manejo de equipo:</strong> {{ $requisicion->manejo_equipo }}</li>
                    <li><strong>Sueldo mensual inicial:</strong>
                        ${{ number_format($requisicion->sueldo_mensual_inicial, 2) }}</li>
                    <li><strong>Comisiones:</strong> ${{ number_format($requisicion->comisiones, 2) }}</li>
                    <li><strong>Total de posiciones:</strong> {{ $requisicion->total_posiciones }}</li>
                    <li><strong>Tipo de vacante:</strong> {{ $requisicion->tipo_vacante }}</li>
                    <li><strong>Motivo de vacante:</strong> {{ $requisicion->motivo_vacante }}</li>
                    <li><strong>Especificación de vacante:</strong> {{ $requisicion->especificar_vacante }}</li>
                </ul>

                <p style="margin-top: 30px;">
                    <em>Validado por:</em>
                    <strong>
                        {{ $requisicion->auth->nombreCompleto ?? 'Pendiente' }}
                    </strong><br>
                    <em>Fecha:</em> {{ \Carbon\Carbon::parse($requisicion->updated_at)->format('d/m/Y') }}
                </p>

                <p style="margin-top: 10px;">
                    <em>Solicitada por:</em>
                    <strong>
                        {{ $requisicion->solicita->nombreCompleto ?? 'N/A' }}
                    </strong><br>
                    <em>Fecha:</em> {{ \Carbon\Carbon::parse($requisicion->created_at)->format('d/m/Y') }}
                </p>

            </td>
        </tr>
        <tr>
            <td style="background-color: #f1f1f1; text-align: center; padding: 15px; font-size: 12px; color: #777;">
                © {{ date('Y') }} Equipos y tractores del bajio. Todos los derechos reservados.
            </td>
        </tr>
    </table>
</body>

</html>
