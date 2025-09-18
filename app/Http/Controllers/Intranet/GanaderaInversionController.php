<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Ganado;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\GanaderaInversion;
use App\Http\Requests\Intranet\GanaderaInversion\StoreRequest;

class GanaderaInversionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(GanaderaInversion::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $ganaderaInversion = GanaderaInversion::create($request->validated());
        return $this->respondCreated($ganaderaInversion);
    }

    /**
     * Display the specified resource.
     */
    public function show(GanaderaInversion $ganaderaInversion)
    {
        return $this->respond($ganaderaInversion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, GanaderaInversion $ganaderaInversion)
    {
        $ganaderaInversion->update($request->validated());
        return $this->respond($ganaderaInversion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GanaderaInversion $ganaderaInversion)
    {
        $ganaderaInversion->delete();
        return $this->respondSuccess();
    }


    public function getPerCliente(Cliente $cliente, int $year)
    {
        $ganaderaInversion = GanaderaInversion::where('cliente_id', $cliente->id)
            ->where('year', $year)
            ->with('ganado')
            ->get();

        // Calcular sumas
        $totales = [
            'total'    => $ganaderaInversion->sum('total'),
            'costo'    => $ganaderaInversion->sum('costo'),
            'precio'   => $ganaderaInversion->sum('precio'),
            'ingreso'  => $ganaderaInversion->sum('ingreso'),
            'utilidad' => $ganaderaInversion->sum('utilidad'),
        ];

        $data = [
            'inverciones' => $ganaderaInversion,
            'totales'     => $totales,
        ];

        return $this->respond($data);
    }

    public function getOptions()
    {
        $data = [
            'ganados' => Ganado::all(),
        ];

        return $this->respond($data);
    }
}
