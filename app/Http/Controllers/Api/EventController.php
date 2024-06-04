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

    public function getKardex($anio = null, $mes = null)
    {
        // Validar que el mes y el año sean válidos si se proporcionan
        if ($mes && $anio && !checkdate($mes, 1, $anio)) {
            return response()->json(['error' => 'Fecha no válida'], 400);
        }

        // Variables para la consulta
        $empleadosConEventosQuery = Empleado::query();

        if ($anio) {
            if ($mes) {
                // Filtrar por mes y año
                $startDate = \Carbon\Carbon::create($anio, $mes, 1)->startOfMonth();
                $endDate = \Carbon\Carbon::create($anio, $mes, 1)->endOfMonth();
            } else {
                // Filtrar por todo el año
                $startDate = \Carbon\Carbon::create($anio, 1, 1)->startOfYear();
                $endDate = \Carbon\Carbon::create($anio, 12, 31)->endOfYear();
            }

            $empleadosConEventosQuery = $empleadosConEventosQuery->whereHas('events', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })->with(['events' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->with(['activity']) // Cargar relación 'activity'
                    ->orderBy('date', 'asc'); // Ordenar por fecha ascendente
            }]);
        } else {
            // Si no se proporciona ni mes ni año, cargar todos los eventos
            $empleadosConEventosQuery = $empleadosConEventosQuery->whereHas('events')->with(['events' => function ($query) {
                $query->with(['activity']) // Cargar relación 'activity'
                    ->orderBy('date', 'asc'); // Ordenar por fecha ascendente
            }]);
        }

        // Obtener los empleados con eventos
        $empleadosConEventos = $empleadosConEventosQuery->get();

        // Mapear los datos de los empleados con sus eventos y sucursales
        $result = $empleadosConEventos->map(function ($empleado) {
            $sucursales = [];
            foreach ($empleado->events as $event) {
                $sucursalId = $event->sucursal->id;
                $sucursalName = $event->sucursal->nombre;
                if (!isset($sucursales[$sucursalId])) {
                    $sucursales[$sucursalId] = [
                        'nombre' => $sucursalName,
                        'conteo' => 0,
                        'eventos' => []
                    ];
                }
                $sucursales[$sucursalId]['conteo']++;
                $sucursales[$sucursalId]['eventos'][] = $event;  // Agregar el evento al array de eventos
            }
            return [
                'id' => $empleado->id,
                'empleado' => $empleado->nombreCompleto,
                'sucursales' => array_values($sucursales)
            ];
        });

        return response()->json($result);
    }
}
