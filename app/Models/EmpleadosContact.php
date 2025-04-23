<?php

namespace App\Models;

use App\Traits\FilterableModel;
use App\Models\Intranet\Kinship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpleadosContact extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'empleado_id',
        'kinship_id'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre','telefono','direccion']);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function kinship()
    {
        return $this->belongsTo(Kinship::class, 'kinship_id');
    }
}
