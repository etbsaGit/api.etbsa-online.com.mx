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
        'controlable_id',
        'controlable_type'
    ];

    public function documento(){
        return $this->belongsTo(Documento::class,'estatus_id');
    }

    public function controlable(): MorphTo
    {
        return $this->morphTo();
    }

}
