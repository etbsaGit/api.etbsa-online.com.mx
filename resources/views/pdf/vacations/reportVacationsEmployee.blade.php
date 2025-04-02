<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Vacaciones</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            background-color: #ffffff;
            margin: 0px;
            padding: 0px;
        }

        .container {
            background: white;
            padding: 0px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 100, 42, 0.2);
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 3px solid #447c1f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header {
            font-size: 24px;
            font-weight: bold;
            color: #447c1f;
            text-align: center;
            flex-grow: 1;
        }

        .avatar {
            width: 100px;
            height: 60px;
            object-fit: cover;
            margin-left: 15px;
        }

        .avatar2 {
            width: 120px;
            height: 60px;
            object-fit: cover;
            margin-left: 65%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #447c1f;
        }

        th {
            background-color: #447c1f;
            color: white;
            padding: 12px;
            text-align: center;
        }

        td {
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
        }

        .declaration {
            margin-top: 30px;
            font-size: 14px;
            text-align: justify;
            border-top: 2px solid #447c1f;
            padding-top: 15px;
        }

        .signature {
            margin-top: 50px;
            text-align: center;
            color: #447c1f;
            font-weight: bold;
        }

        .signature-line {
            margin-top: 40px;
            border-top: 2px solid #447c1f;
            width: 250px;
            margin-left: auto;
            margin-right: auto;
            padding-top: 5px;
        }

        .employee-name {
            margin-top: 10px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-container">
            <img src="storage/images/logo40.png" alt="Avatar" class="avatar">
            <img src="storage/images/logo.png" alt="Avatar" class="avatar2">
            <div class="header">Reporte de Vacaciones</div>
        </div>
        <p><strong>Empleado:</strong> {{ $empleado->nombreCompleto }}</p>
        <p><strong>Sucursal:</strong> {{ $empleado->sucursal->nombre }}</p>
        <p><strong>Del: </strong> {{ $start }}</p>
        <p><strong>Al: </strong> {{ $end }}</p>

        <table>
            <thead>
                <tr>
                    <th>Dias gozados de vacaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($empleado->vacationDetails as $index => $fecha)
                    <tr>
                        <td>{{ $fecha }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="declaration">
            Yo, <strong>{{ $empleado->nombreCompleto }}</strong>, confirmo que he tomado los días de vacaciones
            mencionados en este reporte. Declaro que esta información es correcta y corresponde a mis días de descanso
            oficial aprobados por la empresa.
        </p>
        <div class="signature">
            <p>Firma del empleado</p>
            <div class="signature-line"></div>
            <p class="employee-name">{{ $empleado->nombreCompleto }}</p>
        </div>
    </div>
</body>

</html>
