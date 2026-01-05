<?php

namespace App\Models\Intranet;

use App\Models\ProspectCultivo;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoCultivo extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'tipos_cultivo';

    protected $fillable = [
        'name',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    public function clienteCultivo()
    {
        return $this->hasMany(ClienteCultivo::class, 'tipo_cultivo_id');
    }

    public function prospectCultivo()
    {
        return $this->hasMany(ProspectCultivo::class, 'tipo_cultivo_id');
    }
}
