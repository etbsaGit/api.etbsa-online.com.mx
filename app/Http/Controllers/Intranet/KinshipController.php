<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Kinship;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Kinship\StoreKinshipRequest;

class KinshipController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Kinship::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKinshipRequest $request)
    {
        $kinship = Kinship::create($request->validated());
        return $this->respondCreated($kinship);
    }

    /**
     * Display the specified resource.
     */
    public function show(Kinship $kinship)
    {
        return $this->respond($kinship);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreKinshipRequest $request, Kinship $kinship)
    {
        $kinship->update($request->validated());
        return $this->respond($kinship);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kinship $kinship)
    {
        $kinship->delete();
        return $this->respondSuccess();
    }
}
