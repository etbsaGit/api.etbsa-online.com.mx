<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\Abastecimiento;
use App\Http\Requests\Intranet\Abastecimiento\AbastecimientoRequest;

class AbastecimientoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Abastecimiento::filter($filters)->paginate(10),
            'Listado de abastecimientos cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AbastecimientoRequest $request)
    {
        $abastecimiento = Abastecimiento::create($request->validated());
        return $this->respondCreated(
            $abastecimiento,
            'Abastecimiento registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Abastecimiento $abastecimiento)
    {
        return $this->respond(
            $abastecimiento,
            'Detalle del abastecimiento'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AbastecimientoRequest $request, Abastecimiento $abastecimiento)
    {
        $abastecimiento->update($request->validated());
        return $this->respond(
            $abastecimiento,
            'Abastecimiento actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Abastecimiento $abastecimiento)
    {
        $abastecimiento->delete();
        return $this->respondSuccess(
            'Abastecimiento eliminado correctamente'
        );
    }
}
