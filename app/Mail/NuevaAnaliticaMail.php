<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NuevaAnaliticaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $analitica;
    public $cliente;
    public $empleado;
    public $accion; // "creada" o "actualizada"

    /**
     * Create a new message instance.
     */
    public function __construct($analitica, $cliente, $empleado, string $accion = 'creada')
    {
        $this->analitica = $analitica;
        $this->cliente = $cliente;
        $this->empleado = $empleado;
        $this->accion = $accion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Solicitud de financiamiento',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.analitica_email',
            with: [
                'analitica' => $this->analitica,
                'cliente'   => $this->cliente,
                'empleado'  => $this->empleado,
                'accion'    => $this->accion,
            ]
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
