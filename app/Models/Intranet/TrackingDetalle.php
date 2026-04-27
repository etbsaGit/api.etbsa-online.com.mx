<?php
namespace App\Models\Intranet;

use App\Models\Intranet\Product;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class TrackingDetalle extends Model{
    use HasFactory;
    use FilterableModel;
    protected $table = 'tracking_detalle';
    protected $fillable = [
        'tracking_id',
        'product_id',
        'cantidad',
        'subtotal',
        'precio_unidad',
        'currency_id'
    ];

    public function tracking(){
        return $this->belongsTo(Tracking::class,'tracking_id');
    }
    public function productos(){
        return $this->belongsTo(Product::class,'product_id');
    }
    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function scopeFilter(Builder $query,$filters){
        return $this->scopeFilterSearch($query,$filters,['tracking_id']);
    }

}
