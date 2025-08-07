<?php

namespace App\Models\Caja;

use App\Models\User;
use App\Models\Sucursal;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CajaCorte extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'efectivo',
        'tarjeta_debito',
        'tarjeta_credito',
        'transferencias',
        'depositos',
        'cheques',
        'fecha_corte',
        'comentarios',
        'user_id',
        'sucursal_id'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['fecha_corte', 'comentarios']);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function detalleEfectivo()
    {
        return $this->hasMany(CajaDetalleEfectivo::class, 'corte_id');
    }
}
