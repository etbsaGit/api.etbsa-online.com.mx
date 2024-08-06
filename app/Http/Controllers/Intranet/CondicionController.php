<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Condicion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Condicion\StoreCondicionRequest;

class CondicionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Condicion::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCondicionRequest $request)
    {
        $condicion = Condicion::create($request->validated());
        return $this->respondCreated($condicion);
    }

    /**
     * Display the specified resource.
     */
    public function show(Condicion $condicion)
    {
        return $this->respond($condicion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCondicionRequest $request, Condicion $condicion)
    {
        $condicion->update($request->validated());
        return $this->respond($condicion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Condicion $condicion)
    {
        $condicion->delete();
        return $this->respondSuccess();
    }
}
