<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDeDesvinculacion extends Model
{
    use HasFactory;

    protected $table = 'tipos_de_desvinculaciones';

    protected $fillable = [
        'nombre',
    ];


    public function desvinculacion(){
        return $this->hasMany(Desvinculacion::class,'desvinculacion_id');
    }
}
