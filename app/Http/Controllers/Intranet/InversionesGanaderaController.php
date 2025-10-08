<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Ganado;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\InversionesGanadera;
use App\Http\Requests\Intranet\InversionesGanadera\StoreRequest;

class InversionesGanaderaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(InversionesGanadera::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $inversionesGanadera = InversionesGanadera::create($request->validated());
        return $this->respondCreated($inversionesGanadera);
    }

    /**
     * Display the specified resource.
     */
    public function show(InversionesGanadera $inversionesGanadera)
    {
        return $this->respond($inversionesGanadera);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, InversionesGanadera $inversionesGanadera)
    {
        $inversionesGanadera->update($request->validated());
        return $this->respond($inversionesGanadera);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InversionesGanadera $inversionesGanadera)
    {
        $inversionesGanadera->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente, int $year)
    {
        $inversionesGanadera = InversionesGanadera::where('cliente_id', $cliente->id)
            ->where('year', $year)
            ->with('ganado')
            ->get();

        // Calcular sumas
        $totales = [
            'total'    => $inversionesGanadera->sum('total'),
            'costo'    => $inversionesGanadera->sum('costo'),
        ];

        $data = [
            'inverciones' => $inversionesGanadera,
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
