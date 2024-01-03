<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoQueAvala extends Model
{
    use HasFactory;

    protected $table = 'documentos_que_avalan';

    protected $fillable = [
        'nombre',
    ];

    public function estudio(){
        return $this->hasMany(Estudio::class,'documento_que_avala_id');
    }
}
