<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Classifications\StoreClassificationsRequest;
use App\Models\Intranet\Classification;

class ClassificationsController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Classification::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassificationsRequest $request)
    {
        $classification = Classification::create($request->validated());
        return $this->respondCreated($classification);
    }

    /**
     * Display the specified resource.
     */
    public function show(Classification $classification)
    {
        return $this->respond($classification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClassificationsRequest $request, Classification $classification)
    {
        $classification->update($request->validated());
        return $this->respond($classification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classification $classification)
    {
        $classification->delete();
        return $this->respondSuccess();
    }
}
