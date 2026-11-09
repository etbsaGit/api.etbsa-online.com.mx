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
use App\Http\Requests\Vehicle\HistorialPlacasRequest;
use App\Models\VehicleHistorialPlacas;
use App\Models\VehiclesMarcas;
use App\Models\VehiclesModelos;
use App\Models\VehiclesTipos;

class VehicleController extends ApiController
{
    /**
     * Limpia los $appends pesados de Empleado en vehículos cargados.
     */
    private function optimizeVehicleEmpleados($vehicles): void
    {
        $items = $vehicles instanceof \Illuminate\Support\Collection ? $vehicles : collect([$vehicles]);
        $empAppends = ['nombreCompleto', 'apellidoCompleto'];

        foreach ($items as $vehicle) {
            if (!$vehicle) {
                continue;
            }
            if ($vehicle->relationLoaded('responsable') && $vehicle->responsable) {
                $vehicle->responsable->setAppends($empAppends);
            }
            if ($vehicle->relationLoaded('empleados') && $vehicle->empleados) {
                $vehicle->empleados->each->setAppends($empAppends);
            }
            if ($vehicle->relationLoaded('ordenesServicio') && $vehicle->ordenesServicio) {
                foreach ($vehicle->ordenesServicio as $orden) {
                    if ($orden->relationLoaded('solicitante') && $orden->solicitante) {
                        $orden->solicitante->setAppends($empAppends);
                    }
                }
            }
            if ($vehicle->relationLoaded('dispersionesGasolina') && $vehicle->dispersionesGasolina) {
                foreach ($vehicle->dispersionesGasolina as $disp) {
                    if ($disp->relationLoaded('solicitante') && $disp->solicitante) {
                        $disp->solicitante->setAppends($empAppends);
                    }
                    if ($disp->relationLoaded('bitacora') && $disp->bitacora) {
                        foreach ($disp->bitacora as $bit) {
                            if ($bit->relationLoaded('user') && $bit->user && $bit->user->relationLoaded('empleado') && $bit->user->empleado) {
                                $bit->user->empleado->setAppends($empAppends);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $paginated = Vehicle::filter($filters)->with([
            'departamento',
            'linea',
            'sucursal',
            'responsable:id,nombre,segundo_nombre,apellido_paterno,apellido_materno,vehicle_id',
            'empleados:empleados.id,empleados.nombre,empleados.segundo_nombre,empleados.apellido_paterno,empleados.apellido_materno,empleados.vehicle_id',
            'marca',
            'modelo',
            'tipo',
        ])->paginate(10);

        $this->optimizeVehicleEmpleados($paginated->getCollection());

        return $this->respond($paginated);
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
        $vehicle->load([
            'departamento',
            'linea',
            'sucursal',
            'marca',
            'modelo',
            'tipo',
            'responsable:id,nombre,segundo_nombre,apellido_paterno,apellido_materno,vehicle_id',
            'empleados:empleados.id,empleados.nombre,empleados.segundo_nombre,empleados.apellido_paterno,empleados.apellido_materno,empleados.vehicle_id',
            'historialPlacas',
            'ordenesServicio.proveedor',
            'ordenesServicio.solicitante:id,nombre,segundo_nombre,apellido_paterno,apellido_materno',
            'ordenesServicio.sucursalCargo',
            'ordenesServicio.departamentoCargo',
            'ordenesServicio.detalles.servicio.tipoServicio',
            'dispersionesGasolina.solicitante:id,nombre,segundo_nombre,apellido_paterno,apellido_materno',
            'dispersionesGasolina.registrador:id,name',
            'dispersionesGasolina.sucursalCargo',
            'dispersionesGasolina.departamentoCargo',
            'dispersionesGasolina.estatus',
        ]);

        $this->optimizeVehicleEmpleados($vehicle);

        return $this->respond($vehicle);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Vehicle $vehicle)
    {
        $oldPlacas = $vehicle->placas;

        $vehicle->update($request->validated());

        if (!empty($oldPlacas) && $vehicle->wasChanged('placas')) {
            $this->storeHistorialPlacas($vehicle->id, $vehicle->placas);
        }

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
        $empleados = Empleado::whereHas('estatus', function ($q) {
            $q->where('nombre', 'Activo');
        })
            ->select('id', 'nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno', 'vehicle_id')
            ->orderBy('apellido_paterno')
            ->get()
            ->each->setAppends(['nombreCompleto', 'apellidoCompleto']);

        $data = [
            'sucursales' => Sucursal::all(),
            'lineas' => Linea::all(),
            'departamentos' => Departamento::all(),
            'empleados' => $empleados,
            'marcas' => VehiclesMarcas::orderBy('nombre')->get(),
            'modelos' => VehiclesModelos::orderBy('nombre')->get(),
            'tipos' => VehiclesTipos::orderBy('nombre')->get(),
        ];
        return $this->respond($data);
    }

    public function asignEmployees(Request $request, Vehicle $vehicle)
    {
        $responsableId = $request->input('responsable_id');
        $usuariosIds = $request->input('usuarios', []);

        if (!is_array($usuariosIds)) {
            $usuariosIds = [];
        }

        // 1. Limpiar el responsable anterior de este vehículo en la tabla empleados
        Empleado::where('vehicle_id', $vehicle->id)->update(['vehicle_id' => null]);

        // 2. Si se seleccionó un responsable, asignarle vehicle_id en su registro de empleados
        if (!empty($responsableId)) {
            Empleado::where('id', $responsableId)->update(['vehicle_id' => $vehicle->id]);
        }

        // 3. Unir al responsable y a los usuarios para guardarlos todos en la tabla pivote usuarios_unidades
        $allEmployeeIds = array_values(array_unique(array_filter(array_merge(
            $responsableId ? [(int) $responsableId] : [],
            array_map('intval', $usuariosIds)
        ))));

        $vehicle->empleados()->sync($allEmployeeIds);

        return $this->respond(['message' => 'Vehicle employees synced successfully.']);
    }

    public function baja(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'motivo_baja' => 'required|string|max:255',
        ]);

        $vehicle->update([
            'activo' => false,
            'motivo_baja' => $request->input('motivo_baja'),
        ]);
        return $this->respond(['message' => 'Vehicle dado de baja con éxito']);
    }

    public function activar(Request $request, Vehicle $vehicle)
    {

        $vehicle->update([
            'activo' => true,
            'motivo_baja' => null,
        ]);
        return $this->respond(['message' => 'Vehicle activado con éxito']);
    }

    public function storeHistorialPlacas($vehicle_id, $placa)
    {
        $vehicleHistorialPlacas = VehicleHistorialPlacas::create([
            'vehicle_id' => $vehicle_id,
            'placa' => $placa,
        ]);
        return $this->respondCreated($vehicleHistorialPlacas);
    }

    public function exportServices(Vehicle $vehicle)
    {
        $vehicle->load([
            'departamento',
            'linea',
            'sucursal',
            'empleados:empleados.id,empleados.nombre,empleados.segundo_nombre,empleados.apellido_paterno,empleados.apellido_materno,empleados.vehicle_id',
            'historialPlacas',
            'ordenesServicio.proveedor',
            'ordenesServicio.solicitante:id,nombre,segundo_nombre,apellido_paterno,apellido_materno',
            'ordenesServicio.sucursalCargo',
            'ordenesServicio.departamentoCargo',
            'ordenesServicio.detalles.servicio.tipoServicio',
            'dispersionesGasolina.solicitante:id,nombre,segundo_nombre,apellido_paterno,apellido_materno',
            'dispersionesGasolina.registrador:id,name',
            'dispersionesGasolina.sucursalCargo',
            'dispersionesGasolina.departamentoCargo',
            'dispersionesGasolina.estatus',
        ]);

        $this->optimizeVehicleEmpleados($vehicle);

        $exporter = new \App\Exports\VehicleServicesExport($vehicle);
        $rawXlsx = $exporter->generateRawXlsx();

        $placaClean = preg_replace('/[^A-Za-z0-9_-]/', '_', $vehicle->placas ?: 'unidad_' . $vehicle->id);

        return $this->respond([
            'file_name' => "Historial_Servicios_{$placaClean}.xlsx",
            'file_base64' => base64_encode($rawXlsx),
        ]);
    }
}
