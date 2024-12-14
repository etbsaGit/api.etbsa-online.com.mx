<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Travel;
use App\Models\Activity;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Mail\TravelMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Event\StoreEventRequest;

class EventController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eventos = Event::with('activity', 'empleado', 'parentEvent', 'childEvents', 'travel', 'travel.startPointR', 'travel.endPointR')->get();

        $data = [
            'sucursales' => Sucursal::all(),
            'eventos' => $eventos,
        ];

        return $this->respond($data);
    }

    public function getAll(Request $request)
    {
        $filters = $request->all();
        $sucursalCorpId = Sucursal::where('nombre', 'Corporativo')->value('id');

        $user = Auth::user();

        if (!$user->hasRole('Admin') && $user->empleado && $user->empleado->sucursal_id != $sucursalCorpId) {
            $filters['end_point'] = $user->empleado->sucursal_id;

            $events = Event::filterByTravel($filters)
                ->with('activity', 'empleado', 'parentEvent', 'childEvents', 'travel', 'travel.startPointR', 'travel.endPointR')
                ->get();
        }

        if ($user->hasRole('Admin') || $user->empleado->sucursal_id == $sucursalCorpId) {
            $events = Event::filterByTravelAdmin($filters)
                ->with('activity', 'empleado', 'parentEvent', 'childEvents', 'travel', 'travel.startPointR', 'travel.endPointR')
                ->get();
        }


        $data = [
            'sucursales' => Sucursal::all(),
            'eventos' => $events,
        ];

        return $this->respond($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();
        $event = Event::create($data);

        foreach ($data['travels'] as $travelData) {
            $travelData['event_id'] = $event->id;
            Travel::create($travelData);
        }

        return $this->respondCreated($event);
    }


    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return response()->json($event->load('activity', 'empleado', 'parentEvent', 'childEvents.empleado', 'travel', 'travel.startPointR', 'travel.endPointR'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreEventRequest $request, Event $event)
    {
        $data = $request->validated();

        $event->update($data);

        if (isset($request['travels']) && is_array($request['travels'])) {
            foreach ($request['travels'] as $travelData) {
                if (isset($travelData['id'])) {
                    $travel = Travel::findOrFail($travelData['id']);
                    $travel->update($travelData);
                } else {
                    $travelData['event_id'] = $event->id;
                    Travel::create($travelData);
                }
            }
        }
        return $this->respond($event);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return $this->respondSuccess();
    }

    public function destroyTravel(Travel $travel)
    {
        $travel->delete();
        return $this->respondSuccess();
    }

    public function getPerDay($day)
    {
        $eventos = Event::whereDate('date', $day)
            ->with('activity', 'empleado', 'parentEvent', 'childEvents.empleado', 'travel', 'travel.startPointR', 'travel.endPointR')
            ->get();

        return $this->respond($eventos);
    }

    public function setEvent(Event $event, Request $request)
    {
        // Obtener el ID del evento desde el request
        $eventId = $request->input('event_id');

        // Asignar el ID del evento al modelo Event
        $event->event_id = $eventId;

        // Guardar el evento actualizado
        $event->save();

        // Retornar una respuesta adecuada
        return $this->respondSuccess();
    }

    public function quitEvent(Event $event)
    {
        $event->event_id = null;

        $event->save();

        return $this->respondSuccess();
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
            '*.comments' => ['nullable', 'string', 'max:255'],
            '*.empleado_id' => ['nullable', 'exists:empleados,id'],
            '*.completed' => ['nullable', 'boolean'],
        ]);


        // Añadir event_id a cada actividad validada
        $activitiesData = collect($validatedData)->map(function ($activityData) use ($event) {
            return array_merge($activityData, ['event_id' => $event->id]);
        });

        // Suponiendo que $activitiesData es una colección de datos de actividades
        $activities = $activitiesData->map(function ($activityData) use ($event) {
            // Crear la actividad en la base de datos
            $activity = Activity::create($activityData);

            // Validar la existencia de la relación 'empleado' y 'correo_institucional'
            if ($activity->empleado && $activity->empleado->correo_institucional) {
                // Construir los datos para el correo
                $to_email = $activity->empleado->correo_institucional;
                $correo = [
                    'from_name' => $event->empleado->nombreCompleto,
                    'to_name' => $activity->empleado->nombreCompleto,
                    'activity_details' => $activity->details,
                    'comments' => $activity->comments,
                    'date' => $activity->event->date
                ];

                // Enviar el correo usando el Mailable creado (TravelMailable en este caso)
                Mail::to($to_email)->send(new TravelMailable($correo));

                // Puedes agregar lógica adicional aquí después de enviar el correo si es necesario
            } else {
                // Manejar el caso donde no se puede enviar el correo porque falta información
                // Por ejemplo, registrar un log, lanzar una excepción, etc.
                // Dependiendo de tu flujo de aplicación y requisitos
            }

            return $activity; // Asegúrate de devolver la actividad creada
        });

        // Devolver una respuesta con las actividades creadas
        return response()->json($activities, 201);
    }

    // -------------------------------------------------------Kardex---------------------------------------------------------

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
                    ->with(['activity', 'travel']) // Cargar relación 'activity'
                    ->orderBy('date', 'asc'); // Ordenar por fecha ascendente
            }]);
        } else {
            // Si no se proporciona ni mes ni año, cargar todos los eventos
            $empleadosConEventosQuery = $empleadosConEventosQuery->whereHas('events')->with(['events' => function ($query) {
                $query->with(['activity', 'travel']) // Cargar relación 'activity'
                    ->orderBy('date', 'asc'); // Ordenar por fecha ascendente
            }]);
        }

        // Obtener los empleados con eventos
        $empleadosConEventos = $empleadosConEventosQuery->get();


        // Mapear los datos de los empleados con sus eventos y sucursales
        $result = $empleadosConEventos->map(function ($empleado) {
            $sucursales = [];

            foreach ($empleado->events as $event) {
                foreach ($event->travel as $travel) {
                    // Verificar si endPointR existe y no es nulo
                    if ($travel->endPointR) {
                        $sucursalId = $travel->endPointR->id;
                        $sucursalName = $travel->endPointR->nombre;

                        if (!isset($sucursales[$sucursalId])) {
                            $sucursales[$sucursalId] = [
                                'nombre' => $sucursalName,
                                'conteo' => 0,
                                'eventos' => []
                            ];
                        }

                        $sucursales[$sucursalId]['conteo']++;
                        $sucursales[$sucursalId]['eventos'][] = $event;
                    }
                }
            }

            return [
                'id' => $empleado->id,
                'empleado' => $empleado->nombreCompleto,
                'sucursales' => array_values($sucursales)
            ];
        });


        return response()->json($result);
    }

    // ----------------------------------------------------------------------------------------------------------------


    public function cloneEvent(Event $event, Request $request)
    {
        $newEvent = $event->replicate();

        $newEvent->event_id = null;
        $newEvent->date = $request->input('date');
        $newEvent->save();

        $oldTravels = $event->travel;

        foreach ($oldTravels as $travelOriginal) {
            $newTravel = $travelOriginal->replicate();
            $newTravel->event_id = $newEvent->id;
            $newTravel->save();
        }

        $oldActivities = $event->activity;

        if (!is_null($oldActivities)) {
            foreach ($oldActivities as $activityOriginal) {
                if ($activityOriginal->completed == 0) {
                    $newActivity = $activityOriginal->replicate();
                    $newActivity->event_id = $newEvent->id;
                    $newActivity->save();
                }
            }
        }
        return $this->respondSuccess();
    }
}
