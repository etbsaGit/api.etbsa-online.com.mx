<?php

namespace App\Http\Controllers\Api;

use App\Models\Linea;
use App\Models\Puesto;
use App\Models\Sucursal;
use App\Models\Competencia;
use App\Models\Escolaridad;
use App\Models\Herramienta;
use App\Models\Departamento;
use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use Illuminate\Support\Facades\DB;
use App\Models\RequisicionPersonal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Mail\RequisicionAutorizadaMail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RequisicionPersonal\StoreRequest;

class RequisicionPersonalController extends ApiController
{
    use UploadableFile;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = $request->user();

        $query = RequisicionPersonal::filter($filters)
            ->with([
                'puesto',
                'sucursal',
                'linea',
                'departamento',
                'escolaridad',
                'solicita',
                'autoriza',
                'voBo',
                'recibe',
                'auth',
                'competencias',
                'herramientas',
            ]);

        // Si el usuario no tiene el rol RRHH, filtramos por las requisiciones relacionadas a su empleado
        if (!$user->hasRole('RRHH')) {
            $empleadoId = $user->empleado->id ?? null;

            $query->where(function ($q) use ($empleadoId) {
                $q->where('solicita_id', $empleadoId)
                    ->orWhere('autoriza_id', $empleadoId)
                    ->orWhere('vo_bo_id', $empleadoId);
            });
        }

        $requisiciones = $query->latest()->paginate(10);

        return $this->respond($requisiciones);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        // Crear la requisición principal
        $requisicionPersonal = RequisicionPersonal::create($request->validated());

        // Obtener correos relacionados a la requisición
        $emailSolicita = optional($requisicionPersonal->solicita)->correo_institucional;
        $emailAutoriza = optional($requisicionPersonal->autoriza)->correo_institucional;
        $emailVoBo     = optional($requisicionPersonal->voBo)->correo_institucional;

        // Construir array eliminando duplicados si autoriza y voBo son iguales
        $destinatarios = collect([$emailSolicita, $emailAutoriza, $emailVoBo])
            ->filter()            // elimina null o vacíos
            ->unique()            // elimina correos duplicados
            ->values()            // reindexa array
            ->all();

        if (!empty($destinatarios)) {
            Mail::to($destinatarios)->send(new RequisicionAutorizadaMail($requisicionPersonal));
        }

        // Guardar competencias (IDs simples)
        if ($request->has('competencias')) {
            $requisicionPersonal->competencias()->sync($request->input('competencias'));
        }

        // Guardar herramientas (IDs simples)
        if ($request->has('herramientas')) {
            $requisicionPersonal->herramientas()->sync($request->input('herramientas'));
        }

        if (!is_null($request['base64'])) {
            if ($requisicionPersonal->path) {
                Storage::disk('s3')->delete($requisicionPersonal->path);
            }
            $relativePath  = $this->saveDoc($request['base64'], $requisicionPersonal->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['path' => $relativePath];
            $requisicionPersonal->update($updateData);
        }

        return $this->respond($requisicionPersonal);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequisicionPersonal $requisicionPersonal)
    {
        $requisicionPersonal->load([
            'puesto',
            'sucursal',
            'linea',
            'departamento',
            'escolaridad',
            'solicita',
            'autoriza',
            'voBo',
            'recibe',
            'competencias',
            'herramientas',
        ]);

        return response()->json([
            'data' => $requisicionPersonal
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, RequisicionPersonal $requisicionPersonal)
    {
        DB::beginTransaction();

        try {
            // Actualizar campos principales
            $requisicionPersonal->update($request->validated());

            // Actualizar competencias (IDs simples)
            if ($request->has('competencias')) {
                $requisicionPersonal->competencias()->sync($request->input('competencias'));
            }

            // Actualizar herramientas (IDs simples)
            if ($request->has('herramientas')) {
                $requisicionPersonal->herramientas()->sync($request->input('herramientas'));
            }

            if (!is_null($request['base64'])) {
                if ($requisicionPersonal->path) {
                    Storage::disk('s3')->delete($requisicionPersonal->path);
                }
                $relativePath  = $this->saveDoc($request['base64'], $requisicionPersonal->default_path_folder);
                $request['base64'] = $relativePath;
                $updateData = ['path' => $relativePath];
                $requisicionPersonal->update($updateData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Requisición actualizada exitosamente.',
                'data' => $requisicionPersonal->load(['competencias', 'herramientas']),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar la requisición.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequisicionPersonal $requisicionPersonal)
    {
        DB::beginTransaction();

        try {
            // Eliminar relaciones en tablas pivote (opcional, pero recomendable si no usas `onDelete('cascade')`)
            $requisicionPersonal->competencias()->detach();
            $requisicionPersonal->herramientas()->detach();

            // Eliminar la requisición
            $requisicionPersonal->delete();

            DB::commit();

            return response()->json([
                'message' => 'Requisición eliminada exitosamente.'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al eliminar la requisición.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getforms()
    {
        $user = Auth::user();

        // Cargar las relaciones necesarias
        $user->load('empleado.sucursal');

        // Verificamos si el usuario tiene el rol 'rh' y su sucursal se llama 'Corporativo'
        $esRh = $user->hasRole('RRHH');
        $esCorporativo = optional($user->empleado?->sucursal)->nombre === 'Corporativo';

        if ($esRh || $esCorporativo) {
            $sucursales = Sucursal::all();
        } else {
            $sucursalId = optional($user->empleado)->sucursal_id;
            $sucursales = Sucursal::where('id', $sucursalId)->get();
        }

        $data = [
            'puestos' => Puesto::all(),
            'sucursales' => $sucursales,
            'lineas' => Linea::all(),
            'departamentos' => Departamento::all(),
            'escolaridades' => Escolaridad::all(),
            'competencias' => Competencia::all(),
            'herramientas' => Herramienta::all()
        ];

        return $this->respond($data);
    }

    public function changeAuth(RequisicionPersonal $requisicionPersonal, int $auth)
    {
        $user = auth()->user();

        $requisicionPersonal->autorizacion = $auth;
        $requisicionPersonal->auth_by = $user->empleado->id;
        $requisicionPersonal->save();
        $requisicionPersonal->load('auth');

        // Obtener correo del empleado que debe recibir el aviso
        $emailDestinatario = optional($requisicionPersonal->recibe)->correo_institucional;

        if ($emailDestinatario) {
            Mail::to($emailDestinatario)->send(new RequisicionAutorizadaMail($requisicionPersonal));
        }

        return $this->respondSuccess();
    }


    public function changeEstatus(RequisicionPersonal $requisicionPersonal, int $estatus)
    {
        $requisicionPersonal->estatus = $estatus;
        $requisicionPersonal->save();
        return $this->respondSuccess();
    }

    public function getAll()
    {
        $requisiciones = RequisicionPersonal::where('autorizacion', 1)
            ->where('estatus', 1)
            ->with([
                'puesto',
                'sucursal',
                'linea',
                'departamento',
                'escolaridad',

                'competencias',
                'herramientas',
            ])
            ->get();

        return $this->respond($requisiciones);
    }
}
