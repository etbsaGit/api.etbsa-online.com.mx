<?php

namespace App\Http\Controllers\Api;

use App\Models\Linea;
use App\Models\Empleado;
use App\Models\Technician;
use Illuminate\Http\Request;
use App\Models\Qualification;
use App\Models\LineaTechnician;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        $clave = $request->clave;
        $linea_id = $request->linea_id;
        $technician_id = $request->technician_id;
        $linea_technician_id = LineaTechnician::where('linea_id', $linea_id)
            ->where('technician_id', $technician_id)
            ->value('id');

        return response()->json(Qualification::create([
            'name' => $name,
            'clave' => $clave,
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
        $user = Auth::user();
        $roles = $user->roles->pluck('name')->toArray();

        $lineasConstruccion = Linea::where('nombre', 'Construccion')->first();
        $lineasAgricola = Linea::where('nombre', 'Agricola')->first();
        $sucursalId = $user->empleado?->sucursal->id;

        if (in_array('Admin', $roles)) {

            $tecnicosConstruccion = LineaTechnician::where('linea_id', $lineasConstruccion->id)
                ->whereHas('technician.empleado', function ($query) {
                    $query->where('estatus_id', 5);
                })
                ->with(['technician.empleado' => function ($query) {
                    $query->where('estatus_id', 5)
                        ->with(['linea', 'departamento', 'sucursal', 'puesto', 'qualification']);
                }])
                ->get();

            $tecnicosAgricola = LineaTechnician::where('linea_id', $lineasAgricola->id)
                ->whereHas('technician.empleado', function ($query) {
                    $query->where('estatus_id', 5);
                })
                ->with(['technician.empleado' => function ($query) {
                    $query->where('estatus_id', 5)
                        ->with(['linea', 'departamento', 'sucursal', 'puesto', 'qualification']);
                }])
                ->get();
        } elseif (in_array('Taller', $roles)) {

            $tecnicosConstruccion = LineaTechnician::where('linea_id', $lineasConstruccion->id)
                ->whereHas('technician.empleado', function ($query) use ($sucursalId) {
                    $query->where('estatus_id', 5)->where('sucursal_id', $sucursalId);
                })
                ->with(['technician.empleado' => function ($query) use ($sucursalId) {
                    $query->where('estatus_id', 5)->where('sucursal_id', $sucursalId)
                        ->with(['linea', 'departamento', 'sucursal', 'puesto', 'qualification']);
                }])
                ->get();

            $tecnicosAgricola = LineaTechnician::where('linea_id', $lineasAgricola->id)
                ->whereHas('technician.empleado', function ($query) use ($sucursalId) {
                    $query->where('estatus_id', 5)->where('sucursal_id', $sucursalId);
                })
                ->with(['technician.empleado' => function ($query) use ($sucursalId) {
                    $query->where('estatus_id', 5)->where('sucursal_id', $sucursalId)
                        ->with(['linea', 'departamento', 'sucursal', 'puesto', 'qualification']);
                }])
                ->get();
        }

        $empleadosTecnicosSinAsignar = Empleado::whereHas('puesto', function ($query) {
            $query->where('nombre', 'Tecnico');
        })
        ->whereDoesntHave('technician')
        ->whereHas('estatus', function ($query) use ($sucursalId) {
            $query->where('id', 5);
            // Solo agregar la condición de sucursal_id si sucursalId no es nulo
            if ($sucursalId !== null) {
                $query->where('sucursal_id', $sucursalId);
            }
        })
        ->with('linea', 'departamento', 'sucursal', 'puesto', 'qualification')
        ->get();


        $empleadosAgricolaSinTecnico = [];
        $empleadosConstruccionSinTecnico = [];

        foreach ($empleadosTecnicosSinAsignar as $empleado) {
            if ($empleado->linea->nombre === 'Agricola') {
                $empleadosAgricolaSinTecnico[] = $empleado;
            } elseif ($empleado->linea->nombre === 'Construccion') {
                $empleadosConstruccionSinTecnico[] = $empleado;
            }
        }

        return response()->json([
            'agricola' => $tecnicosAgricola,
            'construccion' => $tecnicosConstruccion,
            'sinAsignar' => [
                'agricola' => $empleadosAgricolaSinTecnico,
                'construccion' => $empleadosConstruccionSinTecnico
            ]
        ]);
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
