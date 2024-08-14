<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use App\Models\TechniciansLog;
use App\Models\ActivityTechnician;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\TechniciansLog\StoreTechniciansLogRequest;

class TechniciansLogController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(TechniciansLog::with('tecnico', 'wo', 'activityTechnician')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTechniciansLogRequest $request)
    {
        $techniciansLog = TechniciansLog::create($request->validated());
        return $this->respondCreated($techniciansLog);
    }

    /**
     * Display the specified resource.
     */
    public function show(TechniciansLog $techniciansLog)
    {
        return $this->respond($techniciansLog);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTechniciansLogRequest $request, TechniciansLog $techniciansLog)
    {
        $techniciansLog->update($request->validated());
        return $this->respond($techniciansLog);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TechniciansLog $techniciansLog)
    {
        $techniciansLog->delete();
        return $this->respondSuccess();
    }

    public function getOptions(Empleado $empleado = null)
    {
        $user = Auth::user();

        // Si no se pasa el parámetro empleado o no existe, maneja el caso
        if (!$empleado) {
            $empleado = $user->empleado;
        }

        // Obtener el ID del estatus "En taller" usando el modelo Estatus
        $estatusIds = Estatus::whereIn('nombre', ['En taller', 'En campo'])->pluck('id');
        $today = Carbon::today();

        // Consultar las WorkOrder que coincidan con el tecnico_id y el estatus_taller_id
        $wos = WorkOrder::where('tecnico_id', $empleado->id)
            ->whereIn('estatus_taller_id', $estatusIds)
            ->get();

        $types = ActivityTechnician::all();

        // Consultar los TechniciansLog que coincidan con el tecnico_id y la fecha de hoy
        $logs = TechniciansLog::where('tecnico_id', $empleado->id)
            ->whereDate('fecha', $today)
            ->with('wo', 'activityTechnician')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        $data = [
            'logs' => $logs,
            'wos' => $wos,
            'types' => $types
        ];

        return $this->respond($data);
    }

    public function getPerTech(Empleado $empleado)
    {
        $logs = TechniciansLog::where('tecnico_id', $empleado->id)
            ->with('wo', 'activityTechnician')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return $this->respond($logs);
    }

    public function getPerTechDay(Empleado $empleado, $day)
    {
        // Asegúrate de que $day esté en formato de fecha válido
        $date = Carbon::parse($day)->toDateString(); // Convierte a formato de fecha YYYY-MM-DD

        $logs = TechniciansLog::where('tecnico_id', $empleado->id)
            ->whereDate('fecha', $date) // Filtra por la fecha proporcionada
            ->with('wo', 'activityTechnician')
            ->orderBy('hora_inicio', 'asc') // Ordena por la hora de inicio de la más temprana a la más tarde
            ->get();

        return $this->respond($logs);
    }
}
