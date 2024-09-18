<?php

namespace App\Models\Intranet;

use App\Models\Estatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientesDoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'extension',
        'path',
        'expiration_date',
        'comments',
        'status_id',
        'cliente_id',
    ];

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
            get: fn() => "intranet/cliente/id_" . $this->cliente->rfc,
        );
    }

    public function status()
    {
        return $this->belongsTo(Estatus::class, 'status_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
