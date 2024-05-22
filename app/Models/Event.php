<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'date',
        'sucursal_id',
        'empleado_id'
    ];

    // public function getDateAttribute($value)
    // {
    //     return Carbon::parse($value)->format('d-m-Y');
    // }

    // public function getEndTimeAttribute($value)
    // {
    //     return Carbon::createFromFormat('H:i:s', $value)->format('H:i');
    // }

    // public function getStartTimeAttribute($value)
    // {
    //     return Carbon::createFromFormat('H:i:s', $value)->format('H:i');
    // }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
