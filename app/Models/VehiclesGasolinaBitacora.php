<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesGasolinaBitacora extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_gasolina_bitacora';

    protected $fillable = [
        'dispersion_id',
        'estatus_id',
        'user_id',
        'comentarios',
    ];

    // -Relations-
    public function dispersion()
    {
        return $this->belongsTo(VehiclesDispersionGasolina::class, 'dispersion_id');
    }

    public function disperson()
    {
        return $this->belongsTo(VehiclesDispersionGasolina::class, 'dispersion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['dispersion_id'])) {
            $query->where('dispersion_id', $filters['dispersion_id']);
        }

        if (!empty($filters['estatus_id'])) {
            $query->where('estatus_id', $filters['estatus_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filters['fecha_hasta']);
        }

        return $query;
    }
}
