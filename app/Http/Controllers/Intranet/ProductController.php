<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Models\intranet\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Intranet\Products\ProductRequest;

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
            'supplier'
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
}
