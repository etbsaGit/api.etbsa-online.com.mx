<?php

namespace App\Http\Controllers\Intranet;

use App\Exports\ReporteClientes\MaquinariaCliente;
use App\Exports\ReporteClientes\MaquinariaClienteExport;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ClasEquipo;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Condicion;
use App\Models\Intranet\Machine;
use App\Models\Intranet\Marca;
use App\Models\Intranet\StateEntity;
use App\Models\Intranet\TipoEquipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\VehiculosClientesExport;
use App\Models\Intranet\Cultivo;
use App\Models\Intranet\InversionesAgricola;
use App\Models\Intranet\TipoCultivo;
use Maatwebsite\Excel\Facades\Excel;

class ReporteClientesController extends ApiController
{
    public function maquinaria(Request $request)
    {
        $filters = $request->all();

        $clientes = Machine::query()
            ->filter($filters)
            ->with('cliente', 'condicion', 'marca', 'clasEquipo', 'tipoEquipo')
            ->paginate(10);

        return $this->respond([
            'maquinas' => $clientes,
            'filters' => [
                'marcas' => Marca::orderBy('name', 'asc')->get(),
                'condiciones' => Condicion::all(),
                'clasEquipos' => ClasEquipo::all(),
                'tiposEquipo' => TipoEquipo::orderBy('name', 'asc')->get(),
                'states' => StateEntity::orderBy('name')->get(),
                'anios' => Machine::select('anio')
                    ->distinct()
                    ->orderBy('anio')
                    ->pluck('anio'),
            ]
        ], 'Vehículos de clientes cargados con éxito');
    }

    public function exportMaquinaria(Request $request)
    {
        $filters = $request->all();
        $export = new MaquinariaClienteExport($filters);
        $data = $export->collection();

        if ($data->isEmpty()) {
            return $this->respond([
                'error' => 'No hay datos para exportar.'
            ]);
        }

        $fileContent = Excel::raw(
            $export,
            \Maatwebsite\Excel\Excel::XLSX
        );

        $base64 = base64_encode($fileContent);

        return $this->respond([
            'file_name' => 'maquinaria_clientes_report',
            'file_base64' => $base64,
        ]);
    }

    public function cultivo(Request $request)
    {
        $filters = $request->all();

        $clientes = InversionesAgricola::query()
            ->filter($filters)
            ->with('cliente', 'cultivo' )
            ->paginate(10);

        return $this->respond([
            'cultivos' => $clientes,
            'filters' => [
                'cultivo' => Cultivo::orderBy('name', 'asc')->get(),
                'tipo_cultivo' => TipoCultivo::all(),
                'ciclo' => InversionesAgricola::select('ciclo')
                    ->distinct()
                    ->pluck('ciclo'),
                'states' => StateEntity::orderBy('name')->get(),
            ]
        ], 'Cultivos de clientes cargados con éxito');
    }
}
