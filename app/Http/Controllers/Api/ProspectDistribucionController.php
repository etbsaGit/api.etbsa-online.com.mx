<?php

namespace App\Http\Controllers\Api;

use App\Models\Prospect;
use Illuminate\Http\Request;
use App\Models\ProspectDistribucion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProspectDistribucion\StoreRequest;

class ProspectDistribucionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ProspectDistribucion::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $prospectDistribucion = ProspectDistribucion::create($request->validated());
        return $this->respondCreated($prospectDistribucion);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProspectDistribucion $prospectDistribucion)
    {
        return $this->respond($prospectDistribucion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, ProspectDistribucion $prospectDistribucion)
    {
        $prospectDistribucion->update($request->validated());
        return $this->respond($prospectDistribucion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProspectDistribucion $prospectDistribucion)
    {
        $prospectDistribucion->delete();
        return $this->respondSuccess();
    }

    public function getPerProspect(Prospect $prospect)
    {
        $prospectDistribucion = ProspectDistribucion::where('prospect_id', $prospect->id)->get();
        return $this->respond($prospectDistribucion);
    }
}
