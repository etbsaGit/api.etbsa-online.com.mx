<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\FincaDoc;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class FincaDocController extends ApiController
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
    public function show(FincaDoc $fincaDoc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FincaDoc $fincaDoc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FincaDoc $fincaDoc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FincaDoc $fincaDoc)
    {
        Storage::disk('s3')->delete($fincaDoc->path);
        $fincaDoc->delete();
        return $this->respondSuccess();
    }
}
