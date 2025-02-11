<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prospect extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre',
        'ubicacion',
        'telefono',
        'empleado_id',
        'candidato_agp'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'ubicacion', 'telefono']);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function prospectCultivo()
    {
        return $this->hasMany(ProspectCultivo::class, 'prospect_id');
    }

    public function prospectRiego()
    {
        return $this->hasMany(ProspectRiego::class, 'prospect_id');
    }

    public function prospectDistribucion()
    {
        return $this->hasMany(ProspectDistribucion::class, 'prospect_id');
    }

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'prospect_id');
    }

    public function prospectAgp()
    {
        return $this->hasMany(ProspectAgp::class, 'prospect_id');
    }

    public function prospectServicio()
    {
        return $this->hasMany(ProspectServicio::class, 'prospect_id');
    }
}
