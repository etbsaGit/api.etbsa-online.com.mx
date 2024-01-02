<?php

namespace App\Http\Controllers\Api;

use App\Models\TipoDeAsignacion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\TipoDeAsignacion\PutRequest;
use App\Http\Requests\TipoDeAsignacion\StoreRequest;

class TipoDeAsignacionController extends ApiController
{
    public function index()
    {
        return response()->json(TipoDeAsignacion::paginate(5));
    }

    public function all()
    {
        return response()->json(TipoDeAsignacion::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(TipoDeAsignacion::create($request->validated()));
    }

    public function show(TipoDeAsignacion $tipoDeAsignacion)
    {
        return response()->json($tipoDeAsignacion);
    }

    public function update(PutRequest $request, TipoDeAsignacion $tipoDeAsignacion)
    {
        $tipoDeAsignacion->update($request->validated());
        return response()->json($tipoDeAsignacion);
    }

    public function destroy(TipoDeAsignacion $tipoDeAsignacion)
    {
        $tipoDeAsignacion->delete();
        return response()->json("ok");
    }
}