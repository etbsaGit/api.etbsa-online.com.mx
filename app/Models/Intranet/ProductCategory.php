<?php

namespace App\Models\Intranet;


use Illuminate\Database\Eloquent\Model;
use App\Models\Intranet\Product;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;


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

    public function subcategorias(){
        return $this->hasMany(ProductSubCategory::class);
    }

    public function condicionesPago(){
        return $this->belongsToMany(
            ProductCondicionPago::class,
            'condicion_pago_categorias',
            'categoria_id',
            'condicion_id'
        );
    }

    //filtros
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query,$filters,['name']);
    }
}
