<?php

namespace App\Http\Controllers\Api;

use App\Models\EstadoDeEstudio;
use App\Http\Controllers\ApiController;
use App\Http\Requests\EstadoDeEstudio\PutRequest;
use App\Http\Requests\EstadoDeEstudio\StoreRequest;

class EstadoDeEstudioController extends ApiController
{
    public function index()
    {
        return response()->json(EstadoDeEstudio::paginate(5));
    }

    public function all()
    {
        return response()->json(EstadoDeEstudio::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(EstadoDeEstudio::create($request->validated()));
    }

    public function show(EstadoDeEstudio $estadoDeEstudio)
    {
        return response()->json($estadoDeEstudio);
    }

    public function update(PutRequest $request, EstadoDeEstudio $estadoDeEstudio)
    {
        $estadoDeEstudio->update($request->validated());
        return response()->json($estadoDeEstudio);
    }

    public function destroy(EstadoDeEstudio $estadoDeEstudio)
    {
        $estadoDeEstudio->delete();
        return response()->json("ok");
    }
}