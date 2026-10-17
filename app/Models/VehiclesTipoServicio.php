<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesTipoServicio extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_tipo_servicio';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    // -Relations-
    public function servicios()
    {
        return $this->hasMany(VehiclesServiciosCat::class, 'tipo_servicio_id');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }
}
