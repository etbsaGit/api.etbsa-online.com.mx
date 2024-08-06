<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ClienteTechnology;
use App\Http\Requests\Intranet\ClienteTechnology\StoreClienteTechnologyRequest;

class ClienteTechnologyController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ClienteTechnology::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteTechnologyRequest $request)
    {
        $clienteTechnology = ClienteTechnology::create($request->validated());
        return $this->respondCreated($clienteTechnology);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClienteTechnology $clienteTechnology)
    {
        return $this->respond($clienteTechnology);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClienteTechnologyRequest $request, ClienteTechnology $clienteTechnology)
    {
        $clienteTechnology->update($request->validated());
        return $this->respond($clienteTechnology);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClienteTechnology $clienteTechnology)
    {
        $clienteTechnology->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $machine = ClienteTechnology::where('cliente_id', $cliente->id)
            ->with('nuevaTecnologia')
            ->get();
        return $this->respond($machine);
    }
}
