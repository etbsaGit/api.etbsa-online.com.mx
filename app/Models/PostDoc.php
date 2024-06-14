<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostDoc extends Model
{
    use HasFactory;

    protected $fillable = ['name','extension','post_id', 'path'];

    protected $appends = ['realpath'];

    public function realpath(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->path ? Storage::disk('s3')->url($this->path) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn () => "blog/docs/id_" . $this->post_id,
        );
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
