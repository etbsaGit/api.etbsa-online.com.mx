<?php

namespace App\Models\Intranet;

use App\Models\ProspectMaquina;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClasEquipo extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'clas_equipos';

    protected $fillable = [
        'name',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    public function machine()
    {
        return $this->hasMany(Machine::class, 'clas_equipo_id');
    }

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'clas_equipo_id');
    }

    public function invModels()
    {
        return $this->hasMany(InvModel::class, 'clas_equipo_id');
    }
}
