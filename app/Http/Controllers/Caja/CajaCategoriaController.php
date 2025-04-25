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
        $cajaCategorium = CajaCategoria::create($request->validated());

        return $this->respondCreated($cajaCategorium);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaCategoria $cajaCategorium)
    {
        return $this->respond($cajaCategorium);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaCategoria $cajaCategorium)
    {
        $cajaCategorium->update($request->validated());
        return $this->respond($cajaCategorium);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaCategoria $cajaCategorium)
    {
        $cajaCategorium->delete();
        return $this->respondSuccess();
    }
}
