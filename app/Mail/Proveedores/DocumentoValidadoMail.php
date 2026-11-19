<?php

namespace App\Mail\Proveedores;

use App\Models\ProveedoresDocs;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentoValidadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public ProveedoresDocs $documento;
    public ?User $validador;
    public string $estatusNombre;
    public string $urlPortal;

    /**
     * Create a new message instance.
     */
    public function __construct(ProveedoresDocs $documento, ?User $validador = null)
    {
        $this->documento = $documento;
        $this->validador = $validador;
        $this->estatusNombre = $documento->estatus?->nombre ?? 'Actualizado';

        $baseUrl = config('app.frontend_url') ?: config('app.url', 'https://corporativo.etbsa-online.com.mx');
        $this->urlPortal = rtrim($baseUrl, '/') . '/proveedores-autorizados';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $docNombre = $this->documento->nombre_doc ?: 'Documento';
        $proveedor = $this->documento->proveedor;
        $provNombre = $proveedor?->razon_social ?: 'Proveedor';

        if (strtolower($this->estatusNombre) === 'corregir') {
            $prefix = 'Documento Requiere Corrección';
        } elseif (strtolower($this->estatusNombre) === 'validado') {
            $prefix = 'Documento Validado Exitosamente';
        } else {
            $prefix = "Estatus de Documento: {$this->estatusNombre}";
        }

        return new Envelope(
            subject: "{$prefix}: {$docNombre} - {$provNombre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.Proveedores.documento_validado',
            with: [
                'documento' => $this->documento,
                'proveedor' => $this->documento->proveedor,
                'validador' => $this->validador,
                'estatusNombre' => $this->estatusNombre,
                'urlPortal' => $this->urlPortal,
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
        return [];
    }
}
