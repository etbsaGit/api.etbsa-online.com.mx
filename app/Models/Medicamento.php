<?php

namespace App\Models;

use App\Models\Alergia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    public function alergia(){
        return $this->hasMany(Alergia::class,'medicamento_id');
    }

    public function enfermedad(){
        return $this->hasMany(Enfermedad::class,'medicamento_id');
    }
}
