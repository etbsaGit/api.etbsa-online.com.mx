<?php

namespace App\Http\Controllers\Api;

use App\Models\Prospect;
use Illuminate\Http\Request;
use App\Models\ProspectRiego;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProspectRiego\StoreRequest;

class ProspectRiegoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ProspectRiego::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $prospectRiego = ProspectRiego::create($request->validated());
        return $this->respondCreated($prospectRiego);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProspectRiego $prospectRiego)
    {
        return $this->respond($prospectRiego);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, ProspectRiego $prospectRiego)
    {
        $prospectRiego->update($request->validated());
        return $this->respond($prospectRiego);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProspectRiego $prospectRiego)
    {
        $prospectRiego->delete();
        return $this->respondSuccess();
    }

    public function getPerProspect(Prospect $prospect)
    {
        $clienteRiego = ProspectRiego::where('prospect_id', $prospect->id)
            ->with('riego')
            ->get();
        return $this->respond($clienteRiego);
    }
}
