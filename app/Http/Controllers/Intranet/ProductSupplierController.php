<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ProductSupplier;
use App\Http\Requests\Intranet\Products\ProductSupplierRequest;

class ProductSupplierController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            ProductSupplier::filter($filters)->paginate(10),
            'Lista de proveedores cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductSupplierRequest $request)
    {
        $productSupplier = ProductSupplier::create($request->validated());
        return $this->respondCreated(
            $productSupplier,
            'Proveedor registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductSupplier $productSupplier)
    {
        return $this->respond(
            $productSupplier,
            'Detalle del proveedor'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductSupplierRequest $request, ProductSupplier $productSupplier)
    {
        $productSupplier->update($request->validated());
        return $this->respond(
            $productSupplier,
            'Proveedor actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductSupplier $productSupplier)
    {
        $productSupplier->delete();
        return $this->respondSuccess(
            'Proveedor eliminado correctamente'
        );
    }
}
