<?php

namespace App\Http\Controllers\Api;

use App\Models\UsedDoc;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class UsedDocController extends ApiController
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
    public function show(UsedDoc $usedDoc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UsedDoc $usedDoc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UsedDoc $usedDoc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UsedDoc $usedDoc)
    {
        Storage::disk('s3')->delete($usedDoc->path);
        $usedDoc->delete();
        return $this->respondSuccess();
    }
}
