<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VacationDay extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'empleado_id',
        'sucursal_id',
        'puesto_id',
        'vehiculo_utilitario',
        'periodo_correspondiente',
        'anios_cumplidos',
        'dias_periodo',
        'subtotal_dias',
        'dias_disfrute',
        'dias_pendientes',
        'fecha_inicio',
        'fecha_termino',
        'fecha_regreso',
        'validated',
        'comentarios'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['comentarios']);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }
}
