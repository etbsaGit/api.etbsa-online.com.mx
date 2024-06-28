<?php

namespace App\Models;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',

    ];

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'departamento_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'departamento_id');
    }
}
