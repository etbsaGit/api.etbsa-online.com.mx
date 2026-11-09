<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\VehiclesDispersionGasolina;
use App\Models\VehiclesGasolinaBitacora;
use App\Models\VehiclesSucCargoA;
use App\Models\VehiclesDeptoCargoA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Http\Controllers\ApiController;
use App\Http\Requests\OrdenCompra\Vehicle\OCGasolinaVehicleRequest;
use App\Mail\OrdenesCompra\Vehicles\SendGasAutorizacion;
use App\Mail\OrdenesCompra\Vehicles\SendGasDispersada;
use App\Mail\OrdenesCompra\Vehicles\SendGasSolicitud;

class OCGasolinaVehicleController extends ApiController
{
    private const DISPERSION_RELATIONS = [
        'vehicle.estatus',
        'vehicle.sucursal',
        'vehicle.departamento',
        'vehicle.linea',
        'solicitante',
        'registrador.empleado',
        'sucursalCargo',
        'departamentoCargo',
        'estatus',
        'bitacora.user.empleado',
        'bitacora.estatus',
    ];

    /**
     * Evita que al serializar una o varias dispersiones se disparen consultas N+1 por los $appends de Empleado.
     */
    private function optimizeDispersionEmpleados($dispersiones): void
    {
        $items = $dispersiones instanceof \Illuminate\Support\Collection ? $dispersiones : collect([$dispersiones]);

        foreach ($items as $disp) {
            if (!$disp) {
                continue;
            }
            if ($disp->relationLoaded('solicitante') && $disp->solicitante) {
                $disp->solicitante->setAppends(['nombreCompleto', 'apellidoCompleto']);
            }
            if ($disp->relationLoaded('registrador') && $disp->registrador && $disp->registrador->relationLoaded('empleado') && $disp->registrador->empleado) {
                $disp->registrador->empleado->setAppends(['nombreCompleto', 'apellidoCompleto']);
            }
            if ($disp->relationLoaded('bitacora') && $disp->bitacora) {
                foreach ($disp->bitacora as $bit) {
                    if ($bit->relationLoaded('user') && $bit->user && $bit->user->relationLoaded('empleado') && $bit->user->empleado) {
                        $bit->user->empleado->setAppends(['nombreCompleto', 'apellidoCompleto']);
                    }
                }
            }
        }
    }

