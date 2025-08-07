<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Caja\CajaTiposFactura;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaTiposFactura\PutRequest;
use App\Http\Requests\Caja\CajaTiposFactura\StoreRequest;

class CajaTiposFacturaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CajaTiposFactura::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $cajaTiposFactura = CajaTiposFactura::create($request->validated());

        return $this->respondCreated($cajaTiposFactura);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaTiposFactura $cajaTiposFactura)
    {
        return $this->respond($cajaTiposFactura);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaTiposFactura $cajaTiposFactura)
    {
        $cajaTiposFactura->update($request->validated());
        return $this->respond($cajaTiposFactura);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaTiposFactura $cajaTiposFactura)
    {
        $cajaTiposFactura->delete();
        return $this->respondSuccess();
    }
}
