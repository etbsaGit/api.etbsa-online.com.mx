<?php

namespace App\Http\Controllers\Api;

use App\Models\TipoDeDesvinculacion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\TipoDeDesvinculacion\PutRequest;
use App\Http\Requests\TipoDeDesvinculacion\StoreRequest;

class TipoDeDesvinculacionController extends ApiController
{
    public function index()
    {
        return response()->json(TipoDeDesvinculacion::paginate(5));
    }

    public function all()
    {
        return response()->json(TipoDeDesvinculacion::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(TipoDeDesvinculacion::create($request->validated()));
    }

    public function show(TipoDeDesvinculacion $tipoDeDesvinculacion)
    {
        return response()->json($tipoDeDesvinculacion);
    }

    public function update(PutRequest $request, TipoDeDesvinculacion $tipoDeDesvinculacion)
    {
        $tipoDeDesvinculacion->update($request->validated());
        return response()->json($tipoDeDesvinculacion);
    }

    public function destroy(TipoDeDesvinculacion $tipoDeDesvinculacion)
    {
        $tipoDeDesvinculacion->delete();
        return response()->json("ok");
    }
}