    /**
     * Lista paginada de Dispersiones de Gasolina con sus relaciones y bitácora.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $ordenes = VehiclesDispersionGasolina::filter($filters)
            ->with(self::DISPERSION_RELATIONS)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $this->optimizeDispersionEmpleados($ordenes->getCollection());

        $response = $ordenes->toArray();
        $response['status_counts'] = $this->getEstatusCounts();

        return $this->respond($response);
    }

    /**
     * Lista paginada de Dispersiones de Gasolina únicamente de las unidades asignadas al empleado autenticado.
     */
    public function myIndex(Request $request)
    {
        $user = $request->user();
        $empleado = $user?->empleado;

        $vehicleIds = [];
        if ($empleado) {
            $vehicleIds = $empleado->vehicles()->pluck('vehicles.id')->toArray();
            if ($empleado->vehicle_id && !in_array((int) $empleado->vehicle_id, array_map('intval', $vehicleIds), true)) {
                $vehicleIds[] = (int) $empleado->vehicle_id;
            }
        }

        if (empty($vehicleIds)) {
            return $this->respond([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'next_page_url' => null,
                'prev_page_url' => null,
                'total' => 0,
                'assigned_vehicle' => null,
                'assigned_vehicles' => [],
                'status_counts' => [
                    'solicitado' => 0,
                    'por_dispersar' => 0,
                    'dispersado' => 0,
                    'no_autorizado' => 0,
                ],
            ]);
        }

        $filters = $request->all();
        if (!empty($filters['vehicle_id']) && in_array((int) $filters['vehicle_id'], array_map('intval', $vehicleIds), true)) {
            $filters['vehicle_id'] = (int) $filters['vehicle_id'];
        } else {
            unset($filters['vehicle_id']);
            $filters['vehicle_ids'] = $vehicleIds;
        }

        if ($empleado) {
            $filters['solicitado_por'] = $empleado->id;
        }

        $ordenes = VehiclesDispersionGasolina::filter($filters)
            ->with(self::DISPERSION_RELATIONS)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $this->optimizeDispersionEmpleados($ordenes->getCollection());

        $maxKmsGasolina = DB::table('vehicles_dispersion_gasolina')
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereNotNull('km_evento')
            ->groupBy('vehicle_id')
            ->select('vehicle_id', DB::raw('MAX(km_evento) as ultimo_km'))
            ->pluck('ultimo_km', 'vehicle_id');

        $maxKmsServicios = DB::table('vehicles_detalles_servicio')
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereNotNull('km_evento')
            ->groupBy('vehicle_id')
            ->select('vehicle_id', DB::raw('MAX(km_evento) as ultimo_km'))
            ->pluck('ultimo_km', 'vehicle_id');

        $assignedVehicles = Vehicle::with(['estatus', 'sucursal', 'departamento', 'linea'])
            ->whereIn('id', $vehicleIds)
            ->get()
            ->map(function ($v) use ($maxKmsGasolina, $maxKmsServicios) {
                $kmGas = isset($maxKmsGasolina[$v->id]) ? (int) $maxKmsGasolina[$v->id] : 0;
                $kmServ = isset($maxKmsServicios[$v->id]) ? (int) $maxKmsServicios[$v->id] : 0;
                $v->ultimo_km = max($kmGas, $kmServ);
                return $v;
            });

        $response = $ordenes->toArray();
        $response['assigned_vehicle'] = $assignedVehicles->first();
        $response['assigned_vehicles'] = $assignedVehicles;
        $response['status_counts'] = $this->getEstatusCounts($vehicleIds, $empleado?->id);

        return $this->respond($response);
    }

