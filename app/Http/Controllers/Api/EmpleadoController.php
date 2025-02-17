<?php

namespace App\Http\Controllers\Api;

use App\Exports\EmpleadosExport;
use App\Exports\EmpleadosVacationsExport;
use App\Models\User;
use App\Models\Linea;
use App\Models\Puesto;
use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Plantilla;
use App\Models\Escolaridad;
use App\Models\EstadoCivil;
use App\Models\Departamento;
use App\Models\TipoDeSangre;
use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Empleado\PutRequest;
use App\Http\Requests\Empleado\StoreRequest;
use App\Models\Termination;
use Maatwebsite\Excel\Facades\Excel;


class EmpleadoController extends ApiController
{
    use UploadableFile;


    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();
        $empleado = $user->empleado;

        if ($user->hasRole('RRHH')) {
            $empleados = Empleado::filter($filters)
                ->where('estatus_id', 5)
                ->with(['archivable', 'archivable.requisito', 'escolaridad', 'departamento', 'estado_civil', 'jefe_directo', 'linea', 'puesto', 'sucursal', 'tipo_de_sangre', 'user', 'estatus', 'termination.estatus', 'termination.reason'])
                ->orderBy('sucursal_id')
                ->paginate(10);
        } else {
            $empleados = $this->getAllSubordinates($empleado);
        }

