<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travel extends Model
{
    use HasFactory;

    protected $table = 'travels';

    protected $fillable = [
        'start_point',
        'end_point',
        'start_time',
        'end_time',
        'event_id',
    ];

    // Accesor para start_time
    public function getStartTimeAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('H:i'); // Devuelve sin segundos
    }

    // Accesor para start_time
    public function getEndTimeAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('H:i'); // Devuelve sin segundos
    }

    public function startPointR()
    {
        return $this->belongsTo(Sucursal::class, 'start_point');
    }

    /**
     * Get the end point of the travel.
     */
    public function endPointR()
    {
        return $this->belongsTo(Sucursal::class, 'end_point');
    }

    /**
     * Get the event associated with the travel.
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
