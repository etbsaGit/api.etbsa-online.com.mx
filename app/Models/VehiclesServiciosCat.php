<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesServiciosCat extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_servicios_cat';

    protected $fillable = [
        'nombre',
        'tipo_servicio_id',
        'es_periodico_km',
        'intervalo_km_sugerido',
        'intervalo_meses_sugerido'
    ];

    // -Relations-
    public function tipoServicio()
    {
        return $this->belongsTo(VehiclesTipoServicio::class, 'tipo_servicio_id');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }
}
