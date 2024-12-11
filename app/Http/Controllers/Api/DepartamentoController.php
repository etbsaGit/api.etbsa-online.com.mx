<?php

namespace App\Http\Controllers\Api;

use App\Models\Departamento;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Departamento\PutRequest;
use App\Http\Requests\Departamento\StoreRequest;

class DepartamentoController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();
        $departamentos = Departamento::filter($filters)->paginate(10);
        return $this->respond($departamentos);
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
