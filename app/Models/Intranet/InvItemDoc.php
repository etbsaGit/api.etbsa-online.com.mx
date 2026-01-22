<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvItemDoc extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'path', 'inv_item_id'];

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
            get: fn() => "invItem/id_" . $this->inv_item_id,
        );
    }

    public function invItem()
    {
        return $this->belongsTo(InvItem::class, 'inv_item_id');
    }
}
