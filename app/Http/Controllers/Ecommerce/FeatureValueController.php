<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreFeatureValueRequest;
use App\Http\Requests\Ecommerce\UpdateFeatureValueRequest;
use App\Models\Ecommerce\FeatureValue;

class FeatureValueController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeatureValueRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FeatureValue $featureValue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeatureValueRequest $request, FeatureValue $featureValue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeatureValue $featureValue)
    {
        //
    }
}
