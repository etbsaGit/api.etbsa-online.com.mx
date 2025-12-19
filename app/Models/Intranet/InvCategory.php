<?php

namespace App\Models\Intranet;

use App\Models\Estatus;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvCategory extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'name',
        'description',
        'status_id',
        'inv_group_id',
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    public function status()
    {
        return $this->belongsTo(Estatus::class, 'status_id');
    }

    public function invGroup()
    {
        return $this->belongsTo(InvGroup::class, 'inv_group_id');
    }

    public function invConfigurations()
    {
        return $this->hasMany(InvConfiguration::class, 'inv_category_id');
    }
}
