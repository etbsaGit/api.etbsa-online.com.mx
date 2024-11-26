<?php

namespace App\Http\Controllers\Api;

use App\Models\Bay;
use App\Models\Linea;
use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\WorkOrder\PutWorkOrderRequest;
use App\Http\Requests\WorkOrder\StoreWorkOrderRequest;
use App\Models\WorkOrderDoc;

class WorkOrderController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(WorkOrder::with('tecnico', 'estatus', 'estatusTaller', 'type', 'bay', 'sucursal', 'linea', 'workOrderDoc'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkOrderRequest $request)
    {
        $wo = WorkOrder::create($request->validated());
        $docs = $request->archivos;

        if ($wo->bay) {
            $statusEnUso = Estatus::where('nombre', 'En uso')->firstOrFail();
            $wo->bay->estatus()->associate($statusEnUso);
            $wo->bay->save();
        }

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $wod = WorkOrderDoc::create(['work_order_id' => $wo->id, 'name' => $doc['name'], 'extension' => $doc['extension']]);
                $relativePath  = $this->saveDoc($doc['base64'], $wod->default_path_folder);
                $updateData = ['path' => $relativePath];
                $wod->update($updateData);
            }
        }

        return $this->respond($wo);
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkOrder $workOrder)
    {
        return $this->respond($workOrder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutWorkOrderRequest $request, WorkOrder $workOrder)
    {
        $oldBayId = $workOrder->bay_id;
        $workOrder->update($request->validated());
        $docs = $request->archivos;
        $newBayId = $workOrder->bay_id;

        if ($oldBayId !== $newBayId) {

            if ($oldBayId) {
                $oldBahia = Bay::findOrFail($oldBayId);
                $oldBahia->estatus()->associate(Estatus::where('nombre', 'Disponible')->firstOrFail());
                $oldBahia->save();
            }

            if ($newBayId) {
                $newBahia = Bay::findOrFail($newBayId);
                $newBahia->estatus()->associate(Estatus::where('nombre', 'En uso')->firstOrFail());
                $newBahia->save();
            }
        }

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $wod = WorkOrderDoc::create(['work_order_id' => $workOrder->id, 'name' => $doc['name'], 'extension' => $doc['extension']]);
                $relativePath  = $this->saveDoc($doc['base64'], $wod->default_path_folder);
                $updateData = ['path' => $relativePath];
                $wod->update($updateData);
            }
        }

        return $this->respond($workOrder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkOrder $workOrder)
    {
        $bay = $workOrder->bay;

        if ($bay) {
            $estatusDisponibleId = Estatus::where('nombre', 'Disponible')->value('id');
            $bay->estatus_id = $estatusDisponibleId;
            $bay->save();
        }

        $workOrder->bay()->dissociate();

        $workOrder->save();

        $workOrder->delete();

        return $this->respondSuccess();
    }

    public function getWOS(Request $request)
    {
        $user = Auth::user();
        $filters = $request->all();

        if (!$user->hasRole('Admin') && $user->hasRole('Taller') && $user->empleado) {
            $filters['sucursal_id'] = $user->empleado->sucursal_id;
            $filters['linea_id'] = $user->empleado->linea_id;
        }

        $liberadoStatusId = Estatus::where('nombre', 'Liberado')->value('id');

        $wos = WorkOrder::filterWithPage($filters)
            ->with('tecnico', 'estatus', 'estatusTaller', 'type', 'bay', 'sucursal', 'linea', 'workOrderDoc')
            ->orderByRaw("estatus_taller_id != ? desc", [$liberadoStatusId])  // Ordena primero los que no son "Liberado"
            ->orderBy('fecha_ingreso', 'desc')  // Luego ordena por fecha de actualización
            ->paginate(10);

        return $this->respond($wos);

    }

    public function getForm()
    {
        $user = Auth::user();

        if ($user->empleado) {
            // Si el usuario tiene un empleado asociado, procedemos con la consulta de técnicos
            $tecnicos = Empleado::where('sucursal_id', $user->empleado->sucursal->id)
                ->where('linea_id', $user->empleado->linea->id)
                ->where('estatus_id', 5) // Agregamos la condición para el estatus
                ->whereHas('puesto', function ($query) {
                    $query->where('nombre', 'tecnico');
                })
                ->get();

            $bays = Bay::where('sucursal_id', $user->empleado->sucursal->id)
                ->where('linea_id', $user->empleado->linea->id)
                ->with('linea', 'sucursal')
                ->get();
        } else {
            // Si el usuario no tiene un empleado asociado, traemos todos los empleados con puesto 'tecnico' y estatus_id igual a 5
            $tecnicos = Empleado::where('estatus_id', 5) // Agregamos la condición para el estatus
                ->whereHas('puesto', function ($query) {
                    $query->where('nombre', 'tecnico');
                })
                ->get();

            $bays = Bay::with('linea', 'sucursal')->get();
        }


        $data = [
            'tecnicos' => $tecnicos,
            'bays' => $bays,
            'sucursales' => Sucursal::all(),
            'lineas' => Linea::all(),
            'estatus' => Estatus::where('tipo_estatus', 'woStatus')->get(),
            'estatus_taller' => Estatus::where('tipo_estatus', 'woTaller')->get(),
            'types' => Estatus::where('tipo_estatus', 'woType')->get()
        ];

        return $this->respond($data);
    }
}
