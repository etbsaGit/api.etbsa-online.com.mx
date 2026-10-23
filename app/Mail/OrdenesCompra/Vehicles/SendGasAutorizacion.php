<?php

namespace App\Mail\OrdenesCompra\Vehicles;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendGasAutorizacion extends Mailable
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
        return new Envelope(
            subject: 'Actualización de Solicitud de Dispersión de Gasolina #' . ($this->orden->id ?? ''),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.OrdenesCompra.gas_autorizacion',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if (!empty($this->orden->cotizacion_url)) {
            $pos = strpos($this->orden->cotizacion_url, 'ordenes_compra/');
            $relativePath = $pos !== false
                ? substr($this->orden->cotizacion_url, $pos)
                : ltrim(parse_url($this->orden->cotizacion_url, PHP_URL_PATH), '/');

            if (Storage::disk('s3')->exists($relativePath)) {
                $attachments[] = Attachment::fromStorageDisk('s3', $relativePath)
                    ->as("Cotizacion_Orden_{$this->orden->id}.pdf")
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
