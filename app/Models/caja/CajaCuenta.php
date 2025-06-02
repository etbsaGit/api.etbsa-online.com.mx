<?php

namespace App\Models\Caja;

use App\Models\Sucursal;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CajaCuenta extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'numeroCuenta',
        'descripcion',
        'moneda',
        'caja_banco_id',
        'sucursal_id',
        'caja_categoria_id'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['numeroCuenta', 'descripcion']);
    }

    public function cajaBanco()
    {
        return $this->belongsTo(CajaBanco::class, 'caja_banco_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CajaCategoria::class, 'caja_categoria_id');
    }

    // ---------------------------------Pago---------------------------------------------------------

    public function transaccion()
    {
        return $this->hasMany(CajaTransaccion::class, 'cuenta_id');
    }
}
