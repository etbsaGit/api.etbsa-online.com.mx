<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProspectAgp extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca',
        'equipo',
        'prospect_id'
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
}
