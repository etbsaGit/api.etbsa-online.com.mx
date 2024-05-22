<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
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
        $eventos = Event::with('sucursal', 'empleado')->get();
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
            ->with('sucursal', 'empleado')
            ->get();
        return response()->json($eventos);
    }
}
