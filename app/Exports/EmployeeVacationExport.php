<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Festivo;
use App\Models\Empleado;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

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

        // Obtener festivos como array formateado correctamente
        $festivos = Festivo::pluck('fecha')->map(fn($date) => Carbon::parse($date)->toDateString())->toArray();

        // Obtener empleados con vacaciones válidas
        $employees = Empleado::where('sucursal_id', $sucursal_id)
            ->whereHas('vacationDays', function ($query) use ($start, $end, $festivos) {
                $query->where('validated', 1)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_termino', '>=', $end);
                            });
                    });
            })
            ->with(['vacationDays' => function ($query) use ($start, $end, $festivos) {
                $query->where('validated', 1)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_termino', '>=', $end);
                            });
                    })
                    ->select('empleado_id', 'fecha_inicio', 'fecha_termino');
            }])
            ->get();

        // Crear los datos para las filas
        $data = [];

        // Encabezados: nombres de los empleados
        $header = [];
        foreach ($employees as $employee) {
            $header[] = $employee->nombreCompleto;
        }

        // Inicializamos un arreglo para almacenar las fechas de vacaciones de cada empleado
        $vacationDates = [];
        foreach ($employees as $employee) {
            $vacationDays = [];

            foreach ($employee->vacationDays as $vacation) {
                $periodStart = Carbon::parse($vacation->fecha_inicio);
                $periodEnd = Carbon::parse($vacation->fecha_termino);

                $currentDate = $periodStart->copy();
                while ($currentDate->lte($periodEnd)) {
                    if ($currentDate->gte($start) && $currentDate->lte($end) &&
                        !in_array($currentDate->toDateString(), $festivos) && // Excluir festivos
                        $currentDate->dayOfWeek !== Carbon::SUNDAY // Excluir domingos
                    ) {
                        $vacationDays[] = $currentDate->format('d-m-Y');
                    }
                    $currentDate->addDay();
                }
            }

            // Evitar duplicados en las fechas
            $vacationDates[$employee->id] = array_values(array_unique($vacationDays));
        }

        // Determinar la cantidad máxima de filas necesarias
        $maxRows = max(array_map('count', $vacationDates));

        // Crear filas con las fechas de vacaciones de cada empleado
        for ($i = 0; $i < $maxRows; $i++) {
            $row = [];
            foreach ($employees as $employee) {
                $row[] = $vacationDates[$employee->id][$i] ?? ''; // Si no hay fecha, dejar vacío
            }
            $data[] = $row;
        }

        // Insertar el encabezado al principio
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
