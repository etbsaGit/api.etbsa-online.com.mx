<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    public function enfermedad(){
        return $this->hasMany(Enfermedad::class,'medicamento_id');
    }
}
