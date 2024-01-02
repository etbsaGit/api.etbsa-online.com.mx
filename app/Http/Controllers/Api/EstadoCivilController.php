<?php

namespace App\Http\Controllers\Api;

use App\Models\EstadoCivil;
use App\Http\Controllers\ApiController;
use App\Http\Requests\EstadoCivil\PutRequest;
use App\Http\Requests\EstadoCivil\StoreRequest;

class EstadoCivilController extends ApiController
{
    public function index()
    {
        return response()->json(EstadoCivil::paginate(5));
    }

    public function all()
    {
        return response()->json(EstadoCivil::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(EstadoCivil::create($request->validated()));
    }

    public function show(EstadoCivil $estadoCivil)
    {
        return response()->json($estadoCivil);
    }

    public function update(PutRequest $request, EstadoCivil $estadoCivil)
    {
        $estadoCivil->update($request->validated());
        return response()->json($estadoCivil);
    }

    public function destroy(EstadoCivil $estadoCivil)
    {
        $estadoCivil->delete();
        return response()->json("ok");
    }
}