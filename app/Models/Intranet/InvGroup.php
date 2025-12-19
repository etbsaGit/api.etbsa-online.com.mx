<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvGroup extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'name',
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    // ---------------------------------invCategories---------------------------------------------------------
    public function invCategories()
    {
        return $this->hasMany(InvCategory::class, 'inv_group_id');
    }
}
