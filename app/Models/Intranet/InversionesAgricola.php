<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InversionesAgricola extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'ciclo',
        'hectareas',
        'costo',
        'cultivo_id',
        'cliente_id',
    ];

    protected $appends = ['total'];


    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class, 'cultivo_id');
    }

    public function getTotalAttribute()
    {
        return $this->hectareas * $this->costo;
    }
}
