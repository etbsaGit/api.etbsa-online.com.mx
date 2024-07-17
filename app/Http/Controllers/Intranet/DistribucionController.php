<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Distribucion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Distribucion\StoreDistribucionRequest;

class DistribucionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Distribucion::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDistribucionRequest $request)
    {
        $distribucion = Distribucion::create($request->validated());
        return $this->respondCreated($distribucion);
    }

    /**
     * Display the specified resource.
     */
    public function show(Distribucion $distribucion)
    {
        return $this->respond($distribucion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDistribucionRequest $request, Distribucion $distribucion)
    {
        $distribucion->update($request->validated());
        return $this->respond($distribucion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Distribucion $distribucion)
    {
        $distribucion->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $machine = Distribucion::where('cliente_id', $cliente->id)->get();
        return $this->respond($machine);
    }
}
