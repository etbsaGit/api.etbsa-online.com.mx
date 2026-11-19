<?php

namespace App\Mail\Proveedores;

use App\Models\Proveedores;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoProveedorMail extends Mailable
{
    use Queueable, SerializesModels;

    public Proveedores $proveedor;
    public ?User $registrador;
    public string $urlPortal;

    /**
     * Create a new message instance.
     */
    public function __construct(Proveedores $proveedor, ?User $registrador = null)
    {
        $this->proveedor = $proveedor;
        $this->registrador = $registrador;

        $baseUrl = config('app.frontend_url') ?: config('app.url', 'https://corporativo.etbsa-online.com.mx');
        $this->urlPortal = rtrim($baseUrl, '/') . '/proveedores-autorizados';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $nombre = $this->proveedor->razon_social ?: 'Nuevo Proveedor';
        $rfc = $this->proveedor->rfc ? " ({$this->proveedor->rfc})" : '';

        return new Envelope(
            subject: "Nuevo Proveedor Registrado: {$nombre}{$rfc}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.Proveedores.nuevo_proveedor',
            with: [
                'proveedor' => $this->proveedor,
                'registrador' => $this->registrador,
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
