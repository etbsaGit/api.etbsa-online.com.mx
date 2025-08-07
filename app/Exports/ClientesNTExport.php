<?php

// app/Exports/ClientesNTExport.php

namespace App\Exports;

use App\Models\Cliente;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClientesNTExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $clientes;

    public function __construct($clientes)
    {
        $this->clientes = $clientes;
    }

    public function collection()
    {
        return $this->clientes->map(function ($cliente) {
            return [
                'Nombre' => $cliente->nombre,
                'Telefono' => $cliente->telefono,
                'Estado' => $cliente->stateEntity->name ?? '',
                'Municipio' => $cliente->town->name ?? '',
                'Propias' => $cliente->hectareas_conectadas->hectareas_propias ?? 0,
                'Rentadas' => $cliente->hectareas_conectadas->hectareas_rentadas ?? 0,
                'Conectadas' => $cliente->hectareas_conectadas->hectareas_conectadas ?? 0,
                'Sin conectar' => $cliente->hectareas_conectadas->hectareas_sin_conectar ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Telefono',
            'Estado',
            'Municipio',
            'Propias',
            'Rentadas',
            'Conectadas',
            'Sin conectar'
        ];
    }
}
