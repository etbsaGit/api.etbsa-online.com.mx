<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingTipoSeguimiento extends Model{
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking_tipo_seguimiento';
    protected $fillable = [
        'name'
    ];

    public function activities(){
        return $this->hasMany(TrackingActivity::class);
    }


}
