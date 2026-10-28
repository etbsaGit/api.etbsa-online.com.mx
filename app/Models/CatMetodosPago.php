<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CatMetodosPago extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'cat_metodos_pago';

    protected $fillable = [
        'nombre',
        'clave'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'clave']);
    }
}
