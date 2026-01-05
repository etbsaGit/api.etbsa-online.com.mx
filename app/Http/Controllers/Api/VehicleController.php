<?php

namespace App\Http\Controllers\Api;

use App\Models\Linea;
use App\Models\Estatus;
use App\Models\Vehicle;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Departamento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Vehicle\PutRequest;
use App\Http\Requests\Vehicle\StoreRequest;

class VehicleController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(Vehicle::filter($filters)->with('departamento', 'linea', 'sucursal', 'estatus', 'empleados')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $vehicle = Vehicle::create($request->validated());

        return $this->respondCreated($vehicle);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        return $this->respond($vehicle);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Vehicle $vehicle)
    {
        $vehicle->update($request->validated());
        return $this->respond($vehicle);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return $this->respondSuccess();
    }

    public function getforms()
    {
        $data = [
            'sucursales' => Sucursal::all(),
            'lineas' => Linea::all(),
            'departamentos' => Departamento::all(),
            'estatus' => Estatus::where('tipo_estatus', 'vehicle')->get(),
            'empleados' => Empleado::where('estatus_id', 5)->orderBy('apellido_paterno')->get(),
        ];
        return $this->respond($data);
    }

    public function asignEmployees(Request $request, Vehicle $vehicle)
    {
        $newEmployeeIds = $request->all(); // array directo: [99, 78, 71]

        // Paso 1: Desasociar a todos los empleados actuales del vehículo
        $vehicle->empleados()->update(['vehicle_id' => null]);

        // Paso 2: Asociar los nuevos empleados al vehículo
        Empleado::whereIn('id', $newEmployeeIds)->update(['vehicle_id' => $vehicle->id]);

        return $this->respond(['message' => 'Vehicle employees synced successfully.']);
    }

}
