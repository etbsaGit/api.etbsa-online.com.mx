<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Condicion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Condicion\CondicionRequest;

class CondicionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Condicion::filter($filters)->paginate(10),
            'Listado de condiciones cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CondicionRequest $request)
    {
        $condicion = Condicion::create($request->validated());

        return $this->respondCreated(
            $condicion,
            'Condicion registrada correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Condicion $condicion)
    {
        return $this->respond(
            $condicion,
            'Detalle de la condicion'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CondicionRequest $request, Condicion $condicion)
    {
        $condicion->update($request->validated());

        return $this->respond(
            $condicion,
            'Condicion actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Condicion $condicion)
    {
        $condicion->delete();
        return $this->respondSuccess(
            'Condicion eliminada correctamente'
        );
    }
}
