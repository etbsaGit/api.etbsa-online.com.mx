<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\TipoEquipo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\TipoEquipo\StoreTipoEquipoRequest;

class TipoEquipoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(TipoEquipo::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTipoEquipoRequest $request)
    {
        $tipoEquipo = TipoEquipo::create($request->validated());
        return $this->respondCreated($tipoEquipo);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoEquipo $tipoEquipo)
    {
        return $this->respond($tipoEquipo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTipoEquipoRequest $request, TipoEquipo $tipoEquipo)
    {
        $tipoEquipo->update($request->validated());
        return $this->respond($tipoEquipo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoEquipo $tipoEquipo)
    {
        $tipoEquipo->delete();
        return $this->respondSuccess();
    }
}
