<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Referencia;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Referencia\PutReferenciaRequest;
use App\Http\Requests\Intranet\Referencia\StoreReferenciaRequest;
use App\Models\Intranet\Kinship;

class ReferenciaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Referencia::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReferenciaRequest $request)
    {
        $referencia = Referencia::create($request->validated());
        return $this->respondCreated($referencia);
    }

    /**
     * Display the specified resource.
     */
    public function show(Referencia $referencia)
    {
        return $this->respond($referencia);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutReferenciaRequest $request, Referencia $referencia)
    {
        $referencia->update($request->validated());
        return $this->respond($referencia);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Referencia $referencia)
    {
        $referencia->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $referencias = Referencia::where('cliente_id', $cliente->id)
            ->with('kinship')
            ->get();
        return $this->respond($referencias);
    }

    public function getOptions()
    {
        $data = [
            'kinships' => Kinship::all()
        ];

        return $this->respond($data);
    }
}
