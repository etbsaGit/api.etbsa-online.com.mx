<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserTipo extends Model
{
    use HasFactory;
    
    protected $table = "user_tipo";

    protected $fillable = ['name'];

    public function roles(){
        return $this->hasMany(Role::class,'user_tipo_id');
    }
    
}
