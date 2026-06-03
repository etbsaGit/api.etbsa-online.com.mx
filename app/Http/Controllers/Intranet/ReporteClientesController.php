<?php

namespace App\Http\Controllers\Intranet;

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

class ReporteClientesController extends ApiController
{
    public function vehicles(Request $request)
    {
        $filters = $request->all();

        $clientes = Machine::query()
            ->filter($filters)
            ->with('cliente','condicion','marca','clasEquipo','tipoEquipo')
            ->paginate(10);

        return $this->respond([
            'vehicles' => $clientes,
            'filters' => [
                'marcas' => Marca::orderBy('name', 'asc')->get(),
                'condiciones' => Condicion::all(),
                'clasEquipos' => ClasEquipo::all(),
                'tiposEquipo' => TipoEquipo::orderBy('name', 'asc')->get(),
                'states' => StateEntity::orderBy('name')->get(),
            ]
        ], 'Vehículos de clientes cargados con éxito');
    }
}
