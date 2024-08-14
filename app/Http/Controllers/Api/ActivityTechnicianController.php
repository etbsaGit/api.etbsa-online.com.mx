<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ActivityTechnician;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ActivityTechnician\PutActivityTechnicianRequest;
use App\Http\Requests\ActivityTechnician\StoreActivityTechnicianRequest;

class ActivityTechnicianController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ActivityTechnician::with('estatus')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityTechnicianRequest $request)
    {
        $activityTechnician = ActivityTechnician::create($request->validated());
        return $this->respondCreated($activityTechnician);
    }

    /**
     * Display the specified resource.
     */
    public function show(ActivityTechnician $activityTechnician)
    {
        return $this->respond($activityTechnician);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutActivityTechnicianRequest $request, ActivityTechnician $activityTechnician)
    {
        $activityTechnician->update($request->validated());
        return $this->respond($activityTechnician);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActivityTechnician $activityTechnician)
    {
        $activityTechnician->delete();
        return $this->respondSuccess();
    }
}
