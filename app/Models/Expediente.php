<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Expediente extends Model
{
    use HasFactory;

    protected $table = 'expedientes';

    protected $fillable = [
        'nombre',
        'archivable_id',
        'archivable_type'
    ];

    public function documento()
    {
        return $this->hasMany(Documento::class, 'expediente_id');
    }

    public function archivable(): MorphTo
    {
        return $this->morphTo();
    }
}
