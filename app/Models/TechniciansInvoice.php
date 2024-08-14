<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechniciansInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'cantidad',
        'fecha',
        'horas_facturadas',
        'comentarios',
        'tecnico_id',
        'wo_id'
    ];

    public function tecnico()
    {
        return $this->belongsTo(Empleado::class, 'tecnico_id');
    }

    public function wo()
    {
        return $this->belongsTo(WorkOrder::class, 'wo_id');
    }
}
