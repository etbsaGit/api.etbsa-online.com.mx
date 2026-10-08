<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VacationDiaCuenta extends Model
{
    use HasFactory, FilterableModel;
    protected $table = 'vacation_dia_cuenta';

    protected $fillable = [
        'nombre',
        'fecha'
    ];

    public function vacationDays()
    {
        return $this->hasMany(VacationDay::class, 'vacation_dia_cuenta_id');
    }
}
