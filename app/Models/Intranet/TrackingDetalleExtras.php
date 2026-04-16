<?php
namespace App\Models\Intranet;

use App\Models\Intranet\Product;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingDetalleExtras extends Model{
    use HasFactory;
    use FilterableModel;
    protected $table = 'tracking_detalle_extras';
    protected $fillable = [
        'tracking_id',
        'extra_id',
        'cantidad',
        'subtotal',
        'precio_unidad',
    ];

    public function tracking(){
        return $this->belongsTo(Tracking::class,'tracking_id');
    }
    public function item(){
        return $this->belongsTo(ProductExtras::class,'extra_id');
    }

    public function scopeFilter(Builder $query,$filters){
        return $this->scopeFilterSearch($query,$filters,['tracking_id']);
    }

}
