<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoDeEstudio extends Model
{
    use HasFactory;

    protected $table = 'estados_del_estudio';

    protected $fillable = [
        'nombre',
    ];

    public function estudio(){
        return $this->hasMany(Estudio::class,'estadoDelEstudio_id');
    }
}
