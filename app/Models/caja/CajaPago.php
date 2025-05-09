<?php

namespace App\Models\Caja;

use App\Models\Sucursal;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CajaPago extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'monto',
        'descripcion',
        'sucursal_id',
        'categoria_id',
        'transaccion_id',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['monto', 'descripcion']);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CajaCategoria::class, 'categoria_id');
    }

    public function transaccion()
    {
        return $this->belongsTo(CajaTransaccion::class, 'transaccion_id');
    }
}
