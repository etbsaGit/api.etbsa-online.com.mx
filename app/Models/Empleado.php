<?php

namespace App\Models;

use App\Models\User;
use App\Models\Linea;
use App\Models\Puesto;
use App\Models\Alergia;
use App\Models\Estudio;
use App\Models\Sucursal;
use App\Models\Asignacion;
use App\Models\Enfermedad;
use App\Models\Expediente;
use App\Models\EstadoCivil;
use App\Models\Constelacion;
use App\Models\Departamento;
use App\Models\TipoDeSangre;
use App\Models\ExperienciaLaboral;
use App\Models\ReferenciaPersonal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'fotografia',
        'nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'telefono_institucional',
        'fecha_de_nacimiento',
        'curp',
        'rfc',
        'ine',
        'licencia_de_manejo',
        'nss',
        'fecha_de_ingreso',
        'hijos',
        'dependientes_economicos',
        'cedula_profesional',
        'matriz',
        'sueldo_base',
        'comision',
        'numero_exterior',
        'numero_interior',
        'calle',
        'colonia',
        'codigo_postal',
        'ciudad',
        'estado',
        'cuenta_bancaria',
        'constelacion_familiar',
        'status',
        'correo_institucional',

        'escolaridad_id',
        'user_id',
        'puesto_id',
        'sucursal_id',
        'linea_id',
        'departamento_id',
        'estado_civil_id',
        'tipo_de_sangre_id',
        'desvinculacion_id',
        'jefe_directo_id',

        'descripcion_puesto',
        'carrera',

        'technician_id'
    ];

    protected $appends = ['picture'];

    public function picture(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fotografia ? Storage::disk('s3')->url($this->fotografia) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn () => "empleados/id_" . $this->id . "/foto_de_perfil",
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function escolaridad()
    {
        return $this->belongsTo(Escolaridad::class, 'escolaridad_id');
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function estado_civil()
    {
        return $this->belongsTo(EstadoCivil::class, 'estado_civil_id');
    }

    public function tipo_de_sangre()
    {
        return $this->belongsTo(TipoDeSangre::class, 'tipo_de_sangre_id');
    }

    public function archivable()
    {
        return $this->morphMany(Expediente::class, 'archivable');
    }

    public function desvinculacion()
    {
        return $this->belongsTo(Desvinculacion::class, 'desvinculacion_id');
    }

    public function jefe_directo()
    {
        return $this->belongsTo(Empleado::class, 'jefe_directo_id');
    }

    public function estudio()
    {
        return $this->hasMany(Estudio::class, 'empleado_id');
    }

    public function referencia_personal()
    {
        return $this->hasMany(ReferenciaPersonal::class, 'empleado_id');
    }

    public function experiencia_laboral()
    {
        return $this->hasMany(ExperienciaLaboral::class, 'empleado_id');
    }

    public function asignacion()
    {
        return $this->hasMany(Asignacion::class, 'empleado_id');
    }

    // -----------------------------------------------------------------

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'jefe_directo_id');
    }

    public function career()
    {
        return $this->hasMany(Career::class, 'empleado_id');
    }

    // ----------------------------------------------------------------------------------

    public function constelacion()
    {
        return $this->belongsToMany(Constelacion::class, 'p_constelaciones_empleados', 'empleado_id', 'constelacion_id')->withTimestamps();
    }

    public function alergias()
    {
        return $this->belongsToMany(Alergia::class, 'p_alergias_empleados', 'empleado_id', 'alergias_id')->withTimestamps();
    }

    public function enfermedad()
    {
        return $this->belongsToMany(Enfermedad::class, 'p_enfermedades_empleados', 'empleado_id', 'enfermedad_id')->withTimestamps();
    }
    //----------------------------------Qualification---------------------------------------------------------
    public function qualification()
    {
        return $this->belongsToMany(Qualification::class, 'p_empleado_qualification', 'empleado_id', 'qualification_id');
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    //----------------------------------SkillRating---------------------------------------------------------
    public function skillRating()
    {
        return $this->hasMany(SkillRating::class, 'empleado_id');
    }

    // ---------------------------------scope---------------------------------------------------------

    public function scopeFilter(Builder $query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $query->where($key, $value);
            }
        }
    }

    public function scopeFiltertwo(Builder $query, array $filters)
    {
        foreach ($filters as $key => $values) {
            if ($values !== null) {
                if (is_array($values)) {
                    $query->where(function ($query) use ($key, $values) {
                        foreach ($values as $value) {
                            $query->orWhere($key, $value);
                        }
                    });
                } else {
                    $query->Where($key, $values);
                }
            }
        }
    }
}
