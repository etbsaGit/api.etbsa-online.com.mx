<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $table = 'features';

    public $fillable = [
        'name',
    ];

    // public function values()
    // {
    //     return $this->hasMany(FeatureValue::class, 'feature_id');
    // }

    public function values()
    {
        return $this->belongsToMany(Product::class, 'feature_product', 'feature_id', 'product_id')
            ->select('products.id', 'feature_product.value');
    }
}
