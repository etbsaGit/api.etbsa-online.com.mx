<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ProductSubCategory;
use App\Http\Requests\Intranet\Products\ProductSubCategoryRequest;

class ProductSubCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            ProductSubCategory::filter($filters)->paginate(10),
            'Lista de subgategorías cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductSubCategoryRequest $request)
    {
        $productSubcategoria = ProductSubCategory::create($request->validated());
        return $this->respondCreated(
            $productSubcategoria,
            'Subcategoría registrada correctamente'
        );
    }
    /**
     * Display the specified resource.
     */
    public function show(ProductSubCategory $productSubcategoria)
    {
        return $this->respond(
            $productSubcategoria,
            'Detalle de la subcategoría'
        );
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(ProductSubCategoryRequest $request, ProductSubCategory $productSubcategoria)
    {
        $productSubcategoria->update($request->validated());
        return $this->respond($productSubcategoria, 'Subcategoría actualizada correctamente');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductSubCategory $productSubcategoria){
        $productSubcategoria->delete();
        return $this->respondSuccess('Subcategoría eliminada correctamente');
    }
}
