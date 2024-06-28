<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'linea_id',
        'sucursal_id',
        'departamento_id',
        'puesto_id',
        'estatus_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function postDoc()
    {
        return $this->hasMany(PostDoc::class, 'post_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {

            $post->postDoc->each(function ($one_post) {

                Storage::disk('s3')->delete($one_post->path);

                $one_post->delete();
            });
        });
    }
}
