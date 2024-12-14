<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TechniciansInvoice extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $fillable = [
        'folio',
        'cantidad',
        'fecha',
        'horas_facturadas',
        'comentarios',
        'tecnico_id',
        'wo_id'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['folio']);
    }

    public function tecnico()
    {
        return $this->belongsTo(Empleado::class, 'tecnico_id');
    }

    public function wo()
    {
        return $this->belongsTo(WorkOrder::class, 'wo_id');
    }
}
