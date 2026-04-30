<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ProductExtras extends Model
{
    use FilterableModel;
    use HasFactory;

    protected $table = 'product_extras';

    protected $fillable = [
        'name',
        'precio_unidad',
        'currency_id'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function subcategorias()
    {
        return $this->belongsToMany(
            ProductSubCategory::class,
            'product_extras_subcat',
            'extra_id',
            'subcategory_id'
        );
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }
}
