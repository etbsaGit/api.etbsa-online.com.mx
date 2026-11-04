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

class SendOrdenAutorizacion extends Mailable
{
    use Queueable, SerializesModels;

    public $orden;
    public $ordenes;

    /**
     * Create a new message instance.
     *
     * @param mixed $orden Orden principal
     * @param mixed $ordenes Colección o array de todas las órdenes aprobadas bajo esta autorización
     */
    public function __construct($orden, $ordenes = null)
    {
        $this->orden = $orden;

        if (!empty($ordenes)) {
            $this->ordenes = $ordenes instanceof \Illuminate\Support\Collection ? $ordenes : collect($ordenes);
        } else {
            $this->ordenes = collect([$orden]);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $estatusNombre = $this->orden->estatus?->nombre ?? 'Actualización';
        $isAutorizado = stripos($estatusNombre, 'Autorizado') !== false && stripos($estatusNombre, 'No Autorizado') === false;
        $placas = $this->orden->vehicle?->placas ?? 'S/P';
        $autStr = $this->orden->autorizacion_compras ? "Aut: {$this->orden->autorizacion_compras}" : "Orden #{$this->orden->id}";

        if ($isAutorizado) {
            $totalOcs = count($this->ordenes);
            if ($totalOcs > 1) {
                $ocsList = $this->ordenes->pluck('id')->map(fn($id) => "#{$id}")->join(', ');
                $subject = "Órdenes de Compra AUTORIZADAS ({$autStr}) - OCs {$ocsList} - {$placas}";
            } else {
                $subject = "Orden de Compra AUTORIZADA #{$this->orden->id} ({$autStr}) - {$placas}";
            }
        } elseif (stripos($estatusNombre, 'No Autorizado') !== false) {
            $subject = "Orden de Compra NO AUTORIZADA #{$this->orden->id} - {$placas}";
        } else {
            $subject = "Actualización de Orden de Compra #{$this->orden->id} - {$placas}";
        }

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
            view: 'emails.OrdenesCompra.orden_autorizacion',
            with: [
                'orden' => $this->orden,
                'ordenes' => $this->ordenes,
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

        foreach ($this->ordenes as $oc) {
            // Archivo general si existe
            if (!empty($oc->cotizacion_url) && !in_array($oc->cotizacion_url, $attachedUrls, true)) {
                $attachedUrls[] = $oc->cotizacion_url;
                $pos = strpos($oc->cotizacion_url, 'ordenes_compra/');
                $relativePath = $pos !== false
                    ? substr($oc->cotizacion_url, $pos)
                    : ltrim(parse_url($oc->cotizacion_url, PHP_URL_PATH), '/');
                $relativePath = urldecode(explode('?', $relativePath)[0]);

                if (Storage::disk('s3')->exists($relativePath)) {
                    $attachments[] = Attachment::fromStorageDisk('s3', $relativePath)
                        ->as("Cotizacion_General_OC_{$oc->id}.pdf")
                        ->withMime('application/pdf');
                }
            }

            // Archivos de cada detalle autorizado
            if ($oc->detalles) {
                foreach ($oc->detalles as $det) {
                    if (!empty($det->cotizacion_url) && !in_array($det->cotizacion_url, $attachedUrls, true)) {
                        $attachedUrls[] = $det->cotizacion_url;

                        $pos = strpos($det->cotizacion_url, 'ordenes_compra/');
                        $relativePath = $pos !== false
                            ? substr($det->cotizacion_url, $pos)
                            : ltrim(parse_url($det->cotizacion_url, PHP_URL_PATH), '/');
                        $relativePath = urldecode(explode('?', $relativePath)[0]);

                        if (Storage::disk('s3')->exists($relativePath)) {
                            $provName = Str::slug($det->proveedor?->nombre ?? ($oc->proveedor?->nombre ?? 'proveedor'), '_');
                            $srvName = Str::slug($det->servicio?->nombre ?? 'concepto', '_');
                            $filename = "OC_{$oc->id}_Cotizacion_{$srvName}_{$provName}.pdf";

                            $attachments[] = Attachment::fromStorageDisk('s3', $relativePath)
                                ->as($filename)
                                ->withMime('application/pdf');
                        }
                    }
                }
            }
        }

        return $attachments;
    }
}
