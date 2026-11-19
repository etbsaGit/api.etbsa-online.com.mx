<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedores extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'proveedores';

    protected $fillable = [
        'razon_social',
        'tipo_persona',
        'rfc',
        'domicilio',
        'correo',
        'telefono',
        'dias_credito',
        'descripcion',
        'registered_by',
    ];

    protected $appends = [
        'nombre',
        'can_edit',
    ];

    /**
     * Accessor de compatibilidad para código que accede a $proveedor->nombre
     */
    public function getNombreAttribute(): ?string
    {
        return $this->razon_social;
    }

    /**
     * Accessor que indica si el usuario autenticado tiene permisos para editar
     * la información del proveedor o subir/modificar sus documentos.
     */
    public function getCanEditAttribute(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('Proveedores') || $user->hasRole('Admin')) {
            return true;
        }

        return !empty($this->registered_by) && (int) $this->registered_by === (int) $user->id;
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function docs()
    {
        return $this->hasMany(ProveedoresDocs::class, 'proveedor_id');
    }

    public function ordenesServicio()
    {
        return $this->hasMany(VehiclesOrdenesServicio::class, 'proveedor_id');
    }

    public function detallesServicio()
    {
        return $this->hasMany(VehiclesDetallesServicio::class, 'proveedor_id');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('razon_social', 'LIKE', '%' . $search . '%')
                    ->orWhere('rfc', 'LIKE', '%' . $search . '%')
                    ->orWhere('correo', 'LIKE', '%' . $search . '%')
                    ->orWhere('telefono', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($filters['tipo_persona'])) {
            $query->where('tipo_persona', $filters['tipo_persona']);
        }

        if (!empty($filters['rfc'])) {
            $query->where('rfc', 'LIKE', '%' . $filters['rfc'] . '%');
        }

        if (!empty($filters['razon_social'])) {
            $query->where('razon_social', 'LIKE', '%' . $filters['razon_social'] . '%');
        }

        return $query;
    }
}
