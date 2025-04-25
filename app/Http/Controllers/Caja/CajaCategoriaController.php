<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Models\Caja\CajaCategoria;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaCategoria\PutRequest;
use App\Http\Requests\Caja\CajaCategoria\StoreRequest;

class CajaCategoriaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CajaCategoria::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $cajaCategoria = CajaCategoria::create($request->validated());

        return $this->respondCreated($cajaCategoria);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaCategoria $cajaCategoria)
    {
        return $this->respond($cajaCategoria);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaCategoria $cajaCategoria)
    {
        $cajaCategoria->update($request->validated());
        return $this->respond($cajaCategoria);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaCategoria $cajaCategoria)
    {
        $cajaCategoria->delete();
        return $this->respondSuccess();
    }
}
