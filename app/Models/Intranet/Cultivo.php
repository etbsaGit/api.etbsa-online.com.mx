<?php

namespace App\Models\Intranet;

use App\Models\ProspectCultivo;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cultivo extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'cultivos';

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
        return $this->hasMany(ClienteCultivo::class, 'cultivo_id');
    }

    public function prospectCultivo()
    {
        return $this->hasMany(ProspectCultivo::class, 'cultivo_id');
    }

    public function invercionesAgricolas()
    {
        return $this->hasMany(AgricolaInversion::class, 'cultivo_id');
    }

    public function invercionesA()
    {
        return $this->hasMany(InversionesAgricola::class, 'cultivo_id');
    }
}
