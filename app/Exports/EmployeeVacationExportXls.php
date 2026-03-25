<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class EmployeeVacationExportXls implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data['vacaciones'] as $vacation) {
            $rows[] = [
                $vacation['fecha_inicio'],
                $vacation['fecha_termino'],
                $vacation['fecha_regreso'],
                $vacation['periodo'],
                $vacation['anios_cumplidos'],
                $vacation['dias_periodo'],
                $vacation['subtotal'],
                $vacation['disfrutados'],
                $vacation['pendientes'],
                $vacation['created_at'],
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Fecha Inicio',
            'Fecha Término',
            'Fecha Regreso',
            'Periodo',
            'Años Cumplidos',
            'Días Periodo',
            'Subtotal',
            'Disfrutados',
            'Pendientes',
            'Creado',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $empleado = $this->data['empleado'];

                // 🔥 insertar espacio antes de todo (header + tabla)
                $event->sheet->insertNewRowBefore(1, 4);

                // 🔝 HEADER SUPERIOR
                $event->sheet->setCellValue('A1', 'Empleado: ' . $empleado['nombre']);
                $event->sheet->setCellValue('A2', 'Sucursal: ' . $empleado['sucursal']);
                $event->sheet->setCellValue('A3', 'Fecha de ingreso: ' . $empleado['fecha_ingreso']);

                // 🎨 estilo
                $event->sheet->getStyle('A1:A3')->getFont()->setBold(true);

                // 🎯 (opcional) juntar columnas para que se vea más limpio
                $event->sheet->mergeCells('A1:J1');
                $event->sheet->mergeCells('A2:J2');
                $event->sheet->mergeCells('A3:J3');
            },
        ];
    }
}
