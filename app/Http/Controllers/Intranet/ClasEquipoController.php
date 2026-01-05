<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\ClasEquipo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\ClasEquipo\ClasEquipoRequest;

class ClasEquipoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            ClasEquipo::filter($filters)->paginate(10),
            'Listado de clasificacion cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClasEquipoRequest $request)
    {
        $clasEquipo = ClasEquipo::create($request->validated());

        return $this->respondCreated(
            $clasEquipo,
            'Clasificacion registrada correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(ClasEquipo $clasEquipo)
    {
        return $this->respond(
            $clasEquipo,
            'Detalle de la clasificacion'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClasEquipoRequest $request, ClasEquipo $clasEquipo)
    {
        $clasEquipo->update($request->validated());

        return $this->respond(
            $clasEquipo,
            'Clasificacion actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClasEquipo $clasEquipo)
    {
        $clasEquipo->delete();

        return $this->respondSuccess(
            'Clasificacion eliminada correctamente'
        );
    }
}
