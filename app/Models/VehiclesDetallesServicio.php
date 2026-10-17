<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesDetallesServicio extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_detalles_servicio';

    protected $fillable = [
        'orden_servicio_id',
        'servicio_id',
        'descripcion',
        'subtotal',
        'iva',
        'total',
        'km_evento'
    ];

    // -Relations-
    public function ordenServicio()
    {
        return $this->belongsTo(VehiclesOrdenesServicio::class, 'orden_servicio_id');
    }

    public function servicio()
    {
        return $this->belongsTo(VehiclesServiciosCat::class, 'servicio_id');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['descripcion']);
    }
}
