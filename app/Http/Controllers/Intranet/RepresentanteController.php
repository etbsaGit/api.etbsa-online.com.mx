<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\Controller;
use App\Models\Intranet\Representante;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Representante\PutRepresentanteRequest;
use App\Http\Requests\Intranet\Representante\StoreRepresentanteRequest;

use function Aws\load_compiled_json;

class RepresentanteController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Representante::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRepresentanteRequest $request)
    {
        $representante = Representante::create($request->validated());
        return $this->respondCreated($representante);
    }

    /**
     * Display the specified resource.
     */
    public function show(Representante $representante)
    {
        return $this->respond($representante);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRepresentanteRequest $request, Representante $representante)
    {
        $representante->update($request->validated());
        return $this->respond($representante);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Representante $representante)
    {
        $representante->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $cliente->load('representante.stateEntity', 'representante.town');

        // Obtener el representante del cliente
        $representante = $cliente->representante;

        // Verificar si se encontró el representante
        if (!$representante) {
            return $this->respond(['message' => 'Representante no encontrado para este cliente'], 404);
        }

        // Devolver los datos del representante con sus relaciones cargadas
        return $this->respond($representante);
    }

}
