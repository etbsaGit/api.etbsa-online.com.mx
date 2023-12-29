<?php

namespace App\Http\Controllers\Api;

use App\Models\Medicamento;
use App\Http\Controllers\Controller;
use App\Http\Requests\Medicamento\PutRequest;
use App\Http\Requests\Medicamento\StoreRequest;

class MedicamentoController extends Controller
{
    public function index()
    {
        return response()->json(Medicamento::paginate(5));
    }

    public function all()
    {
        return response()->json(Medicamento::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Medicamento::create($request->validated()));
    }

    public function show(Medicamento $medicamento)
    {
        return response()->json($medicamento);
    }

    public function update(PutRequest $request, Medicamento $medicamento)
    {
        $medicamento->update($request->validated());
        return response()->json($medicamento);
    }

    public function destroy(Medicamento $medicamento)
    {
        $medicamento->delete();
        return response()->json("ok");
    }
}