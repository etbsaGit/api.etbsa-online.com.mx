<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsedDoc extends Model
{
    use HasFactory;

    protected $fillable = ['name','extension','used_id', 'path'];

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
            get: fn () => "used/id_" . $this->used->serial,
        );
    }

    public function used()
    {
        return $this->belongsTo(Used::class, 'used_id');
    }
}
