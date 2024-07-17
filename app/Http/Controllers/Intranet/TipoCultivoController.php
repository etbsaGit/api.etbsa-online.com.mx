<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\TipoCultivo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\TipoCultivo\StoreTipoCultivoRequest;

class TipoCultivoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(TipoCultivo::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTipoCultivoRequest $request)
    {
        $tipoCultivo = TipoCultivo::create($request->validated());
        return $this->respondCreated($tipoCultivo);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoCultivo $tipoCultivo)
    {
        return $this->respond($tipoCultivo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTipoCultivoRequest $request, TipoCultivo $tipoCultivo)
    {
        $tipoCultivo->update($request->validated());
        return $this->respond($tipoCultivo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoCultivo $tipoCultivo)
    {
        $tipoCultivo->delete();
        return $this->respondSuccess();
    }
}
