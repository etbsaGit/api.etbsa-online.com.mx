<?php

namespace App\Models\Caja;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CajaCliente extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'clave',
        'nombre'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['clave', 'nombre']);
    }

    public function cajaTransaccion()
    {
        return $this->hasMany(CajaTransaccion::class, 'cliente_id');
    }
}
