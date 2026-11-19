<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ProveedoresDocs extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'proveedores_docs';

    protected $fillable = [
        'nombre_doc',
        'url',
        'estatus_id',
        'notas',
        'validated_by',
        'proveedor_id',
        'fecha_vencimiento',
        'uploaded_by'
    ];

    protected $appends = [
        'documento_url'
    ];

    public function getDocumentoUrlAttribute(): ?string
    {
        if (empty($this->url)) {
            return null;
        }

        if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')) {
            return $this->url;
        }

        return Storage::disk('s3')->url($this->url);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'proveedor_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
