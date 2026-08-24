<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsto(ProductSupplier::class, 'vendor');
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
}
