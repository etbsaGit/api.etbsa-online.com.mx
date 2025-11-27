<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\SoftSkillNivel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;

class SoftSkillNivelController extends ApiController
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
    public function show(SoftSkillNivel $softSkillNivel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SoftSkillNivel $softSkillNivel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SoftSkillNivel $softSkillNivel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SoftSkillNivel $softSkillNivel)
    {
        $softSkillNivel->delete();
        return $this->respondSuccess();
    }
}
