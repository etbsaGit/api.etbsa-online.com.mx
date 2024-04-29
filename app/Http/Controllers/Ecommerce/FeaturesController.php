<?php

namespace App\Http\Controllers\Ecommerce;

use App\Contracts\FeatureContract;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreFeaturesRequest;
use App\Http\Requests\Ecommerce\UpdateFeaturesRequest;
use App\Models\Ecommerce\Feature;

class FeaturesController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Feature::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeaturesRequest $request)
    {
        return response()->json(Feature::create($request->validated()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Feature $feature)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeaturesRequest $request, Feature $feature)
    {
        $feature->update($request->validated());
        return response()->json($feature);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feature $feature)
    {
        $feature->delete();
        return $this->respondSuccess();
    }
}
