<?php

namespace App\Http\Controllers\Api;

use App\Models\Competencia;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Competencia\PutRequest;
use App\Http\Requests\Competencia\StoreRequest;

class CompetenciaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        return $this->respond(Competencia::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $competencium = Competencia::create($request->validated());
        return $this->respondCreated($competencium);
    }

    /**
     * Display the specified resource.
     */
    public function show(Competencia $competencium)
    {
        return $this->respond($competencium);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Competencia $competencium)
    {
        $competencium->update($request->validated());
        return $this->respond($competencium);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competencia $competencium)
    {
        $competencium->delete();
        return $this->respondSuccess();
    }
}
