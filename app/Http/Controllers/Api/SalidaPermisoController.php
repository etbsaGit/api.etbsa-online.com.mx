<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Puesto;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Departamento;
use Illuminate\Http\Request;
use App\Models\SalidaPermiso;
use App\Mail\PermisoSolicitadoMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalidasPermisoExport;
use App\Http\Controllers\ApiController;
use App\Http\Requests\SalidaPermiso\StoreRequest;

class SalidaPermisoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = $request->all();

        if ($user->hasRole('RRHH')) {
            $salidaPermisos = SalidaPermiso::filter($filters)
                ->with(['empleado', 'sucursal'])
                ->orderBy('status', 'asc') // Ordenar por ID de forma descendente
                ->orderBy('date', 'desc')
                ->paginate(10);
        } else {
            // Obtiene el arreglo de empleados subordinados
            $empleados = $this->getAllSubordinates($user->empleado);

            // Si $empleados es un arreglo de objetos o instancias de modelo, extrae los IDs
            $empleadoIds = collect($empleados)->pluck('id')->toArray();

            // Filtra las SalidaPermiso únicamente de los empleados en el arreglo
            $salidaPermisos = SalidaPermiso::filter($filters)
                ->whereIn('empleado_id', $empleadoIds)
                ->with(['empleado', 'sucursal'])
                ->orderBy('status', 'asc')
                ->orderBy('date', 'desc')
                ->paginate(10);
        }

        return $this->respond($salidaPermisos);
    }

    public function myIndex(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();

        if ($user->empleado) {
            $filters['empleado_id'] = $user->empleado->id;
        }

        $salidaPermisos = SalidaPermiso::filter($filters)
            ->with(['empleado', 'sucursal'])
            ->orderBy('status', 'asc') // Ordenar por ID de forma descendente
            ->orderBy('date', 'desc')
            ->paginate(10);

        return $this->respond($salidaPermisos);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $salidaPermiso = SalidaPermiso::create($request->validated());

        $this->enviarNotificacionPermiso($salidaPermiso);


        return $this->respondCreated($salidaPermiso);
    }

    /**
     * Display the specified resource.
     */
    public function show(SalidaPermiso $salidaPermiso)
    {
        return $this->respond($salidaPermiso->load('empleado', 'sucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, SalidaPermiso $salidaPermiso)
    {
        $salidaPermiso->update($request->validated());

        $this->enviarNotificacionPermiso($salidaPermiso);


        return $this->respondCreated($salidaPermiso);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalidaPermiso $salidaPermiso)
    {
        $salidaPermiso->delete();
        return $this->respondSuccess();
    }

    private function getAllSubordinates($employee)
    {
        $subordinates = $employee->empleado()->get();

        $allSubordinates = collect();
        foreach ($subordinates as $subordinate) {
            $allSubordinates->push($subordinate);
            $allSubordinates = $allSubordinates->merge($this->getAllSubordinates($subordinate));
        }
        return $allSubordinates;
    }

    public function getforms()
    {
        $user = Auth::user();
        $empleado = $user->empleado;

        if ($user->hasRole('RRHH')) {
            $empleados = Empleado::where('estatus_id', 5)
                ->orderBy('apellido_paterno')
                ->get();
        } else {
            // Obtiene subordinados
            $empleados = $this->getAllSubordinates($empleado);

            // Agrega el empleado autenticado al listado (si no está)
            if ($empleado && !$empleados->contains('id', $empleado->id)) {
                $empleados->push($empleado);
            }
        }

        if ($empleado && !$user->hasRole('RRHH')) {
            $empleadosAll = Empleado::where('estatus_id', 5)
                ->where('sucursal_id', $empleado->sucursal_id)
                ->orderBy('apellido_paterno')
                ->get();
        } else {
            $empleadosAll = $empleados;
        }

        $data = [
            'empleados' => $empleados,
            'sucursales' => Sucursal::all(),
            'empleadosAll' => $empleadosAll,
        ];

        return $this->respond($data);
    }


    public function actualizarStatus(SalidaPermiso $salidaPermiso, int $status)
    {
        $user = Auth::user();
        // Cambiar el status
        $salidaPermiso->status = $status;
        $salidaPermiso->validate_by = $user->id;

        // Guardar los cambios en la base de datos
        $salidaPermiso->save();

        $this->enviarNotificacionPermiso($salidaPermiso);

        // Retornar la instancia actualizada
        return $this->respondSuccess();
    }

    public function getCalendar(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();
        $empleado = $user->empleado;

        // Sucursal corporativo
        $corporativo = Sucursal::where('nombre', 'Corporativo')->first();
        $sucursalCorporativo = $corporativo ? $corporativo->id : null;

        // Fechas en formato YYYY-MM-DD
        $startDate = Carbon::parse($request->date)->toDateString();
        $endDate = Carbon::parse($request->date)->addDays(6)->toDateString();

        // Quitar 'date' del filtro para no sobreescribir el rango
        unset($filters['date']);

        // ------------------ ADMIN ------------------
        if ($user->hasRole('Admin')) {
            $filters['sucursal_id'] = null;
            $filters['empleado_id'] = null;

            $salidas = SalidaPermiso::query()
                ->filter($filters)  // ya no filtra por date
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 1)
                ->with(['empleado', 'sucursal'])
                ->get();

            return $this->respond($salidas);
        }

        // ------------------ NO ADMIN ------------------
        if (!$empleado) {
            $filters['sucursal_id'] = [$sucursalCorporativo];
        } else if (!$empleado->sucursal_id) {
            $filters['sucursal_id'] = [$sucursalCorporativo];
        } else if ($empleado->sucursal_id === $sucursalCorporativo) {
            $filters['sucursal_id'] = null;
        } else {
            $filters['sucursal_id'] = [
                $empleado->sucursal_id,
                $sucursalCorporativo
            ];
        }

        $salidas = SalidaPermiso::query()
            ->filter($filters)  // ya no filtra por date
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->with(['empleado', 'sucursal'])
            ->get();

        return $this->respond($salidas);
    }

    public function exportXls(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('RRHH')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $filters = $request->all();

        // Obtener los datos (sin paginar)
        $salidaPermisos = SalidaPermiso::filter($filters)
            ->with(['empleado', 'sucursal'])
            ->orderBy('status', 'asc')
            ->orderBy('date', 'desc')
            ->get();

        // Crear el archivo en memoria
        $tempFile = Excel::raw(new SalidasPermisoExport($salidaPermisos), \Maatwebsite\Excel\Excel::XLSX);

        // Convertir a Base64
        $base64 = base64_encode($tempFile);

        return response()->json([
            'file_name' => 'salidas_permiso.xlsx',
            'file_base64' => $base64
        ]);
    }

    private function enviarNotificacionPermiso(SalidaPermiso $salidaPermiso)
    {
        // Obtener el empleado que solicitó el salidaPermiso
        $empleado = $salidaPermiso->empleado;

        // Crear un arreglo con los destinatarios iniciales
        $destinatarios = [$empleado->correo_institucional];

        $user = Auth::user();

        // Agregar jefe directo si existe
        if ($empleado->jefe_directo) {
            $destinatarios[] = $empleado->jefe_directo->correo_institucional;
        }

        // Agregar gerente corporativo de Recursos Humanos
        $rh = Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente corporativo')->value('id'))
            ->where('departamento_id', Departamento::where('nombre', 'Recursos Humanos')->value('id'))
            ->first();

        if ($rh) {
            $destinatarios[] = $rh->correo_institucional;
        }

        // Enviar correos uno por uno
        foreach ($destinatarios as $correo) {
            Mail::to($correo)
                ->send(new PermisoSolicitadoMail($salidaPermiso, $empleado, $user));
        }
    }
}
