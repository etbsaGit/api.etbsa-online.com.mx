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

        $categorias = ProductCategory::with(['condicionesPago'])
            ->filter($filters)
            ->paginate(10);

        return $this->respond(
            $categorias,
            'Lista de categorías cargada correctamente'
        );
    }

    public function store(ProductCategoryRequest $request){
        $productCategory = ProductCategory::create(['name' => $request->name]);

        if($request->filled('condicion_ids')){
            $productCategory->condicionesPago()->sync($request->condicion_ids);
        }

        return $this->respondCreated($productCategory->load('condicionesPago'),'Categoria Creada');
    }
    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategorium)
    {
        return $this->respond(
            $productCategorium,
            'Detalle de la categoría'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductCategoryRequest $request, ProductCategory $product_categorium)
    {

        $product_categorium->update([
            'name' => $request->name
        ]);

        if($request->has('condicion_ids')){
            $product_categorium->condicionesPago()->sync($request->condicion_ids);
        }

        return $this->respond(
            $product_categorium->load('condicionesPago'),
            'Categoría actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategorium)
    {
        $productCategorium->delete();
        return $this->respondSuccess(
            'Categoróa eliminado correctamente'
        );
    }
}
