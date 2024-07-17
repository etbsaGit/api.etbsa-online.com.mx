<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\ClienteRiego;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\ClienteRiego\StoreClienteRiegoRequest;

class ClienteRiegoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ClienteRiego::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRiegoRequest $request)
    {
        $clienteRiego = ClienteRiego::create($request->validated());
        return $this->respondCreated($clienteRiego);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClienteRiego $clienteRiego)
    {
        return $this->respond($clienteRiego);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClienteRiegoRequest $request, ClienteRiego $clienteRiego)
    {
        $clienteRiego->update($request->validated());
        return $this->respond($clienteRiego);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClienteRiego $clienteRiego)
    {
        $clienteRiego->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $clienteRiego = ClienteRiego::where('cliente_id', $cliente->id)
            ->with('riego')
            ->get();
        return $this->respond($clienteRiego);
    }
}
