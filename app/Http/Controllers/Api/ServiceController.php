<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Service\StoreRequest;
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
use Illuminate\Support\Facades\Storage;

class ServiceController extends ApiController
{
    use UploadableFile;
    /**
     * Lista paginada de Órdenes de Servicio con sus detalles y relaciones.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $ordenes = VehiclesOrdenesServicio::filter($filters)
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

        return $this->respond($ordenes);
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
            ]);
        }

        $filters = $request->all();
        if (!empty($filters['vehicle_id']) && in_array((int) $filters['vehicle_id'], array_map('intval', $vehicleIds), true)) {
            $filters['vehicle_id'] = (int) $filters['vehicle_id'];
        } else {
            unset($filters['vehicle_id']);
            $filters['vehicle_ids'] = $vehicleIds;
        }

        $ordenes = VehiclesOrdenesServicio::filter($filters)
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

        return $this->respond($response);
    }

    /**
     * Registra una nueva Orden de Servicio junto con sus detalles de servicio.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $orden = DB::transaction(function () use ($validated, $request) {
            $estatus_id = Estatus::where('nombre', 'Solicitado')->where('tipo_estatus', 'servicio-unidad')->first()->id;
            $ordenServicio = VehiclesOrdenesServicio::create([
                'autorizacion_compras' => $validated['autorizacion_compras'] ?? null,
                'orden_compra' => null,
                'fecha_elaboracion' => $validated['fecha_elaboracion'],
                'vehicle_id' => $validated['vehicle_id'],
                'proveedor_id' => $validated['proveedor_id'],
                'solicitante_id' => $validated['solicitante_id'] ?? null,
                'suc_cargo_id' => $validated['suc_cargo_id'] ?? null,
                'depto_cargo_id' => $validated['depto_cargo_id'] ?? null,
                'factura' => $validated['factura'] ?? null,
                'estatus_id' => $estatus_id,
            ]);

            $ordenServicio->orden_compra = (string) $ordenServicio->id;
            $ordenServicio->save();

            // Procesar archivo PDF de cotización si se proporcionó
            $archivoInput = $request->input('archivo');
            $base64 = is_array($archivoInput) ? ($archivoInput['base64'] ?? null) : $request->input('base64');

            if (!empty($base64)) {
                $cotizacionUrl = $this->saveCotizacionPdf($base64, $ordenServicio->id);
                $ordenServicio->cotizacion_url = $cotizacionUrl;
                $ordenServicio->save();
            }

            foreach ($validated['detalles'] as $detalle) {
                VehiclesDetallesServicio::create([
                    'orden_servicio_id' => $ordenServicio->id,
                    'vehicle_id' => !empty($detalle['vehicle_id']) ? $detalle['vehicle_id'] : $validated['vehicle_id'],
                    'servicio_id' => $detalle['servicio_id'],
                    'km_evento' => isset($detalle['km_evento']) && $detalle['km_evento'] !== ''
                        ? (int) $detalle['km_evento']
                        : null,
                    'descripcion' => $detalle['descripcion'],
                    'precio_unitario' => isset($detalle['precio_unitario']) && $detalle['precio_unitario'] !== ''
                        ? (float) $detalle['precio_unitario']
                        : null,
                    'cantidad' => isset($detalle['cantidad']) && $detalle['cantidad'] !== ''
                        ? (int) $detalle['cantidad']
                        : null,
                    'llanta_anchura' => isset($detalle['llanta_anchura']) && $detalle['llanta_anchura'] !== ''
                        ? (int) $detalle['llanta_anchura']
                        : null,
                    'llanta_altura' => isset($detalle['llanta_altura']) && $detalle['llanta_altura'] !== ''
                        ? (int) $detalle['llanta_altura']
                        : null,
                    'llanta_rin' => isset($detalle['llanta_rin']) && $detalle['llanta_rin'] !== ''
                        ? (int) $detalle['llanta_rin']
                        : null,
                    'subtotal' => (float) $detalle['subtotal'],
                    'iva' => (float) $detalle['iva'],
                    'total' => (float) $detalle['total'],
                ]);
            }

            $bitacora = $this->insertBitacora($ordenServicio->id, $estatus_id, 'Orden de Servicio creada');
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

        return $this->respondCreated($orden);
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
    public function update(StoreRequest $request, $id)
    {
        $ordenServicio = VehiclesOrdenesServicio::findOrFail($id);
        $validated = $request->validated();

        $orden = DB::transaction(function () use ($ordenServicio, $validated, $request) {
            $ordenServicio->update([
                'autorizacion_compras' => $validated['autorizacion_compras'] ?? null,
                'orden_compra' => (string) $ordenServicio->id,
                'fecha_elaboracion' => $validated['fecha_elaboracion'],
                'vehicle_id' => $validated['vehicle_id'],
                'proveedor_id' => $validated['proveedor_id'],
                'solicitante_id' => $validated['solicitante_id'] ?? null,
                'suc_cargo_id' => $validated['suc_cargo_id'] ?? null,
                'depto_cargo_id' => $validated['depto_cargo_id'] ?? null,
                'factura' => $validated['factura'] ?? null,
                'cotizacion_url' => array_key_exists('cotizacion_url', $validated)
                    ? $validated['cotizacion_url']
                    : $ordenServicio->cotizacion_url,
            ]);

            // Procesar archivo PDF de cotización si se envió
            $archivoInput = $request->input('archivo');
            $base64 = is_array($archivoInput) ? ($archivoInput['base64'] ?? null) : $request->input('base64');

            if (!empty($base64)) {
                $cotizacionUrl = $this->saveCotizacionPdf($base64, $ordenServicio->id);
                $ordenServicio->cotizacion_url = $cotizacionUrl;
                $ordenServicio->save();
            }

            $ordenServicio->detalles()->delete();

            foreach ($validated['detalles'] as $detalle) {
                VehiclesDetallesServicio::create([
                    'orden_servicio_id' => $ordenServicio->id,
                    'vehicle_id' => !empty($detalle['vehicle_id']) ? $detalle['vehicle_id'] : $validated['vehicle_id'],
                    'servicio_id' => $detalle['servicio_id'],
                    'km_evento' => isset($detalle['km_evento']) && $detalle['km_evento'] !== ''
                        ? (int) $detalle['km_evento']
                        : null,
                    'descripcion' => $detalle['descripcion'],
                    'precio_unitario' => isset($detalle['precio_unitario']) && $detalle['precio_unitario'] !== ''
                        ? (float) $detalle['precio_unitario']
                        : null,
                    'cantidad' => isset($detalle['cantidad']) && $detalle['cantidad'] !== ''
                        ? (int) $detalle['cantidad']
                        : null,
                    'llanta_anchura' => isset($detalle['llanta_anchura']) && $detalle['llanta_anchura'] !== ''
                        ? (int) $detalle['llanta_anchura']
                        : null,
                    'llanta_altura' => isset($detalle['llanta_altura']) && $detalle['llanta_altura'] !== ''
                        ? (int) $detalle['llanta_altura']
                        : null,
                    'llanta_rin' => isset($detalle['llanta_rin']) && $detalle['llanta_rin'] !== ''
                        ? (int) $detalle['llanta_rin']
                        : null,
                    'subtotal' => (float) $detalle['subtotal'],
                    'iva' => (float) $detalle['iva'],
                    'total' => (float) $detalle['total'],
                ]);
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
     * Guarda el archivo PDF de cotización en S3 y retorna su URL pública accesible.
     * Valida estrictamente que sea un documento PDF.
     */
    private function saveCotizacionPdf(string $base64, int $ordenId): string
    {
        if (!preg_match('/^data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+)?;base64,/', $base64, $matches)) {
            throw new \Exception('El formato del archivo no es válido.');
        }

        $mimeType = strtolower($matches[1] ?? '');
        if ($mimeType !== 'application/pdf') {
            throw new \Exception('Solo se permiten archivos en formato PDF.');
        }

        $data = substr($base64, strpos($base64, ',') + 1);
        $decoded = base64_decode($data);
        if ($decoded === false || substr($decoded, 0, 4) !== '%PDF') {
            throw new \Exception('El archivo proporcionado no es un documento PDF válido.');
        }

        $folder = "intranet/servicios_unidades/orden_{$ordenId}";
        $relativePath = $this->saveDoc($base64, $folder);

        return Storage::disk('s3')->url($relativePath);
    }

