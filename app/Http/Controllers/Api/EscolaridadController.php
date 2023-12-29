<?php

namespace App\Http\Controllers\Api;

use App\Models\Escolaridad;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escolaridad\PutRequest;
use App\Http\Requests\Escolaridad\StoreRequest;

class EscolaridadController extends Controller
{
    public function index()
    {
        return response()->json(Escolaridad::paginate(5));
    }

    public function all()
    {
        return response()->json(Escolaridad::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Escolaridad::create($request->validated()));
    }

    public function show(Escolaridad $escolaridad)
    {
        return response()->json($escolaridad);
    }

    public function update(PutRequest $request, Escolaridad $escolaridad)
    {
        $escolaridad->update($request->validated());
        return response()->json($escolaridad);
    }

    public function destroy(Escolaridad $escolaridad)
    {
        $escolaridad->delete();
        return response()->json("ok");
    }
}