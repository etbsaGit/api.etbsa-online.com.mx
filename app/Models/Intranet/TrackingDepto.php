<?php
namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingDepto extends Model{
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking_depto';
    protected $fillable = [
        'name'
    ];

    public function tracking(){
        return $this->hasMany(Tracking::class,'depto_id');
    }


}
