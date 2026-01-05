<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tactic extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'name',
    ];
    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    public function cliente()
    {
        return $this->hasMany(Cliente::class, 'tactic_id');
    }
}
