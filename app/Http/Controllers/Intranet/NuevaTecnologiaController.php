<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\NuevaTecnologia;
use App\Http\Requests\Intranet\NuevaTecnologia\NuevaTecnologiaRequest;

class NuevaTecnologiaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            NuevaTecnologia::filter($filters)->paginate(10),
            'Listado de nuevas tecnologias cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NuevaTecnologiaRequest $request)
    {
        $nuevaTecnologium = NuevaTecnologia::create($request->validated());

        return $this->respondCreated(
            $nuevaTecnologium,
            'Nueva tecnologia registrada correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(NuevaTecnologia $nuevaTecnologium)
    {
        return $this->respond(
            $nuevaTecnologium,
            'Detalle de la nueva tecnologia'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NuevaTecnologiaRequest $request, NuevaTecnologia $nuevaTecnologium)
    {
        $nuevaTecnologium->update($request->validated());

        return $this->respond(
            $nuevaTecnologium,
            'Nueva tecnologia actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NuevaTecnologia $nuevaTecnologium)
    {
        $nuevaTecnologium->delete();

        return $this->respondSuccess(
            'Nueva tecnologia eliminada correctamente'
        );
    }
}
