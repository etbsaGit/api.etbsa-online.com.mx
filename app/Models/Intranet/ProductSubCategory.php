<?php

namespace App\Models\Intranet;

use App\Models\Intranet\Product;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class ProductSubCategory extends Model
{
    use FilterableModel;
    use HasFactory;

    protected $table = 'product_subcategory';

    public $fillable = [
        'name',
        'category_id'
    ];

    public function categoria()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function extras(){
        return $this->belongsToMany(
            ProductExtras::class,
            'product_extras_subcat',
            'subcategory_id',
            'extra_id'
        );
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name', 'category_id']);
    }
}
