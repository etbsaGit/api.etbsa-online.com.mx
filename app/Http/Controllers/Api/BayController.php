<?php

namespace App\Http\Controllers\Api;

use App\Models\Bay;
use App\Models\Post;
use App\Models\Linea;
use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Models\TechniciansLog;
use App\Models\ActivityTechnician;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Bay\PutBayRequest;
use App\Http\Requests\Bay\StoreBayRequest;

class BayController extends ApiController
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Bay::with('estatus', 'sucursal', 'linea')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBayRequest $request)
    {
        return $this->respond(Bay::create($request->validated()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bay $bay)
    {
        return $this->respond($bay->load('estatus', 'sucursal', 'linea'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutBayRequest $request, Bay $bay)
    {
        $bay->update($request->validated());
        return $this->respond($bay);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bay $bay)
    {
        $bay->delete();
        return $this->respond("ok");
    }

    public function getAllData()
    {
        $sucursales = Sucursal::all();

        $lineas = Linea::all();


        return $this->respond([
            'sucursales' => $sucursales,
            'lineas' => $lineas,
            'estatus' => Estatus::where('tipo_estatus', 'bay')->get()
        ]);
    }

    public function getTechData(Sucursal $sucursal, Linea $linea)
    {
        // Obtener los técnicos que pertenecen a la sucursal y línea especificadas
        $tecnicos = Empleado::where('sucursal_id', $sucursal->id)
            ->where('linea_id', $linea->id)
            ->whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
            ->get();

        return $this->respond($tecnicos);
    }

    public function getConstruccionBySucursal(Sucursal $sucursal)
    {
        // Asumiendo que 'agricola' es el nombre de la línea
        $construccionLinea = Linea::where('nombre', 'construccion')->first();

        if (!$construccionLinea) {
            return response()->json(['error' => 'Línea construccion no encontrada'], 404);
        }

        $bays = Bay::where('sucursal_id', $sucursal->id)
            ->where('linea_id', $construccionLinea->id)
            ->with(['estatus', 'workOrder', 'workOrder.estatus', 'workOrder.tecnico', 'workOrder.workOrderDoc'])
            ->get();

        return $this->respond($bays);
    }

    public function getAll(Request $request)
    {
        $user = Auth::user();
        $filters = $request->all();

        // Obtener los roles del usuario
        $roles = $user->roles->pluck('name')->toArray();

        // Base query
        $query = Bay::with('estatus', 'sucursal', 'linea');

        if (in_array('Admin', $roles)) {
            // Si el usuario tiene rol de servicio, obtener todas las bays
            $bays = $query->filter($filters)->get();
        } elseif (in_array('Taller', $roles)) {
            // Si el usuario tiene rol de taller, filtrar por sucursal_id y linea_id del empleado
            $empleado = $user->empleado;
            if ($empleado && isset($empleado->sucursal_id) && isset($empleado->linea_id)) {
                $query->where('sucursal_id', $empleado->sucursal_id)
                    ->where('linea_id', $empleado->linea_id);
            }
            $bays = $query->get();
        } else {
            // Si el usuario no tiene los roles mencionados, devolver un error o un resultado vacío
            $bays = collect();
        }

        $data = [
            'bays' => $bays,
            'sucursales' => Sucursal::all(),
            'lineas' => Linea::all(),
        ];

        return $this->respond($data);
    }

    public function pantallaAgricola(Sucursal $sucursal)
    {
        $agricolaLinea = Linea::where('nombre', 'agricola')->first();

        if (!$agricolaLinea) {
            return response()->json(['error' => 'Línea agrícola no encontrada'], 404);
        }

        $bays = Bay::where('sucursal_id', $sucursal->id)
            ->where('linea_id', $agricolaLinea->id)
            ->with(['estatus', 'workOrder', 'workOrder.estatus', 'workOrder.tecnico', 'workOrder.workOrderDoc'])
            ->get();

        $totalBays = $bays->count();
        $bahiasEnUso = $bays->filter(function ($bay) {
            return $bay->estatus->nombre === 'En uso';
        });
        $porcentajeBahiasEnUso = $totalBays > 0 ? round(($bahiasEnUso->count() / $totalBays) * 100, 2) : 0;

        // Obtener empleados de la sucursal que tengan el puesto de técnico y sean de línea agrícola
        $tecnicos = Empleado::where('sucursal_id', $sucursal->id)
            ->whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
            ->whereHas('linea', function ($query) {
                $query->where('nombre', 'agricola');
            })
            ->whereHas('estatus', function ($query) {
                $query->where('nombre', 'Activo');
            })
            ->with('sucursal', 'technician') // Cargar la relación 'sucursal'
            ->get();

        // Calcular la productividad promedio de todos los técnicos
        $totalTecnicos = $tecnicos->count();
        $sumProductividad = $tecnicos->sum('productividad');
        $promedioProductividad = $totalTecnicos > 0 ? round(($sumProductividad / $totalTecnicos), 2) : 0;

        // Calcular el promedio del desempeño de mano de obra
        $totalDesempeno = $tecnicos->sum(function ($tecnico) {
            return $tecnico->desempeno_mano_obra ?? 0;
        });
        $promedioDesempeno = $totalTecnicos > 0 ? round(($totalDesempeno / $totalTecnicos), 2) : 0;

        $post = Post::whereHas('estatus', function ($query) {
            $query->where('nombre', 'Pantalla');
        })
            ->whereHas('linea', function ($query) {
                $query->where('nombre', 'construccion');
            })
            ->where('activo', 1) // Condición para el campo 'activo'
            ->where(function ($query) {
                $query->whereNull('fecha_caducidad') // Posts con fecha_caducidad nula
                    ->orWhere(DB::raw('DATE(fecha_caducidad)'), '>=', now()->toDateString()); // Posts con fecha_caducidad mayor o igual a hoy
            })
            ->with('postDoc') // Cargar la relación 'postDoc'
            ->get();


        // Obtener TechnicianLogs
        $technicianLogs = TechniciansLog::whereIn('tecnico_id', $tecnicos->pluck('id'))
            ->with('activityTechnician', 'tecnico')
            ->orderBy('hora_inicio', 'asc') // Ordena de la más temprana a la más tardía
            ->get();

        $data = [
            'tecnicos' => $tecnicos,
            'post' => $post,
            'bays' => $bays,
            'technicianLogs' => $technicianLogs, // Agregar los logs de técnicos
            'charts' => [
                'en_uso' => $porcentajeBahiasEnUso,
                'prod_taller' => $promedioProductividad,
                'desempeno' => $promedioDesempeno // Agregar promedio de desempeño de mano de obra
            ]
        ];

        return response()->json($data);
    }



    public function pantallaConstruccion(Sucursal $sucursal)
    {
        $construccionLinea = Linea::where('nombre', 'construccion')->first();

        if (!$construccionLinea) {
            return response()->json(['error' => 'Línea construcción no encontrada'], 404);
        }

        $bays = Bay::where('sucursal_id', $sucursal->id)
            ->where('linea_id', $construccionLinea->id)
            ->with(['estatus', 'workOrder', 'workOrder.estatus', 'workOrder.tecnico', 'workOrder.workOrderDoc'])
            ->get();

        $totalBays = $bays->count();
        $bahiasEnUso = $bays->filter(function ($bay) {
            return $bay->estatus->nombre === 'En uso';
        });
        $porcentajeBahiasEnUso = $totalBays > 0 ? ($bahiasEnUso->count() / $totalBays) * 100 : 0;

        // Obtener empleados de la sucursal que tengan el puesto de técnico y sean de línea construcción
        $tecnicos = Empleado::where('sucursal_id', $sucursal->id)
            ->whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
            ->whereHas('linea', function ($query) {
                $query->where('nombre', 'construccion');
            })
            ->whereHas('estatus', function ($query) {
                $query->where('nombre', 'Activo');
            })
            ->with('sucursal', 'technician') // Cargar la relación 'sucursal'
            ->get();

        // Calcular la productividad promedio de todos los técnicos
        $totalTecnicos = $tecnicos->count();
        $sumProductividad = $tecnicos->sum('productividad');
        $promedioProductividad = $totalTecnicos > 0 ? ($sumProductividad / $totalTecnicos) : 0;

        // Calcular el promedio del desempeño de mano de obra
        $totalDesempeno = $tecnicos->sum(function ($tecnico) {
            return $tecnico->desempeno_mano_obra ?? 0;
        });
        $promedioDesempeno = $totalTecnicos > 0 ? ($totalDesempeno / $totalTecnicos) : 0;

        $post = Post::whereHas('estatus', function ($query) {
            $query->where('nombre', 'Pantalla');
        })
            ->whereHas('linea', function ($query) {
                $query->where('nombre', 'construccion');
            })
            ->where('activo', 1) // Condición para el campo 'activo'
            ->where(function ($query) {
                $query->whereNull('fecha_caducidad') // Posts con fecha_caducidad nula
                    ->orWhere(DB::raw('DATE(fecha_caducidad)'), '>=', now()->toDateString()); // Posts con fecha_caducidad mayor o igual a hoy
            })
            ->with('postDoc') // Cargar la relación 'postDoc'
            ->get();

        $technicianLogs = TechniciansLog::whereIn('tecnico_id', $tecnicos->pluck('id'))
            ->with('activityTechnician', 'tecnico')
            ->orderBy('hora_inicio', 'asc') // Ordena de la más temprana a la más tardía
            ->get();


        $data = [
            'tecnicos' => $tecnicos,
            'post' => $post,
            'bays' => $bays,
            'technicianLogs' => $technicianLogs, // Agregar los logs de técnicos
            'charts' => [
                'en_uso' => $porcentajeBahiasEnUso,
                'prod_taller' => $promedioProductividad,
                'desempeno' => $promedioDesempeno // Agregar promedio de desempeño de mano de obra
            ]
        ];

        return response()->json($data);
    }

    public function getDisponibility()
    {
        $user = Auth::user();
        $empleado = $user->empleado;

        // Si el empleado es null, obtener técnicos activos globalmente
        if (!$empleado) {
            $tecnicos = $this->filterTecnicosByTechnicianLog(Empleado::whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
                ->whereHas('estatus', function ($query) {
                    $query->where('nombre', 'Activo');
                }));

            return $this->respond($tecnicos);
        }

        // Si el empleado existe, continuar con los filtros de sucursal y línea
        $sucursal = $empleado->sucursal;
        $linea = $empleado->linea;

        $tecnicos = $this->filterTecnicosByTechnicianLog(Empleado::where('sucursal_id', $sucursal->id)
            ->whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
            ->whereHas('linea', function ($query) use ($linea) {
                $query->where('id', $linea->id);
            })
            ->whereHas('estatus', function ($query) {
                $query->where('nombre', 'Activo');
            }));

        return $this->respond($tecnicos);
    }

    private function filterTecnicosByTechnicianLog($query)
    {
        $today = now()->toDateString();

        // Obtener todos los técnicos (para asegurar que nadie sea excluido prematuramente)
        $allTecnicos = $query->with('techniciansLog.activityTechnician')->get();

        // Filtrar técnicos con TechnicianLog del día y actividades específicas
        $withLogs = $allTecnicos->filter(function ($tecnico) use ($today) {
            return $tecnico->techniciansLog->contains(function ($log) use ($today) {
                return $log->created_at->toDateString() === $today &&
                    in_array($log->activityTechnician->nombre, ['Servicio en campo', 'Diagnostico en campo']);
            });
        });

        // Obtener técnicos que no están en withLogs
        $withoutLogs = $allTecnicos->diff($withLogs);

        return [
            'withLogs' => $withLogs->values(),
            'withoutLogs' => $withoutLogs->values(),
        ];
    }

    public function getCalendar()
    {
        $user = Auth::user();
        $empleado = $user->empleado;
        $roles = $user->roles->pluck('name')->toArray();

        // Si el empleado es null, obtener técnicos activos globalmente
        if (!$empleado || in_array('Admin', $roles)) {
            $tecnicos = Empleado::whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
                ->whereHas('estatus', function ($query) {
                    $query->where('nombre', 'Activo');
                })
                ->with('sucursal', 'technician') // Cargar la relación 'sucursal'
                ->get();
        } else {
            // Si el empleado existe, continuar con los filtros de sucursal y línea
            $sucursal = $empleado->sucursal;
            $linea = $empleado->linea;

            $tecnicos = Empleado::where('sucursal_id', $sucursal->id)
                ->whereHas('puesto', function ($query) {
                    $query->where('nombre', 'tecnico');
                })
                ->whereHas('linea', function ($query) use ($linea) {
                    $query->where('id', $linea->id);
                })
                ->whereHas('estatus', function ($query) {
                    $query->where('nombre', 'Activo');
                })
                ->with('sucursal', 'technician') // Cargar la relación 'sucursal'
                ->get();
        }

        $today = now()->toDateString();

        $technicianLogs = TechniciansLog::whereIn('tecnico_id', $tecnicos->pluck('id'))
            ->whereDate('fecha', '>=', $today) // Filtra logs con fecha igual o posterior a hoy
            ->with('activityTechnician', 'tecnico')
            ->orderBy('hora_inicio', 'asc') // Ordena de la más temprana a la más tardía
            ->get();

        return $this->respond($technicianLogs);
    }

    public function getTech()
    {
        $user = Auth::user();
        $empleado = $user->empleado;
        $roles = $user->roles->pluck('name')->toArray();

        // Si el empleado es null, obtener técnicos activos globalmente
        if (!$empleado || in_array('Admin', $roles)) {
            $tecnicos = Empleado::whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
                ->whereHas('estatus', function ($query) {
                    $query->where('nombre', 'Activo');
                })
                ->with('sucursal', 'technician') // Cargar la relación 'sucursal'
                ->get();

            return $this->respond($tecnicos);
        }

        // Si el empleado existe, continuar con los filtros de sucursal y línea
        $sucursal = $empleado->sucursal;
        $linea = $empleado->linea;

        $tecnicos = Empleado::where('sucursal_id', $sucursal->id)
            ->whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
            ->whereHas('linea', function ($query) use ($linea) {
                $query->where('id', $linea->id);
            })
            ->whereHas('estatus', function ($query) {
                $query->where('nombre', 'Activo');
            })
            ->with('sucursal', 'technician') // Cargar la relación 'sucursal'
            ->get();

        return $this->respond($tecnicos);
    }

    public function getSucursal()
    {
        // Obtener las sucursales con las relaciones necesarias
        $data = Sucursal::with('bay.workOrder.workOrderDoc','bay.workOrder.estatus','bay.workOrder.tecnico','bay.linea')->get();

        return $this->respond($data);
    }




}
