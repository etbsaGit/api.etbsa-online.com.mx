<?php

namespace App\Http\Controllers\Api;

use App\Models\Prospect;
use App\Models\ProspectAgp;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ProspectAgp\StoreRequest;

class ProspectAgpController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ProspectAgp::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $prospectAgp = ProspectAgp::create($request->validated());
        return $this->respondCreated($prospectAgp);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProspectAgp $prospectAgp)
    {
        return $this->respond($prospectAgp);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, ProspectAgp $prospectAgp)
    {
        $prospectAgp->update($request->validated());
        return $this->respond($prospectAgp);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProspectAgp $prospectAgp)
    {
        $prospectAgp->delete();
        return $this->respondSuccess();
    }

    public function getPerProspect(Prospect $prospect)
    {
        $prospectAgp = ProspectAgp::where('prospect_id', $prospect->id)->get();
        return $this->respond($prospectAgp);
    }
}
