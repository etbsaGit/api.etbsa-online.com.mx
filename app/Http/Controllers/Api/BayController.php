<?php

namespace App\Http\Controllers\Api;

use App\Models\Bay;
use App\Models\Linea;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bay\PutBayRequest;
use App\Http\Requests\Bay\StoreBayRequest;

class BayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Bay::with('tecnico', 'sucursal', 'linea')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBayRequest $request)
    {
        return response()->json(Bay::create($request->validated()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bay $bay)
    {
        return response()->json($bay->load('tecnico', 'sucursal', 'linea'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutBayRequest $request, Bay $bay)
    {
        $bay->update($request->validated());
        return response()->json($bay);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bay $bay)
    {
        $bay->delete();
        return response()->json("ok");
    }

    public function getAllData()
    {
        $sucursales = Sucursal::all();

        $lineas = Linea::all();

        return response()->json([
            'sucursales' => $sucursales,
            'lineas' => $lineas,
        ]);
    }

    public function getTechData(Sucursal $sucursal, Linea $linea)
    {
        // Obtener los técnicos que pertenecen a la sucursal y línea especificadas
        $tecnicos = Empleado::where('sucursal_id', $sucursal->id)
            ->where('linea_id', $linea->id)
            ->whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
            ->get();

        return response()->json($tecnicos);
    }

    public function getAgricolaBySucursal(Sucursal $sucursal)
    {
        // Asumiendo que 'agricola' es el nombre de la línea
        $agricolaLinea = Linea::where('nombre', 'agricola')->first();

        if (!$agricolaLinea) {
            return response()->json(['error' => 'Línea agricola no encontrada'], 404);
        }

        // Obtener bays por sucursal y línea agricola
        $bays = Bay::where('sucursal_id', $sucursal->id)
            ->where('linea_id', $agricolaLinea->id)
            ->with('tecnico')
            ->get();

        return response()->json($bays);
    }

    public function getConstruccionBySucursal(Sucursal $sucursal)
    {
        // Asumiendo que 'agricola' es el nombre de la línea
        $agricolaLinea = Linea::where('nombre', 'construccion')->first();

        if (!$agricolaLinea) {
            return response()->json(['error' => 'Línea agricola no encontrada'], 404);
        }

        // Obtener bays por sucursal y línea agricola
        $bays = Bay::where('sucursal_id', $sucursal->id)
            ->where('linea_id', $agricolaLinea->id)
            ->with('tecnico')
            ->get();

        return response()->json($bays);
    }
}
