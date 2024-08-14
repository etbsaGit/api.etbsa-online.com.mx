<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkOrder extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $fillable = [
        'ot',
        'cliente',
        'maquina',
        'descripcion',
        'fecha_ingreso',
        'fecha_entrega',
        'mano_obra',
        'refacciones',
        'horas_facturadas',
        'horas_reales',
        'comentarios',

        'tecnico_id',
        'estatus_id',
        'estatus_taller_id',
        'type_id',
        'bay_id',
        'sucursal_id',
        'linea_id'
    ];

    public function tecnico()
    {
        return $this->belongsTo(Empleado::class, 'tecnico_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function estatusTaller()
    {
        return $this->belongsTo(Estatus::class, 'estatus_taller_id');
    }

    public function type()
    {
        return $this->belongsTo(Estatus::class, 'type_id');
    }

    public function bay()
    {
        return $this->belongsTo(Bay::class, 'bay_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function workOrderDoc()
    {
        return $this->hasMany(WorkOrderDoc::class, 'work_order_id');
    }

    public function invoices()
    {
        return $this->hasMany(TechniciansInvoice::class, 'wo_id');
    }

    public function techniciansLog()
    {
        return $this->hasMany(TechniciansInvoice::class, 'wo_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($workOrder) {

            $workOrder->workOrderDoc->each(function ($one_doc) {

                Storage::disk('s3')->delete($one_doc->path);

                $one_doc->delete();
            });
        });
    }
}
