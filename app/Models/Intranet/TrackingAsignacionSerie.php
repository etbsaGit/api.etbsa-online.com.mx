<?php

namespace App\Models\Intranet;

use App\Models\Empleado;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class TrackingAsignacionSerie extends Model{
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking_asignacion_serie';
    protected $fillable = [
        'tracking_id',
        'inv_item_id',
        'asignado_por',
        'comentarios',
    ];

    public function tracking(){
        return $this->belongsTo(Tracking::class,'tracking_id');
    }
    public function asignadoPor(){
        return $this->belongsTo(Empleado::class, 'asignado_por');
    }
    public function invItem(){
        return $this->belongsTo(InvItem::class, 'inv_item_id');
    }
    public function empleado(){
        return $this->belongsTo(Empleado::class, 'asignado_por');
    }
}
