<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Products\ProductBrandRequest;
use App\Models\Intranet\ProductBrand;

class ProductBrandController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            ProductBrand::filter($filters)->paginate(10),
            'Lista de marcas cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductBrandRequest $request)
    {
        $productBrand = ProductBrand::create($request->validated());

        return $this->respondCreated(
            $productBrand,
            'Marca registrada correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductBrand $productBrand)
    {
        return $this->respond(
            $productBrand,
            'Detalle de la marca'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductBrandRequest $request, ProductBrand $productBrand)
    {
        $productBrand->update($request->validated());
        return $this->respond(
            $productBrand,
            'Marca actualziada correctamente'
        );
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductBrand $productBrand)
    {
        $productBrand->delete();
        return $this->respondSuccess(
            'Marca eliminada correctamente'
        );
    }
}
