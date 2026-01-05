<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cultivo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Cultivo\CultivoRequest;

class CultivoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Cultivo::filter($filters)->paginate(10),
            'Listado de cultivos cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CultivoRequest $request)
    {
        $cultivo = Cultivo::create($request->validated());
        return $this->respondCreated(
            $cultivo,
            'Cultivo registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Cultivo $cultivo)
    {
        return $this->respond(
            $cultivo,
            'Detalle del cultivo'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CultivoRequest $request, Cultivo $cultivo)
    {
        $cultivo->update($request->validated());
        return $this->respond(
            $cultivo,
            'Cultivo actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cultivo $cultivo)
    {
        $cultivo->delete();
        return $this->respondSuccess(
            'cultivo eliminado correctamente'
        );
    }
}
