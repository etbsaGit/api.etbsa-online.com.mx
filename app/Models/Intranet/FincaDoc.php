<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FincaDoc extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'path', 'finca_id'];

    protected $appends = ['realpath'];

    public function realpath(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->path ? Storage::disk('s3')->url($this->path) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "finca/id_" . $this->finca_id,
        );
    }

    public function finca()
    {
        return $this->belongsTo(Finca::class, 'finca_id');
    }
}
