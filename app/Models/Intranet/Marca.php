<?php

namespace App\Models\Intranet;

use App\Models\Caja\CajaPago;
use App\Models\ProspectMaquina;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marca extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'marcas';

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
        return $this->hasMany(Machine::class, 'marca_id');
    }

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'marca_id');
    }

    public function pagos()
    {
        return $this->hasMany(CajaPago::class, 'marca_id');
    }
}
