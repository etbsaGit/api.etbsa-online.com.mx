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
    public string $tipo;
    public ?string $comentarios;

    /**
     * Create a new message instance.
     *
     * @param mixed $orden
     * @param string $tipo 'revision' | 'solicitado' | 'correccion' | 'rechazo_revision' | 'corregida'
     * @param string|null $comentarios
     */
    public function __construct($orden, string $tipo = 'solicitado', ?string $comentarios = null)
    {
        $this->orden = $orden;
        $this->tipo = $tipo;
        $this->comentarios = $comentarios;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $placas = $this->orden->vehicle?->placas ?? 'S/P';
        $folio = $this->orden->id ?? '';

        $subject = match ($this->tipo) {
            'revision' => "Nueva Solicitud de Orden de Compra para Revisión #{$folio} - {$placas}",
            'corregida' => "Orden de Compra Corregida para Revisión #{$folio} - {$placas}",
            'correccion' => "Orden de Compra Requiere Corrección #{$folio} - {$placas}",
            'rechazo_revision' => "Orden de Compra No Aprobada en Revisión #{$folio} - {$placas}",
            'solicitado' => "Solicitud de Orden de Compra para Autorización #{$folio} - {$placas}",
            default => "Solicitud de Orden de Compra #{$folio} - {$placas}",
        };

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
            view: 'emails.OrdenesCompra.orden_compra_solicitada',
            with: [
                'orden' => $this->orden,
                'tipo' => $this->tipo,
                'comentarios' => $this->comentarios,
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
