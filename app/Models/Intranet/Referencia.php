<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referencia extends Model
{
    use HasFactory;

    protected $table = 'referencias';

    protected $fillable = [
        'nombre',
        'telefono',
        'kinship_id',
        'cliente_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function kinship()
    {
        return $this->belongsTo(Kinship::class, 'kinship_id');
    }
}
