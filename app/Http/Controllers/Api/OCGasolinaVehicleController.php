<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Http\Requests\OrdenCompra\Vehicle\AutorizacionServicioRequest as VehicleAutorizacionServicioRequest;
use App\Http\Requests\OrdenCompra\Vehicle\OCGasolinaVehicleRequest;
use App\Http\Requests\Service\AutorizacionServicioRequest;
use App\Http\Requests\Service\StoreRequest;
use App\Mail\OrdenesCompra\Vehicles\SendGasAutorizacion;
use App\Mail\OrdenesCompra\Vehicles\SendGasSolicitud;
use App\Mail\OrdenesCompra\Vehicles\SendOrdenAutorizacion;
use App\Mail\OrdenesCompra\Vehicles\SendOrdenCompraVehicle;
use App\Models\Estatus;
use App\Models\VehiclesOrdenesServicio;
use App\Models\VehiclesDetallesServicio;
use App\Models\VehiclesServiciosCat;
use App\Models\VehiclesTipoServicio;
use App\Models\VehiclesServicioProveedores;
use App\Models\VehiclesSucCargoA;
use App\Models\VehiclesDeptoCargoA;
use App\Models\VehiclesOrdenesBitacora;
use App\Traits\UploadableFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OCGasolinaVehicleController extends ApiController
{
    use UploadableFile;
    /**
     * Lista paginada de Órdenes de Servicio con sus detalles y relaciones.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $ordenes = VehiclesOrdenesServicio::filter($filters)
            ->whereHas('detalles.servicio', function ($q) {
                $q->where('nombre', 'Dispersión de gasolina');
            })
            ->with([
                'vehicle.estatus',
                'vehicle.sucursal',
                'vehicle.departamento',
                'vehicle.linea',
                'proveedor',
                'solicitante',
                'sucursalCargo',
                'departamentoCargo',
                'detalles.servicio.tipoServicio',
                'estatus',
                'bitacora.user.empleado',
                'bitacora.estatus',
            ])
            ->orderBy('fecha_elaboracion', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $response = $ordenes->toArray();
        $response['status_counts'] = $this->getEstatusCounts();

        return $this->respond($response);
    }

    /**
     * Lista paginada de Órdenes de Servicio únicamente de las unidades asignadas al empleado autenticado (tabla usuarios_unidades).
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
                    'autorizado' => 0,
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
            $filters['solicitante_id'] = $empleado->id;
        }

        $ordenes = VehiclesOrdenesServicio::filter($filters)
            ->whereHas('detalles.servicio', function ($q) {
                $q->where('nombre', 'Dispersión de gasolina');
            })
            ->with([
                'vehicle.estatus',
                'vehicle.sucursal',
                'vehicle.departamento',
                'vehicle.linea',
                'proveedor',
                'solicitante',
                'sucursalCargo',
                'departamentoCargo',
                'detalles.servicio.tipoServicio',
                'estatus',
                'bitacora.user.empleado',
                'bitacora.estatus',
            ])
            ->orderBy('fecha_elaboracion', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $maxKmsByVehicle = DB::table('vehicles_detalles_servicio')
            ->join('vehicles_ordenes_servicio', 'vehicles_detalles_servicio.orden_servicio_id', '=', 'vehicles_ordenes_servicio.id')
            ->whereIn('vehicles_ordenes_servicio.vehicle_id', $vehicleIds)
            ->whereNotNull('vehicles_detalles_servicio.km_evento')
            ->groupBy('vehicles_ordenes_servicio.vehicle_id')
            ->select('vehicles_ordenes_servicio.vehicle_id', DB::raw('MAX(vehicles_detalles_servicio.km_evento) as ultimo_km'))
            ->pluck('ultimo_km', 'vehicle_id');

        $assignedVehicles = Vehicle::with(['estatus', 'sucursal', 'departamento', 'linea'])
            ->whereIn('id', $vehicleIds)
            ->get()
            ->map(function ($v) use ($maxKmsByVehicle) {
                $v->ultimo_km = isset($maxKmsByVehicle[$v->id]) ? (int) $maxKmsByVehicle[$v->id] : 0;
                return $v;
            });

        $response = $ordenes->toArray();
        $response['assigned_vehicle'] = $assignedVehicles->first();
        $response['assigned_vehicles'] = $assignedVehicles;
        $response['status_counts'] = $this->getEstatusCounts($vehicleIds, $empleado?->id);

        return $this->respond($response);
    }

    /**
     * Registra una nueva Orden de Servicio junto con sus detalles de servicio.
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

            $ordenServicio = VehiclesOrdenesServicio::create([
                'autorizacion_compras' => $validated['autorizacion_compras'] ?? null,
                'orden_compra' => null,
                'fecha_elaboracion' => $validated['fecha_elaboracion'],
                'vehicle_id' => $validated['vehicle_id'],
                // 'proveedor_id' => $validated['proveedor_id'],
                'solicitante_id' => $validated['solicitante_id'] ?? null,
                'suc_cargo_id' => $validated['suc_cargo_id'] ?? null,
                'depto_cargo_id' => $validated['depto_cargo_id'] ?? null,
                'factura' => $validated['factura'] ?? null,
                'estatus_id' => $estatus_id,
            ]);

            $ordenServicio->orden_compra = (string) $ordenServicio->id;
            $ordenServicio->save();

            $servicioId = VehiclesServiciosCat::where('nombre', 'Dispersión de gasolina')->first();
            foreach ($validated['detalles'] as $detalle) {
                VehiclesDetallesServicio::create([
                    'orden_servicio_id' => $ordenServicio->id,
                    'vehicle_id' => !empty($detalle['vehicle_id']) ? $detalle['vehicle_id'] : $validated['vehicle_id'],
                    'servicio_id' => $servicioId->id,
                    'km_evento' => isset($detalle['km_evento']) && $detalle['km_evento'] !== ''
                        ? (int) $detalle['km_evento']
                        : null,
                    'descripcion' => $detalle['descripcion'] ?? 'Dispersión de gasolina para unidad',
                    'precio_unitario' => isset($detalle['precio_unitario']) && $detalle['precio_unitario'] !== ''
                        ? (float) $detalle['precio_unitario']
                        : null,
                    'cantidad' => isset($detalle['cantidad']) && $detalle['cantidad'] !== ''
                        ? (int) $detalle['cantidad']
                        : null,
                    'llanta_anchura' => null,
                    'llanta_altura' => null,
                    'llanta_rin' => null,
                    'subtotal' => (float) $detalle['subtotal'] ?? null,
                    'iva' => (float) $detalle['iva'] ?? null,
                    'total' => (float) $detalle['total'] ?? null,
                ]);
            }

            $bitacora = $this->insertBitacora($ordenServicio->id, $estatus_id, 'Orden de dispersión de gasolina creada');
            if (!$bitacora) {
                throw new \Exception('Error al insertar la bitácora');
            }

            DB::commit();
            $this->mailSolicitudGasolina($ordenServicio->id);

            $orden = $ordenServicio->load([
                'vehicle.estatus',
                'vehicle.sucursal',
                'vehicle.departamento',
                'vehicle.linea',
                'proveedor',
                'solicitante',
                'sucursalCargo',
                'departamentoCargo',
                'detalles.servicio.tipoServicio',
                'estatus',
                'bitacora.user.empleado',
                'bitacora.estatus',
            ]);

            return $this->respondCreated($orden);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra una Orden de Servicio con todos sus detalles.
     */
    public function show($id)
    {
        $orden = VehiclesOrdenesServicio::with([
            'vehicle.estatus',
            'vehicle.sucursal',
            'vehicle.departamento',
            'vehicle.linea',
            'proveedor',
            'solicitante',
            'sucursalCargo',
            'departamentoCargo',
            'detalles.servicio.tipoServicio',
            'estatus',
            'bitacora.user.empleado',
            'bitacora.estatus',
        ])->findOrFail($id);

        return $this->respond($orden);
    }

    /**
     * Actualiza una Orden de Servicio y sincroniza sus detalles.
     */
    public function update(OCGasolinaVehicleRequest $request, $id)
    {
        $ordenServicio = VehiclesOrdenesServicio::findOrFail($id);
        $validated = $request->validated();

        $orden = DB::transaction(function () use ($ordenServicio, $validated, $request) {
            $ordenServicio->update([
                'autorizacion_compras' => $validated['autorizacion_compras'] ?? null,
                'orden_compra' => (string) $ordenServicio->id,
                'fecha_elaboracion' => $validated['fecha_elaboracion'],
                'vehicle_id' => $validated['vehicle_id'],
                // 'proveedor_id' => $validated['proveedor_id'],
                'solicitante_id' => $validated['solicitante_id'] ?? null,
                'suc_cargo_id' => $validated['suc_cargo_id'] ?? null,
                'depto_cargo_id' => $validated['depto_cargo_id'] ?? null,
                'factura' => $validated['factura'] ?? null,
                'cotizacion_url' => array_key_exists('cotizacion_url', $validated)
                    ? $validated['cotizacion_url']
                    : $ordenServicio->cotizacion_url,
            ]);


            $ordenServicio->detalles()->delete();
            $servicioId = VehiclesServiciosCat::where('nombre', 'Dispersión de gasolina')->first();

            foreach ($validated['detalles'] as $detalle) {
                VehiclesDetallesServicio::create([
                    'orden_servicio_id' => $ordenServicio->id,
                    'vehicle_id' => !empty($detalle['vehicle_id']) ? $detalle['vehicle_id'] : $validated['vehicle_id'],
                    'servicio_id' => !empty($detalle['servicio_id']) ? $detalle['servicio_id'] : ($servicioId ? $servicioId->id : 3417),
                    'km_evento' => isset($detalle['km_evento']) && $detalle['km_evento'] !== ''
                        ? (int) $detalle['km_evento']
                        : null,
                    'descripcion' => $detalle['descripcion'] ?? 'Dispersión de gasolina para unidad',
                    'precio_unitario' => isset($detalle['precio_unitario']) && $detalle['precio_unitario'] !== ''
                        ? (float) $detalle['precio_unitario']
                        : null,
                    'cantidad' => isset($detalle['cantidad']) && $detalle['cantidad'] !== ''
                        ? (int) $detalle['cantidad']
                        : null,
                    'llanta_anchura' => null,
                    'llanta_altura' => null,
                    'llanta_rin' => null,
                    'subtotal' => (float) $detalle['subtotal'] ?? null,
                    'iva' => (float) $detalle['iva'] ?? null,
                    'total' => (float) $detalle['total'] ?? null,
                ]);
            }

            $bitacora = $this->insertBitacora($ordenServicio->id, $validated['estatus_id'] ?? $ordenServicio->estatus_id, 'Orden de dispersión de gasolina actualizada');
            if (!$bitacora) {
                throw new \Exception('Error al insertar la bitácora');
            }

            return $ordenServicio->load([
                'vehicle.estatus',
                'vehicle.sucursal',
                'vehicle.departamento',
                'vehicle.linea',
                'proveedor',
                'solicitante',
                'sucursalCargo',
                'departamentoCargo',
                'detalles.servicio.tipoServicio',
                'estatus',
                'bitacora.user.empleado',
                'bitacora.estatus',
            ]);
        });

        return $this->respond($orden);
    }

    /**
     * Elimina una Orden de Servicio y sus detalles asociados.
     */
    public function destroy($id)
    {
        $ordenServicio = VehiclesOrdenesServicio::findOrFail($id);

        DB::transaction(function () use ($ordenServicio) {
            $ordenServicio->detalles()->delete();
            $servicioId = VehiclesServiciosCat::where('nombre', 'Dispersión de gasolina')->first();
            $ordenServicio->delete();
        });

        return $this->respondSuccess();
    }

    public function insertBitacora(int $orden_servicio_id, int $estatus_id, string $comentarios)
    {
        $ordenServicio = VehiclesOrdenesServicio::findOrFail($orden_servicio_id);
        $bitacora = VehiclesOrdenesBitacora::create([
            'orden_servicio_id' => $ordenServicio->id,
            'estatus_id' => $estatus_id,
            'user_id' => auth()->user()->id,
            'comentarios' => $comentarios,
        ]);
        return $bitacora;
    }

    // autorización de solicitud
    public function autorizarOrdenCompra(VehicleAutorizacionServicioRequest $request)
    {
        DB::beginTransaction();
        try {
            $user_id = Auth::user()->id;
            $data = $request->validated();
            $orden = VehiclesOrdenesServicio::findOrFail($data['orden_servicio_id']);

            if (!$orden) {
                return $this->respondNotFound('Orden de compra no encontrada.');
            }

            $estatusId = $data['autorizado'] ? Estatus::where('nombre', 'Por dispersar')->where('tipo_estatus', 'servicio-unidad')->first()->id : Estatus::where('nombre', '')->where('tipo_estatus', 'servicio-unidad')->first()->id;

            $this->insertBitacora($orden->id, $estatusId, $data['comentarios'] ?? 'Sin comentarios');

            $orden->update([
                'estatus_id' => $estatusId,
            ]);

            if ($data['autorizado']) {
                if (!$orden->autorizacion_compras) {
                    $sucCargo = $orden->sucursalCargo->siglas;
                    $deptoCargo = $orden->departamentoCargo->siglas;
                    $aut = DB::select("SELECT MAX(id) AS max_id FROM contador_folio_autorizacion_compras");
                    $folio = $aut[0]->max_id + 1;
                    $siguiente_folio = $sucCargo . '-' . $deptoCargo . '-' . $folio;
                    $orden->update([
                        'autorizacion_compras' => $siguiente_folio,
                    ]);

                    DB::insert("insert into contador_folio_autorizacion_compras() values();");
                }
                $this->mailDispersionAutorizacion($orden->id, true);
            } else {
                $this->mailDispersionAutorizacion($orden->id, false);
            }

            DB::commit();

            return $this->respond($orden->fresh([
                'vehicle.estatus',
                'vehicle.sucursal',
                'vehicle.departamento',
                'vehicle.linea',
                'proveedor',
                'solicitante',
                'sucursalCargo',
                'departamentoCargo',
                'detalles.servicio.tipoServicio',
                'estatus',
                'bitacora.user.empleado',
                'bitacora.estatus',
            ]), $data['autorizado'] ? 'Orden de servicio autorizada correctamente.' : 'Orden de servicio marcada como no autorizada.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // función para marcar orden como dispersada
    public function marcarDispersada(int $id)
    {
        DB::beginTransaction();
        try {
            $orden = VehiclesOrdenesServicio::findOrFail($id);
            $orden->update([
                'estatus_id' => Estatus::where('nombre', 'Dispersada')->where('tipo_estatus', 'servicio-unidad')->first()->id,
            ]);
            $this->insertBitacora($orden->id, Estatus::where('nombre', 'Dispersada')->where('tipo_estatus', 'servicio-unidad')->first()->id, 'La solicitud de dispersión de gasolina ha sido surtida');
            DB::commit();
            return $this->respond($orden->fresh([
                'vehicle.estatus',
                'vehicle.sucursal',
                'vehicle.departamento',
                'vehicle.linea',
                'proveedor',
                'solicitante',
                'sucursalCargo',
                'departamentoCargo',
                'detalles.servicio.tipoServicio',
                'estatus',
                'bitacora.user.empleado',
                'bitacora.estatus',
            ]), 'Orden de servicio marcada como dispersada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Catálogos para filtros y formulario de registro/edición de servicios.
     */
    public function getforms()
    {
        $maxKmsByVehicle = DB::table('vehicles_detalles_servicio')
            ->join('vehicles_ordenes_servicio', 'vehicles_detalles_servicio.orden_servicio_id', '=', 'vehicles_ordenes_servicio.id')
            ->whereNotNull('vehicles_detalles_servicio.km_evento')
            ->groupBy('vehicles_ordenes_servicio.vehicle_id')
            ->select('vehicles_ordenes_servicio.vehicle_id', DB::raw('MAX(vehicles_detalles_servicio.km_evento) as ultimo_km'))
            ->pluck('ultimo_km', 'vehicle_id');

        $vehicles = Vehicle::with(['estatus', 'sucursal', 'departamento', 'linea', 'empleados'])
            ->orderBy('placas', 'asc')
            ->get()
            ->map(function ($v) use ($maxKmsByVehicle) {
                $v->ultimo_km = isset($maxKmsByVehicle[$v->id]) ? (int) $maxKmsByVehicle[$v->id] : 0;
                return $v;
            });

        $maxId = (int) DB::table('vehicles_ordenes_servicio')->max('id');
        $nextId = $maxId > 0 ? $maxId + 1 : 1;

        $data = [
            'next_id' => $nextId,
            'vehicles' => $vehicles,
            'empleados' => Empleado::where('estatus_id', 5)
                ->orderBy('apellido_paterno', 'asc')
                ->get(),
            'proveedores' => VehiclesServicioProveedores::orderBy('nombre', 'asc')->get(),
            'tipos_servicio' => VehiclesTipoServicio::with('servicios')->where('nombre', 'Gasolina')->get(),
            'servicios_cat' => VehiclesServiciosCat::with('tipoServicio')->where('nombre', 'Dispersión de gasolina')->get(),
            'sucursales_cargo' => VehiclesSucCargoA::orderBy('nombre', 'asc')->get(),
            'deptos_cargo' => VehiclesDeptoCargoA::orderBy('nombre', 'asc')->get(),
            'estatus' => Estatus::where('tipo_estatus', 'servicio-unidad')->get(),
            'status_counts' => $this->getEstatusCounts(),
        ];

        return $this->respond($data);
    }

    /**
     * Obtiene el conteo de solicitudes por estatus (Solicitado, Autorizado, No Autorizado).
     */
    private function getEstatusCounts(?array $vehicleIds = null, ?int $solicitanteId = null): array
    {
        $estatusList = Estatus::where('tipo_estatus', 'servicio-unidad')->get();
        $solicitado = $estatusList->firstWhere('nombre', 'Solicitado');
        $porDispersar = $estatusList->firstWhere('nombre', 'Por dispersar');
        $dispersado = $estatusList->firstWhere('nombre', 'Dispersada');
        $noAutorizado = $estatusList->firstWhere('nombre', 'No Autorizado');

        $query = VehiclesOrdenesServicio::query()
            ->whereHas('detalles.servicio', function ($q) {
                $q->where('nombre', 'Dispersión de gasolina');
            });
        if (!empty($vehicleIds)) {
            $query->whereIn('vehicle_id', $vehicleIds);
        }
        if (!empty($solicitanteId)) {
            $query->where('solicitante_id', $solicitanteId);
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

    public function mailSolicitudGasolina(int $ordenId)
    {
        $orden = VehiclesOrdenesServicio::findOrFail($ordenId);
        $orden->load(
            'vehicle.departamento',
            'vehicle.linea',
            'vehicle.sucursal',
            'vehicle.estatus',
            'solicitante',
            'detalles.servicio',
            'detalles.servicio.tipoServicio',
            'sucursalCargo',
            'departamentoCargo',
            'proveedor',
        );

        $dirAdmin = Empleado::whereHas('puesto', function ($q) {
            $q->where('nombre', 'Director Administrativo');
        })->whereHas('estatus', function ($q) {
            $q->where('nombre', 'Activo');
        })->first();
        $solicitante = $orden->solicitante;

        // sacamos mails con optional por si usuario no tiene correo
        // $correoDir = Optional($dirAdmin)->correo_institucional;
        $correoDir = null;
        $correoSolicitante = Optional($solicitante)->correo_institucional;

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
        return $this->respond(true, 'Orden de servicio enviada correctamente.');
    }

    public function mailDispersionAutorizacion(int $ordenId, bool $aut)
    {
        $orden = VehiclesOrdenesServicio::findOrFail($ordenId);
        $orden->load(
            'vehicle.departamento',
            'vehicle.linea',
            'vehicle.sucursal',
            'vehicle.estatus',
            'solicitante',
            'detalles.servicio',
            'detalles.servicio.tipoServicio',
            'sucursalCargo',
            'departamentoCargo',
            'proveedor',
            'estatus',
            'bitacora.user.empleado',
            'bitacora.estatus',
        );

        $solicitante = $orden->solicitante;
        $correoSolicitante = Optional($solicitante)->correo_institucional;

        if (!$aut) {
            Mail::to($correoSolicitante)->send(new SendGasAutorizacion($orden));
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

            // sacamos mails con optional por si usuario no tiene correo
            // $correoDir = Optional($dirAdmin)->correo_institucional;
            $correoDir = null;
            // $correoCoord = Optional($coordCompras)->correo_institucional;
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
}
