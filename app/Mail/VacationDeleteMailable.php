<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class VacationDeleteMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $vacaciones_pasadas;
    public $vacaciones_futuras;

    /**
     * Create a new message instance.
     */
    public function __construct($data,$vacaciones_pasadas,$vacaciones_futuras)
    {
        $this->data = $data;
        $this->vacaciones_pasadas = $vacaciones_pasadas;
        $this->vacaciones_futuras = $vacaciones_futuras;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Solicitud de Vacaciones Eliminada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vacation_delete_email',
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
