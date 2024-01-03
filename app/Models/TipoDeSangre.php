<?php

namespace App\Models;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoDeSangre extends Model
{
    use HasFactory;

    protected $table = 'tipos_de_sangre';

    protected $fillable = [
        'nombre',
        'puede_recibir',
        'puede_donar',
    ];

    public function empleado(){
        return $this->hasMany(Empleado::class,'tipo_de_sangre_id');
    }
}
