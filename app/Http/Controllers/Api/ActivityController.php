<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\PutActivityRequest;
use App\Http\Requests\Activity\StoreActivityRequest;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activities = Activity::with('event')->get();
        return response()->json($activities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityRequest $request)
    {
        $activity = Activity::create($request->validated());
        return response()->json($activity);
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        return response()->json($activity->load('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutActivityRequest $request, Activity $activity)
    {
        $activity->update($request->validated());
        return response()->json($activity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();
        return response()->json('ok');
    }

    public function showPerEvent(Event $event)
    {
        $activities = $event->activity;
        return response()->json($activities);
    }
}
