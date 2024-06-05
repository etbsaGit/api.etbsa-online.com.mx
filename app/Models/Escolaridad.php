<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escolaridad extends Model
{
    use HasFactory;

    protected $table = 'escolaridades';

    protected $fillable = [
        'nombre',
    ];

    public function empleado(){
        return $this->hasMany(Empleado::class,'escolaridad_id');
    }
}
