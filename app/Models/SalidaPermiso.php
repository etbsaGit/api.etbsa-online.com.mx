<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalidaPermiso extends Model
{
    use HasFactory, FilterableModel;

    // Si quieres que Laravel los trate automáticamente como fechas:
    protected $casts = [
        'date' => 'date:Y-m-d',
        'start' => 'datetime:H:i',
        'end' => 'datetime:H:i',
        'lunch_start' => 'datetime:H:i',
        'lunch_end' => 'datetime:H:i',
    ];

    protected $fillable = [
        'date',
        'start',
        'end',
        'lunch_start',
        'lunch_end',
        'status',
        'description',
        'feedback',
        'empleado_id',
        'sucursal_id'
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearchPermiso($query, $filters, ['description', 'feedback']);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
