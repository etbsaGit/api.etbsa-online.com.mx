<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Código de verificación</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .email-header {
            background-color: #447c1f;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .email-body {
            padding: 30px;
        }

        .code {
            font-size: 32px;
            font-weight: bold;
            color: #447c1f;
            text-align: center;
            margin: 30px 0;
        }

        .footer {
            font-size: 13px;
            color: #6c757d;
            text-align: center;
            padding: 20px;
        }

        @media (max-width: 600px) {
            .email-body {
                padding: 20px;
            }
        }

    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Verificación en dos pasos</h1>
        </div>
        <div class="email-body">
            <p>Hola {{ $user->name ?? 'usuario' }},</p>
            <p>Para continuar con tu inicio de sesión, por favor utiliza el siguiente código de verificación:</p>

            <div class="code">
                {{ $user->two_factor_code }}
            </div>

            <p>Este código expirará en <strong>10 minutos</strong>.</p>

            <p>Si tú no solicitaste este código, puedes ignorar este mensaje.</p>
        </div>
        <div class="footer">
            © {{ now()->year }} Corporativo ETBSA. Todos los derechos reservados.
        </div>
    </div>
</body>

</html>
