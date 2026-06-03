<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'serie',
        'modelo',
        'anio',
        'valor',
        'cliente_id',
        'marca_id',
        'condicion_id',
        'clas_equipo_id',
        'tipo_equipo_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function condicion()
    {
        return $this->belongsTo(Condicion::class, 'condicion_id');
    }

    public function clasEquipo()
    {
        return $this->belongsTo(ClasEquipo::class, 'clas_equipo_id');
    }

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['marca_id'])) {
            $query->where('marca_id', $filters['marca_id']);
        }

        if (!empty($filters['condicion_id'])) {
            $query->where('condicion_id', $filters['condicion_id']);
        }

        if (!empty($filters['tipo_equipo_id'])) {
            $query->where('tipo_equipo_id', $filters['tipo_equipo_id']);
        }

        if (!empty($filters['clas_equipo_id'])) {
            $query->where('clas_equipo_id', $filters['clas_equipo_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($sub) use ($filters) {
                $sub->where('modelo', 'like', "%{$filters['search']}%");

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
