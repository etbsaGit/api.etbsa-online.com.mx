<?php

namespace App\Http\Controllers\Api;

use App\Models\Escuela;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Escuela\PutRequest;
use App\Http\Requests\Escuela\StoreRequest;

class EscuelaController extends ApiController
{
    public function index()
    {
        return response()->json(Escuela::paginate(5));
    }

    public function all()
    {
        return response()->json(Escuela::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Escuela::create($request->validated()));
    }

    public function show(Escuela $escuela)
    {
        return response()->json($escuela);
    }

    public function update(PutRequest $request, Escuela $escuela)
    {
        $escuela->update($request->validated());
        return response()->json($escuela);
    }

    public function destroy(Escuela $escuela)
    {
        $escuela->delete();
        return response()->json("ok");
    }
}