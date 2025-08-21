<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceArchive extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'path', 'status', 'service_id'];

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
            get: fn() => "servicios/id_" . $this->service_id,
        );
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
