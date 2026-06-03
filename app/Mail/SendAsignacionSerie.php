<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendAsignacionSerie extends Mailable
{
    use Queueable, SerializesModels;

    public $tracking;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($tracking, $pdfContent)
    {
        $this->tracking = $tracking;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        return $this->subject('Actualización de Asignación de Número de Serie a pedido')
            ->view('emails.actualizacion_asignacion_serie')
            ->attachData(
                $this->pdfContent,
                'cotizacion_' . $this->tracking->id . '.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }
}
