<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Notificación de creación de solicitud de financiamiento</title>
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
            /* verde John Deere */
        }

        h2 {
            color: #367c2b;
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
        <h2>📋 Solicitud de financiamiento {{ $accion === 'creada' ? 'creada' : 'actualizada' }}</h2>

        <p>Se ha {{ $accion === 'creada' ? 'registrado una nueva' : 'actualizado una' }} solicitud de financiamiento en
            el sistema de <span class="brand">Intranet ETBSA</span>.</p>

        <div class="data">
            <p><strong>Cliente:</strong> {{ $cliente->nombre ?? 'No especificado' }}</p>
            <p><strong>Empleado:</strong> {{ $empleado->name ?? 'No asignado' }}</p>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($analitica->fecha)->format('d/m/Y') }}</p>
        </div>

        <div class="footer">
            <p>Este mensaje fue enviado automáticamente por personal autorizado de <span class="brand">John
                    Deere</span>.</p>
            <p>Por favor, no responda a este correo.</p>
        </div>
    </div>
</body>

</html>
