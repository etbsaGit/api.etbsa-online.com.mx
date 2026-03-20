<?php

namespace App\Models\Intranet;

use App\Models\Ecommerce\Product;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ProductBrand extends Model
{
    use FilterableModel;
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'name',
    ];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query,$filters,['name']);
    }
}
