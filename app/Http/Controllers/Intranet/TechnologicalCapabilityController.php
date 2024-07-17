<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\TechnologicalCapability;
use App\Http\Requests\Intranet\TechnologicalCapability\StoreTechnologicalCapabilityRequest;

class TechnologicalCapabilityController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(TechnologicalCapability::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTechnologicalCapabilityRequest $request)
    {
        $technologicalCapability = TechnologicalCapability::create($request->validated());
        return $this->respondCreated($technologicalCapability);
    }

    /**
     * Display the specified resource.
     */
    public function show(TechnologicalCapability $technologicalCapability)
    {
        return $this->respond($technologicalCapability);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTechnologicalCapabilityRequest $request, TechnologicalCapability $technologicalCapability)
    {
        $technologicalCapability->update($request->validated());
        return $this->respond($technologicalCapability);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TechnologicalCapability $technologicalCapability)
    {
        $technologicalCapability->delete();
        return $this->respondSuccess();
    }
}
