<?php

namespace App\Http\Controllers\Ecommerce;

use App\Contracts\BrandContract;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreBrandRequest;
use App\Http\Requests\Ecommerce\UpdateBrandRequest;
use App\Models\Ecommerce\Brand;

class BrandController extends ApiController
{

    private BrandContract $brandRepository;

    public function __construct(BrandContract $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = $this->brandRepository->index(request()->all());

        return $this->respond([
            'data' => $items,
            'message' => 'Recursos Encontrados'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {

        $payload = $request->validated();
        $brand = $this->brandRepository->createBrand($payload);

        return $this->respondCreated([
            "success" => true,
            "message" => "Brand created successfully",
            "item" => $brand
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {

        $payload = $request->validated();
        $updated = $this->brandRepository->updateBrand($brand, $payload);

        return $this->respond([
            'success' => true,
            'message' => 'Brand has been updated.',
            'updated' => $updated
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        //
    }
}
