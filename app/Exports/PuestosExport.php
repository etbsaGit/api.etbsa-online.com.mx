<?php

namespace App\Exports;

use App\Models\Puesto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Schema;

class PuestosExport implements FromCollection, WithHeadings
{
    protected $filters;

    // Constructor con los filtros
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    // Método que obtiene los datos para exportar
    public function collection()
    {
        // Consulta básica con filtros aplicados
        $query = Puesto::query();

        // Aplicar filtros dinámicos
        foreach ($this->filters as $key => $value) {
            if ($value) {
                $query->where($key, $value);
            }
        }

        // Obtener los registros de la tabla de puestos
        return $query->get();
    }

    // Método que obtiene los encabezados dinámicamente
    public function headings(): array
    {
        // Obtener los nombres de las columnas de la tabla de puestos
        return Schema::getColumnListing((new Puesto())->getTable());
    }
}
