<?php

namespace App\Models;

use App\Models\User;
use App\Models\Linea;
use App\Models\Puesto;
use App\Models\Sucursal;
use App\Models\Expediente;
use App\Models\EstadoCivil;
use App\Models\Departamento;
use App\Models\TipoDeSangre;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\Action;

class Empleado extends Model
{
    use HasFactory;

    use FilterableModel;

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
        'correo_institucional',
        'usuario_x',
        'productividad',

        'escolaridad_id',
        'user_id',
        'puesto_id',
        'sucursal_id',
        'linea_id',
        'departamento_id',
        'estado_civil_id',
        'tipo_de_sangre_id',
        'jefe_directo_id',
        'estatus_id',

        'descripcion_puesto',
        'carrera',

        'technician_id'
    ];

    protected $appends = ['picture', 'nombreCompleto', 'desempenoManoObra'];

    public function picture(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->fotografia ? Storage::disk('s3')->url($this->fotografia) : null
        );
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->segundo_nombre . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno;
    }

    public function getDesempenoManoObraAttribute()
    {
        // Obtén el mes y año más actuales de HorasTechnician
        $latestRecord = HorasTechnician::where('tecnico_id', $this->id)
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->first();

        if (!$latestRecord) {
            return null; // No hay registros en HorasTechnician
        }

        // Obtén el mes y año más actuales
        $mes = $latestRecord->mes;
        $anio = $latestRecord->anio;

        // Encuentra el registro del mes y año más actuales para el empleado
        $currentMonthYearRecord = HorasTechnician::where('mes', $mes)
            ->where('anio', $anio)
            ->where('tecnico_id', $this->id) // Asegúrate de que `tecnico_id` coincida con el ID del empleado
            ->first();

        if (!$currentMonthYearRecord) {
            return null; // No hay registros para el mes y año más actuales
        }

        // Calcula el desempeño de mano de obra
        $facturadas = $currentMonthYearRecord->facturadas;
        $conIngreso = $currentMonthYearRecord->con_ingreso;

        if ($conIngreso == 0) {
            return null; // Evita la división por cero
        }

        return round(($facturadas / $conIngreso) * 100, 2);
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "empleados/id_" . $this->id . "/foto_de_perfil",
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

    public function jefe_directo()
    {
        return $this->belongsTo(Empleado::class, 'jefe_directo_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'empleado_id');
    }

    public function activity()
    {
        return $this->hasMany(Activity::class, 'empleado_id');
    }

    public function termination()
    {
        return $this->hasOne(Termination::class);
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

    public function horasFacturadas()
    {
        return $this->hasMany(HorasTechnician::class, 'tecnico_id');
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

    public function bay()
    {
        return $this->hasMany(Bay::class, 'tecnico_id');
    }

    public function workOrder()
    {
        return $this->hasMany(WorkOrder::class, 'tecnico_id');
    }

    //----------------------------------SkillRating---------------------------------------------------------
    public function skillRating()
    {
        return $this->hasMany(SkillRating::class, 'empleado_id');
    }

    // ---------------------------------scope---------------------------------------------------------

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
