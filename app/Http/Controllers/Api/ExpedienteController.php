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
        return $this->respond(Expediente::paginate(5));
    }

    public function all()
    {
        return $this->respond(Expediente::with('archivable')->get());
    }

    public function store(StoreRequest $request)
    {
        return $this->respond(Expediente::create($request->validated()));
    }

    public function show(Expediente $expediente)
    {
        return $this->respond($expediente);
    }

    public function update(PutRequest $request, Expediente $expediente)
    {
        $expediente->update($request->validated());
        return $this->respond($expediente);
    }

    public function destroy(Expediente $expediente)
    {
        $expediente->delete();
        return $this->respond("ok");
    }

    public function buscarExpedientePorArchivable($tipoModelo, $idModelo)
    {
    $tipoModelo = Str::startsWith($tipoModelo, 'App\\Models\\') ? $tipoModelo : 'App\\Models\\' . $tipoModelo;

    $expediente = Expediente::whereHas('archivable', function ($query) use ($tipoModelo, $idModelo) {
        $query->where('archivable_type', $tipoModelo)
            ->where('archivable_id', $idModelo);
    })->first();

    return $this->respond($expediente);
    }
}
