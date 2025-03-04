<?php

namespace App\Exports;

use App\Models\Empleado;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeVacationExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $start;
    protected $end;
    protected $sucursal_id;

    public function __construct($start, $end, $sucursal_id)
    {
        $this->start = $start;
        $this->end = $end;
        $this->sucursal_id = $sucursal_id;
    }

    public function collection()
    {
        $start = Carbon::parse($this->start);
        $end = Carbon::parse($this->end);
        $sucursal_id = $this->sucursal_id;


        // Obtener todos los empleados con sus días de vacaciones dentro del rango
        $employees = Empleado::where('sucursal_id', $sucursal_id)->whereHas('vacationDays', function ($query) use ($start, $end) {
            $query->where('validated', 1)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('fecha_inicio', [$start, $end])
                        ->orWhereBetween('fecha_termino', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('fecha_inicio', '<=', $start)
                                ->where('fecha_termino', '>=', $end);
                        });
                });
        })->with(['vacationDays' => function ($query) use ($start, $end) {
            $query->where('validated', 1)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('fecha_inicio', [$start, $end])
                        ->orWhereBetween('fecha_termino', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('fecha_inicio', '<=', $start)
                                ->where('fecha_termino', '>=', $end);
                        });
                })->select('empleado_id', 'fecha_inicio', 'fecha_termino');
        }])->get();

        // Crear los datos para las filas
        $data = [];

        // Primero agregamos los encabezados: solo los empleados
        $header = [];
        foreach ($employees as $employee) {
            $header[] = $employee->nombreCompleto;
        }

        // Ahora, por cada empleado, agregamos sus fechas de vacaciones
        $vacationDates = [];

        // Inicializamos un arreglo para cada empleado
        foreach ($employees as $employee) {
            $vacationDates[$employee->id] = [];
            foreach ($employee->vacationDays as $vacation) {
                $periodStart = Carbon::parse($vacation->fecha_inicio);
                $periodEnd = Carbon::parse($vacation->fecha_termino);

                $currentDate = $periodStart->copy();
                while ($currentDate->lte($periodEnd)) {
                    if ($currentDate->gte($start) && $currentDate->lte($end)) {
                        // Formateamos la fecha a dd/mm/aaaa
                        $vacationDates[$employee->id][] = $currentDate->format('d-m-Y');
                    }
                    $currentDate->addDay();
                }
            }
        }

        // Creamos filas para cada fecha
        $maxRows = max(array_map('count', $vacationDates)); // Determinamos la cantidad máxima de fechas para cualquier empleado

        for ($i = 0; $i < $maxRows; $i++) {
            $row = [];
            foreach ($employees as $employee) {
                // Añadimos la fecha correspondiente del empleado o dejamos vacío si no tiene más fechas
                $row[] = isset($vacationDates[$employee->id][$i]) ? $vacationDates[$employee->id][$i] : '';
            }
            $data[] = $row;
        }

        // Añadimos el encabezado con los empleados al principio
        array_unshift($data, $header);

        return collect($data);
    }

    public function headings(): array
    {
        return []; // Ya hemos agregado los encabezados en la colección
    }

    public function map($row): array
    {
        return $row; // Mapeamos cada fila tal como está
    }
}
