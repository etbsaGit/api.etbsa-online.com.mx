<?php

namespace App\Http\Controllers\Api;

use App\Models\Expediente;
use Illuminate\Support\Str;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Expediente\PutRequest;
use App\Http\Requests\Expediente\StoreRequest;

class ExpedienteController extends ApiController
{
    public function index()
    {
        return response()->json(Expediente::paginate(5));
    }

    public function all()
    {
        return response()->json(Expediente::with('archivable')->get());
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

    public function buscarExpedientePorArchivable($tipoModelo, $idModelo)
    {
    $tipoModelo = Str::startsWith($tipoModelo, 'App\\Models\\') ? $tipoModelo : 'App\\Models\\' . $tipoModelo;

    $expediente = Expediente::whereHas('archivable', function ($query) use ($tipoModelo, $idModelo) {
        $query->where('archivable_type', $tipoModelo)
            ->where('archivable_id', $idModelo);
    })->first();

    return response()->json($expediente);
    }
}
