<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InversionesAgricola extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'ciclo',
        'hectareas',
        'costo',
        'cultivo_id',
        'cliente_id',
    ];

    protected $appends = ['total'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class, 'cultivo_id');
    }

    public function getTotalAttribute()
    {
        return $this->hectareas * $this->costo;
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['ciclo'])) {
            $query->where('ciclo', $filters['ciclo']);
        }

        if(!empty($filters['cultivo_id'])){
            $query->where('cultivo_id',$filters['cultivo_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($sub) use ($filters) {
                $sub->where('hectareas', 'like', "%{$filters['search']}%")
                ->orWhere('costo', 'like', "%{$filters['search']}%");
            });
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
