<?php

namespace App\Http\Controllers\Api;

use PDF;
use Carbon\Carbon;
use App\Models\Puesto;
use App\Models\Festivo;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeVacationExport;
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
                ->with(['empleado.vehicle', 'sucursal', 'puesto', 'cubre_rel', 'departamento','validateBy.empleado'])
                ->orderBy('validated', 'asc') // Ordenar por ID de forma descendente
                ->orderBy('fecha_inicio', 'desc')
                ->paginate(10);
        } else {
            // Obtiene el arreglo de empleados subordinados
            $empleados = $this->getAllSubordinates($user->empleado);

            // Si $empleados es un arreglo de objetos o instancias de modelo, extrae los IDs
            $empleadoIds = collect($empleados)->pluck('id')->toArray();

            // Filtra las VacationDay únicamente de los empleados en el arreglo
            $vacations = VacationDay::filter($filters)
                ->whereIn('empleado_id', $empleadoIds)
                ->with(['empleado', 'sucursal', 'puesto', 'cubre_rel', 'departamento','validateBy.empleado'])
                ->orderBy('validated', 'asc')
                ->orderBy('fecha_inicio', 'desc')
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
            ->with(['empleado.vehicle', 'sucursal', 'puesto', 'cubre_rel', 'departamento','validateBy.empleado'])
            ->orderBy('validated', 'asc') // Ordenar por ID de forma descendente
            ->orderBy('fecha_inicio', 'desc')
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
        return $this->respond($vacationDay->load('empleado.vehicle', 'puesto', 'sucursal', 'cubre_rel', 'departamento','validateBy.empleado'));
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

    public function getforms($year)
    {
        $user = Auth::user();
        $empleado = $user->empleado;

        if ($user->hasRole('RRHH')) {
            $empleados = Empleado::where('estatus_id', 5)
                ->with('vehicle', 'jefe_directo', 'notificar')
                ->orderBy('apellido_paterno')
                ->get();
        } else {
            $empleados = $this->getAllSubordinates($empleado);
        }

        if ($empleado && !$user->hasRole('RRHH')) {
            $empleadosAll = Empleado::where('estatus_id', 5)
                ->with('vehicle', 'jefe_directo', 'notificar')
                ->where('sucursal_id', $empleado->sucursal_id)
                ->orderBy('apellido_paterno')
                ->get();
        } else {
            $empleadosAll = $empleados;
        }

        $fechas = Festivo::whereYear('fecha', $year)
            ->orWhereYear('fecha', $year - 1)
            ->orWhereYear('fecha', $year + 1)
            ->pluck('fecha')
            ->toArray();

        $data = [
            'empleados' => $empleados,
            'puestos' => Puesto::all(),
            'sucursales' => Sucursal::all(),
            'departamentos' => Departamento::all(),
            'festivos' => $fechas,
            'empleadosAll' => $empleadosAll,
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

        $dg = Empleado::where('puesto_id', Puesto::where('nombre', 'Director general')->value('id'))->first();
        $da = Empleado::where('puesto_id', Puesto::where('nombre', 'Director administrativo')->value('id'))->first();

        $solicitante = $vacationDay->empleado;
        $jefe = $vacationDay->empleado->jefe_directo;
        $not = $vacationDay->empleado->notificar;

        $correos = [
            'rh' => $rh?->correo_institucional, // Usa null safe operator si $rh puede ser null
            'solicitante' => $solicitante->correo_institucional,
            'jefe' => $jefe ? $jefe->correo_institucional : null, // Verifica si $jefe es null
            'notificar' => $not ? $not->correo_institucional : null,
        ];

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
        $user = Auth::user();
        $vacationDay->validate_by = $user->id;
        $vacationDay->validated = 1;
        $vacationDay->save();

        $rh = Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente corporativo')->value('id'))
            ->where('departamento_id', Departamento::where('nombre', 'Recursos Humanos')->value('id'))
            ->first();

        $dg = Empleado::where('puesto_id', Puesto::where('nombre', 'Director general')->value('id'))->first();
        $da = Empleado::where('puesto_id', Puesto::where('nombre', 'Director administrativo')->value('id'))->first();

        $solicitante = $vacationDay->empleado;
        $jefe = $vacationDay->empleado->jefe_directo;
        $not = $vacationDay->empleado->notificar;
        $qc = $vacationDay->cubre_rel;
        $cc = Empleado::where('puesto_id', Puesto::where('nombre', 'Coordinador de compras')->value('id'))->first();

        $correos = [
            'rh' => $rh?->correo_institucional, // Usa null safe operator si $rh puede ser null
            'solicitante' => $solicitante->correo_institucional,
            'jefe' => $jefe ? $jefe->correo_institucional : null, // Verifica si $jefe es null
            'notificar' => $not ? $not->correo_institucional : null,
            'qc' => $qc
                ? $qc->correo_institucional
                : null,
        ];

        if ($vacationDay->vehiculo_utilitario) {
            $correos['cc'] = $cc?->correo_institucional; // Agregar correo si $cc no es null
        }

        if ($jefe && $jefe->id === $dg?->id) {
            $correos['da'] = $da?->correo_institucional;
        } elseif ($jefe && $jefe->id === $da?->id) {
            $correos['dg'] = $dg?->correo_institucional;
        }

        foreach ($correos as $to_email) {
            if ($to_email) {
                Mail::to($to_email)->send(new VacationOnMailable($vacationDay->load('empleado', 'puesto', 'sucursal','validateBy.empleado')));
            }
        }

        return $this->respondSuccess();
    }

    public function setValidatedOff(VacationDay $vacationDay)
    {
        $user = Auth::user();
        $vacationDay->validate_by = $user->id;
        $vacationDay->validated = 0;
        $vacationDay->save();

        $rh = Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente corporativo')->value('id'))
            ->where('departamento_id', Departamento::where('nombre', 'Recursos Humanos')->value('id'))
            ->first();

        $dg = Empleado::where('puesto_id', Puesto::where('nombre', 'Director general')->value('id'))->first();
        $da = Empleado::where('puesto_id', Puesto::where('nombre', 'Director administrativo')->value('id'))->first();

        $solicitante = $vacationDay->empleado;
        $jefe = $vacationDay->empleado->jefe_directo;
        $not = $vacationDay->empleado->notificar;
        $cc = Empleado::where('puesto_id', Puesto::where('nombre', 'Coordinador de compras')->value('id'))->first();

        $correos = [
            'rh' => $rh?->correo_institucional, // Usa null safe operator si $rh puede ser null
            'solicitante' => $solicitante->correo_institucional,
            'jefe' => $jefe ? $jefe->correo_institucional : null, // Verifica si $jefe es null
            'notificar' => $not ? $not->correo_institucional : null,
        ];

        if ($vacationDay->vehiculo_utilitario) {
            $correos['cc'] = $cc?->correo_institucional; // Agregar correo si $cc no es null
        }

        if ($jefe && $jefe->id === $dg?->id) {
            $correos['da'] = $da?->correo_institucional;
        } elseif ($jefe && $jefe->id === $da?->id) {
            $correos['dg'] = $dg?->correo_institucional;
        }

        foreach ($correos as $to_email) {
            if ($to_email) {
                Mail::to($to_email)->send(new VacationOffMailable($vacationDay->load('empleado', 'puesto', 'sucursal','validateBy.empleado')));
            }
        }
        return $this->respondSuccess();
    }

    public function getVacationCalendar(Request $request, $date)
    {
        $filters = $request->all();
        $user = Auth::user();
        $empleado = $user->empleado;

        // Obtener el ID de la sucursal "Corporativo"
        $corporativo = Sucursal::where('nombre', 'Corporativo')->first();
        $sucursalCorporativo = $corporativo ? $corporativo->id : null;

        // Convertir la fecha en un objeto Carbon
        $parsedDate = Carbon::parse($date);
        $month = $parsedDate->month;
        $year = $parsedDate->year;

        // Construcción de la consulta base con filtro de fecha
        $vacationDaysQuery = VacationDay::filter($filters)
            ->with('empleado')
            ->where('validated', 1)
            ->where(function ($query) use ($month, $year) {
                $query->whereMonth('fecha_inicio', $month)->whereYear('fecha_inicio', $year)
                    ->orWhereMonth('fecha_termino', $month)->whereYear('fecha_termino', $year);
            });

        // Si no hay empleado autenticado, traer todas las vacaciones
        if (!$empleado) {
            return $this->respond($vacationDaysQuery->get());
        }

        $sucursalEmpleado = $empleado->sucursal_id;

        // Si el empleado pertenece a "Corporativo", traer todas las vacaciones
        if ($sucursalEmpleado === $sucursalCorporativo) {
            return $this->respond($vacationDaysQuery->get());
        }

        // Si el empleado pertenece a otra sucursal, filtrar por su sucursal y la del corporativo
        $vacationDaysQuery->whereHas('empleado', function ($query) use ($sucursalEmpleado, $sucursalCorporativo) {
            $query->where('sucursal_id', $sucursalEmpleado);
            if ($sucursalCorporativo) {
                $query->orWhere('sucursal_id', $sucursalCorporativo);
            }
        });

        return $this->respond($vacationDaysQuery->get());
    }

    public function getReport(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $sucursal_id = $request->sucursal_id;

        // Validación: start no puede ser después de end
        if (Carbon::parse($start)->gt(Carbon::parse($end))) {
            return response()->json([
                'error' => 'La fecha de inicio no puede ser posterior a la fecha de término.'
            ], 422);
        }

        // Obtener festivos como array asegurando formato DATE
        $festivos = Festivo::pluck('fecha')->map(fn($date) => Carbon::parse($date)->toDateString())->toArray();

        $employees = Empleado::where('sucursal_id', $sucursal_id)
            ->whereHas('vacationDays', function ($query) use ($start, $end, $festivos) {
                $query->where('validated', 1)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_termino', '>=', $end);
                            });
                    })
                    ->whereNotIn('fecha_inicio', $festivos) // Excluir festivos
                    ->whereNotIn('fecha_termino', $festivos) // Excluir festivos
                    ->whereRaw("DAYOFWEEK(fecha_inicio) != 1") // Excluir domingos
                    ->whereRaw("DAYOFWEEK(fecha_termino) != 1"); // Excluir domingos
            })
            ->with(['vacationDays' => function ($query) use ($start, $end, $festivos) {
                $query->where('validated', 1)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_termino', '>=', $end);
                            });
                    })
                    ->whereNotIn('fecha_inicio', $festivos) // Excluir festivos
                    ->whereNotIn('fecha_termino', $festivos) // Excluir festivos
                    ->whereRaw("DAYOFWEEK(fecha_inicio) != 1") // Excluir domingos
                    ->whereRaw("DAYOFWEEK(fecha_termino) != 1") // Excluir domingos
                    ->select('empleado_id', 'fecha_inicio', 'fecha_termino');
            }])
            ->with('sucursal')
            ->get();

        // Agregar vacationDetails con los días específicos
        $employees->transform(function ($employee) use ($start, $end, $festivos) {
            $vacationDays = [];

            foreach ($employee->vacationDays as $vacation) {
                $periodStart = Carbon::parse($vacation->fecha_inicio);
                $periodEnd = Carbon::parse($vacation->fecha_termino);

                if ($periodEnd->lt($start) || $periodStart->gt($end)) {
                    continue; // Si las vacaciones están fuera del rango, omitirlas
                }

                // Limitar los días generados al rango solicitado
                $currentDate = $periodStart->copy();
                $finalDate = $periodEnd->copy();

                while ($currentDate->lte($finalDate)) {
                    // Excluir domingos y festivos
                    if (
                        $currentDate->gte($start) && $currentDate->lte($end) &&
                        !in_array($currentDate->toDateString(), $festivos) &&
                        $currentDate->dayOfWeek !== Carbon::SUNDAY
                    ) {
                        $vacationDays[] = $currentDate->toDateString();
                    }
                    $currentDate->addDay();
                }
            }

            $employee->vacationDetails = array_values(array_unique($vacationDays)); // Evita duplicados
            return $employee;
        });

        return $this->respond($employees);
    }

    public function exportReport(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $sucursal_id = $request->sucursal_id;

        if (Carbon::parse($start)->gt(Carbon::parse($end))) {
            return response()->json([
                'error' => 'La fecha de inicio no puede ser posterior a la fecha de término.'
            ], 422);
        }

        $export = new EmployeeVacationExport($start, $end, $sucursal_id);

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
            'file_name' => Sucursal::find($sucursal_id)->nombre,
            'file_base64' => $base64,
        ]);
    }

    public function getEmployeeReport(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $empleado_id = $request->empleado_id;

        if ($start->gt($end)) {
            return response()->json([
                'error' => 'La fecha de inicio no puede ser posterior a la fecha de término.'
            ], 422);
        }

        // Obtener festivos en formato string (Y-m-d)
        $festivos = Festivo::pluck('fecha')->map(fn($date) => Carbon::parse($date)->toDateString())->toArray();

        // Obtener el empleado con vacaciones validadas dentro del periodo
        $employee = Empleado::where('id', $empleado_id)
            ->whereHas('vacationDays', function ($query) use ($start, $end) {
                $query->where('validated', 1)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_termino', '>=', $end);
                            });
                    });
            })
            ->with(['vacationDays' => function ($query) use ($start, $end) {
                $query->where('validated', 1)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_termino', '>=', $end);
                            });
                    })
                    ->select('empleado_id', 'fecha_inicio', 'fecha_termino');
            }])
            ->with('sucursal')
            ->first();

        if (!$employee) {
            return response()->json(['error' => 'Empleado no encontrado o sin vacaciones registradas en este periodo.'], 404);
        }

        // Generar días de vacaciones válidos
        $vacationDays = [];
        foreach ($employee->vacationDays as $vacation) {
            $periodStart = Carbon::parse($vacation->fecha_inicio);
            $periodEnd = Carbon::parse($vacation->fecha_termino);

            // Si no intersecta con el rango, omitir
            if ($periodEnd->lt($start) || $periodStart->gt($end)) {
                continue;
            }

            $currentDate = $periodStart->copy();
            $finalDate = $periodEnd->copy();

            while ($currentDate->lte($finalDate)) {
                // Excluir domingos y festivos, y limitar al rango solicitado
                if (
                    $currentDate->gte($start) &&
                    $currentDate->lte($end) &&
                    !in_array($currentDate->toDateString(), $festivos) &&
                    $currentDate->dayOfWeek !== Carbon::SUNDAY
                ) {
                    $vacationDays[] = $currentDate->toDateString();
                }
                $currentDate->addDay();
            }
        }

        $employee->vacationDetails = array_values(array_unique($vacationDays)); // Evitar duplicados

        return $this->respond($employee);
    }


    public function getEmployeeReportPdf(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $empleado_id = $request->empleado_id;

        // Validación: start no puede ser después de end
        if (Carbon::parse($start)->gt(Carbon::parse($end))) {
            return response()->json([
                'error' => 'La fecha de inicio no puede ser posterior a la fecha de término.'
            ], 422);
        }

        // Obtener festivos como array asegurando formato DATE
        $festivos = Festivo::pluck('fecha')->map(fn($date) => Carbon::parse($date)->toDateString())->toArray();

        // Obtener el empleado con vacaciones validadas dentro del periodo
        $employee = Empleado::where('id', $empleado_id)
            ->whereHas('vacationDays', function ($query) use ($start, $end) {
                $query->where('validated', 1)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_termino', '>=', $end);
                            });
                    });
            })
            ->with(['vacationDays' => function ($query) use ($start, $end) {
                $query->where('validated', 1)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_termino', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_termino', '>=', $end);
                            });
                    })
                    ->select('empleado_id', 'fecha_inicio', 'fecha_termino');
            }])
            ->with('sucursal')
            ->first();

        if (!$employee) {
            return response()->json(['error' => 'Empleado no encontrado o sin vacaciones registradas en este periodo.'], 404);
        }

        // Agregar vacationDetails con los días específicos
        $vacationDays = [];
        foreach ($employee->vacationDays as $vacation) {
            $periodStart = Carbon::parse($vacation->fecha_inicio);
            $periodEnd = Carbon::parse($vacation->fecha_termino);

            if ($periodEnd->lt($start) || $periodStart->gt($end)) {
                continue; // Si las vacaciones están fuera del rango, omitirlas
            }

            // Limitar los días generados al rango solicitado
            $currentDate = $periodStart->copy();
            $finalDate = $periodEnd->copy();

            while ($currentDate->lte($finalDate)) {
                // Excluir domingos y festivos
                if (
                    $currentDate->gte($start) && $currentDate->lte($end) &&
                    !in_array($currentDate->toDateString(), $festivos) &&
                    $currentDate->dayOfWeek !== Carbon::SUNDAY
                ) {
                    $vacationDays[] = $currentDate->toDateString();
                }
                $currentDate->addDay();
            }
        }

        $employee->vacationDetails = array_values(array_unique($vacationDays)); // Evita duplicados

        // Generar PDF en horizontal
        $pdf = Pdf::loadView('pdf.vacations.reportVacationsEmployee', [
            'empleado' => $employee,
            'start' => Carbon::parse($start)->format('d-m-Y'),
            'end' => Carbon::parse($end)->format('d-m-Y')
        ]);
        // Descargar el PDF para pruebas en postman
        // return $pdf->download('reporte_empleado_' . $employee->id . '.pdf');

        // Obtener el contenido del PDF como cadena binaria
        $pdfContent = $pdf->output();

        // Convertir el contenido a Base64
        $pdfBase64 = base64_encode($pdfContent);

        // Retornar el PDF en Base64
        return $this->respond($pdfBase64);
    }
}
