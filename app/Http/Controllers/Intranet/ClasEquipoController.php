<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\ClasEquipo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\ClasEquipo\StoreClasEquipoRequest;

class ClasEquipoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ClasEquipo::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClasEquipoRequest $request)
    {
        $clasEquipo = ClasEquipo::create($request->validated());
        return $this->respondCreated($clasEquipo);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClasEquipo $clasEquipo)
    {
        return $this->respond($clasEquipo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClasEquipoRequest $request, ClasEquipo $clasEquipo)
    {
        $clasEquipo->update($request->validated());
        return $this->respond($clasEquipo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClasEquipo $clasEquipo)
    {
        $clasEquipo->delete();
        return $this->respondSuccess();
    }
}
