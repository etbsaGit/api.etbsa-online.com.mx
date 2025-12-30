<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TechnologicalCapability extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'name',
        'level',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name', 'level']);
    }

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'p_clientes_technological_capabilities');
    }
}
