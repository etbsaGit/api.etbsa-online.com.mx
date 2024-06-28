<?php

namespace App\Http\Controllers\Api;

use App\Models\WorkOrderDoc;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class WorkOrderDocController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkOrderDoc $workOrderDoc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkOrderDoc $workOrderDoc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkOrderDoc $workOrderDoc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkOrderDoc $workOrderDoc)
    {
        Storage::disk('s3')->delete($workOrderDoc->path);
        $workOrderDoc->delete();
        return $this->respondSuccess();
    }
}
