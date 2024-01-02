<?php

namespace App\Http\Controllers\Api;

use App\Models\Constelacion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Constelacion\PutRequest;
use App\Http\Requests\Constelacion\StoreRequest;

class ConstelacionController extends ApiController
{
    public function index()
    {
        return response()->json(Constelacion::paginate(5));
    }

    public function all()
    {
        return response()->json(Constelacion::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Constelacion::create($request->validated()));
    }

    public function show(Constelacion $constelacion)
    {
        return response()->json($$constelacion);
    }

    public function update(PutRequest $request, Constelacion $constelacion)
    {
        $constelacion->update($request->validated());
        return response()->json($constelacion);
    }

    public function destroy(Constelacion $constelacion)
    {
        $constelacion->delete();
        return response()->json("ok");
    }
}