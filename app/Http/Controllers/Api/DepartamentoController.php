<?php

namespace App\Http\Controllers\Api;

use App\Models\Departamento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Departamento\PutRequest;
use App\Http\Requests\Departamento\StoreRequest;

class DepartamentoController extends Controller
{
    public function index()
    {
        return response()->json(Departamento::paginate(5));
    }

    public function all()
    {
        return response()->json(Departamento::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Departamento::create($request->validated()));
    }

    public function show(Departamento $departamento)
    {
        return response()->json($departamento);
    }

    public function update(PutRequest $request, Departamento $departamento)
    {
        $departamento->update($request->validated());
        return response()->json($departamento);
    }

    public function destroy(Departamento $departamento)
    {
        $departamento->delete();
        return response()->json("ok");
    }
}