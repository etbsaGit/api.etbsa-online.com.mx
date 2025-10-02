<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Analitica;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Analitica\StoreRequest;

class AnaliticaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Analitica::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $analitica = Analitica::create($request->validated());
        return $this->respondCreated($analitica);
    }

    /**
     * Display the specified resource.
     */
    public function show(Analitica $analitica)
    {
        return $this->respond($analitica);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Analitica $analitica)
    {
        $analitica->update($request->validated());
        return $this->respond($analitica);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Analitica $analitica)
    {
        $analitica->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $analiticas = Analitica::where('cliente_id', $cliente->id)->with('cliente')->get();

        $data = [
            'analiticas' => $analiticas,
        ];

        return $this->respond($data);
    }

    public function getReport(Analitica $analitica)
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        $analitica->load([
            'cliente.stateEntity',
            'cliente.town',
            'cliente.referencia.kinship',
            'cliente.referenciaComercial',
            'cliente.fincas.estatus',
            'cliente.ingresos'
        ]);

        // ✅ Agrícolas agrupadas por año con cálculos (solo actual y pasado)
        $agricolasPorAnio = $analitica->cliente->invercionesAgricolas
            ->groupBy('year')
            ->map(function ($items) {
                return [
                    'total'    => $items->sum('total'),
                    'costo'    => $items->sum('costo'),
                    'precio'   => $items->sum('precio'),
                    'ingreso'  => $items->sum('ingreso'),
                    'utilidad' => $items->sum('utilidad'),
                    'registros' => $items->values()->load('cultivo'),
                ];
            })
            ->only([$currentYear, $lastYear]); // 👈 Filtramos solo actual y pasado

        // ✅ Ganaderas agrupadas por año con cálculos (solo actual y pasado)
        $ganaderasPorAnio = $analitica->cliente->invercionesGanaderas
            ->groupBy('year')
            ->map(function ($items) {
                return [
                    'total'    => $items->sum('total'),
                    'costo'    => $items->sum('costo'),
                    'precio'   => $items->sum('precio'),
                    'ingreso'  => $items->sum('ingreso'),
                    'utilidad' => $items->sum('utilidad'),
                    'registros' => $items->values()->load('ganado'),
                ];
            })
            ->only([$currentYear, $lastYear]); // 👈 Filtramos solo actual y pasado

        // ✅ Unificamos todo en la respuesta
        $data = [
            'analitica' => $analitica,
            'totales'   => [
                'agricolas' => $agricolasPorAnio,
                'ganaderas' => $ganaderasPorAnio,
            ]
        ];

        return $this->respond($data);
    }
}
