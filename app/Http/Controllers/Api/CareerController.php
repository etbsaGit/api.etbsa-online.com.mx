<?php

namespace App\Http\Controllers\Api;

use App\Models\Career;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Career\PutRequest;
use App\Http\Requests\Career\StoreRequest;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        return response()->json(Career::create($request->validated()));
    }

    public function storeNewCareer(Empleado $empleado)
    {
        // Verificar si el empleado tiene registros en la tabla careers
        if ($empleado->career()->exists()) {
            // Si el empleado tiene registros en la tabla careers, obtener todos los registros ordenados por fecha
            $careers = $empleado->career()->orderBy('date')->get();
            return response()->json($careers);
        } else {
            // Si el empleado no tiene registros en la tabla careers, crear el primer registro
            $fechaIngreso = $empleado->fecha_de_ingreso;
            $nuevaCarrera = new Career();
            $nuevaCarrera->title = "Aqui comenzamos";
            $nuevaCarrera->date = $fechaIngreso;
            $nuevaCarrera->description = "Este día te uniste a la familia de ETBSA";
            $nuevaCarrera->empleado_id = $empleado->id;
            $nuevaCarrera->save();
            return response()->json([$nuevaCarrera]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Career $career)
    {
        //
    }

    public function showPerEmpleado(Empleado $empleado)
    {
        $carreras = $empleado->career()->orderBy('date')->get();
        return response()->json($carreras);
    }

    public function empleadosWithAndWithoutCareer()
    {
        // Obtener empleados con carrera
        $empleadosConCarrera = Empleado::has('career')
            ->with(['career' => function ($query) {
                $query->orderBy('date');
            }, 'linea', 'departamento', 'sucursal', 'puesto'])
            ->orderBy('nombre')
            ->get();

        // Obtener empleados sin carrera
        $empleadosSinCarrera = Empleado::doesntHave('career')
            ->with(['linea', 'departamento', 'sucursal', 'puesto'])
            ->orderBy('nombre')
            ->get();

        $empleados = [
            'con_carrera' => $empleadosConCarrera,
            'sin_carrera' => $empleadosSinCarrera
        ];

        return response()->json($empleados);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Career $career)
    {
        $career->update($request->validated());
        return response()->json($career);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Career $career)
    {
        $career->delete();
        return response()->json("ok");
    }
}
