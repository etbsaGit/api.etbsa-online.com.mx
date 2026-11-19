<?php

namespace App\Mail\Proveedores;

use App\Models\Proveedores;
use App\Models\ProveedoresDocs;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentoProveedorSubidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Proveedores $proveedor;
    public ProveedoresDocs $documento;
    public ?User $uploader;
    public bool $esReemplazo;
    public string $urlPortal;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Proveedores $proveedor,
        ProveedoresDocs $documento,
        ?User $uploader = null,
        bool $esReemplazo = false
    ) {
        $this->proveedor = $proveedor;
        $this->documento = $documento;
        $this->uploader = $uploader;
        $this->esReemplazo = $esReemplazo;

        $baseUrl = config('app.frontend_url') ?: config('app.url', 'https://corporativo.etbsa-online.com.mx');
        $this->urlPortal = rtrim($baseUrl, '/') . '/proveedores-autorizados';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $accion = $this->esReemplazo ? 'Documento Actualizado' : 'Nuevo Documento Subido';
        $docNombre = $this->documento->nombre_doc ?: 'Documento';
        $provNombre = $this->proveedor->razon_social ?: 'Proveedor';

        return new Envelope(
            subject: "{$accion}: {$docNombre} - {$provNombre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.Proveedores.documento_subido',
            with: [
                'proveedor' => $this->proveedor,
                'documento' => $this->documento,
                'uploader' => $this->uploader,
                'esReemplazo' => $this->esReemplazo,
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
