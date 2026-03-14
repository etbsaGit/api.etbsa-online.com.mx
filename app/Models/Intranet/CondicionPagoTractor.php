<?php

namespace App\Models\Intranet;
use App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleTractor extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'condicion_pago_tractor';

    protected $fillable = [
        'sale_tractor_id',
        'enganche',
        'monto_financiado',
        'plazo_meses',
        'comments',
        'pago_periodo',
        'fecha_primer_pago',
        'estatus_id'

    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['order']);
    }

    public function saleTractor()
    {
        return $this->belongsTo(SaleTractor::class, 'sale_tractor_id');
    }

    public function estatus(){
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }




}
