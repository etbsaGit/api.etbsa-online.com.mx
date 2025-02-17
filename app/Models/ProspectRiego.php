<?php

namespace App\Models;

use App\Models\Intranet\Riego;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProspectRiego extends Model
{
    use HasFactory;

    protected $table = 'prospect_riegos';

    protected $fillable = [
        'hectareas_propias',
        'hectareas_rentadas',
        'marca',
        'prospect_id',
        'riego_id',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }

    public function riego()
    {
        return $this->belongsTo(Riego::class, 'riego_id');
    }
}
