<?php

namespace App\Models\Intranet;

use App\Models\ProspectRiego;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Riego extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'riegos';

    protected $fillable = [
        'name',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    public function clienteRiego()
    {
        return $this->hasMany(ClienteRiego::class, 'riego_id');
    }

    public function prospectRiego()
    {
        return $this->hasMany(ProspectRiego::class, 'riego_id');
    }
}
