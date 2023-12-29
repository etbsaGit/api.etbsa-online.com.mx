<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escuela extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'direccion'
    ];

    public function estudio(){
        return $this->hasMany(Estudio::class,'escuela_id');
    }
}
