<?php

namespace App\Models\Caja;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CajaDenominacion extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'caja_denominaciones';

    protected $fillable = [
        'nombre',
        'valor',
        'tipo'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'valor', 'tipo']);
    }

    public function detalleEfectivo()
    {
        return $this->hasMany(CajaDetalleEfectivo::class, 'denominacion_id');
    }
}
