<?php

namespace App\Mail\OrdenesCompra\Vehicles;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendGasDispersada extends Mailable
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
        $estatusNombre = strtolower(optional($this->orden->estatus)->nombre ?? '');
        $subject = $estatusNombre === 'dispersada'
            ? 'Gasolina Dispersada - Solicitud #' . ($this->orden->id ?? '')
            : 'Solicitud de Dispersión de Gasolina No Autorizada #' . ($this->orden->id ?? '');

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.OrdenesCompra.gas_dispersada',
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
