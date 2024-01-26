<?php

namespace App\Http\Controllers\Api;

use App\Models\Desvinculacion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Desvinculacion\PutRequest;
use App\Http\Requests\Desvinculacion\StoreRequest;

class DesvinculacionController extends ApiController
{
    public function index()
    {
        return response()->json(Desvinculacion::paginate(5));
    }

    public function all()
    {
        return response()->json(Desvinculacion::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Desvinculacion::create($request->validated()));
    }

    public function show(Desvinculacion $desvinculacion)
    {
        return response()->json($desvinculacion);
    }

    public function update(PutRequest $request, Desvinculacion $desvinculacion)
    {
        $desvinculacion->update($request->validated());
        return response()->json($desvinculacion);
    }

    public function destroy(Desvinculacion $desvinculacion)
    {
        $desvinculacion->delete();
        return response()->json("ok");
    }
}