<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingCerteza extends Model
{
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking_certeza';
    protected $fillable = [
        'name',
        'porcentaje',
        'color'
    ];

    public function tracking(){
        return $this->hasMany(Tracking::class,'certeza_id');
    }

    public function activities(){
        return $this->hasMany(TrackingActivity::class,'certeza_id');
    }

}
