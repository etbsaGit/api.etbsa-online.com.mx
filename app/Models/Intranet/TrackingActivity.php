<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class TrackingActivity extends Model{
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking_activity';
    protected $fillable = [
        'tracking_id',
        'tipo_seguimiento_id',
        'certeza_id',
        'ultimo_precio_tratar',
        'tarifa_cambio',
        'currency_id',
        'notas',
        'date_next_tracking'
    ];

    public function tracking()
    {
        return $this->belongsTo(Tracking::class,'tracking_id');
    }
    public function tipoSeguimiento(){
        return $this->belongsTo(TrackingTipoSeguimiento::class,'tipo_seguimiento_id');
    }
    public function certeza(){
        return $this->belongsTo(TrackingCerteza::class,'certeza_id');
    }
    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }
}
