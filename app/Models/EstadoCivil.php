<?php

namespace App\Models;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstadoCivil extends Model
{
    use HasFactory;

    protected $table = 'estados_civiles';

    protected $fillable = [
        'nombre',
    ];

    public function empleado(){
        return $this->hasMany(Empleado::class,'estado_civil_id');
    }
}
