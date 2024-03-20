<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Asignación de Evaluación</title>
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

        h1 {
            color: #333;
        }

        p {
            color: #666;
        }

        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Hola {{$data['to_name']}}!</h1>
        <p>Has sido asignado como encargado de la evaluación <strong>{{$data['survey_title']}}</strong></p>
        <p>Por favor, revisa tu cuenta para obtener más detalles</p>
        <p>Gracias por tu colaboración</p>
        <p>Saludos cordiales</p>
        <p>Equipos y Tractores del Bajio</p>
        <a href="https://corporativo.etbsa-online.com.mx/#/login" class="btn">Iniciar Sesión</a>
    </div>
</body>
</html>

