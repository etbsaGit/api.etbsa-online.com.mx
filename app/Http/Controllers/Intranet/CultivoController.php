<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cultivo;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Cultivo\StoreCultivoRequest;

class CultivoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Cultivo::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCultivoRequest $request)
    {
        $cultivo = Cultivo::create($request->validated());
        return $this->respondCreated($cultivo);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cultivo $cultivo)
    {
        return $this->respond($cultivo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCultivoRequest $request, Cultivo $cultivo)
    {
        $cultivo->update($request->validated());
        return $this->respond($cultivo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cultivo $cultivo)
    {
        $cultivo->delete();
        return $this->respondSuccess();
    }
}
