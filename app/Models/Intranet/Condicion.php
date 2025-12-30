<?php

namespace App\Models\Intranet;

use App\Models\ProspectMaquina;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Condicion extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'condiciones';

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
        return $this->hasMany(Machine::class, 'condicion_id');
    }

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'condicion_id');
    }
}
