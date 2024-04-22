<?php

namespace App\Contracts;

use App\Models\Ecommerce\Brand;


interface BrandContract
{
    // public function filterFor(array $params);

    public function listBrands(string $order = 'id', string $sort = 'desc', array $columns = ['*']);

    public function findBrandById(int $id);

    public function createBrand(array $params);

    public function updateBrand(Brand $brand, array $params);

    public function deleteBrand($id);

    public function deleteLogo(Brand $brand);
}
