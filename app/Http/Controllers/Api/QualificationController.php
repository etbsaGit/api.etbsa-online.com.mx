<?php

namespace App\Http\Controllers\Api;

use App\Models\Linea;
use App\Models\Empleado;
use App\Models\Technician;
use Illuminate\Http\Request;
use App\Models\Qualification;
use App\Models\LineaTechnician;
use App\Http\Controllers\Controller;
use App\Http\Requests\Qualification\PutRequest;
use App\Http\Requests\Qualification\ArrayRequest;
use App\Http\Requests\Qualification\StoreRequest;

class QualificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Qualification::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $name = $request->name;
        $linea_id = $request->linea_id;
        $technician_id = $request->technician_id;
        $linea_technician_id = LineaTechnician::where('linea_id', $linea_id)
            ->where('technician_id', $technician_id)
            ->value('id');

        return response()->json(Qualification::create([
            'name' => $name,
            'linea_technician_id' => $linea_technician_id,
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Qualification $qualification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Qualification $qualification)
    {

        $qualification->update($request->validated());

        return response()->json($qualification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Qualification $qualification)
    {
        $qualification->delete();
        return response()->json("ok");
    }

    public function getEmployeeTechnician()
    {
        // Obtener todos los empleados con el puesto 'tecnico' y cargar la relación 'linea' y 'qualification'
        $empleadosTecnicos = Empleado::whereHas('puesto', function ($query) {
            $query->where('nombre', 'Tecnico');
        })->with('linea', 'departamento', 'sucursal', 'puesto', 'qualification','technician')->get();

        // Inicializar arreglos para los empleados de las diferentes líneas
        $empleadosAgricola = [];
        $empleadosConstruccion = [];

        // Separar los empleados según su línea
        foreach ($empleadosTecnicos as $empleado) {
            if ($empleado->linea->nombre === 'Agricola') {
                $empleadosAgricola[] = $empleado;
            } elseif ($empleado->linea->nombre === 'Construccion') {
                $empleadosConstruccion[] = $empleado;
            }
        }

        // Devolver los empleados separados por línea
        return [
            'agricola' => $empleadosAgricola,
            'construccion' => $empleadosConstruccion
        ];
    }

    public function getPerLine(Linea $linea)
    {
        if ($linea->nombre !== 'Agricola' && $linea->nombre !== 'Construccion') {
            return response()->json(['message' => 'El nombre de la línea no es válido'], 400);
        }

        $calificaciones = $linea->lineaTechnician()
            ->where('linea_id', $linea->id)
            ->with(['qualification', 'technician'])
            ->get();
            $calificaciones = $calificaciones->sortBy(function ($lineaTechnician) {
                return $lineaTechnician->technician->level;
            })->values();

        return response()->json($calificaciones);
    }


    public function storeQualifications(Empleado $empleado, ArrayRequest $request)
    {
        $qualificationIds = $request->qualifications;

        // Realizar la sincronización de las calificaciones al empleado
        $empleado->qualification()->sync($qualificationIds);

        return response()->json(['message' => 'Requisitos asignados correctamente al empleado']);
    }
}
