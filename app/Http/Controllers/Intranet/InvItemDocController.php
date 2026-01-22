<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\InvItemDoc;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class InvItemDocController extends ApiController
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
    public function show(InvItemDoc $invItemDoc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvItemDoc $invItemDoc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvItemDoc $invItemDoc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvItemDoc $invItemDoc)
    {
        Storage::disk('s3')->delete($invItemDoc->path);
        $invItemDoc->delete();
        return $this->respondSuccess(
            'Item eliminado correctamente'
        );
    }
}
