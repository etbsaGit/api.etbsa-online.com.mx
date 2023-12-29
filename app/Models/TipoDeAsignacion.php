<?php

namespace App\Models;

use App\Models\Asignacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoDeAsignacion extends Model
{
    use HasFactory;

    protected $table = 'tipos_de_asignaciones';

    protected $fillable = [
        'nombre',
    ];

    public function asignacion(){
        return $this->hasMany(Asignacion::class,'tipoDeAsignacion_id');
    }
}
