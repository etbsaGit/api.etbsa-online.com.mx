<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\ApiController;
use App\Http\Requests\OrdenCompra\Vehicle\OCServiceVehicleRequest;
use App\Http\Requests\OrdenCompra\Vehicle\AutorizacionServicioRequest;
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
use App\Models\CatFormasPago;
use App\Models\CatMetodosPago;
use App\Models\CatUsosCfdi;
use App\Traits\UploadableFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OCServiceVehicleController extends ApiController
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
                $q->where('nombre', '!=', 'Dispersión de gasolina');
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
                'detalles.proveedor',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
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
            ->whereHas('detalles.servicio', function ($q) {
                $q->where('nombre', '!=', 'Dispersión de gasolina');
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
                'detalles.proveedor',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
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
        $response['status_counts'] = $this->getEstatusCounts($vehicleIds);

        return $this->respond($response);
    }

    /**
     * Registra una nueva Orden de Servicio junto con sus detalles de servicio / cotizaciones.
     */
    public function store(OCServiceVehicleRequest $request)
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
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'solicitante_id' => $validated['solicitante_id'] ?? null,
                'suc_cargo_id' => $validated['suc_cargo_id'] ?? null,
                'depto_cargo_id' => $validated['depto_cargo_id'] ?? null,
                'factura' => $validated['factura'] ?? null,
                'estatus_id' => $estatus_id,
            ]);

            $ordenServicio->orden_compra = (string) $ordenServicio->id;
            $ordenServicio->save();

            // Si se envió cotización general en cabecera (compatibilidad)
            $archivoInput = $request->input('archivo');
            $base64 = is_array($archivoInput) ? ($archivoInput['base64'] ?? null) : $request->input('base64');
            if (!empty($base64)) {
                $cotizacionUrl = $this->saveCotizacionPdf($base64, $ordenServicio->id);
                $ordenServicio->cotizacion_url = $cotizacionUrl;
                $ordenServicio->save();
            }

            // Guardar detalles y subir cotizaciones individuales
            $cotizacionCountsByServicio = [];
            foreach ($validated['detalles'] as $detalle) {
                $srvId = $detalle['servicio_id'];
                $cotizacionCountsByServicio[$srvId] = ($cotizacionCountsByServicio[$srvId] ?? 0) + 1;
                $cotIndex = $cotizacionCountsByServicio[$srvId];

                $cotizacionUrl = null;
                $archivoDetalle = $detalle['archivo'] ?? null;
                $detalleBase64 = is_array($archivoDetalle) ? ($archivoDetalle['base64'] ?? null) : ($detalle['base64'] ?? null);

                if (!empty($detalleBase64)) {
                    $cotizacionUrl = $this->saveDetalleCotizacionPdf(
                        $detalleBase64,
                        $ordenServicio->id,
                        $ordenServicio->orden_compra,
                        (int) $detalle['servicio_id'],
                        (int) $detalle['proveedor_id'],
                        $cotIndex
                    );
                } elseif (!empty($detalle['cotizacion_url'])) {
                    $cotizacionUrl = $detalle['cotizacion_url'];
                }

                VehiclesDetallesServicio::create([
                    'orden_servicio_id' => $ordenServicio->id,
                    'vehicle_id' => !empty($detalle['vehicle_id']) ? $detalle['vehicle_id'] : $validated['vehicle_id'],
                    'servicio_id' => $detalle['servicio_id'],
                    'proveedor_id' => $detalle['proveedor_id'],
                    'cotizacion_url' => $cotizacionUrl,
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
                    'forma_pago_id' => !empty($detalle['forma_pago_id']) ? (int) $detalle['forma_pago_id'] : null,
                    'metodo_pago_id' => !empty($detalle['metodo_pago_id']) ? (int) $detalle['metodo_pago_id'] : null,
                    'uso_cfdi_id' => !empty($detalle['uso_cfdi_id']) ? (int) $detalle['uso_cfdi_id'] : null,
                ]);
            }

            $bitacora = $this->insertBitacora($ordenServicio->id, $estatus_id, 'Orden de Servicio creada');
            if (!$bitacora) {
                throw new \Exception('Error al insertar la bitácora');
            }

            DB::commit();
            $this->mailSolicitudOrden($ordenServicio->id);

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
                'detalles.proveedor',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
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
            'detalles.proveedor',
            'detalles.formaPago',
            'detalles.metodoPago',
            'detalles.usoCfdi',
            'estatus',
            'bitacora.user.empleado',
            'bitacora.estatus',
        ])->findOrFail($id);

        return $this->respond($orden);
    }

    /**
     * Actualiza una Orden de Servicio y sincroniza sus detalles.
     */
    public function update(OCServiceVehicleRequest $request, $id)
    {
        $ordenServicio = VehiclesOrdenesServicio::findOrFail($id);
        $validated = $request->validated();

        $orden = DB::transaction(function () use ($ordenServicio, $validated, $request) {
            $ordenServicio->update([
                'autorizacion_compras' => $validated['autorizacion_compras'] ?? null,
                'orden_compra' => (string) $ordenServicio->id,
                'fecha_elaboracion' => $validated['fecha_elaboracion'],
                'vehicle_id' => $validated['vehicle_id'],
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'solicitante_id' => $validated['solicitante_id'] ?? null,
                'suc_cargo_id' => $validated['suc_cargo_id'] ?? null,
                'depto_cargo_id' => $validated['depto_cargo_id'] ?? null,
                'factura' => $validated['factura'] ?? null,
                'cotizacion_url' => array_key_exists('cotizacion_url', $validated)
                    ? $validated['cotizacion_url']
                    : $ordenServicio->cotizacion_url,
            ]);

            // Procesar archivo PDF de cotización general si se envió
            $archivoInput = $request->input('archivo');
            $base64 = is_array($archivoInput) ? ($archivoInput['base64'] ?? null) : $request->input('base64');

            if (!empty($base64)) {
                $cotizacionUrl = $this->saveCotizacionPdf($base64, $ordenServicio->id);
                $ordenServicio->cotizacion_url = $cotizacionUrl;
                $ordenServicio->save();
            }

            $ordenServicio->detalles()->delete();

            $cotizacionCountsByServicio = [];
            foreach ($validated['detalles'] as $detalle) {
                $srvId = $detalle['servicio_id'];
                $cotizacionCountsByServicio[$srvId] = ($cotizacionCountsByServicio[$srvId] ?? 0) + 1;
                $cotIndex = $cotizacionCountsByServicio[$srvId];

                $cotizacionUrl = null;
                $archivoDetalle = $detalle['archivo'] ?? null;
                $detalleBase64 = is_array($archivoDetalle) ? ($archivoDetalle['base64'] ?? null) : ($detalle['base64'] ?? null);

                if (!empty($detalleBase64)) {
                    $cotizacionUrl = $this->saveDetalleCotizacionPdf(
                        $detalleBase64,
                        $ordenServicio->id,
                        $ordenServicio->orden_compra,
                        (int) $detalle['servicio_id'],
                        (int) $detalle['proveedor_id'],
                        $cotIndex
                    );
                } elseif (!empty($detalle['cotizacion_url'])) {
                    $cotizacionUrl = $detalle['cotizacion_url'];
                }

                VehiclesDetallesServicio::create([
                    'orden_servicio_id' => $ordenServicio->id,
                    'vehicle_id' => !empty($detalle['vehicle_id']) ? $detalle['vehicle_id'] : $validated['vehicle_id'],
                    'servicio_id' => $detalle['servicio_id'],
                    'proveedor_id' => $detalle['proveedor_id'],
                    'cotizacion_url' => $cotizacionUrl,
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
                    'forma_pago_id' => !empty($detalle['forma_pago_id']) ? (int) $detalle['forma_pago_id'] : null,
                    'metodo_pago_id' => !empty($detalle['metodo_pago_id']) ? (int) $detalle['metodo_pago_id'] : null,
                    'uso_cfdi_id' => !empty($detalle['uso_cfdi_id']) ? (int) $detalle['uso_cfdi_id'] : null,
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
                'detalles.proveedor',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
                'estatus',
                'bitacora.user.empleado',
                'bitacora.estatus',
            ]);
        });

        return $this->respond($orden);
    }

    /**
     * Guarda el archivo PDF de cotización de un detalle en S3 con el nombre formateado:
     * {orden de compra}_{SER o REF}_{proveedor}_{cotizacion(N)}.pdf
     */
    private function saveDetalleCotizacionPdf(string $base64, int $ordenId, ?string $ordenCompra, int $servicioId, int $proveedorId, int $cotizacionIndex): string
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

        // Determinar SER o REF según tipo de servicio
        $servicioCat = VehiclesServiciosCat::with('tipoServicio')->find($servicioId);
        $tipoNombre = mb_strtolower($servicioCat?->tipoServicio?->nombre ?? '');
        $tipoCode = (str_contains($tipoNombre, 'refaccion') || str_contains($tipoNombre, 'refacción')) ? 'REF' : 'SER';

        // Obtener nombre del proveedor limpio
        $proveedor = VehiclesServicioProveedores::find($proveedorId);
        $provNombre = $proveedor ? Str::slug($proveedor->nombre, '_') : 'PROV_' . $proveedorId;

        $ocFolio = !empty($ordenCompra) ? $ordenCompra : (string) $ordenId;
        $fileName = "{$ocFolio}_{$tipoCode}_{$provNombre}_cotizacion{$cotizacionIndex}.pdf";

        $folder = "ordenes_compra/ordenes_compras_vehicles/orden_{$ordenId}";
        $filePath = "{$folder}/{$fileName}";

        Storage::disk('s3')->put($filePath, $decoded);

        return Storage::disk('s3')->url($filePath);
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

        $folder = "ordenes_compra/ordenes_compras_vehicles/orden_{$ordenId}";
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
                'detalles.proveedor',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
                'detalles.formaPago',
                'detalles.metodoPago',
                'detalles.usoCfdi',
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
     * Elimina un archivo de cotización de S3 dada su URL completa o ruta relativa.
     */
    private function deleteCotizacionFromS3(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        try {
            $relativePath = $url;
            $pos = strpos($url, 'ordenes_compra/');
            if ($pos !== false) {
                $relativePath = substr($url, $pos);
            }
            $relativePath = urldecode(explode('?', $relativePath)[0]);
            if (Storage::disk('s3')->exists($relativePath)) {
                Storage::disk('s3')->delete($relativePath);
            }
        } catch (\Throwable $e) {
            \Log::error("Error eliminando archivo S3 de cotización ($url): " . $e->getMessage());
        }
    }

    // autorización de solicitud
    public function autorizarOrdenCompra(AutorizacionServicioRequest $request)
    {
        DB::beginTransaction();
        try {
            $user_id = Auth::user()->id;
            $data = $request->validated();
            $orden = VehiclesOrdenesServicio::findOrFail($data['orden_servicio_id']);

            if (!$orden) {
                return $this->respondNotFound('Orden de compra no encontrada.');
            }

            $estatusAutorizado = Estatus::where('nombre', 'Autorizado')->where('tipo_estatus', 'servicio-unidad')->first();
            $estatusNoAutorizado = Estatus::where('nombre', 'No Autorizado')->where('tipo_estatus', 'servicio-unidad')->first();

            if ($data['autorizado']) {
                $estatusId = $estatusAutorizado->id;
                $detallesSeleccionadosIds = array_map('intval', $data['detalles_ids'] ?? []);

                $detallesExistentes = VehiclesDetallesServicio::where('orden_servicio_id', $orden->id)->get();

                // Validar que todos los IDs seleccionados pertenezcan a esta orden
                $existentesIds = $detallesExistentes->pluck('id')->all();
                foreach ($detallesSeleccionadosIds as $selId) {
                    if (!in_array($selId, $existentesIds, true)) {
                        return response()->json([
                            'message' => 'Una o más cotizaciones seleccionadas no pertenecen a esta orden de compra.'
                        ], 422);
                    }
                }

                $conservar = $detallesExistentes->whereIn('id', $detallesSeleccionadosIds);
                $descartar = $detallesExistentes->whereNotIn('id', $detallesSeleccionadosIds);

                if ($conservar->isEmpty()) {
                    return response()->json([
                        'message' => 'Debe seleccionar al menos una cotización para autorizar la orden de compra.'
                    ], 422);
                }

                // Validar que no se haya seleccionado más de una cotización para el mismo concepto
                $conceptosVistos = [];
                foreach ($conservar as $det) {
                    $conceptoKey = ($det->servicio_id ?? 'sin_srv') . '_' .
                        trim($det->descripcion ?? '') . '_' .
                        ($det->llanta_anchura ?? '') . '_' .
                        ($det->llanta_altura ?? '') . '_' .
                        ($det->llanta_rin ?? '');

                    if (isset($conceptosVistos[$conceptoKey])) {
                        return response()->json([
                            'message' => 'No se puede seleccionar más de una cotización para el mismo concepto.'
                        ], 422);
                    }
                    $conceptosVistos[$conceptoKey] = true;
                }

                // Borrar cotizaciones no seleccionadas y sus archivos PDF de S3
                foreach ($descartar as $detDesc) {
                    if (!empty($detDesc->cotizacion_url)) {
                        $this->deleteCotizacionFromS3($detDesc->cotizacion_url);
                    }
                    $detDesc->delete();
                }

                // Obtener o generar el folio de Autorización de Compras (SE COMPARTE ENTRE TODAS LAS ÓRDENES GENERADAS)
                $autorizacionCompras = $orden->autorizacion_compras;
                if (!$autorizacionCompras) {
                    $sucCargo = $orden->sucursalCargo ? $orden->sucursalCargo->siglas : 'SUC';
                    $deptoCargo = $orden->departamentoCargo ? $orden->departamentoCargo->siglas : 'DEP';
                    $aut = DB::select("SELECT MAX(id) AS max_id FROM contador_folio_autorizacion_compras");
                    $folio = ($aut[0]->max_id ?? 0) + 1;
                    $autorizacionCompras = $sucCargo . '-' . $deptoCargo . '-' . $folio;
                    DB::insert("insert into contador_folio_autorizacion_compras() values();");
                }

                // Agrupar las cotizaciones conservadas por proveedor
                $detallesPorProveedor = $conservar->groupBy('proveedor_id');
                $ordenesGeneradas = [];
                $isFirstGroup = true;

                foreach ($detallesPorProveedor as $proveedorId => $detallesGrupo) {
                    if ($isFirstGroup) {
                        // El primer grupo mantiene la orden de compra original
                        $orden->update([
                            'proveedor_id' => $proveedorId ?: null,
                            'estatus_id' => $estatusId,
                            'autorizacion_compras' => $autorizacionCompras,
                        ]);

                        $this->insertBitacora(
                            $orden->id,
                            $estatusId,
                            $data['comentarios'] ?? 'Orden autorizada correctamente.'
                        );

                        try {
                            $this->mailOrdenAutorizacion($orden->id, true);
                        } catch (\Throwable $mEx) {
                            \Log::error("Error enviando correo de autorización para orden #{$orden->id}: " . $mEx->getMessage());
                        }

                        $ordenesGeneradas[] = $orden;
                        $isFirstGroup = false;
                    } else {
                        // Proveedores subsecuentes: crear nueva orden con el MISMO número de autorización
                        $nuevaOrden = VehiclesOrdenesServicio::create([
                            'autorizacion_compras' => $autorizacionCompras,
                            'orden_compra' => null,
                            'fecha_elaboracion' => $orden->fecha_elaboracion,
                            'vehicle_id' => $orden->vehicle_id,
                            'proveedor_id' => $proveedorId ?: null,
                            'solicitante_id' => $orden->solicitante_id,
                            'suc_cargo_id' => $orden->suc_cargo_id,
                            'depto_cargo_id' => $orden->depto_cargo_id,
                            'factura' => null,
                            'estatus_id' => $estatusId,
                        ]);

                        $nuevaOrden->orden_compra = (string) $nuevaOrden->id;
                        $nuevaOrden->save();

                        // Reasignar los detalles de este proveedor a la nueva orden
                        foreach ($detallesGrupo as $det) {
                            $det->orden_servicio_id = $nuevaOrden->id;
                            $det->save();
                        }

                        $comentariosBitacora = ($data['comentarios'] ?? 'Orden autorizada correctamente.') .
                            " (Generada por división de proveedor desde la orden #{$orden->id} con autorización {$autorizacionCompras})";

                        $this->insertBitacora(
                            $nuevaOrden->id,
                            $estatusId,
                            $comentariosBitacora
                        );

                        try {
                            $this->mailOrdenAutorizacion($nuevaOrden->id, true);
                        } catch (\Throwable $mEx) {
                            \Log::error("Error enviando correo de autorización para nueva orden #{$nuevaOrden->id}: " . $mEx->getMessage());
                        }

                        $ordenesGeneradas[] = $nuevaOrden;
                    }
                }

                DB::commit();

                $totalOrdenes = count($ordenesGeneradas);
                $foliosTexto = collect($ordenesGeneradas)->pluck('id')->map(fn($id) => "#{$id}")->join(', ');
                $mensajeFinal = $totalOrdenes > 1
                    ? "Se autorizaron los conceptos y se generaron {$totalOrdenes} órdenes de compra ({$foliosTexto}) con el folio de autorización {$autorizacionCompras}."
                    : "Orden de compra #{$orden->id} autorizada correctamente con folio de autorización {$autorizacionCompras}.";

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
                    'detalles.proveedor',
                    'detalles.formaPago',
                    'detalles.metodoPago',
                    'detalles.usoCfdi',
                    'estatus',
                    'bitacora.user.empleado',
                    'bitacora.estatus',
                ]), $mensajeFinal);

            } else {
                // No autorizado
                $estatusId = $estatusNoAutorizado->id;
                $this->insertBitacora($orden->id, $estatusId, $data['comentarios'] ?? 'Sin comentarios');
                $orden->update([
                    'estatus_id' => $estatusId,
                ]);

                try {
                    $this->mailOrdenAutorizacion($orden->id, false);
                } catch (\Throwable $mEx) {
                    \Log::error("Error enviando correo de no autorización para orden #{$orden->id}: " . $mEx->getMessage());
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
                    'detalles.proveedor',
                    'detalles.formaPago',
                    'detalles.metodoPago',
                    'detalles.usoCfdi',
                    'estatus',
                    'bitacora.user.empleado',
                    'bitacora.estatus',
                ]), 'Orden de servicio marcada como no autorizada.');
            }
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
            'tipos_servicio' => VehiclesTipoServicio::with('servicios')->where('nombre', '!=', 'Gasolina')
                ->orderBy('nombre', 'asc')
                ->get(),
            'servicios_cat' => VehiclesServiciosCat::with('tipoServicio')->where('nombre', '!=', 'Dispersión de gasolina')
                ->orderBy('nombre', 'asc')
                ->get(),
            'sucursales_cargo' => VehiclesSucCargoA::orderBy('nombre', 'asc')->get(),
            'deptos_cargo' => VehiclesDeptoCargoA::orderBy('nombre', 'asc')->get(),
            'formas_pago' => CatFormasPago::orderBy('clave', 'asc')->get(),
            'metodos_pago' => CatMetodosPago::orderBy('clave', 'asc')->get(),
            'usos_cfdi' => CatUsosCfdi::orderBy('clave', 'asc')->get(),
            'estatus' => Estatus::where('tipo_estatus', 'servicio-unidad')->get(),
            'status_counts' => $this->getEstatusCounts(),
        ];

        return $this->respond($data);
    }

    /**
     * Obtiene el conteo de solicitudes por estatus (Solicitado, Autorizado, No Autorizado).
     */
    private function getEstatusCounts(?array $vehicleIds = null): array
    {
        $estatusList = Estatus::where('tipo_estatus', 'servicio-unidad')->get();
        $solicitado = $estatusList->firstWhere('nombre', 'Solicitado');
        $autorizado = $estatusList->firstWhere('nombre', 'Autorizado');
        $noAutorizado = $estatusList->firstWhere('nombre', 'No Autorizado');

        $query = VehiclesOrdenesServicio::query()
            ->whereHas('detalles.servicio', function ($q) {
                $q->where('nombre', '!=', 'Dispersión de gasolina');
            });

        if (!empty($vehicleIds)) {
            $query->whereIn('vehicle_id', $vehicleIds);
        }

        $countsByStatus = (clone $query)
            ->whereIn('estatus_id', $estatusList->pluck('id'))
            ->groupBy('estatus_id')
            ->select('estatus_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'estatus_id');

        return [
            'solicitado' => (int) ($countsByStatus[$solicitado?->id] ?? 0),
            'autorizado' => (int) ($countsByStatus[$autorizado?->id] ?? 0),
            'no_autorizado' => (int) ($countsByStatus[$noAutorizado?->id] ?? 0),
            'solicitado_id' => $solicitado?->id,
            'autorizado_id' => $autorizado?->id,
            'no_autorizado_id' => $noAutorizado?->id,
        ];
    }

    public function mailSolicitudOrden(int $ordenId)
    {
        $orden = VehiclesOrdenesServicio::findOrFail($ordenId);
        $orden->load(
            'vehicle.departamento',
            'vehicle.linea',
            'vehicle.sucursal',
            'solicitante',
            'detalles.servicio',
            'detalles.servicio.tipoServicio',
            'detalles.proveedor',
            'detalles.formaPago',
            'detalles.metodoPago',
            'detalles.usoCfdi',
            'sucursalCargo',
            'departamentoCargo',
        );

        $dirAdmin = Empleado::whereHas('puesto', function ($q) {
            $q->where('nombre', 'Director Administrativo');
        })->whereHas('estatus', function ($q) {
            $q->where('nombre', 'Activo');
        })->first();
        $solicitante = $orden->solicitante;

        $correoDir = null;
        $correoSolicitante = Optional($solicitante)->correo_institucional;

        if ($correoDir) {
            $mail = Mail::to($correoDir);
            if ($correoSolicitante) {
                $mail->cc($correoSolicitante);
            }
            $mail->send(new SendOrdenCompraVehicle($orden));
        } else {
            if ($correoSolicitante) {
                $mail = Mail::to($correoSolicitante);
                $mail->send(new SendOrdenCompraVehicle($orden));
            }
        }
        return $this->respond(true, 'Orden de servicio enviada correctamente.');
    }

    public function mailOrdenAutorizacion(int $ordenId, bool $aut)
    {
        $orden = VehiclesOrdenesServicio::findOrFail($ordenId);
        $orden->load(
            'vehicle.departamento',
            'vehicle.linea',
            'vehicle.sucursal',
            'solicitante',
            'detalles.servicio',
            'detalles.servicio.tipoServicio',
            'detalles.proveedor',
            'detalles.formaPago',
            'detalles.metodoPago',
            'detalles.usoCfdi',
            'sucursalCargo',
            'departamentoCargo',
            'estatus'
        );

        $solicitante = $orden->solicitante;
        $correoSolicitante = Optional($solicitante)->correo_institucional;

        if (!$aut) {
            Mail::to($correoSolicitante)->send(new SendOrdenAutorizacion($orden));
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

            $correoDir = null;
            $correoCoord = null;

            if ($correoDir) {
                $mail = Mail::to($correoDir);
                if ($correoSolicitante) {
                    $mail->cc($correoSolicitante);
                }
                if ($correoCoord) {
                    $mail->cc($correoCoord);
                }
                $mail->send(new SendOrdenAutorizacion($orden));
            } elseif ($correoCoord) {
                $mail = Mail::to($correoCoord);
                if ($correoSolicitante) {
                    $mail->cc($correoSolicitante);
                }
                $mail->send(new SendOrdenAutorizacion($orden));
            } elseif ($correoSolicitante) {
                Mail::to($correoSolicitante)->send(new SendOrdenAutorizacion($orden));
            }
        }
        return $this->respond(true, 'Autorización enviada correctamente.');
    }
}
