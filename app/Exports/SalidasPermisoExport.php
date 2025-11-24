<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\SalidaPermiso;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SalidasPermisoExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {

            // Formateo de fecha (solo fecha)
            $fecha = $item->date
                ? Carbon::parse($item->date)->format('d-m-Y')
                : '';

            // Función helper para formatear solo la hora
            $formatHour = fn($value) =>
            $value ? Carbon::parse($value)->format('H:i') : '';

            // Traducción del status
            $status = match ($item->status) {
                null => 'Pendiente',
                0 => 'Rechazado',
                1 => 'Autorizado',
                default => 'Desconocido'
            };

            return [
                $item->empleado->nombreCompleto ?? '',
                $item->sucursal->nombre ?? '',
                $fecha,
                $formatHour($item->start),
                $formatHour($item->end),
                // $formatHour($item->lunch_start),
                // $formatHour($item->lunch_end),
                $status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Empleado',
            'Sucursal',
            'Fecha',
            'Inicio',
            'Fin',
            // 'Inicio Comida',
            // 'Fin Comida',
            'Status',
        ];
    }
}
