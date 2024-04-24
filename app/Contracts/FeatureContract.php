<?php

namespace App\Contracts;

use App\Models\Ecommerce\Features;


interface FeatureContract
{
    // public function filterFor(array $params);

    public function index(array $params);

    public function listFeature(string $order = 'id', string $sort = 'desc', array $columns = ['*']);

    public function findFeatureById(int $id);

    public function createFeature(array $params);

    public function updateFeature(Features $feature, array $params);

    public function deleteFeature($id);

}
