<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\TechnologicalCapability;
use App\Http\Requests\Intranet\TechnologicalCapability\TechnologicalCapabilityRequest;

class TechnologicalCapabilityController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            TechnologicalCapability::filter($filters)->paginate(10),
            'Listado cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TechnologicalCapabilityRequest $request)
    {
        $technologicalCapability = TechnologicalCapability::create($request->validated());

        return $this->respondCreated(
            $technologicalCapability,
            'Registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(TechnologicalCapability $technologicalCapability)
    {
        return $this->respond(
            $technologicalCapability,
            'Detalle'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TechnologicalCapabilityRequest $request, TechnologicalCapability $technologicalCapability)
    {
        $technologicalCapability->update($request->validated());

        return $this->respond(
            $technologicalCapability,
            'Actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TechnologicalCapability $technologicalCapability)
    {
        $technologicalCapability->delete();

        return $this->respondSuccess(
            'Eliminado correctamente'
        );
    }
}
