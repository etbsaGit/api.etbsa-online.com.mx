<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verifica tu correo</title>
</head>
<body style="background-color: #f4f6f8; margin: 0; padding: 0; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 20px;">
                    <tr>
                        <td align="center" style="padding-bottom: 20px;">
                            <h1 style="color: #333333; font-size: 24px; margin: 0;">Verifica tu correo</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="color: #555555; font-size: 16px; line-height: 1.6; padding-bottom: 20px;">
                            <p style="margin: 0;">Hola,</p>
                            <p>Por favor verifica tu correo haciendo clic en el siguiente enlace:</p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <a href="{{ $verificationUrl }}" target="_blank" style="display: inline-block; padding: 12px 25px; background-color: #3490dc; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                                Verificar correo
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="color: #777777; font-size: 14px; line-height: 1.6; border-top: 1px solid #eeeeee; padding-top: 15px;">
                            <p style="margin: 0;">Si no solicitaste esta verificación, puedes ignorar este correo.</p>
                            <p style="margin: 0;">¡Gracias!</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
