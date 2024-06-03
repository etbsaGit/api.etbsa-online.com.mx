<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Activity;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Event\PutEventRequest;
use App\Http\Requests\Event\StoreEventRequest;

class EventController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eventos = Event::with('activity', 'sucursal', 'empleado')->get();
        return response()->json($eventos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $evento = Event::create($request->validated());
        return response()->json($evento);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return response()->json($event->load('sucursal', 'empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutEventRequest $request, Event $event)
    {
        $event->update($request->validated());
        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json('ok');
    }

    public function getPerDay($day)
    {
        $eventos = Event::whereDate('date', $day)
            ->with('activity', 'sucursal', 'empleado')
            ->get();
        return response()->json($eventos);
    }

    public function changeDay(Event $event, Request $request)
    {

        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $newDate = $request->input('date');

        $event->update([
            'date' => $newDate,
        ]);

        return response()->json($event);
    }

    public function changeCompleted(Activity $activity)
    {
        $activity->update(['completed' => !$activity->completed]);

        return response()->json($activity);
    }

    public function storeActivitiesEvent(Request $request, Event $event)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            '*.details' => ['required', 'string', 'max:255'],
            '*.completed' => ['nullable', 'boolean'],
        ]);

        // Añadir event_id a cada actividad validada
        $activitiesData = collect($validatedData)->map(function ($activityData) use ($event) {
            return array_merge($activityData, ['event_id' => $event->id]);
        });

        // Crear las actividades en la base de datos
        $activities = $activitiesData->map(function ($activityData) {
            return Activity::create($activityData);
        });

        // Devolver una respuesta con las actividades creadas
        return response()->json($activities, 201);
    }

    public function getKardex()
    {

        // $empleadosConEventos = Empleado::whereHas('events')->with('events.sucursal')->get();

        // return response()->json($empleadosConEventos);

        // Obtener empleados que tienen al menos un evento asociado y cargar los eventos con la sucursal
        $empleadosConEventos = Empleado::whereHas('events')->with('events.sucursal')->get();

        // Procesar los datos para estructurarlos de la forma deseada
        $result = $empleadosConEventos->map(function ($empleado) {
            $sucursales = [];

            // Contar las visitas del empleado a cada sucursal
            foreach ($empleado->events as $event) {
                $sucursalId = $event->sucursal->id;
                $sucursalName = $event->sucursal->nombre;

                if (!isset($sucursales[$sucursalId])) {
                    $sucursales[$sucursalId] = [
                        'nombre' => $sucursalName,
                        'conteo' => 0
                    ];
                }

                $sucursales[$sucursalId]['conteo']++;
            }

            return [
                'empleado' => $empleado->nombreCompleto,
                'sucursales' => array_values($sucursales) // Convertir a array para facilitar el manejo en el frontend
            ];
        });

        return response()->json($result);
    }
}
