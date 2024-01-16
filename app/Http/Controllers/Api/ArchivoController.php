<?php

namespace App\Http\Controllers\Api;

use App\Models\Archivo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Archivo\PutRequest;
use App\Http\Requests\Archivo\StoreRequest;

class ArchivoController extends ApiController
{
    public function index()
    {
        return response()->json(Archivo::paginate(5));
    }

    public function all()
    {
        return response()->json(Archivo::with('asignable')->get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Archivo::create($request->validated()));
    }

    public function show(Archivo $archivo)
    {
        return response()->json($archivo->load('asignable'));
    }

    public function update(PutRequest $request, Archivo $archivo)
    {
        $archivo->update($request->validated());
        return response()->json($archivo);
    }

    public function destroy(Archivo $archivo)
    {
        $archivo->delete();
        return response()->json("ok");
    }
}
