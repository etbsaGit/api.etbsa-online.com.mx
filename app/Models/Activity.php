<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'details',
        'completed',
        'event_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}