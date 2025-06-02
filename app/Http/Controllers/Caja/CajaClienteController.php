<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Models\Caja\CajaCliente;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaCliente\PutRequest;
use App\Http\Requests\Caja\CajaCliente\StoreRequest;

class CajaClienteController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CajaCliente::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $cajaCliente = CajaCliente::create($request->validated());

        return $this->respondCreated($cajaCliente);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaCliente $cajaCliente)
    {
        return $this->respond($cajaCliente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaCliente $cajaCliente)
    {
        $cajaCliente->update($request->validated());
        return $this->respond($cajaCliente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaCliente $cajaCliente)
    {
        $cajaCliente->delete();
        return $this->respondSuccess();
    }
}
