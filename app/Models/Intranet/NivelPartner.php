<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelPartner extends Model
{
    use HasFactory;
    protected $table = 'nivel_partner';
    protected $fillable = [
        'name'
    ];

    public function partnerRiego()
    {
        return $this->belongsTo(Cliente::class, 'nivel_partner_riego_id');
    }
}
