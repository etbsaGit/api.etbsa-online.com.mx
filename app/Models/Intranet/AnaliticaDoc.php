<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnaliticaDoc extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'path', 'analitica_id', 'status', 'comentarios'];

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
            get: fn() => "analitica/id_" . $this->analitica_id,
        );
    }

    public function analitica()
    {
        return $this->belongsTo(Analitica::class, 'analitica_id');
    }
}
