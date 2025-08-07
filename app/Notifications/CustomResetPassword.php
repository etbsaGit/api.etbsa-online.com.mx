<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Lang;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword
{
    /**
     * Personaliza el correo de restablecimiento de contraseña.
     */
    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:9000');
        $resetUrl = "{$frontendUrl}/reset-password?token={$this->token}&email=" . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject(Lang::get('Restablece tu contraseña'))
            ->markdown('emails.reset_password', [
                'resetUrl' => $resetUrl,
                'userName' => $notifiable->name,
            ]);
    }
}
