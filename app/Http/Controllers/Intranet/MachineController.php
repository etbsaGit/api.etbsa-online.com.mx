<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Marca;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Machine;
use App\Models\Intranet\Condicion;
use App\Models\Intranet\ClasEquipo;
use App\Models\Intranet\TipoEquipo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Machine\StoreMachineRequest;

class MachineController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Machine::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMachineRequest $request)
    {
        $machine = Machine::create($request->validated());
        return $this->respondCreated($machine);
    }

    /**
     * Display the specified resource.
     */
    public function show(Machine $machine)
    {
        return $this->respond($machine);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMachineRequest $request, Machine $machine)
    {
        $machine->update($request->validated());
        return $this->respond($machine);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Machine $machine)
    {
        $machine->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $machine = Machine::where('cliente_id', $cliente->id)
            ->with('marca','condicion','clasEquipo','tipoEquipo')
            ->get();
        return $this->respond($machine);
    }

    public function getOptions()
    {
        $data = [
            'marcas' => Marca::all(),
            'condiciones' => Condicion::all(),
            'clasEquipos' => ClasEquipo::all(),
            'tiposEquipo' => TipoEquipo::all(),
        ];

        return $this->respond($data);
    }
}
