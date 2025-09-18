<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Cultivo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\AgricolaInversion;
use App\Http\Requests\Intranet\AgricolaInversion\StoreRequest;

class AgricolaInversionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(AgricolaInversion::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $agricolaInversion = AgricolaInversion::create($request->validated());
        return $this->respondCreated($agricolaInversion);
    }

    /**
     * Display the specified resource.
     */
    public function show(AgricolaInversion $agricolaInversion)
    {
        return $this->respond($agricolaInversion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, AgricolaInversion $agricolaInversion)
    {
        $agricolaInversion->update($request->validated());
        return $this->respond($agricolaInversion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AgricolaInversion $agricolaInversion)
    {
        $agricolaInversion->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente, int $year)
    {
        $agricolaInversion = AgricolaInversion::where('cliente_id', $cliente->id)
            ->where('year', $year)
            ->with('cultivo')
            ->get();

        // Calcular sumas
        $totales = [
            'total'    => $agricolaInversion->sum('total'),
            'costo'    => $agricolaInversion->sum('costo'),
            'precio'   => $agricolaInversion->sum('precio'),
            'ingreso'  => $agricolaInversion->sum('ingreso'),
            'utilidad' => $agricolaInversion->sum('utilidad'),
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
            'cultivos' => Cultivo::all(),
        ];

        return $this->respond($data);
    }
}
