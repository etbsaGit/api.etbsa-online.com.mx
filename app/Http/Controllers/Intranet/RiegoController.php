<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Riego;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Riego\StoreRiegoRequest;

class RiegoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Riego::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRiegoRequest $request)
    {
        $abastecimiento = Riego::create($request->validated());
        return $this->respondCreated($abastecimiento);
    }

    /**
     * Display the specified resource.
     */
    public function show(Riego $riego)
    {
        return $this->respond($riego);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRiegoRequest $request, Riego $riego)
    {
        $riego->update($request->validated());
        return $this->respond($riego);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Riego $riego)
    {
        $riego->delete();
        return $this->respondSuccess();
    }
}