    /**
     * Sube o actualiza la cotización en PDF para una Orden de Servicio específica.
     */
    public function uploadCotizacion(Request $request, int $id)
    {
        $ordenServicio = VehiclesOrdenesServicio::findOrFail($id);

        $archivoInput = $request->input('archivo');
        $base64 = is_array($archivoInput) ? ($archivoInput['base64'] ?? null) : $request->input('base64');

        if (empty($base64)) {
            return response()->json([
                'message' => 'El archivo de cotización en formato PDF es obligatorio.'
            ], 422);
        }

        try {
            $cotizacionUrl = $this->saveCotizacionPdf($base64, $ordenServicio->id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }

        $ordenServicio->cotizacion_url = $cotizacionUrl;
        $ordenServicio->save();

        $this->insertBitacora($ordenServicio->id, $ordenServicio->estatus_id, 'Cotización en formato PDF adjuntada');

        return $this->respond([
            'message' => 'Cotización PDF subida correctamente.',
            'cotizacion_url' => $cotizacionUrl,
            'orden' => $ordenServicio->fresh([
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
        ]);
    }

    /**
     * Elimina una Orden de Servicio y sus detalles asociados.
     */
    public function destroy($id)
    {
        $ordenServicio = VehiclesOrdenesServicio::findOrFail($id);

        DB::transaction(function () use ($ordenServicio) {
            $ordenServicio->detalles()->delete();
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
            'tipos_servicio' => VehiclesTipoServicio::with('servicios')
                ->orderBy('nombre', 'asc')
                ->get(),
            'servicios_cat' => VehiclesServiciosCat::with('tipoServicio')
                ->orderBy('nombre', 'asc')
                ->get(),
            'sucursales_cargo' => VehiclesSucCargoA::orderBy('nombre', 'asc')->get(),
            'deptos_cargo' => VehiclesDeptoCargoA::orderBy('nombre', 'asc')->get(),
        ];

        return $this->respond($data);
    }
}
