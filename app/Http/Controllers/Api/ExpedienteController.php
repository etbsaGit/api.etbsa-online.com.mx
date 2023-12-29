<?php

namespace App\Http\Controllers\Api;

use App\Models\Expediente;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expediente\PutRequest;
use App\Http\Requests\Expediente\StoreRequest;

class ExpedienteController extends Controller
{
    public function index()
    {
        return response()->json(Expediente::paginate(5));
    }

    public function all()
    {
        return response()->json(Expediente::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Expediente::create($request->validated()));
    }

    public function show(Expediente $expediente)
    {
        return response()->json($expediente);
    }

    public function update(PutRequest $request, Expediente $expediente)
    {
        $expediente->update($request->validated());
        return response()->json($expediente);
    }

    public function destroy(Expediente $expediente)
    {
        $expediente->delete();
        return response()->json("ok");
    }
}