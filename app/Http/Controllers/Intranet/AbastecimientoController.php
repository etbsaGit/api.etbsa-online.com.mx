<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Abastecimiento\StoreAbastecimientoRequest;
use App\Models\Intranet\Abastecimiento;

class AbastecimientoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Abastecimiento::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAbastecimientoRequest $request)
    {
        $abastecimiento = Abastecimiento::create($request->validated());
        return $this->respondCreated($abastecimiento);
    }

    /**
     * Display the specified resource.
     */
    public function show(Abastecimiento $abastecimiento)
    {
        return $this->respond($abastecimiento);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAbastecimientoRequest $request, Abastecimiento $abastecimiento)
    {
        $abastecimiento->update($request->validated());
        return $this->respond($abastecimiento);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Abastecimiento $abastecimiento)
    {
        $abastecimiento->delete();
        return $this->respondSuccess();
    }
}
