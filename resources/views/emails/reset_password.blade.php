@component('mail::message')
# Restablece tu contraseña

Hola {{ $userName ?? 'usuario' }},

Recibimos una solicitud para restablecer tu contraseña. Para continuar, haz clic en el botón que aparece a continuación:

@component('mail::button', ['url' => $resetUrl, 'color' => 'success'])
Restablecer contraseña
@endcomponent

**Este enlace expirará en 60 minutos.**

Si no solicitaste este cambio, simplemente ignora este correo.

Gracias,
© {{ now()->year }} Corporativo ETBSA. Todos los derechos reservados.

@endcomponent
