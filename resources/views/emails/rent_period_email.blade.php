<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación Mensual de Equipo Rentado ETBSA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #4a4a4a;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 20px;
        }

        .content h2 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #4a4a4a;
        }

        .content p {
            margin: 5px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table th,
        .table td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 10px;
        }

        .table th {
            background-color: #4a4a4a;
            color: white;
        }

        .footer {
            text-align: center;
            padding: 10px;
            background-color: #f4f4f4;
            font-size: 14px;
            color: #666666;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4a4a4a;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }

        .btn:hover {
            background-color: #333333;
        }

        .image {
            text-align: center;
            margin: 10px 0;
        }

        .image img {
            max-width: 100%;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Facturación Mensual de Equipo Rentado ETBSA</h1>
        </div>
        <div class="content">
            <h3>Estimado/a:</h3>
            <p>Le informamos que se acerca la fecha de facturación del equipo rentado. Por favor, tome las acciones
                necesarias para garantizar la emisión de la factura en tiempo y forma.</p>
        </div>

        <div class="content">
            <h2>Detalles del Periodo de Renta</h2>
            <p><strong>Folio:</strong> {{ $data['folio'] }}</p>
            <p><strong>Fecha de inicio:</strong> {{ $data['start_date'] }}</p>
            <p><strong>Fecha de término:</strong> {{ $data['end_date'] }}</p>
            <p><strong>Día de facturación:</strong> {{ $data['billing_day'] }} de cada mes</p>
            <p><strong>Valor de renta:</strong> ${{ number_format($data['rental_value']) }}</p>
            <p><strong>Comentarios:</strong> {{ $data['comments'] ?? 'N/A' }}</p>
        </div>

        <div class="content">
            <h2>Empleado Asignado</h2>
            <p><strong>Nombre:</strong> {{ $data['empleado']['nombreCompleto'] }}</p>
            <p><strong>Correo institucional:</strong> {{ $data['empleado']['correo_institucional'] ?? 'N/A' }}</p>
            <p><strong>Teléfono:</strong> {{ $data['empleado']['telefono'] }}</p>
        </div>

        <div class="content">
            <h2>Datos del Cliente</h2>
            <p><strong>Nombre:</strong> {{ $data['cliente']['nombre'] }}</p>
            <p><strong>RFC:</strong> {{ $data['cliente']['rfc'] }}</p>
            <p><strong>Email:</strong> {{ $data['cliente']['email'] }}</p>
            <p><strong>Teléfono:</strong> {{ $data['cliente']['telefono'] }}</p>
            <p><strong>Dirección:</strong> {{ $data['cliente']['calle'] }}, {{ $data['cliente']['colonia'] }}, CP
                {{ $data['cliente']['codigo_postal'] }}, {{ $data['cliente']['town']['name'] ?? 'N/A' }},
                {{ $data['cliente']['stateEntity']['name'] ?? 'N/A' }}
            </p>
        </div>

        <div class="content">
            <h2>Datos del Equipo Rentado</h2>
            <p><strong>Modelo:</strong> {{ $data['rentalMachine']['model'] }}</p>
            <p><strong>Serie:</strong> {{ $data['rentalMachine']['serial'] }}</p>
            <p><strong>Descripción:</strong> {{ $data['rentalMachine']['description'] }}</p>
            @if (!empty($data['rentalMachine']['pic']))
                <div class="image">
                    <img src="{{ $data['rentalMachine']['pic'] }}" alt="Imagen del equipo">
                </div>
            @endif
        </div>

        @if (!empty($data['doc']))
            <div class="content">
                <h2>Documento Asociado</h2>
                <p>Puedes descargar el documento asociado a esta renta en el siguiente enlace:</p>
                <a href="{{ $data['doc'] }}" class="btn">Descargar Documento</a>
            </div>
        @endif


        <div class="footer">
            <p>Este correo es generado automáticamente. Por favor, no respondas a este mensaje.</p>
        </div>
    </div>
</body>

</html>
