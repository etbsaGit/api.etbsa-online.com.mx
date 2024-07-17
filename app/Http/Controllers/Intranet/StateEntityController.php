<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Intranet\StateEntity;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\StateEntities\PutStateEntityRequest;
use App\Http\Requests\Intranet\StateEntities\StoreStateEntityRequest;

class StateEntityController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(StateEntity::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStateEntityRequest $request)
    {
        $stateEntity = StateEntity::create($request->validated());
        return $this->respondCreated($stateEntity);
    }

    /**
     * Display the specified resource.
     */
    public function show(StateEntity $stateEntity)
    {
        return $this->respond($stateEntity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutStateEntityRequest $request, StateEntity $stateEntity)
    {
        $stateEntity->update($request->validated());
        return $this->respond($stateEntity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StateEntity $stateEntity)
    {
        $stateEntity->delete();
        return $this->respondSuccess();
    }
}
