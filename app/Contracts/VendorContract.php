<?php

namespace App\Contracts;
use App\Models\Ecommerce\Vendor;


interface VendorContract
{
    public function filterFor(array $params);

    public function listVendors(string $order = 'id', string $sort = 'desc', array $columns = ['*']);

    public function findVendorById(int $id);

    public function createVendor(array $params);

    public function updateVendor(int $id, array $params);

    public function deleteVendor($id);

    public function deleteLogo(Vendor $vendor);
}
