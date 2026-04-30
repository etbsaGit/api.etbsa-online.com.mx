<?php

namespace App\Models\Intranet;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'description',
        'active',
        'is_usado',
        'is_dollar',
        'price_1',
        'price_2',
        'price_3',
        'price_4',
        'price_5',
        'price_6',
        'price_7',
        'price_8',
        'price_9',
        'price_10',
        'price_11',
        'price_12',
        'price_13',
        'price_14',
        'category_id',
        'subcategory_id',
        'currency_id',
        'agency_id'
    ];

    public function brand()
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function subcategory()
    {
        return $this->belongsTo(ProductSubCategory::class, 'subcategory_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function agency()
    {
        return $this->belongsTo(Sucursal::class, 'agency_id');
    }
    public function supplier()
    {
        return $this->belongsTo(ProductSupplier::class, 'vendor_id');
    }

    public function precios(){
        return $this->hasMany(ProductoPrecio::class,'producto_id')->with('currency');
    }

    public function trackingProduct(){
        return $this->hasMany(TrackingDetalle::class,'product_id');
    }

    public function contrapesos()
    {
        return $this->belongsToMany(Contrapesos::class, 'tractor_contrapesos', 'product_id', 'contrapeso_id');
    }

    public function getExtrasAttribute(){
        return $this->subcategory ? $this->subcategory->extras : collect();
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('sku', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('name', 'like', '%' . $filters['search'] . '%');
            });
        }
        return $query;
    }
}
