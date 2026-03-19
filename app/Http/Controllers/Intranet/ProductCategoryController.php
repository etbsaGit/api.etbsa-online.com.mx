<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ProductCategory;
use App\Http\Requests\Intranet\Products\ProductCategoryRequest;


class ProductCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            ProductCategory::filter($filters)->paginate(10),
            'Lista de categorías cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCategoryRequest $request)
    {
        $productCategory = ProductCategory::create($request->validated());
        return $this->respondCreated(
            $productCategory,
            'Categoría registrada correctamente'
        );
    }
    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategoria)
    {
        return $this->respond(
            $productCategoria,
            'Detalle de la categoría'
        );
    }

     /**
     * Update the specified resource in storage.
     */
    public function update(ProductCategoryRequest $request, ProductCategory $productCategoria)
    {
        $productCategoria->update($request->validated());
        return $this->respond(
            $productCategoria,
            'Proveedor actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategoria)
    {
        $productCategoria->delete();
        return $this->respondSuccess(
            'Proveedor eliminado correctamente'
        );
    }
}
