<?php

namespace App\Mail\Vehicles;

use App\Models\VehiclesChecklist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VehicleChecklistAlertaMail extends Mailable
{
    use Queueable, SerializesModels;

    public VehiclesChecklist $checklist;

    /**
     * Create a new message instance.
     */
    public function __construct(VehiclesChecklist $checklist)
    {
        $this->checklist = $checklist;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $placas = $this->checklist->vehicle?->placas ?: 'S/P';
        $folio = $this->checklist->id;

        return new Envelope(
            subject: "Alerta de Revisión de Flotilla #{$folio}: Documentación Faltante - Unidad {$placas}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $faltantes = $this->checklist->detalles
            ? $this->checklist->detalles->where('existencia', false)->values()
            : collect();

        return new Content(
            view: 'emails.Vehicles.checklist_alerta',
            with: [
                'checklist' => $this->checklist,
                'vehicle' => $this->checklist->vehicle,
                'verificador' => $this->checklist->verificador,
                'detalles' => $this->checklist->detalles ?? collect(),
                'faltantes' => $faltantes,
                'urlPortal' => 'https://corporativo.etbsa-online.com.mx/#/revision-flotillas',
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
