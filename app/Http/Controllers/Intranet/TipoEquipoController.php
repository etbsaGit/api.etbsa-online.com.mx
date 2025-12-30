<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\TipoEquipo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\TipoEquipo\TipoEquipoRequest;

class TipoEquipoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            TipoEquipo::filter($filters)->paginate(10),
            'Listado de tipos de equipo cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TipoEquipoRequest $request)
    {
        $tipoEquipo = TipoEquipo::create($request->validated());

        return $this->respondCreated(
            $tipoEquipo,
            'Tipo de equipo registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoEquipo $tipoEquipo)
    {
        return $this->respond(
            $tipoEquipo,
            'Detalle del tipo de equipo'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TipoEquipoRequest $request, TipoEquipo $tipoEquipo)
    {
        $tipoEquipo->update($request->validated());

        return $this->respond(
            $tipoEquipo,
            'Tipo de equipo actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoEquipo $tipoEquipo)
    {
        $tipoEquipo->delete();

        return $this->respondSuccess(
            'Tipo de equipo eliminado correctamente'
        );
    }
}
