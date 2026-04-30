<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingOrigen extends Model {
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking_origen';

    protected $fillable = [
        'name'
    ];

    public function tracking(){
        return $this->hasMany(Tracking::class,'origen_track_id');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

}
