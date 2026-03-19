<?php

namespace App\Models\Intranet;


use Illuminate\Database\Eloquent\Model;
use App\Models\Intranet\Product;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProductCategory extends Model
{

    use FilterableModel;
    use HasFactory;
    protected $table = 'categories';

    protected $fillable = [
        'name',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        return $query;
    }
}
