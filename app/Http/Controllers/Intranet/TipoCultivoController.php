<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\TipoCultivo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\TipoCultivo\TipoCultivoRequest;

class TipoCultivoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            TipoCultivo::filter($filters)->paginate(10),
            'Listado de tipos de cultivo cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoCultivoRequest $request)
    {
        $tipoCultivo = TipoCultivo::create($request->validated());
        return $this->respondCreated(
            $tipoCultivo,
            'Tipo de cultivo registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoCultivo $tipoCultivo)
    {
        return $this->respond(
            $tipoCultivo,
            'Detalle del tipo de cultivo'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TipoCultivoRequest $request, TipoCultivo $tipoCultivo)
    {
        $tipoCultivo->update($request->validated());

        return $this->respond(
            $tipoCultivo,
            'Tipo de cultivo actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoCultivo $tipoCultivo)
    {
        $tipoCultivo->delete();
        return $this->respondSuccess(
            'Tipo de cultivo eliminado correctamente'
        );
    }
}
