<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Termination extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason_id',
        'comments',
        'date',
        'estatus_id',
        'empleado_id'
    ];

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function reason()
    {
        return $this->belongsTo(Estatus::class, 'reason_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
