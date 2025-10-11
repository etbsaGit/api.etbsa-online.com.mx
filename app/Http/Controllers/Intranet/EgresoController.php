<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Egreso;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Egreso\StoreRequest;

class EgresoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Egreso::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $egreso = Egreso::create($request->validated());
        return $this->respondCreated($egreso);
    }

    /**
     * Display the specified resource.
     */
    public function show(Egreso $egreso)
    {
        return $this->respond($egreso);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Egreso $egreso)
    {
        $egreso->update($request->validated());
        return $this->respond($egreso);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Egreso $egreso)
    {
        $egreso->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente, int $year)
    {
        $egreso = Egreso::where('cliente_id', $cliente->id)
            ->where('year', $year)
            ->get();

        // Calcular sumas
        $totales = [
            'total'    => $egreso->sum('total'),
        ];

        $data = [
            'inverciones' => $egreso,
            'totales'     => $totales,
        ];

        return $this->respond($data);
    }
}
