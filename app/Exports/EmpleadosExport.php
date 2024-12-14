<?php

namespace App\Exports;

use App\Models\Empleado;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Schema;

class EmpleadosExport implements FromCollection, WithHeadings
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
        $query = Empleado::query();

        // Aplicar filtros dinámicos
        foreach ($this->filters as $key => $value) {
            if ($value) {
                $query->where($key, $value);
            }
        }

        // Obtener los empleados con las relaciones necesarias
        $empleados = $query->with(['escolaridad', 'departamento', 'estado_civil', 'jefe_directo', 'linea', 'puesto', 'sucursal', 'tipo_de_sangre', 'estatus'])->get();

        // Recorrer los empleados y transformar las columnas de ID en sus nombres correspondientes
        $empleados->transform(function ($empleado) {
            // Reemplazar los IDs por los nombres correspondientes
            $empleado = $this->replaceIdsWithNames($empleado);
            return $empleado;
        });

        return $empleados;
    }

    // Método que reemplaza los IDs por los nombres asociados
    private function replaceIdsWithNames($empleado)
    {
        // Definir las relaciones y los campos a reemplazar
        $relations = [
            'escolaridad_id' => 'escolaridad',
            'departamento_id' => 'departamento',
            'estado_civil_id' => 'estado_civil',
            'jefe_directo_id' => 'jefe_directo',
            'linea_id' => 'linea',
            'puesto_id' => 'puesto',
            'sucursal_id' => 'sucursal',
            'tipo_de_sangre_id' => 'tipo_de_sangre',
            'estatus_id' => 'estatus',
        ];

        // Iterar sobre las relaciones y reemplazar los IDs por los nombres
        foreach ($relations as $idColumn => $relation) {
            if ($empleado->{$relation}) {
                // Reemplazar el ID por el nombre de la relación
                $empleado->{$idColumn} = $empleado->{$relation} ? $empleado->{$relation}->nombre : null;
            }
        }

        return $empleado;
    }

    // Método que obtiene los encabezados dinámicamente
    public function headings(): array
    {
        // Obtener los nombres de las columnas de la tabla de empleados
        return Schema::getColumnListing((new Empleado())->getTable());
    }
}
