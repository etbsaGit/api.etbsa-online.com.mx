<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ProductSubCategory;
use App\Http\Requests\Intranet\Products\ProductSubCategoryRequest;
use App\Models\Intranet\ProductCategory;

class ProductSubCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            ProductSubCategory::filter($filters)->with('categoria')->paginate(10),
            'Lista de subgategorías cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductSubCategoryRequest $request)
    {
        $productSubcategorium = ProductSubCategory::create($request->validated());
        return $this->respondCreated(
            $productSubcategorium,
            'Subcategoría registrada correctamente'
        );
    }
    /**
     * Display the specified resource.
     */
    public function show(ProductSubCategory $productSubcategorium)
    {
        return $this->respond(
            $productSubcategorium,
            'Detalle de la subcategoría'
        );
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(ProductSubCategoryRequest $request, ProductSubCategory $productSubcategorium)
    {
        $productSubcategorium->update($request->validated());
        return $this->respond($productSubcategorium, 'Subcategoría actualizada correctamente');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductSubCategory $productSubcategorium){
        $productSubcategorium->delete();
        return $this->respondSuccess('Subcategoría eliminada correctamente');
    }

    // getOptions
    public function getOptions(){
        $data = [
            'categorias' => ProductCategory::all(),
        ];
        return $this->respond($data);
    }
}
