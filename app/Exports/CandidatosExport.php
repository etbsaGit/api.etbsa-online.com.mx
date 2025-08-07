<?php

namespace App\Exports;

use App\Models\Candidato;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CandidatosExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $candidatos = Candidato::with([
            'requisicion.puesto',
            'requisicion.sucursal',
            'requisicion.linea',
            'requisicion.departamento',
            'requisicion.escolaridad',
            'requisicion.solicita',
            'requisicion.autoriza',
            'requisicion.voBo',
            'requisicion.recibe',
            'requisicion.competencias',
            'requisicion.herramientas',
        ])->get();

        return $candidatos->map(function ($c) {
            return collect([
                'Nombre' => $c->nombre,
                'Teléfono' => $c->telefono,
                'Status 1' => $c->status_1,
                'Fecha Entrevista 1' => $c->fecha_entrevista_1,
                'Forma de Reclutamiento' => $c->forma_reclutamiento,
                'Status 2' => $c->status_2,
                'Fecha Ingreso' => $c->fecha_ingreso,
                'Puesto' => optional(optional($c->requisicion)->puesto)->nombre,
                'Sucursal' => optional(optional($c->requisicion)->sucursal)->nombre,
                'Línea' => optional(optional($c->requisicion)->linea)->nombre,
                'Departamento' => optional(optional($c->requisicion)->departamento)->nombre,
                'Escolaridad' => optional(optional($c->requisicion)->escolaridad)->nombre,
                'Solicita' => optional(optional($c->requisicion)->solicita)->nombreCompleto,
                'Autoriza' => optional(optional($c->requisicion)->autoriza)->nombreCompleto,
                'VoBo' => optional(optional($c->requisicion)->voBo)->nombreCompleto,
                'Recibe' => optional(optional($c->requisicion)->recibe)->nombreCompleto,
                'Fecha Registro' => $c->created_at ? $c->created_at->format('d/m/y') : null,
                'Competencias' => optional($c->requisicion)->competencias?->pluck('nombre')->implode(', '),
                'Herramientas' => optional($c->requisicion)->herramientas?->pluck('nombre')->implode(', '),
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Teléfono',
            'Status 1',
            'Fecha Entrevista 1',
            'Forma de Reclutamiento',
            'Status 2',
            'Fecha Ingreso',
            'Puesto',
            'Sucursal',
            'Línea',
            'Departamento',
            'Escolaridad',
            'Solicita',
            'Autoriza',
            'VoBo',
            'Recibe',
            'Fecha Registro',
            'Competencias',
            'Herramientas',
        ];
    }
}