        return $this->respond($empleados);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data) {
            return tap(
                Empleado::create($request->validated()),
                function (Empleado $empleado) use ($request, $data) {
                    $plantilla = Plantilla::find(1);
                    $ids = $plantilla->requisito->pluck('id');

                    // Crea el expediente asociado al empleado
                    $expediente = $empleado->archivable()->create(['nombre' => $empleado->rfc . ' expediente']);

                    // Adjunta requisitos a través de la relación
                    $expediente->requisito()->syncWithPivotValues($ids, ['comentaio' => 'com 1']);

                    // Se le crea un usuario
                    $correo = $empleado->correo_institucional;
                    if ($correo) {
                        $usuario = User::firstOrCreate(
                            ['email' => $correo],
                            ['password' => Hash::make('password123'), 'name' => $empleado->nombre]
                        );
                        if (!$empleado->user) {
                            $empleado->user()->associate($usuario);
                            $empleado->save();
                            $empleado->user->syncRoles('Empleado');
                        }
                    }

                    if (!is_null($request['base64'])) {
                        if ($empleado->fotografia) {
                            Storage::disk('s3')->delete($empleado->fotografia);
                        }
                        $relativePath  = $this->saveImage($request['base64'], $empleado->default_path_folder);
                        $request['base64'] = $relativePath;
                        $updateData = ['fotografia' => $relativePath];
                        $empleado->update($updateData);
                    }

                    // Verifica si en $data existe 'desvinculacion'
                    if ((is_object($data) && property_exists($data, 'desvinculacion')) || (is_array($data) && array_key_exists('desvinculacion', $data))) {
                        $desvinculacionData = $request->input('desvinculacion');
                        Termination::create([
                            'reason_id' => $desvinculacionData['reason_id'],
                            'estatus_id' => $desvinculacionData['estatus_id'],
                            'date' => $desvinculacionData['date'],
                            'comments' => $desvinculacionData['comments'] ?? null,
                            'empleado_id' => $empleado->id
                        ]);
                    }
                }
            );
        });
    }


    public function show(Empleado $empleado)
    {
        return response()->json($empleado->load('archivable', 'archivable.requisito', 'escolaridad', 'departamento', 'estado_civil', 'jefe_directo', 'linea', 'puesto', 'sucursal', 'tipo_de_sangre', 'user', 'estatus'));
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
            'correo_institucional',
            'escolaridad_id',
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
        ]));

        $correo = $request->correo_institucional;
        if ($correo) {
            $usuario = User::firstOrCreate(['email' => $correo], ['password' => Hash::make("password123"), 'name' => $empleado->nombre]);
            if (!$empleado->user) {
                $empleado->user()->associate($usuario);
                $empleado->save();
                $empleado->user->syncRoles('Empleado');
            }
        }

        if (!is_null($request['base64'])) {
            if ($empleado->fotografia) {
                Storage::disk('s3')->delete($empleado->fotografia);
            }
            $relativePath  = $this->saveImage($request['base64'], $empleado->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['fotografia' => $relativePath];
            $empleado->update($updateData);
        }

        $data = $request->validated();

        // Verifica si el request contiene información de desvinculación
        if ((is_object($data) && property_exists($data, 'desvinculacion')) || (is_array($data) && array_key_exists('desvinculacion', $data))) {
            // Extrae los datos de desvinculación de la solicitud
            $desvinculacionData = $request->input('desvinculacion');

            // Busca una instancia existente de Termination asociada al empleado
            $termination = Termination::where('empleado_id', $empleado->id)->first();

            // Si se encuentra una instancia, actualiza sus datos con los datos de la solicitud
            // Si no se encuentra ninguna instancia, crea una nueva instancia de Termination
            if ($termination) {
                $termination->update([
                    'reason_id' => $desvinculacionData['reason_id'],
                    'estatus_id' => $desvinculacionData['estatus_id'],
                    'date' => $desvinculacionData['date'],
                    'comments' => $desvinculacionData['comments'] ?? null,
                    'empleado_id' => $empleado->id
                ]);
            } else {
                Termination::create([
                    'reason_id' => $desvinculacionData['reason_id'],
                    'estatus_id' => $desvinculacionData['estatus_id'],
                    'date' => $desvinculacionData['date'],
                    'comments' => $desvinculacionData['comments'] ?? null,
                    'empleado_id' => $empleado->id
                ]);
            }
        }

        return response()->json($empleado);
    }

    public function destroy(Empleado $empleado)
    {
        $empleado->delete();
        return response()->json("ok");
    }

    public function findEmpleadoByRFCandINE($rfc, $ine)
    {
        $empleado = Empleado::where('rfc', $rfc)
            ->where('ine', $ine)
            ->first();

        if ($empleado) {
            return response()->json($empleado->load('archivable', 'archivable.requisito', 'escolaridad', 'departamento', 'estado_civil', 'jefe_directo', 'linea', 'puesto', 'sucursal', 'tipo_de_sangre', 'user'));
        } else {
            return response()->json(['error' => 'No se encontro un empleado con esos datos.'], 400);
        }
    }

    private function getAllSubordinates($employee)
    {
        $subordinates = $employee->empleado()
            ->with([
                'archivable',
                'archivable.requisito',
                'escolaridad',
                'departamento',
                'estado_civil',
                'jefe_directo',
                'linea',
                'puesto',
                'sucursal',
                'tipo_de_sangre',
                'user',
                'estatus'
            ])
            ->orderBy('apellido_paterno')
            ->get();

        $allSubordinates = collect();
        foreach ($subordinates as $subordinate) {
            $allSubordinates->push($subordinate);
            $allSubordinates = $allSubordinates->merge($this->getAllSubordinates($subordinate));
        }
        return $allSubordinates;
    }

    public function getforms()
    {
        $data = [
            'empleados' => Empleado::where('estatus_id', 5)->orderBy('apellido_paterno')->get(),
            'escolaridades' => Escolaridad::all(),
            'estados_civiles' => EstadoCivil::all(),
            'tipos_de_sangre' => TipoDeSangre::all(),
            'sucursales' => Sucursal::all(),
            'departamentos' => Departamento::all(),
            'lineas' => Linea::all(),
            'puestos' => Puesto::all(),
            'estatus' => Estatus::where('tipo_estatus', 'empleado')->get(),
            'terminations' => Estatus::where('tipo_estatus', 'termination')->get(),
            'reasons' => Estatus::where('tipo_estatus', 'terminationType')->get(),
        ];
        return $this->respond($data);
    }

    public function negocios(Request $request)
    {
        $filters = $request->all();

        $data = [
            'empleados' => Empleado::filter($filters)->where('estatus_id', 5)->orderBy('apellido_paterno')->get(),
            'sucursales' => Sucursal::all(),
            'departamentos' => Departamento::all(),
            'lineas' => Linea::all(),
            'puestos' => Puesto::all(),
        ];
        return $this->respond($data);
    }

    public function getformsIndex()
    {
        $data = [
            'sucursales' => Sucursal::all(),
            'departamentos' => Departamento::all(),
            'lineas' => Linea::all(),
            'puestos' => Puesto::all(),
        ];
        return $this->respond($data);
    }

    public function getEmployeesTerminations($anio = null, $mes = null)
    {
        $query = Empleado::with(['archivable', 'archivable.requisito', 'escolaridad', 'departamento', 'estado_civil', 'jefe_directo', 'linea', 'puesto', 'sucursal', 'tipo_de_sangre', 'user', 'estatus', 'termination.estatus', 'termination.reason']); // Cargar las relaciones sucursal y termination

        // Agregar filtros para la fecha en la relación termination
        if (!is_null($anio)) {
            $query->whereHas('termination', function ($q) use ($anio) {
                $q->whereYear('date', $anio);
            });
        }

        if (!is_null($mes)) {
            $query->whereHas('termination', function ($q) use ($mes) {
                $q->whereMonth('date', $mes);
            });
        }

        // Si tanto $anio como $mes son nulos, buscar empleados con estatus_id igual a 6
        if (is_null($anio) && is_null($mes)) {
            $query->where('estatus_id', 6);
        }

        $employees = $query->get();

        return response()->json($employees);
    }

    public function export(Request $request)
    {
        // Recoger los filtros desde la solicitud
        $filters = $request->except(['search', 'page']);
        // Crear una instancia de la clase de exportación con los filtros
        $export = new EmpleadosExport($filters);

        // Obtener los datos para verificar si están vacíos
        $data = $export->collection();

        // Verificar si no hay datos para exportar
        if ($data->isEmpty()) {
            return response()->json(['error' => 'No hay datos para exportar.']);
        }

        // Exportar el archivo en formato XLSX con los filtros aplicados
        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        // Convertir el contenido del archivo a Base64
        $base64 = base64_encode($fileContent);

        // Devolver la respuesta con el archivo en Base64
        return response()->json([
            'file_name' => 'empleados.xlsx',
            'file_base64' => $base64,
        ]);
    }

    public function exportVacations(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        // Validar que 'from' y 'to' no estén vacíos
        if (empty($from) || empty($to)) {
            return response()->json(['Fechas vacías' => 'Los campos "Del:" y "Al:" son obligatorios.'], 400);
        }

        $export = new EmpleadosVacationsExport($from, $to);

        // Obtener los datos para verificar si están vacíos
        $data = $export->collection();

        // Verificar si no hay datos para exportar
        if ($data->isEmpty()) {
            return response()->json(['error' => 'No hay datos para exportar.']);
        }

        // Exportar el archivo en formato XLSX con los filtros aplicados
        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        // Convertir el contenido del archivo a Base64
        $base64 = base64_encode($fileContent);

        // Devolver la respuesta con el archivo en Base64
        return response()->json([
            'file_name' => 'empleados.xlsx',
            'file_base64' => $base64,
        ]);
    }

    public function getVacations(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        // Validar las fechas del request
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        // Filtrar empleados con días de vacaciones entre las fechas indicadas
        $employees = Empleado::with([
            'archivable',
            'archivable.requisito',
            'escolaridad',
            'departamento',
            'estado_civil',
            'jefe_directo',
            'linea',
            'puesto',
            'sucursal',
            'tipo_de_sangre',
            'user',
            'estatus',
            'termination.estatus',
            'termination.reason',
        ])
            ->whereHas('vacationDays', function ($query) use ($from, $to) {
                $query->where(function ($q) use ($from, $to) {
                    $q->whereBetween('fecha_inicio', [$from, $to])
                        ->orWhereBetween('fecha_termino', [$from, $to])
                        ->orWhere(function ($q2) use ($from, $to) {
                            $q2->where('fecha_inicio', '<', $from)
                                ->where('fecha_termino', '>', $to);
                        });
                });
            })->paginate(10);

        return response()->json($employees);
    }
}
