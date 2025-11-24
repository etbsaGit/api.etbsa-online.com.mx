<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\Intranet\Cliente;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClienteActualizadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cliente;
    public $empleado;

    public function __construct(Cliente $cliente, $empleado)
    {
        $this->cliente = $cliente;
        $this->empleado = $empleado;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Actualizacion de datos del cliente',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cliente_actualizado',
            with: [
                'cliente'  => $this->cliente,
                'empleado' => $this->empleado,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
