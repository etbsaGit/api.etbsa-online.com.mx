<?php

namespace App\Http\Controllers\Api;

use App\Models\Bay;
use App\Models\Linea;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Bay\PutBayRequest;
use App\Http\Requests\Bay\StoreBayRequest;

class BayController extends ApiController
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

    public function getAll(Request $request)
    {
        $user = Auth::user();
        $filters = $request->all();

        // Obtener los roles del usuario
        $roles = $user->roles->pluck('name')->toArray();

        // Base query
        $query = Bay::with('tecnico', 'sucursal', 'linea');

        if (in_array('Servicio', $roles)) {
            // Si el usuario tiene rol de servicio, obtener todas las bays
            $bays = $query->filter($filters)->get();
        } elseif (in_array('Taller', $roles)) {
            // Si el usuario tiene rol de taller, filtrar por sucursal_id y linea_id del empleado
            $empleado = $user->empleado;
            if ($empleado && isset($empleado->sucursal_id) && isset($empleado->linea_id)) {
                $query->where('sucursal_id', $empleado->sucursal_id)
                      ->where('linea_id', $empleado->linea_id);
            }
            $bays = $query->get();
        } else {
            // Si el usuario no tiene los roles mencionados, devolver un error o un resultado vacío
            $bays = collect();
        }

        $data = [
            'bays' => $bays,
            'sucursales' => Sucursal::all(),
            'lineas' => Linea::all(),
        ];

        return $this->respond($data);
    }
}
