<?php

namespace App\Models\Intranet;

use App\Models\Used;
use App\Models\ProspectMaquina;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEquipo extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'tipos_equipo';

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
        return $this->hasMany(Machine::class, 'tipo_equipo_id');
    }

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'tipo_equipo_id');
    }

    public function useds()
    {
        return $this->hasMany(Used::class, 'tipo_equipo_id');
    }

    public function invItems()
    {
        return $this->hasMany(InvItem::class, 'tipo_equipo_id');
    }
}
