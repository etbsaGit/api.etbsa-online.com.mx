<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Models\Intranet\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Intranet\Products\ProductRequest;
use App\Models\Intranet\ProductBrand;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductSubCategory;
use App\Models\Intranet\ProductSupplier;
use App\Models\Sucursal;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $products = Product::with([
            'brand',
            'category',
            'subcategory',
            'currency',
            'agency',
            'supplier'
        ])
            ->filter($filters)
            ->paginate(10);

        return $this->respond(
            $products,
            'Lista de productos cargada correctamente'
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());

        return $this->respondCreated(
            $product,
            'Producto creado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'brand',
            'category',
            'subcategory',
            'currency',
            'agency',
            'supplier',
            'currency'
        ]);

        return $this->respond(
            $product,
            'Detalle del producto'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return $this->respond(
            $product,
            'Producto actualizado correctamente'
        );
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->respondSuccess(
            'Producto eliminado correctamente'
        );
    }

    // getOptions
    public function getOptions(){
        $data = [
            'proveedores' => ProductSupplier::all(),
            'sucursales' => Sucursal::all(),
            'categorias' => ProductCategory::all(),
            'subcategorias' => ProductSubCategory::all(),
            'marcas' => ProductBrand::all(),
        ];
        return $this->respond($data);
    }
}
