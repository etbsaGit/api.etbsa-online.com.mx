<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'brand_id',
        'vendor_id',
        'sku',
        'name',
        'slug',
        'description',
        'quantity',
        'price',
        'sale_price',
        'active',
        'featured'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'brand_id' => 'integer',
        'vendor_id' => 'integer',
        'active' => 'boolean',
        'featured' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_product', 'product_id', 'feature_id')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category', 'product_id', 'category_id');
    }

    public function scopeFilter($query, $filters)
    {
        return $query
            ->when(isset($filters['features']) && count($filters['features']) > 0, function ($query) use ($filters) {
                $query->whereHas('features', function ($query) use ($filters) {
                    foreach ($filters['features'] as $feature) {
                        if (isset($feature['feature_id']) && isset($feature['value'])) {
                            $query->where('feature_product.feature_id', $feature['feature_id'])
                                ->where('feature_product.value', 'LIKE', '%' . $feature['value'] . '%');
                        }
                    }
                }, '=', count($filters['features']));
            })
            ->when(isset($filters['categories']) && count($filters['categories']) > 0, function ($query) use ($filters) {
                $query->whereHas('categories', function ($query) use ($filters) {
                    $query->whereIn('categories.id', $filters['categories']);
                }, '>=', count($filters['categories']));
            })
            ->when(isset($filters['brands']) && count($filters['brands']) > 0, function ($query) use ($filters) {
                $query->whereIn('brand_id', $filters['brands']);
            });
    }
}
