<?php

namespace App\Models;

use App\Models\User;
use App\Models\Linea;
use App\Models\Puesto;
use App\Models\Alergia;
use App\Models\Estudio;
use App\Models\Sucursal;
use App\Models\Direccion;
use App\Models\Antiguedad;
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
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'nombre',
        'segundoNombre',
        'apellidoPaterno',
        'apellidoMaterno',
        'telefono',
        'fechaDeNacimiento',
        'curp',
        'rfc',
        'ine',
        'licenciaDeManejo',
        'nss',
        'fechaDeIngreso',
        'hijos',
        'dependientesEconomicos',
        'cedulaProfesional',
        'matriz',
        'sueldoBase',
        'comision',
        'foto',
        'numeroExterior',
        'numeroInterior',
        'calle',
        'colonia',
        'codigoPostal',
        'ciudad',
        'estado',
        'cuentaBancaria',
        'constelacionFamiliar',
        'status',

        'escolaridad_id',
        'user_id',
        'puesto_id',
        'sucursal_id',
        'linea_id',
        'departamento_id',
        'estadoCivil_id',
        'tipoDeSangre_id',
        'expediente_id',
        'desvinculacion_id',
        'jefeDirecto_id',
    ];

    public function user_id(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function escolaridad_id(){
        return $this->belongsTo(Escolaridad::class,'escolaridad_id');
    }

    public function puesto_id(){
        return $this->belongsTo(Puesto::class,'puesto_id');
    }

    public function sucursal_id(){
        return $this->belongsTo(Sucursal::class,'sucursal_id');
    }

    public function linea_id(){
        return $this->belongsTo(Linea::class,'linea_id');
    }

    public function departamento_id(){
        return $this->belongsTo(Departamento::class,'departamento_id');
    }

    public function estadoCivil_id(){
        return $this->belongsTo(EstadoCivil::class,'estadoCivil_id');
    }

    public function tipoDeSangre_id(){
        return $this->belongsTo(TipoDeSangre::class,'tipoDeSangre_id');
    }

    public function antiguedad_id(){
        return $this->belongsTo(Antiguedad::class,'antiguedad_id');
    }

    public function expediente_id(){
        return $this->belongsTo(Expediente::class,'expediente_id');
    }

    public function desvinculacion_id(){
        return $this->belongsTo(Desvinculacion::class,'desvinculacion_id');
    }

    public function jefeDirecto_id(){
        return $this->belongsTo(Empleado::class,'jefeDirecto_id');
    }

    public function estudio(){
        return $this->hasMany(Estudio::class,'empleado_id');
    }

    public function referenciaPersonal(){
        return $this->hasMany(ReferenciaPersonal::class,'empleado_id');
    }

    public function experienciaLaboral(){
        return $this->hasMany(ExperienciaLaboral::class,'empleado_id');
    }

    public function asignacion(){
        return $this->hasMany(Asignacion::class,'empleado_id');
    }


    // -----------------------------------------------------------------

    public function departamento(){
        return $this->hasOne(Departamento::class,'encargado_id');
    }

    public function linea(){
        return $this->hasOne(Linea::class,'encargado_id');
    }

    public function sucursal(){
        return $this->hasOne(Sucursal::class,'encargado_id');
    }

    public function empleado(){
        return $this->hasMany(Empleado::class,'jefeDirecto_id');
    }

    // ----------------------------------------------------------------------------------

    public function constelaciones()
    {
        return $this->belongsToMany(Constelacion::class, 'p_constelaciones_empleados', 'empleado_id', 'constelacion_id');
    }

    public function alergias()
    {
        return $this->belongsToMany(Alergia::class, 'p_alergias_empleados', 'empleado_id', 'alergias_id');
    }

    public function enfermedad()
    {
        return $this->belongsToMany(Enfermedad::class, 'p_enfermedades_empleados', 'empleado_id', 'enfermedad_id');
    }
}
