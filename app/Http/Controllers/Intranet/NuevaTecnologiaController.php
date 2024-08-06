<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\NuevaTecnologia\StoreNuevaTecnologiaRequest;
use App\Models\Intranet\NuevaTecnologia;

class NuevaTecnologiaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(NuevaTecnologia::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNuevaTecnologiaRequest $request)
    {
        $nuevaTecnologia = NuevaTecnologia::create($request->validated());
        return $this->respondCreated($nuevaTecnologia);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $nuevaTecnologia = NuevaTecnologia::findOrFail($id);
        return $this->respond($nuevaTecnologia);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreNuevaTecnologiaRequest $request, $id)
    {
        $nuevaTecnologia = NuevaTecnologia::findOrFail($id);
        $nuevaTecnologia->update($request->validated());
        return $this->respond($nuevaTecnologia);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $nuevaTecnologia = NuevaTecnologia::findOrFail($id);
        $nuevaTecnologia->delete();
        return $this->respondSuccess();
    }
}
