<?php

namespace App\Http\Controllers\Api;

use App\Models\Prospect;
use Illuminate\Http\Request;
use App\Models\ProspectCultivo;
use App\Models\Intranet\Cultivo;
use App\Models\Intranet\TipoCultivo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProspectCultivo\StoreRequest;

class ProspectCultivoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ProspectCultivo::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $prospectCultivo = ProspectCultivo::create($request->validated());
        return $this->respondCreated($prospectCultivo);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProspectCultivo $prospectCultivo)
    {
        return $this->respond($prospectCultivo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, ProspectCultivo $prospectCultivo)
    {
        $prospectCultivo->update($request->validated());
        return $this->respond($prospectCultivo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProspectCultivo $prospectCultivo)
    {
        $prospectCultivo->delete();
        return $this->respondSuccess();
    }

    public function getPerProspect(Prospect $prospect)
    {
        $machine = ProspectCultivo::where('prospect_id', $prospect->id)
            ->with('cultivo','tipoCultivo')
            ->get();
        return $this->respond($machine);
    }

    public function getOptions()
    {
        $data = [
            'cultivos' => Cultivo::orderBy('name', 'asc')->get(),
            'tiposCultivo' => TipoCultivo::all(),
        ];

        return $this->respond($data);
    }
}
