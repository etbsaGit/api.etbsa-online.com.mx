<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreFeaturesRequest;
use App\Http\Requests\Ecommerce\UpdateFeaturesRequest;
use App\Models\Ecommerce\Features;

class FeaturesController extends ApiController
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
    public function store(StoreFeaturesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Features $features)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeaturesRequest $request, Features $features)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Features $features)
    {
        //
    }
}
