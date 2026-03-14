<?php

namespace App\Models\Intranet;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model{
  use HasFactory;

  protected $table = 'caja_tipos_pagos';

  protected $fillable =[
    'nombre',
    'descripcion'
  ];

}

?>
