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
use Illuminate\Support\Str;

class SendOrdenCompraVehicle extends Mailable
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
        $placas = $this->orden->vehicle?->placas ?? 'S/P';
        return new Envelope(
            subject: 'Solicitud de Orden de Compra #' . ($this->orden->id ?? '') . ' - ' . $placas,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.OrdenesCompra.orden_compra_solicitada',
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
        $attachedUrls = [];

        // 1. Archivo general de cabecera si existe
        if (!empty($this->orden->cotizacion_url)) {
            $pos = strpos($this->orden->cotizacion_url, 'ordenes_compra/');
            $relativePath = $pos !== false
                ? substr($this->orden->cotizacion_url, $pos)
                : ltrim(parse_url($this->orden->cotizacion_url, PHP_URL_PATH), '/');
            $relativePath = urldecode(explode('?', $relativePath)[0]);

            if (Storage::disk('s3')->exists($relativePath)) {
                $attachments[] = Attachment::fromStorageDisk('s3', $relativePath)
                    ->as("Cotizacion_General_Orden_{$this->orden->id}.pdf")
                    ->withMime('application/pdf');
                $attachedUrls[] = $this->orden->cotizacion_url;
            }
        }

        // 2. Archivos individuales de cada cotización en los detalles
        if ($this->orden->detalles) {
            foreach ($this->orden->detalles as $det) {
                if (!empty($det->cotizacion_url) && !in_array($det->cotizacion_url, $attachedUrls, true)) {
                    $attachedUrls[] = $det->cotizacion_url;

                    $pos = strpos($det->cotizacion_url, 'ordenes_compra/');
                    $relativePath = $pos !== false
                        ? substr($det->cotizacion_url, $pos)
                        : ltrim(parse_url($det->cotizacion_url, PHP_URL_PATH), '/');
                    $relativePath = urldecode(explode('?', $relativePath)[0]);

                    if (Storage::disk('s3')->exists($relativePath)) {
                        $provName = Str::slug($det->proveedor?->nombre ?? 'proveedor', '_');
                        $srvName = Str::slug($det->servicio?->nombre ?? 'concepto', '_');
                        $filename = "Cotizacion_{$srvName}_{$provName}_{$det->id}.pdf";

                        $attachments[] = Attachment::fromStorageDisk('s3', $relativePath)
                            ->as($filename)
                            ->withMime('application/pdf');
                    }
                }
            }
        }

        return $attachments;
    }
}
