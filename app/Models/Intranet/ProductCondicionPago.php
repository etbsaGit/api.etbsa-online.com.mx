<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\FilterableModel;

class ProductCondicionPago extends Model
{
    use HasFactory;
    use FilterableModel;
    protected $table = 'products_condicion_pago';

    protected $fillable = [
        'name'
    ];

    public function categorias(){
        return $this->belongsToMany(
            ProductCategory::class,
            'condicion_pago_categorias',
            'condicion_id',
            'categoria_id'
        );
    }
    public function precios(){
        return $this->hasMany(ProductoPrecio::class);
    }

    //filtros
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query,$filters,['name']);
    }
}
