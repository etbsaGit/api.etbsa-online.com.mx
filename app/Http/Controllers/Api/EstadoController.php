<?php

namespace App\Http\Controllers\Api;

use App\Models\Estado;
use App\Http\Controllers\Controller;
use App\Http\Requests\Estado\PutRequest;
use App\Http\Requests\Estado\StoreRequest;

class EstadoController extends Controller
{
    public function index()
    {
        return response()->json(Estado::paginate(5));
    }

    public function all()
    {
        return response()->json(Estado::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Estado::create($request->validated()));
    }

    public function show(Estado $estado)
    {
        return response()->json($estado);
    }

    public function update(PutRequest $request, Estado $estado)
    {
        $estado->update($request->validated());
        return response()->json($estado);
    }

    public function destroy(Estado $estado)
    {
        $estado->delete();
        return response()->json("ok");
    }
}