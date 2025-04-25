<?php

namespace App\Models\Caja;

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
        'caja_banco_id'
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
}
