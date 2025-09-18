<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Ganado;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Ganado\StoreRequest;

class GanadoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Ganado::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $ganado = Ganado::create($request->validated());
        return $this->respondCreated($ganado);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ganado $ganado)
    {
        return $this->respond($ganado);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Ganado $ganado)
    {
        $ganado->update($request->validated());
        return $this->respond($ganado);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ganado $ganado)
    {
        $ganado->delete();
        return $this->respondSuccess();
    }
}
