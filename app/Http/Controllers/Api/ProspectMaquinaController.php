<?php

namespace App\Http\Controllers\Api;

use App\Models\Prospect;
use Illuminate\Http\Request;
use App\Models\Intranet\Marca;
use App\Models\ProspectMaquina;
use App\Models\Intranet\Condicion;
use App\Models\Intranet\ClasEquipo;
use App\Models\Intranet\TipoEquipo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProspectMaquina\StoreRequest;

class ProspectMaquinaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ProspectMaquina::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $prospectMaquina = ProspectMaquina::create($request->validated());
        return $this->respondCreated($prospectMaquina);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProspectMaquina $prospectMaquina)
    {
        return $this->respond($prospectMaquina);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, ProspectMaquina $prospectMaquina)
    {
        $prospectMaquina->update($request->validated());
        return $this->respond($prospectMaquina);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProspectMaquina $prospectMaquina)
    {
        $prospectMaquina->delete();
        return $this->respondSuccess();
    }

    public function getPerProspect(Prospect $prospect)
    {
        $prospectMaquina = ProspectMaquina::where('prospect_id', $prospect->id)
            ->with('marca', 'condicion', 'clasEquipo', 'tipoEquipo')
            ->get();
        return $this->respond($prospectMaquina);
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
