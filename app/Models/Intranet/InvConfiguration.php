<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvConfiguration extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'inv_category_id'
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['code', 'name', 'description', 'price',]);
    }


    public function invCategory()
    {
        return $this->belongsTo(InvCategory::class, 'inv_category_id');
    }

    public function invModels()
    {
        return $this->belongsToMany(InvModel::class);
    }
}
