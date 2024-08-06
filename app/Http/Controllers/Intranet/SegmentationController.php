<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Segmentation;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Segmentation\StoreSegmentationRequest;

class SegmentationController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Segmentation::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSegmentationRequest $request)
    {
        $segmentation = Segmentation::create($request->validated());
        return $this->respondCreated($segmentation);
    }

    /**
     * Display the specified resource.
     */
    public function show(Segmentation $segmentation)
    {
        return $this->respond($segmentation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSegmentationRequest $request, Segmentation $segmentation)
    {
        $segmentation->update($request->validated());
        return $this->respond($segmentation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Segmentation $segmentation)
    {
        $segmentation->delete();
        return $this->respondSuccess();
    }
}
