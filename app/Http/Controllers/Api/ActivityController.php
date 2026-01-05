<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Activity;
use App\Models\Empleado;
use App\Mail\TravelMailable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Activity\PutActivityRequest;
use App\Http\Requests\Activity\StoreActivityRequest;

class ActivityController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activities = Activity::with('event')->get();
        return $this->respond($activities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityRequest $request)
    {
        $activity = Activity::create($request->validated());
        return $this->respond($activity);
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        return $this->respond($activity->load('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutActivityRequest $request, Activity $activity)
    {
        $activity->update($request->validated());
        if ($activity->empleado && $activity->empleado->correo_institucional) {
            // Construir los datos para el correo
            $to_email = $activity->empleado->correo_institucional;
            $correo = [
                'to_name' => $activity->empleado->nombreCompleto,
                'activity_details' => $activity->details,
                'date'=>$activity->event->date
            ];

            // Enviar el correo usando el Mailable creado (TravelMailable en este caso)
            Mail::to($to_email)->send(new TravelMailable($correo));

            // Puedes agregar lógica adicional aquí después de enviar el correo si es necesario
        }
        return $this->respond($activity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        $activity->forceDelete();
        return $this->respond('ok');
    }

    public function showPerEvent(Event $event)
    {
        $travels = $event->travel;

        // Obtener todos los end_point de los travels
        $endPoints = [];
        foreach ($travels as $travel) {
            $endPoints[] = $travel->end_point;
        }

        // Consultar empleados cuya sucursal_id coincide con los end_point
        $employees = Empleado::whereIn('sucursal_id', $endPoints)->get();

        $activities = Activity::where('event_id', $event->id)
            ->with('empleado','event')
            ->get();

        $data = [
            'empleados' => $employees,
            'activities' => $activities
        ];

        return $this->respond($data);
    }

    public function getEmployees(Event $event)
    {
        $travels = $event->travel;

        // Obtener todos los end_point de los travels
        $endPoints = [];
        foreach ($travels as $travel) {
            $endPoints[] = $travel->end_point;
        }

        // Consultar empleados cuya sucursal_id coincide con los end_point
        $employees = Empleado::whereIn('sucursal_id', $endPoints)->get();

        return $this->respond($employees);
    }
}
