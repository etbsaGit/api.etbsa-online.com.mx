<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estatus extends Model
{
    use HasFactory;

    protected $table = 'estatus';

    protected $fillable = [
        'nombre',
        'clave',
        'tipo_estatus',
        'color'
    ];

    public function documento(){
        return $this->hasMany(Documento::class,'estatus_id');
    }


}
