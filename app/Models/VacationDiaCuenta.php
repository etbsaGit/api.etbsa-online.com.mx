<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class VacationDiaCuenta extends Model
{
    use HasFactory, FilterableModel, SoftDeletes;
    protected $table = 'vacation_dia_cuenta';

    protected $fillable = [
        'nombre',
        'fecha'
    ];

    public function vacationDays()
    {
        return $this->hasMany(VacationDay::class, 'vacation_dia_cuenta_id');
    }

    public function bitacoras()
    {
        return $this->hasMany(VacationDiaCuentaBitacora::class, 'dia_cuenta_id');
    }
}
