<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Intranet\AnaliticaDoc;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class AnaliticaDocController extends ApiController
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
    public function show(AnaliticaDoc $analiticaDoc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnaliticaDoc $analiticaDoc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnaliticaDoc $analiticaDoc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnaliticaDoc $analiticaDoc)
    {
        Storage::disk('s3')->delete($analiticaDoc->path);
        $analiticaDoc->delete();
        return $this->respondSuccess();
    }

    public function changeEstatus(AnaliticaDoc $analiticaDoc, int $status)
    {
        $analiticaDoc->status = $status;
        $analiticaDoc->save();

        return $this->respondSuccess();
    }
}
