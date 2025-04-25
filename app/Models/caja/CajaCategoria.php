<?php

namespace App\Models\Caja;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CajaCategoria extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
       'nombre',
       'descripcion'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre','descripcion']);
    }
}
