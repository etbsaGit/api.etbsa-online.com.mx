<?php

namespace App\Models;

use App\Models\Intranet\Cliente;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoDeclaracion extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'giro',
        'inicio',
        'termino',
        'status',
        'cliente_id',
        'feedback'
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['giro']);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function relaciones()
    {
        return $this->hasMany(CreditoRelacion::class, 'credito_declaracion_id');
    }
}
