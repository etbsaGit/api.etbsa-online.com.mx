<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de visita a sucursal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, p {
            color: #333;
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        @php
            // Convertir la fecha de formato 'Y-m-d' a un objeto Carbon
            $carbonDate = \Carbon\Carbon::createFromFormat('Y-m-d', $data['date']);

            // Definir los nombres de los días de la semana y los meses en español
            $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

            // Obtener el nombre del día de la semana y del mes en español
            $nombre_dia = $dias_semana[$carbonDate->dayOfWeek];
            $nombre_mes = $meses[$carbonDate->month - 1]; // Restamos 1 porque los arrays empiezan en índice 0

            // Formatear la fecha en el formato deseado en español
            $formattedDate = $nombre_dia . ' ' . $carbonDate->day . ' de ' . $nombre_mes . ' del ' . $carbonDate->year;
        @endphp

        <h1>¡Hola {{$data['to_name']}}!</h1>

        <h3>Soy {{$data['from_name']}}</h3>

        <p>Estoy escribiéndote para informarte que el día {{$formattedDate}} estaré visitándote para revisar la siguiente actividad:</p>

        <p>{{$data['activity_details']}}</p>

        <p>{{$data['comments']}}</p>

        <p>Espero contar con tu presencia.</p>

        <p>Gracias por tu colaboración.</p>

        <p>Saludos cordiales,</p>
        <p>Equipos y Tractores del Bajío</p>

        <a href="https://corporativo.etbsa-online.com.mx/#/login" class="btn">Ir a la pagina Corporativo ETBSA</a>
    </div>
</body>
</html>
