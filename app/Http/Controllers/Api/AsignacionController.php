<?php

namespace App\Http\Controllers\Api;

use App\Models\Asignacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asignacion\PutRequest;
use App\Http\Requests\Asignacion\StoreRequest;

class AsignacionController extends Controller
{
    public function index()
    {
        return response()->json(Asignacion::paginate(5));
    }

    public function all()
    {
        return response()->json(Asignacion::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Asignacion::create($request->validated()));
    }

    public function show(Asignacion $asignacion)
    {
        return response()->json($asignacion);
    }

    public function update(PutRequest $request, Asignacion $asignacion)
    {
        $asignacion->update($request->validated());
        return response()->json($asignacion);
    }

    public function destroy(Asignacion $asignacion)
    {
        $asignacion->delete();
        return response()->json("ok");
    }
}