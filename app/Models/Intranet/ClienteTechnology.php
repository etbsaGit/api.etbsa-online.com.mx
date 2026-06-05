<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClienteTechnology extends Model
{
    use HasFactory;

    protected $table = 'clientes_technologies';

    protected $fillable = [
        'cantidad',
        'hectareas',
        'cliente_id',
        'nueva_tecnologia_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function nuevaTecnologia()
    {
        return $this->belongsTo(NuevaTecnologia::class, 'nueva_tecnologia_id');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['tecnologia_id'])) {
            $query->where('nueva_tecnologia_id', $filters['tecnologia_id']);
        }

        if (!empty($filters['adopcion'])) {
            $query->whereHas('cliente', function ($q) use ($filters) {
                if (!empty($filters['adopcion'])) {
                    $q->where('currentClassTech', $filters['adopcion']);
                }
            });
        }
    }
}
