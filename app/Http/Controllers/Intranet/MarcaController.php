<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Marca;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Marca\MarcaRequest;

class MarcaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Marca::filter($filters)->paginate(10),
            'Listado de marcas cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MarcaRequest $request)
    {
        $marca = Marca::create($request->validated());
        return $this->respondCreated(
            $marca,
            'Marca registrada correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $marca)
    {
        return $this->respond(
            $marca,
            'Detalle de la marca'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MarcaRequest $request, Marca $marca)
    {
        $marca->update($request->validated());
        return $this->respond(
            $marca,
            'Marca actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marca $marca)
    {
        $marca->delete();
        return $this->respondSuccess(
            'Marca eliminada correctamente'
        );
    }
}
