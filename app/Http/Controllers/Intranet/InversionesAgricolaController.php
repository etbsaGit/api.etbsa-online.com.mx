<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Cultivo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\InversionesAgricola;
use App\Http\Requests\Intranet\InversionesAgricola\StoreRequest;

class InversionesAgricolaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(InversionesAgricola::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $inversionesAgricola = InversionesAgricola::create($request->validated());
        return $this->respondCreated($inversionesAgricola);
    }

    /**
     * Display the specified resource.
     */
    public function show(InversionesAgricola $inversionesAgricola)
    {
        return $this->respond($inversionesAgricola);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, InversionesAgricola $inversionesAgricola)
    {
        $inversionesAgricola->update($request->validated());
        return $this->respond($inversionesAgricola);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InversionesAgricola $inversionesAgricola)
    {
        $inversionesAgricola->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente, int $year)
    {
        $agricolaInversion = InversionesAgricola::where('cliente_id', $cliente->id)
            ->where('year', $year)
            ->with('cultivo')
            ->get();

        // Calcular sumas
        $totales = [
            'total'    => $agricolaInversion->sum('total'),
            'costo'    => $agricolaInversion->sum('costo'),
        ];

        $data = [
            'inverciones' => $agricolaInversion,
            'totales'     => $totales,
        ];

        return $this->respond($data);
    }

    public function getOptions()
    {
        $data = [
            'cultivos' => Cultivo::orderBy('name', 'asc')->get(),
        ];

        return $this->respond($data);
    }
}
