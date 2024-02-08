<?php

namespace App\Http\Controllers\Api;

use App\Models\Empleado;
use App\Models\Plantilla;
use App\Traits\UploadableFile;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Empleado\PicRequest;
use App\Http\Requests\Empleado\PutRequest;
use App\Http\Requests\Empleado\StoreRequest;

class EmpleadoController extends ApiController
{

    use UploadableFile;

    public function index()
    {
        return response()->json(Empleado::paginate(5));
    }

    public function all()
    {
        return response()->json(Empleado::with(['archivable', 'archivable.requisito', 'escolaridad', 'departamento', 'desvinculacion', 'estado_civil', 'jefe_directo', 'linea', 'puesto', 'sucursal', 'tipo_de_sangre', 'user'])->get());
    }

    public function store(StoreRequest $request)
    {
        return DB::transaction(function () use ($request) {
            return tap(
                Empleado::create($request->validated()),
                function (Empleado $empleado) {
                    $plantilla = Plantilla::find(1);
                    $ids = $plantilla->requisito->pluck('id');
                    // Crea el expediente asociado al empleado
                    $expediente = $empleado->archivable()->create(['nombre' => $empleado->rfc . ' expediente']);
                    // Adjunta requisitos a través de la relación
                    $expediente->requisito()->syncWithPivotValues($ids, ['comentaio' => 'com 1']);
                }
            );
        });
    }

    public function show(Empleado $empleado)
    {
        return response()->json($empleado->load('archivable', 'archivable.requisito', 'escolaridad', 'departamento', 'desvinculacion', 'estado_civil', 'jefe_directo', 'linea', 'puesto', 'sucursal', 'tipo_de_sangre', 'user'));
    }

    public function update(PutRequest $request, Empleado $empleado)
    {
        $empleado->update($request->only([
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
        ]));
        $empleado->constelacion()->sync($request->get('constelacion_id'));
        $empleado->alergias()->sync($request->get('alergias_id'));
        $empleado->enfermedad()->sync($request->get('enfermedad_id'));
        return response()->json($empleado);
    }

    public function destroy(Empleado $empleado)
    {
        $empleado->delete();
        return response()->json("ok");
    }

    public function uploadPicture(PicRequest $request, Empleado $empleado){
        if ($request->hasFile('pic')) {
            $pic = $request->file('pic');

            if ($empleado->fotografia) {
                Storage::disk('s3')->delete($empleado->fotografia);
            }
            
            $path = $this->uploadOne($pic, $empleado->default_path_folder, 's3');
    
            $updateData = ['fotografia' => $path];
    
            $empleado->update($updateData);
    
            return response()->json(['message' => 'Fotografía actualizada con éxito']);
        } else {
            return response()->json(['error' => 'No se ha enviado una foto en la solicitud.'], 400);
        }
    }
}
