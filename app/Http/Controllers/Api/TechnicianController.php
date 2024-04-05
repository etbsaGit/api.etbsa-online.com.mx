<?php

namespace App\Http\Controllers\Api;

use App\Models\Linea;
use App\Models\Technician;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\PutRequest;
use App\Http\Requests\Technician\ArrayRequest;
use App\Http\Requests\Technician\StoreRequest;
use App\Models\Empleado;
use App\Models\LineaTechnician;

class TechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $technicians = Technician::with(['lineaTechnician.linea'])
            ->orderBy('level', 'asc')
            ->get();

        $lineas = Linea::whereIn('nombre', ['Agricola', 'Construccion'])->get();

        return response()->json([
            'technicians' => $technicians,
            'lineas' => $lineas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $technician = Technician::create($request->validated());

        $lineas = $request->lineas;

        foreach ($lineas as $linea) {
            LineaTechnician::create([
                'technician_id' => $technician->id,
                'linea_id' => $linea
            ]);
        }

        return response()->json($technician);
    }

    /**
     * Display the specified resource.
     */
    public function show(Technician $technician)
    {
        return response()->json($technician);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Technician $technician)
    {

        $lineasNuevas = $request->lineas;

        $lineasActuales = $technician->lineaTechnician->pluck('linea_id')->toArray();

        $lineasAgregar = array_diff($lineasNuevas, $lineasActuales);
        $lineasEliminar = array_diff($lineasActuales, $lineasNuevas);

        foreach ($lineasEliminar as $lineaId) {
            $lineaTechnician = LineaTechnician::where('linea_id', $lineaId)
                ->where('technician_id', $technician->id)
                ->first();
            if ($lineaTechnician) {
                $lineaTechnician->delete();
            }
        }

        foreach ($lineasAgregar as $lineaId) {
            LineaTechnician::create([
                'linea_id' => $lineaId,
                'technician_id' => $technician->id,
            ]);
        }

        $technician->update($request->validated());

        return response()->json($technician);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technician $technician)
    {
        $technician->delete();
        return response()->json("ok");
    }

    public function getAll()
    {
        $lineaAgricola = Linea::where('nombre', 'Agricola')->first();

        $calificacionesAgricolas = $lineaAgricola->lineaTechnician()
            ->where('linea_id', $lineaAgricola->id)
            ->with(['technician','qualification'])
            ->get();
            $calificacionesAgricolas = $calificacionesAgricolas->sortBy(function ($lineaTechnician) {
                return $lineaTechnician->technician->level;
            })->values();

        $lineaConstruccion = Linea::where('nombre', 'Construccion')->first();
        $calificacionesConstruccion = $lineaConstruccion->lineaTechnician()
            ->where('linea_id', $lineaConstruccion->id)
            ->with(['technician','qualification'])
            ->get();
            $calificacionesConstruccion = $calificacionesConstruccion->sortBy(function ($lineaTechnician) {
                return $lineaTechnician->technician->level;
            })->values();

        return response()->json([
            'agricola' => $calificacionesAgricolas,
            'construccion' => $calificacionesConstruccion
        ]);
    }

    public function changeTypeTechnician(Empleado $empleado, Technician $technician)
    {
        $empleado->update([
            'technician_id' => $technician->id,
        ]);

        return response()->json(['message' => 'Técnico actualizado con éxito']);
    }

    public function getTechnicianLine(Linea $linea)
    {
        $lineaTechnicians = $linea->lineaTechnician()->with('technician')->get();
        $lineaTechnicians = $lineaTechnicians->sortBy(function ($lineaTechnician) {
            return $lineaTechnician->technician->level;
        })->values();

        $techniciansArray = $lineaTechnicians->map(function ($lineaTechnician) {
            return $lineaTechnician->technician;
        });

        return response()->json($techniciansArray);
    }
}
