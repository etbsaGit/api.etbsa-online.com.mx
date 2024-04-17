<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     *
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(static function ($product) {
            $product->slug = \Str::slug($product->name);
            $product->save();
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function features()
    {
        return $this->belongsToMany(Features::class, 'feature_product', 'product_id', 'feature_id')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id');
    }

}
