<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProspectDistribucion extends Model
{
    use HasFactory;

    protected $table = 'prospect_distribiciones';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'hectareas_propias',
        'hectareas_rentadas',
        'prospect_id',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
}
