<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoConcepto extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'tipo',
        'categoria',
        'nombre'
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['tipo', 'categoria', 'nombre']);
    }

    public function relaciones()
    {
        return $this->hasMany(CreditoRelacion::class, 'credito_concepto_id');
    }
}
