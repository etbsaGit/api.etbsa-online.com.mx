<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerAssignmentRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $tracking;
    public $pdfContent;
    public $cliente;

    /**
     * Create a new message instance.
     */
    public function __construct($tracking, $cliente, $pdfContent)
    {
        $this->tracking = $tracking;
        $this->pdfContent = $pdfContent;
        $this->cliente = $cliente;
    }

    public function build()
    {
        return $this->subject('Solicitud de Asignación de Cliente')
            ->view('emails.customer_assigment_request')
            ->attachData(
                $this->pdfContent,
                'cotizacion_'.$this->tracking->id.'.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }

}
