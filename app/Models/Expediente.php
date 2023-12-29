<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    use HasFactory;

    protected $fillable = [
    ];

    public function documento(){
        return $this->hasMany(Documento::class,'expediente_id');
    }

    public function empleado(){
        return $this->hasOne(Empleado::class,'expediente_id');
    }
}
