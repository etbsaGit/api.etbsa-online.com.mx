<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Puesto;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\VacationDay;
use App\Models\Departamento;
use Illuminate\Http\Request;
use App\Mail\VacationOnMailable;
use App\Mail\VacationOffMailable;
use App\Mail\VacationStoreMailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Http\Requests\VacationDay\VacationDayRequest;

class VacationDayController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();

        if ($user->hasRole('RRHH')) {
            $vacations = VacationDay::filter($filters)
                ->with(['empleado', 'sucursal', 'puesto'])
                ->orderBy('id', 'desc') // Ordenar por ID de forma descendente
                ->paginate(10);
        } else {
            // Obtiene el arreglo de empleados subordinados
            $empleados = $this->getAllSubordinates($user->empleado);

            // Si $empleados es un arreglo de objetos o instancias de modelo, extrae los IDs
            $empleadoIds = collect($empleados)->pluck('id')->toArray();

            // Filtra las VacationDay únicamente de los empleados en el arreglo
            $vacations = VacationDay::filter($filters)
                ->whereIn('empleado_id', $empleadoIds)
                ->with(['empleado', 'sucursal', 'puesto'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        return $this->respond($vacations);
    }

    public function myIndex(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();

        if ($user->empleado) {
            $filters['empleado_id'] = $user->empleado->id;
        }

        $vacations = VacationDay::filter($filters)
            ->with(['empleado', 'sucursal', 'puesto'])
            ->orderBy('id', 'desc') // Ordenar por ID de forma descendente
            ->paginate(10);

        return $this->respond($vacations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VacationDayRequest $request)
    {
        $vacation = VacationDay::create($request->validated());

        $this->sendNotify($vacation->id);

        return $this->respondCreated($vacation);
    }

    public function storeOnly(VacationDayRequest $request)
    {
        $vacation = VacationDay::create($request->validated());

        return $this->respondCreated($vacation);
    }

    /**
     * Display the specified resource.
     */
    public function show(VacationDay $vacationDay)
    {
        return $this->respond($vacationDay->load('empleado', 'puesto', 'sucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VacationDayRequest $request, VacationDay $vacationDay)
    {
        $vacationDay->update($request->validated());

        return $this->respond($vacationDay);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VacationDay $vacationDay)
    {
        $vacationDay->delete();

        return $this->respondSuccess();
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
            $empleados = $this->getAllSubordinates($empleado);
        }

        $data = [
            'empleados' => $empleados,
            'puestos' => Puesto::all(),
            'sucursales' => Sucursal::all()
        ];

        return $this->respond($data);
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

    private function sendNotify($vacationDayId)
    {
        $vacationDay = VacationDay::find($vacationDayId);

        $rh = Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente corporativo')->value('id'))
            ->where('departamento_id', Departamento::where('nombre', 'Recursos Humanos')->value('id'))
            ->first();

        $cc = Empleado::where('puesto_id', Puesto::where('nombre', 'Coordinador de compras')->value('id'))->first();
        $dg = Empleado::where('puesto_id', Puesto::where('nombre', 'Director general')->value('id'))->first();
        $da = Empleado::where('puesto_id', Puesto::where('nombre', 'Director administrativo')->value('id'))->first();

        $solicitante = $vacationDay->empleado;
        $jefe = $vacationDay->empleado->jefe_directo;

        $correos = [
            'rh' => $rh?->correo_institucional, // Usa null safe operator si $rh puede ser null
            'solicitante' => $solicitante->correo_institucional,
            'jefe' => $jefe ? $jefe->correo_institucional : null, // Verifica si $jefe es null
        ];

        // Agregar correo de $cc si el vacationDay tiene un vehículo utilitario
        if ($vacationDay->vehiculo_utilitario) {
            $correos['cc'] = $cc?->correo_institucional; // Agregar correo si $cc no es null
        }

        // Si el jefe es DG, agregar también DA, y viceversa
        if ($jefe && $jefe->id === $dg?->id) {
            $correos['da'] = $da?->correo_institucional;
        } elseif ($jefe && $jefe->id === $da?->id) {
            $correos['dg'] = $dg?->correo_institucional;
        }

        foreach ($correos as $to_email) {
            if ($to_email) {
                Mail::to($to_email)->send(new VacationStoreMailable($vacationDay->load('empleado', 'puesto', 'sucursal')));
            }
        }
    }

    public function setValidatedOn(VacationDay $vacationDay)
    {
        $vacationDay->validated = 1;
        $vacationDay->save();

        $rh = Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente corporativo')->value('id'))
            ->where('departamento_id', Departamento::where('nombre', 'Recursos Humanos')->value('id'))
            ->first();

        $solicitante = $vacationDay->empleado;
        $jefe = $vacationDay->empleado->jefe_directo;

        $correos = [
            'rh' => $rh?->correo_institucional, // Usa null safe operator si $rh puede ser null
            'solicitante' => $solicitante->correo_institucional,
            'jefe' => $jefe ? $jefe->correo_institucional : null, // Verifica si $jefe es null
        ];

        foreach ($correos as $to_email) {
            if ($to_email) {
                Mail::to($to_email)->send(new VacationOnMailable($vacationDay->load('empleado', 'puesto', 'sucursal')));
            }
        }

        return $this->respondSuccess();
    }

    public function setValidatedOff(VacationDay $vacationDay)
    {
        $vacationDay->validated = 0;
        $vacationDay->save();

        $rh = Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente corporativo')->value('id'))
            ->where('departamento_id', Departamento::where('nombre', 'Recursos Humanos')->value('id'))
            ->first();

        $solicitante = $vacationDay->empleado;
        $jefe = $vacationDay->empleado->jefe_directo;

        $correos = [
            'rh' => $rh?->correo_institucional, // Usa null safe operator si $rh puede ser null
            'solicitante' => $solicitante->correo_institucional,
            'jefe' => $jefe ? $jefe->correo_institucional : null, // Verifica si $jefe es null
        ];

        foreach ($correos as $to_email) {
            if ($to_email) {
                Mail::to($to_email)->send(new VacationOffMailable($vacationDay->load('empleado', 'puesto', 'sucursal')));
            }
        }
        return $this->respondSuccess();
    }

    public function getVacationCalendar($date)
    {
        // Convierte la fecha proporcionada en un objeto Carbon para manejar el mes y año
        $parsedDate = Carbon::parse($date);
        $month = $parsedDate->month;
        $year = $parsedDate->year;

        // Consulta con Eloquent para filtrar los registros e incluir la relación
        $vacationDays = VacationDay::with('empleado') // Incluye la relación 'empleado'
            ->where('validated', 1) // Solo registros validados
            ->where(function ($query) use ($month, $year) {
                $query->where(function ($subQuery) use ($month, $year) {
                    $subQuery->whereMonth('fecha_inicio', $month)
                        ->whereYear('fecha_inicio', $year);
                })->orWhere(function ($subQuery) use ($month, $year) {
                    $subQuery->whereMonth('fecha_termino', $month)
                        ->whereYear('fecha_termino', $year);
                });
            })
            ->get();

        return $this->respond($vacationDays);
    }
}
