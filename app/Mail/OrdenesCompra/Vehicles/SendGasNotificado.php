<?php

namespace App\Mail\OrdenesCompra\Vehicles;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendGasNotificado extends Mailable
{
    use Queueable, SerializesModels;

    public $orden;

    /**
     * Create a new message instance.
     */
    public function __construct($orden)
    {
        $this->orden = $orden;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $sucursalCargo = $this->orden->sucursalCargo?->nombre ?? 'Sin sucursal';
        $deptoCargo = $this->orden->departamentoCargo?->nombre ?? 'Sin departamento';

        return new Envelope(
            subject: "Aviso de Solicitud de Gasolina #{$this->orden->id} con cargo a {$sucursalCargo} / {$deptoCargo}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.OrdenesCompra.gas_notificados',
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
