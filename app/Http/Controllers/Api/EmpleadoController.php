<?php

namespace App\Http\Controllers\Api;

use App\Models\Empleado;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Empleado\PutRequest;
use App\Http\Requests\Empleado\StoreRequest;

class EmpleadoController extends ApiController
{
    public function index()
    {
        return response()->json(Empleado::paginate(5));
    }

    public function all()
    {
        return response()->json(Empleado::with(['escolaridad_id','departamento_id','desvinculacion_id','estado_civil_id','expediente_id','jefe_directo_id', 'linea_id','puesto_id','sucursal_id','tipo_de_sangre_id','user_id'])->get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Empleado::create($request->validated()));
    }

    public function show(Empleado $empleado)
    {
        return response()->json($empleado);
    }

    public function update(PutRequest $request, Empleado $empleado)
    {
        $empleado->update($request->validated());
        return response()->json($empleado);
    }

    public function destroy(Empleado $empleado)
    {
        $empleado->delete();
        return response()->json("ok");
    }
}