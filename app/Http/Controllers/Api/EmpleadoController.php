<?php

namespace App\Http\Controllers\Api;

use App\Models\Empleado;
use App\Models\Expediente;
use Illuminate\Support\Facades\DB;
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
        return response()->json(Empleado::with(['escolaridad','departamento','desvinculacion','estado_civil','expediente','jefe_directo', 'linea','puesto','sucursal','tipo_de_sangre','user'])->get());
    }

    public function store(StoreRequest $request)
    {
        return DB::transaction(function () use ($request) {
            return tap(
                Empleado::create($request->validated()),
                function (Empleado $empleado) {
                    $empleado->archivable()->create(['nombre'=>$empleado->rfc. ' expediente']);
                });
        });
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