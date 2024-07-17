<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segmentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function cliente()
    {
        return $this->hasMany(Cliente::class, 'segmentation_id');
    }
}
