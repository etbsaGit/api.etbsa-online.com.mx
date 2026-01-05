<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Riego;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Riego\RiegoRequest;

class RiegoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Riego::filter($filters)->paginate(10),
            'Listado de riegos cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RiegoRequest $request)
    {
        $riego = Riego::create($request->validated());

        return $this->respondCreated(
            $riego,
            'Riego registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Riego $riego)
    {
        return $this->respond(
            $riego,
            'Detalle del riego'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RiegoRequest $request, Riego $riego)
    {
        $riego->update($request->validated());

        return $this->respond(
            $riego,
            'Riego actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Riego $riego)
    {
        $riego->delete();

        return $this->respondSuccess(
            'Riego eliminado correctamente'
        );
    }
}
