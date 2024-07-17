<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ClienteAbastecimiento;
use App\Http\Requests\Intranet\ClienteAbastecimiento\StoreClienteAbastecimientoRequest;

class ClienteAbastecimientoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ClienteAbastecimiento::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteAbastecimientoRequest $request)
    {
        $clienteAbastecimiento = ClienteAbastecimiento::create($request->validated());
        return $this->respondCreated($clienteAbastecimiento);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClienteAbastecimiento $clienteAbastecimiento)
    {
        return $this->respond($clienteAbastecimiento);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClienteAbastecimientoRequest $request, ClienteAbastecimiento $clienteAbastecimiento)
    {
        $clienteAbastecimiento->update($request->validated());
        return $this->respond($clienteAbastecimiento);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClienteAbastecimiento $clienteAbastecimiento)
    {
        $clienteAbastecimiento->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $machine = ClienteAbastecimiento::where('cliente_id', $cliente->id)
            ->with('abastecimiento')
            ->get();
        return $this->respond($machine);
    }
}
