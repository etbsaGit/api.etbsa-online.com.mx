<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ProductsRiego extends Model
{
    use HasFactory;

    protected $table = 'products_riego';
    protected $fillable = [
        'brand_id',
        'vendor_id',
        'sku',
        'name',
        'description',
        'active',
        'image_url',
        'category_id',
        'subcategory_id',
        'currency_id',
    ];

    public function marca()
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(ProductSupplier::class, 'vendor_id');
    }

    public function categoria()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function subcategoria()
    {
        return $this->belongsTo(ProductSubCategory::class, 'subcategory_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function precios()
    {
        return $this->hasMany(PrecioProdRiego::class, 'producto_id')->with('currency');
    }

    public function imagenes()
    {
        return $this->hasMany(ProductRiegoImage::class, 'product_id');
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "products_riego/id_" . $this->id . "/galeria",
        );
    }
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('sku', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (!empty($filters['subcategory_id'])) {
            $query->where('subcategory_id', $filters['subcategory_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }
        return $query;
    }
}
