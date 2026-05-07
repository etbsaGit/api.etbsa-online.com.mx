<?php

namespace App\Models\Intranet;

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Sucursal;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingFeedback extends Model
{
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking_feedback';

    protected $fillable = [
        'comentario',
        'empleado_id',
        'tracking_id',
        'situacion_id'
    ];

    public function empleado(){
        return $this->belongsTo(Empleado::class,'empleado_id');
    }

    public function tracking(){
        return $this->belongsTo(Tracking::class,'tracking_id');
    }

    public function situacion(){
        return $this->belongsTo(Estatus::class,'situacion_id');
    }
}
