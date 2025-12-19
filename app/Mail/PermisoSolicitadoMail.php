<?php

namespace App\Mail;

use App\Models\SalidaPermiso;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class PermisoSolicitadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $permiso;
    public $empleado;
    public $user;

    public function __construct(SalidaPermiso $permiso, $empleado, $user)
    {
        $this->permiso = $permiso;
        $this->empleado = $empleado;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Solicitud permiso 2 horas',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.permiso_solicitado',
            with: [
                'permiso'  => $this->permiso,
                'empleado' => $this->empleado,
                'user' => $this->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
