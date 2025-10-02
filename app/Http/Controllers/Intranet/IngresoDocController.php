<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\IngresoDoc;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class IngresoDocController extends ApiController
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
    public function show(IngresoDoc $ingresoDoc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IngresoDoc $ingresoDoc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IngresoDoc $ingresoDoc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IngresoDoc $ingresoDoc)
    {
        Storage::disk('s3')->delete($ingresoDoc->path);
        $ingresoDoc->delete();
        return $this->respondSuccess();
    }
}
