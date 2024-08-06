<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kinship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function referencia()
    {
        return $this->hasMany(Referencia::class, 'kinship_id');
    }
}
