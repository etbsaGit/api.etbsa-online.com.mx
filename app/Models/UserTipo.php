<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTipo extends Model
{
    use HasFactory;
    
    protected $table = "user_tipo";

    protected $fillable = ['name'];

     
}
