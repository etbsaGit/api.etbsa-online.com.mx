<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ClienteRiego extends Model
{
    use HasFactory;

    protected $table = 'clientes_riegos';

    protected $fillable = [
        'hectareas_propias',
        'hectareas_rentadas',
        'cliente_id',
        'riego_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function riego()
    {
        return $this->belongsTo(Riego::class, 'riego_id');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['riego_id'])) {
            $query->where('riego_id', $filters['riego_id']);
        }

        if (
            !empty($filters['state_entity_id']) ||
            !empty($filters['town_id'])
        ) {

            $query->whereHas('cliente', function ($q) use ($filters) {

                if (!empty($filters['state_entity_id'])) {
                    $q->where('state_entity_id', $filters['state_entity_id']);
                }

                if (!empty($filters['town_id'])) {
                    $q->where('town_id', $filters['town_id']);
                }
            });
        }

        return $query;
    }
}
