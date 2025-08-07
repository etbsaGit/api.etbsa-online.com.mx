<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\CandidatoNota;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;

class CandidatoNotaController extends ApiController
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
    public function show(CandidatoNota $candidatoNota)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CandidatoNota $candidatoNota)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CandidatoNota $candidatoNota)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CandidatoNota $candidatoNota)
    {
        $candidatoNota->delete();
        return $this->respondSuccess();
    }
}
