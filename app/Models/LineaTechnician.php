<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineaTechnician extends Model
{
    use HasFactory;

    protected $table = 'p_linea_technician';

    protected $fillable = [
        'technician_id',
        'linea_id',
    ];

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    public function qualification()
    {
        return $this->hasMany(Qualification::class, 'linea_technician_id');
    }
}
