<?php

namespace App\Models;

use App\Models\Requisito;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'fechaDeVencimiento',

        'requisito_id',
        'expediente_id'
    ];

    public function requisito_id(){
        return $this->belongsTo(Requisito::class,'requisito_id');
    }


    public function expediente_id(){
        return $this->belongsTo(Expediente::class,'expediente_id');
    }
}
