<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Solicitud de permiso</title>
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
    </style>
</head>

<body>
    <div class="container">
        <h2>Solicitud de Asignación de Cliente</h2>

        <p>
            Por medio del presente, el empleado
            <strong>{{ $tracking->vendedor->nombreCompleto }}</strong>
            solicita la asignación del siguiente cliente:
        </p>

        <div class="data">
            <h3>Datos del Cliente</h3>

            <p><strong>ID:</strong> {{ $cliente->id }}</p>
            <p><strong>Nombre:</strong> {{ $cliente->nombre }}</p>
            <p><strong>RFC:</strong> {{ $cliente->rfc }}</p>
            <p><strong>Correo electrónico:</strong> {{ $cliente->email }}</p>

            <br>

            <p>
                La presente solicitud se realiza debido a que actualmente se encuentra en proceso una cotización
                comercial con dicho cliente.
            </p>

            <p>
                Se adjunta la cotización correspondiente con folio
                <strong>#{{ $tracking->id }}</strong>
                para su revisión y evaluación.
            </p>

            <p>
                Agradecemos de antemano su atención y apoyo para dar seguimiento a esta solicitud.
            </p>
        </div>

        <div class="footer">
            <p>
                Este mensaje fue generado automáticamente por el sistema
                <span class="brand">Corporativo ETBSA</span>.
            </p>
            <p>Favor de no responder a este correo electrónico.</p>
        </div>
    </div>
</body>

</html>
