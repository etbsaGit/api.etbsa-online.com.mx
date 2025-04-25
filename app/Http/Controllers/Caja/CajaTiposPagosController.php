<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Models\Caja\CajaTiposPago;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaTiposPago\PutRequest;
use App\Http\Requests\Caja\CajaTiposPago\StoreRequest;

class CajaTiposPagosController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CajaTiposPago::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $cajaTiposPago = CajaTiposPago::create($request->validated());

        return $this->respondCreated($cajaTiposPago);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaTiposPago $cajaTiposPago)
    {
        return $this->respond($cajaTiposPago);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaTiposPago $cajaTiposPago)
    {
        $cajaTiposPago->update($request->validated());
        return $this->respond($cajaTiposPago);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaTiposPago $cajaTiposPago)
    {
        $cajaTiposPago->delete();
        return $this->respondSuccess();
    }
}
