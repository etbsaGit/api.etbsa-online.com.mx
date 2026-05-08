@php
    $aprobado = $tracking->situacion->nombre === 'Autorizado';
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Actualización de Formalización de Pedido</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            margin: 30px auto;
            border-top: 6px solid #367c2b;
        }

        h2 {
            color: #367c2b;
            margin-bottom: 12px;
        }

        .data {
            background-color: #f3f6fa;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .comentarios {
            margin-top: 20px;
            background: #fff8e1;
            border-left: 4px solid #ffb300;
            padding: 12px;
            border-radius: 6px;
        }

        .data p {
            margin: 6px 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .brand {
            font-weight: bold;
            color: #367c2b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table td,
        table th {
            border: 1px solid #ccc;
            padding: 6px;
        }

        table .heading td {
            background-color: #e9eef5;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <h2>
                {{ $aprobado ? 'Pedido Aprobado' : 'Pedido No Aprobado' }}
            </h2>
        </div>

        <div class="content">

            <p>
                Hola,
            </p>

            <p>
                Se ha actualizado el estado de la solicitud con folio
                <strong>#{{ $tracking->folio }}</strong>.
            </p>

            <div class="status">
                <span>
                    {{ $aprobado ? '✅ APROBADO' : '❌ NO APROBADO' }}
                </span>
            </div>

            <div class="data">
                <table>
                    <tr>
                        <td class="label">Folio</td>
                        <td>#{{ $tracking->folio }}</td>
                    </tr>

                    <tr>
                        <td class="label">Revisado por</td>
                        <td>{{ $tracking->notificado->nombreCompleto }}</td>
                    </tr>

                    <tr>
                        <td class="label">Fecha de Revisión</td>
                        <td>
                            {{ $tracking->feedback->last()?->created_at?->format('d/m/Y H:i') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Estatus Actual del Pedido</td>
                        <td>{{ $tracking->situacion->nombre }}</td>
                    </tr>
                </table>
            </div>

            @if ($tracking->feedback->last()?->comentario)
                <div class="comentarios">
                    <strong>Comentarios:</strong><br>
                    {{ $tracking->feedback->last()?->comentario }}
                </div>
            @endif

            <p style="margin-top: 25px;">
                {!! $aprobado
                    ? 'El pedido fue autorizada y se puede continuar con el proceso correspondiente.'
                    : 'El pedido no fue autorizado. Favor de revisar los comentarios y realizar las correcciones necesarias.' !!}
            </p>

        </div>

        <div class="footer">
            <p>
                Este mensaje fue generado automáticamente por el sistema
                <span class="brand">Corporativo ETBSA</span>.
            </p>

            <p>
                Favor de no responder a este correo electrónico.
            </p>
        </div>

    </div>

</body>


</html>
