<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityTechnician extends Model
{
    use HasFactory;

    protected $fillable = [
       'nombre',
       'status_id'
    ];

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'status_id');
    }

    public function techniciansLog()
    {
        return $this->hasMany(TechniciansLog::class, 'activity_technician_id');
    }
}
