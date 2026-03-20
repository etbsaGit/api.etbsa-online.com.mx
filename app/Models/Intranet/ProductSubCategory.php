<?php

namespace App\Models\Intranet;

use App\Models\Ecommerce\Product;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if(isset($filters['category'])){
            $query->whereHas('category',function($q) use ($filters){
                $q->where('name','like','%' . $filters['category']. '%');
            });
        }
    }
}
