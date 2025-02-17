<?php

namespace App\Http\Controllers\Api;

use App\Models\Prospect;
use Illuminate\Http\Request;
use App\Models\ProspectServicio;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProspectServicio\StoreRequest;

class ProspectServicioController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ProspectServicio::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $prospectServicio = ProspectServicio::create($request->validated());
        return $this->respondCreated($prospectServicio);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProspectServicio $prospectServicio)
    {
        return $this->respond($prospectServicio);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, ProspectServicio $prospectServicio)
    {
        $prospectServicio->update($request->validated());
        return $this->respond($prospectServicio);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProspectServicio $prospectServicio)
    {
        $prospectServicio->delete();
        return $this->respondSuccess();
    }

    public function getPerProspect(Prospect $prospect)
    {
        $prospectServicio = ProspectServicio::where('prospect_id', $prospect->id)->get();
        return $this->respond($prospectServicio);
    }
}
