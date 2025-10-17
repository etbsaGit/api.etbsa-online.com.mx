<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Cultivo;
use App\Models\Intranet\TipoCultivo;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ClienteCultivo;
use App\Http\Requests\Intranet\ClienteCultivo\StoreClienteCultivoRequest;

class ClienteCultivoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ClienteCultivo::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteCultivoRequest $request)
    {
        $clienteCultivo = ClienteCultivo::create($request->validated());
        return $this->respondCreated($clienteCultivo);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClienteCultivo $clienteCultivo)
    {
        return $this->respond($clienteCultivo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClienteCultivoRequest $request, ClienteCultivo $clienteCultivo)
    {
        $clienteCultivo->update($request->validated());
        return $this->respond($clienteCultivo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClienteCultivo $clienteCultivo)
    {
        $clienteCultivo->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $machine = ClienteCultivo::where('cliente_id', $cliente->id)
            ->with('cultivo','tipoCultivo')
            ->get();
        return $this->respond($machine);
    }

    public function getOptions()
    {
        $data = [
            'cultivos' => Cultivo::orderBy('name', 'asc')->get(),
            'tiposCultivo' => TipoCultivo::all(),
        ];

        return $this->respond($data);
    }
}
