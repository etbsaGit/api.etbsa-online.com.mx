<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ReferenciaComercial;
use App\Http\Requests\Intranet\ReferenciaComercial\StoreRequest;

class ReferenciaComercialController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ReferenciaComercial::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $referencia = ReferenciaComercial::create($request->validated());
        return $this->respondCreated($referencia);
    }

    /**
     * Display the specified resource.
     */
    public function show(ReferenciaComercial $referenciaComercial)
    {
        return $this->respond($referenciaComercial);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, ReferenciaComercial $referenciaComercial)
    {
        $referenciaComercial->update($request->validated());
        return $this->respond($referenciaComercial);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReferenciaComercial $referenciaComercial)
    {
        $referenciaComercial->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $referencias = ReferenciaComercial::where('cliente_id', $cliente->id)->get();
        return $this->respond($referencias);
    }
}
