<?php

namespace App\Http\Controllers\Ecommerce;

use App\Contracts\ProductContract;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreProductRequest;
use App\Http\Requests\Ecommerce\UpdateProductRequest;
use App\Models\Ecommerce\Product;

class ProductController extends ApiController
{
    private ProductContract $productRepository;

    public function __construct(ProductContract $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = $this->productRepository->index(request()->all());

        return $this->respond([
            'data' => $items,
            'message' => 'Recursos Encontrados'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $payload = $request->validated();

        $product = $this->productRepository->createProduct($payload);

        return $this->respondCreated([
            'data' => $product
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $this->respond([
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $payload = $request->validated();
        $updated = $this->productRepository->updateProduct($product->id, $payload);

        return $this->respond([
            'success' => $updated,
            'message' => 'Product has been updated.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
