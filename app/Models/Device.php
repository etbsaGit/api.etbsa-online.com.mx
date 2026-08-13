<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable =[
        'user_id',
        'expo_token',
        'platform',
        'brand',
        'manufacturer',
        'model_name',
        'os_version',
        'application_id',
        'linked_at',
    ];

    protected $cast = [
        'linked_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