    /**
     * Registra una nueva Solicitud de Dispersión de Gasolina.
     */
    public function store(OCGasolinaVehicleRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $estatus = Estatus::where('nombre', 'Solicitado')->where('tipo_estatus', 'servicio-unidad')->first();
            if (!$estatus) {
                throw new \Exception('No se encontró el estatus "Solicitado" para el tipo de estatus servicio-unidad.');
            }
            $estatus_id = $estatus->id;

            $solicitadoPor = $validated['solicitado_por'] ?? auth()->user()?->empleado?->id ?? null;

            $dispersion = VehiclesDispersionGasolina::create([
                'vehicle_id' => $validated['vehicle_id'],
                'solicitado_por' => $solicitadoPor,
                'registrado_por' => auth()->id(),
                'suc_cargo_id' => $validated['suc_cargo_id'] ?? null,
                'depto_cargo_id' => $validated['depto_cargo_id'] ?? null,
                'lts_solicitados' => (int) $validated['lts_solicitados'],
                'lts_dispersados' => isset($validated['lts_dispersados']) ? (int) $validated['lts_dispersados'] : null,
                'km_evento' => (int) $validated['km_evento'],
                'notas' => $validated['notas'] ?? 'Dispersión de gasolina para unidad',
                'estatus_id' => $estatus_id,
                'is_urgente' => (bool) ($validated['is_urgente'] ?? false),
            ]);

            $bitacora = $this->insertBitacora($dispersion->id, $estatus_id, 'Solicitud de dispersión de gasolina creada');
            if (!$bitacora) {
                throw new \Exception('Error al insertar la bitácora');
            }

            DB::commit();

            $dispersionId = $dispersion->id;
            dispatch(function () use ($dispersionId) {
                try {
                    $this->mailSolicitudGasolina($dispersionId);
                } catch (\Throwable $e) {
                    \Log::error("Error enviando correo de gasolina para solicitud #{$dispersionId}: " . $e->getMessage());
                }
            })->afterResponse();

            $orden = $dispersion->load(self::DISPERSION_RELATIONS);
            $this->optimizeDispersionEmpleados($orden);

            return $this->respondCreated($orden);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra una Solicitud de Dispersión de Gasolina con sus relaciones y bitácora.
     */
    public function show($id)
    {
        $orden = VehiclesDispersionGasolina::with(self::DISPERSION_RELATIONS)->findOrFail($id);
        $this->optimizeDispersionEmpleados($orden);

        return $this->respond($orden);
    }

    /**
     * Actualiza una Solicitud de Dispersión de Gasolina.
     */
    public function update(OCGasolinaVehicleRequest $request, $id)
    {
        $dispersion = VehiclesDispersionGasolina::findOrFail($id);
        $validated = $request->validated();

        $orden = DB::transaction(function () use ($dispersion, $validated) {
            $dispersion->update([
                'vehicle_id' => $validated['vehicle_id'],
                'solicitado_por' => $validated['solicitado_por'] ?? $dispersion->solicitado_por,
                'suc_cargo_id' => $validated['suc_cargo_id'] ?? null,
                'depto_cargo_id' => $validated['depto_cargo_id'] ?? null,
                'lts_solicitados' => (int) $validated['lts_solicitados'],
                'lts_dispersados' => array_key_exists('lts_dispersados', $validated) && $validated['lts_dispersados'] !== null
                    ? (int) $validated['lts_dispersados']
                    : $dispersion->lts_dispersados,
                'km_evento' => (int) $validated['km_evento'],
                'notas' => $validated['notas'] ?? $dispersion->notas,
                'is_urgente' => isset($validated['is_urgente']) ? (bool) $validated['is_urgente'] : $dispersion->is_urgente,
            ]);

            $bitacora = $this->insertBitacora(
                $dispersion->id,
                $validated['estatus_id'] ?? $dispersion->estatus_id,
                'Solicitud de dispersión de gasolina actualizada'
            );
            if (!$bitacora) {
                throw new \Exception('Error al insertar la bitácora');
            }

            return $dispersion->load(self::DISPERSION_RELATIONS);
        });

        $this->optimizeDispersionEmpleados($orden);

        return $this->respond($orden);
    }

    /**
     * Elimina una Solicitud de Dispersión de Gasolina y su bitácora asociada.
     */
    public function destroy($id)
    {
        $dispersion = VehiclesDispersionGasolina::findOrFail($id);

        DB::transaction(function () use ($dispersion) {
            $dispersion->bitacora()->delete();
            $dispersion->delete();
        });

        return $this->respondSuccess();
    }

    public function insertBitacora(int $dispersion_id, int $estatus_id, ?string $comentarios)
    {
        return VehiclesGasolinaBitacora::create([
            'dispersion_id' => $dispersion_id,
            'estatus_id' => $estatus_id,
            'user_id' => auth()->user()->id,
            'comentarios' => $comentarios,
        ]);
    }

    /**
     * Autorización o rechazo de solicitud de dispersión de gasolina.
     * Al autorizar, el Director Administrativo define cuántos litros se dispersan (lts_dispersados).
     */
    public function autorizarOrdenCompra(Request $request)
    {
        $inputId = $request->input('dispersion_id') ?? $request->input('orden_servicio_id') ?? $request->input('id');
        $request->merge(['dispersion_id' => $inputId]);

        $data = $request->validate([
            'dispersion_id' => ['required', 'integer', 'exists:vehicles_dispersion_gasolina,id'],
            'autorizado' => ['required', 'boolean'],
            'lts_dispersados' => [
                Rule::requiredIf(fn () => filter_var($request->input('autorizado'), FILTER_VALIDATE_BOOLEAN) === true),
                'nullable',
                'integer',
                'min:1',
            ],
            'comentarios' => [
                Rule::requiredIf(fn () => filter_var($request->input('autorizado'), FILTER_VALIDATE_BOOLEAN) === false),
                'nullable',
                'string',
            ],
        ], [
            'dispersion_id.required' => 'La solicitud de gasolina es obligatoria',
            'dispersion_id.exists' => 'La solicitud de gasolina no existe',
            'autorizado.required' => 'La decisión de autorización es obligatoria',
            'lts_dispersados.required' => 'Debe indicar la cantidad de litros autorizados a dispersar',
            'lts_dispersados.integer' => 'Los litros autorizados deben ser un número entero',
            'lts_dispersados.min' => 'Los litros autorizados deben ser al menos 1',
            'comentarios.required' => 'El comentario es obligatorio cuando no se autoriza la solicitud',
        ]);

        DB::beginTransaction();
        try {
            $dispersion = VehiclesDispersionGasolina::findOrFail($data['dispersion_id']);

            $autorizado = (bool) $data['autorizado'];
            $nombreEstatus = $autorizado ? 'Por dispersar' : 'No Autorizado';
            $estatus = Estatus::where('nombre', $nombreEstatus)->where('tipo_estatus', 'servicio-unidad')->first();
            if (!$estatus) {
                throw new \Exception("No se encontró el estatus \"{$nombreEstatus}\" para servicio-unidad.");
            }
            $estatusId = $estatus->id;

            $ltsDispersados = $autorizado
                ? (int) ($data['lts_dispersados'] ?? $dispersion->lts_solicitados)
                : null;

            $dispersion->update([
                'estatus_id' => $estatusId,
                'lts_dispersados' => $ltsDispersados,
            ]);

            $comentarioBitacora = !empty($data['comentarios'])
                ? trim($data['comentarios'])
                : ($autorizado
                    ? "Solicitud autorizada ({$ltsDispersados} Lts autorizados para dispersión)"
                    : 'Solicitud no autorizada');

            $this->insertBitacora($dispersion->id, $estatusId, $comentarioBitacora);

            DB::commit();

            $dispersionId = $dispersion->id;
            dispatch(function () use ($dispersionId, $autorizado) {
                try {
                    $this->mailDispersionAutorizacion($dispersionId, $autorizado);
                } catch (\Throwable $e) {
                    \Log::error("Error enviando correo de autorización de gasolina #{$dispersionId}: " . $e->getMessage());
                }
            })->afterResponse();

            $ordenFresh = $dispersion->fresh(self::DISPERSION_RELATIONS);
            $this->optimizeDispersionEmpleados($ordenFresh);

            return $this->respond(
                $ordenFresh,
                $autorizado
                    ? 'Solicitud de dispersión de gasolina autorizada correctamente.'
                    : 'Solicitud de dispersión de gasolina marcada como no autorizada.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar solicitud como dispersada (Coordinación de Compras).
     */
    public function marcarDispersada(int $id)
    {
        DB::beginTransaction();
        try {
            $dispersion = VehiclesDispersionGasolina::findOrFail($id);
            $estatusId = Estatus::where('nombre', 'Dispersada')->where('tipo_estatus', 'servicio-unidad')->first()->id;

            $dispersion->update([
                'estatus_id' => $estatusId,
                'lts_dispersados' => $dispersion->lts_dispersados ?? $dispersion->lts_solicitados,
            ]);
            $this->insertBitacora($dispersion->id, $estatusId, 'La solicitud de dispersión de gasolina ha sido surtida');

            DB::commit();

            $dispersionId = $dispersion->id;
            dispatch(function () use ($dispersionId) {
                try {
                    $this->mailGasolinaDispersada($dispersionId);
                } catch (\Throwable $e) {
                    \Log::error("Error enviando correo de gasolina dispersada #{$dispersionId}: " . $e->getMessage());
                }
            })->afterResponse();

            $ordenFresh = $dispersion->fresh(self::DISPERSION_RELATIONS);
            $this->optimizeDispersionEmpleados($ordenFresh);

            return $this->respond($ordenFresh, 'Solicitud marcada como dispersada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Catálogos para filtros y formulario de registro/edición de dispersión de gasolina.
     */
    public function getforms()
    {
        $vehicles = Vehicle::with(['estatus', 'sucursal', 'departamento', 'linea', 'empleados'])
            ->orderBy('placas', 'asc')
            ->get();

        $vehicleIds = $vehicles->pluck('id');

        $maxKmsGasolina = $vehicles->isNotEmpty()
            ? DB::table('vehicles_dispersion_gasolina')
                ->whereIn('vehicle_id', $vehicleIds)
                ->whereNotNull('km_evento')
                ->groupBy('vehicle_id')
                ->select('vehicle_id', DB::raw('MAX(km_evento) as ultimo_km'))
                ->pluck('ultimo_km', 'vehicle_id')
            : collect();

        $maxKmsServicios = $vehicles->isNotEmpty()
            ? DB::table('vehicles_detalles_servicio')
                ->whereIn('vehicle_id', $vehicleIds)
                ->whereNotNull('km_evento')
                ->groupBy('vehicle_id')
                ->select('vehicle_id', DB::raw('MAX(km_evento) as ultimo_km'))
                ->pluck('ultimo_km', 'vehicle_id')
            : collect();

        $vehicles->each(function ($v) use ($maxKmsGasolina, $maxKmsServicios) {
            $kmGas = isset($maxKmsGasolina[$v->id]) ? (int) $maxKmsGasolina[$v->id] : 0;
            $kmServ = isset($maxKmsServicios[$v->id]) ? (int) $maxKmsServicios[$v->id] : 0;
            $v->ultimo_km = max($kmGas, $kmServ);
            $v->empleados->each->setAppends(['nombreCompleto', 'apellidoCompleto']);
        });

        $maxId = (int) DB::table('vehicles_dispersion_gasolina')->max('id');
        $nextId = $maxId > 0 ? $maxId + 1 : 1;

        $data = [
            'next_id' => $nextId,
            'vehicles' => $vehicles,
            'empleados' => Empleado::whereHas('estatus', function ($q) {
                $q->where('nombre', 'Activo');
            })->orderBy('apellido_paterno', 'asc')
                ->get()
                ->each->setAppends(['nombreCompleto', 'apellidoCompleto']),
            'sucursales_cargo' => VehiclesSucCargoA::orderBy('nombre', 'asc')->get(),
            'deptos_cargo' => VehiclesDeptoCargoA::orderBy('nombre', 'asc')->get(),
            'estatus' => Estatus::where('tipo_estatus', 'servicio-unidad')->get(),
            'status_counts' => $this->getEstatusCounts(),
        ];

        return $this->respond($data);
    }

    /**
     * Obtiene el conteo de solicitudes por estatus (Solicitado, Por dispersar, Dispersada, No Autorizado).
     */
    private function getEstatusCounts(?array $vehicleIds = null, ?int $solicitanteId = null): array
    {
        $estatusList = Estatus::where('tipo_estatus', 'servicio-unidad')->get();
        $solicitado = $estatusList->firstWhere('nombre', 'Solicitado');
        $porDispersar = $estatusList->firstWhere('nombre', 'Por dispersar');
        $dispersado = $estatusList->firstWhere('nombre', 'Dispersada');
        $noAutorizado = $estatusList->firstWhere('nombre', 'No Autorizado');

        $query = VehiclesDispersionGasolina::query();
        if (!empty($vehicleIds)) {
            $query->whereIn('vehicle_id', $vehicleIds);
        }
        if (!empty($solicitanteId)) {
            $query->where('solicitado_por', $solicitanteId);
        }

        $countsByStatus = (clone $query)
            ->whereIn('estatus_id', $estatusList->pluck('id'))
            ->groupBy('estatus_id')
            ->select('estatus_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'estatus_id');

        return [
            'solicitado' => (int) ($countsByStatus[$solicitado?->id] ?? 0),
            'por_dispersar' => (int) ($countsByStatus[$porDispersar?->id] ?? 0),
            'dispersado' => (int) ($countsByStatus[$dispersado?->id] ?? 0),
            'no_autorizado' => (int) ($countsByStatus[$noAutorizado?->id] ?? 0),
            'solicitado_id' => $solicitado?->id,
            'por_dispersar_id' => $porDispersar?->id,
            'dispersado_id' => $dispersado?->id,
            'no_autorizado_id' => $noAutorizado?->id,
        ];
    }

    public function mailSolicitudGasolina(int $dispersionId)
    {
        $orden = VehiclesDispersionGasolina::findOrFail($dispersionId);
        $orden->load(self::DISPERSION_RELATIONS);

        $dirAdmin = Empleado::whereHas('puesto', function ($q) {
            $q->where('nombre', 'Director Administrativo');
        })->whereHas('estatus', function ($q) {
            $q->where('nombre', 'Activo');
        })->first();
        $solicitante = $orden->solicitante;

        // $correoDir = optional($dirAdmin)->correo_institucional;
        $correoDir = null;
        $correoSolicitante = optional($solicitante)->correo_institucional;

        if ($correoDir) {
            $mail = Mail::to($correoDir);
            if ($correoSolicitante) {
                $mail->cc($correoSolicitante);
            }
            $mail->send(new SendGasSolicitud($orden));
        } else {
            if ($correoSolicitante) {
                $mail = Mail::to($correoSolicitante);
                $mail->send(new SendGasSolicitud($orden));
            }
        }
        return $this->respond(true, 'Solicitud de gasolina enviada correctamente.');
    }

    public function mailDispersionAutorizacion(int $dispersionId, bool $aut)
    {
        $orden = VehiclesDispersionGasolina::findOrFail($dispersionId);
        $orden->load(self::DISPERSION_RELATIONS);

        $solicitante = $orden->solicitante;
        $correoSolicitante = optional($solicitante)->correo_institucional;

        if (!$aut) {
            if ($correoSolicitante) {
                Mail::to($correoSolicitante)->send(new SendGasDispersada($orden));
            }
        } else {
            $dirAdmin = Empleado::whereHas('puesto', function ($q) {
                $q->where('nombre', 'Director Administrativo');
            })->whereHas('estatus', function ($q) {
                $q->where('nombre', 'Activo');
            })->first();
            $coordCompras = Empleado::whereHas('puesto', function ($q) {
                $q->where('nombre', 'Coordinador de compras');
            })->whereHas('estatus', function ($q) {
                $q->where('nombre', 'Activo');
            })->first();

            // $correoDir = optional($dirAdmin)->correo_institucional;
            $correoDir = null;
            // $correoCoord = optional($coordCompras)->correo_institucional;
            $correoCoord = null;

            if ($correoDir) {
                $mail = Mail::to($correoDir);
                if ($correoSolicitante) {
                    $mail->cc($correoSolicitante);
                }
                if ($correoCoord) {
                    $mail->cc($correoCoord);
                }
                $mail->send(new SendGasAutorizacion($orden));
            } elseif ($correoCoord) {
                $mail = Mail::to($correoCoord);
                if ($correoSolicitante) {
                    $mail->cc($correoSolicitante);
                }
                $mail->send(new SendGasAutorizacion($orden));
            } elseif ($correoSolicitante) {
                Mail::to($correoSolicitante)->send(new SendGasAutorizacion($orden));
            }
        }
        return $this->respond(true, 'Autorización enviada correctamente.');
    }

    /**
     * Envía correo al solicitante notificando que la gasolina ha sido dispersada (o no autorizada).
     */
    public function mailGasolinaDispersada(int $dispersionId)
    {
        $orden = VehiclesDispersionGasolina::findOrFail($dispersionId);
        $orden->load(self::DISPERSION_RELATIONS);

        $solicitante = $orden->solicitante;
        $correoSolicitante = optional($solicitante)->correo_institucional;

        if ($correoSolicitante) {
            Mail::to($correoSolicitante)->send(new SendGasDispersada($orden));
        }

        return $this->respond(true, 'Notificación de dispersión enviada al solicitante correctamente.');
    }
}
