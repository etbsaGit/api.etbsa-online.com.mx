<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NuevaTecnologia extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'nuevas_tecnologias';

    protected $fillable = [
        'name',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    public function clienteTechnology()
    {
        return $this->hasMany(ClienteTechnology::class, 'nueva_tecnologia_id');
    }
}
