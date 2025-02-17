<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProspectServicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribuidor',
        'ubicacion',
        'prospect_id'
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
}
