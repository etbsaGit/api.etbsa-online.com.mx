<?php

namespace App\Http\Controllers\Intranet;

use App\Mail\CustomerAssignmentRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Tracking\TrackingActivityRequest;
use App\Http\Requests\Intranet\Tracking\TrackingRequest;
use App\Mail\SendFormalizarRequest;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Currency;
use App\Models\Intranet\Product;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductCondicionPago;
use App\Models\Intranet\Tracking;
use App\Models\Intranet\TrackingCerteza;
use App\Models\Intranet\TrackingOrigen;
use App\Models\Sucursal;
use App\Models\Intranet\ExchangeRate;
use App\Models\Intranet\TrackingActivity;
use App\Models\Intranet\TrackingDetalle;
use App\Models\Intranet\TrackingDetalleExtras;
use App\Models\Intranet\TrackingFeedback;
use App\Models\Intranet\TrackingProspecto;
use App\Models\Intranet\TrackingTipoSeguimiento;
use App\Models\Puesto;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Mail;

class TrackingController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $trackings = Tracking::with([
            'cliente',
            'prospecto',
            'origen',
            'vendedor',
            'sucursal',
            'categoria',
            'condicionPago',
            'currency',
            'activities' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'activities.certeza',
            'activities.tipoSeguimiento',
            'activities.currency',
            'detalles.productos',
            'estatus',
            'situacion',
            'depto',
            'ultimaActividad.certeza',
            'extras.item',
        ])->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->respond(
            $trackings,
            'Lista de seguimientos cargada correctamente'
        );
    }

    public function myIndex(Request $request)
    {

        $user = Auth::user();

        $filters['vendedor_id'] = $user->empleado->id;

        $trackings = Tracking::with([
            'cliente',
            'prospecto',
            'origen',
            'vendedor',
            'sucursal',
            'categoria',
            'condicionPago',
            'currency',
            'activities' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'activities.certeza',
            'activities.tipoSeguimiento',
            'activities.currency',
            'detalles.productos',
            'estatus',
            'situacion',
            'depto',
            'extras.item',
            'ultimaActividad.certeza'
        ])->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate(10);


        return $this->respond(
            $trackings,
            'Lista de seguimientos cargada correctamente'
        );
    }

    public function store(TrackingRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // crear prospecto
            $prospectoId = null;

            if (!empty($data['prospecto'])) {

                $prospecto = TrackingProspecto::create([
                    'nombre' => $data['prospecto']['nombre'],
                    'email' => $data['prospecto']['email'],
                    'telefono' => $data['prospecto']['telefono'],
                ]);

                $prospectoId = $prospecto->id;
            }

            $trackingData = $data;

            // asignar prospecto creado
            if ($prospectoId) {
                $trackingData['prospecto_id'] = $prospectoId;
            }

            // crear tracking
            if (empty($trackingData['folio'])) {
                $trackingData['folio'] = str_pad(
                    Tracking::max('id') + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }

            // meter estatus_id predeterminado como ACTIVO
            $estatus_activo = Estatus::where('nombre', 'activo')
                ->where('tipo_estatus', 'tracking')
                ->first();
            $trackingData['estatus_id'] = $estatus_activo->id;

            // meter situacion_id como sin formalizar
            $situacion_no_formalizado = Estatus::where('nombre', 'sin formalizar')
                ->where('tipo_estatus', 'tracking-situacion')
                ->first();
            $trackingData['situacion_id'] = $situacion_no_formalizado->id;

            $tracking = Tracking::create($trackingData);

            // detalles
            if (!empty($data['detalles'])) {
                $detalles = collect($data['detalles'])->map(function ($item) use ($tracking) {
                    return [
                        'tracking_id' => $tracking->id,
                        'product_id' => $item['producto_id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unidad' => $item['precio_unidad'],
                        'subtotal' => $item['subtotal'],
                        'currency_id' => $item['currency_id'],
                        'created_at' => now(),
                    ];
                });
                TrackingDetalle::insert($detalles->toArray());
            }

            // extras
            if (!empty($data['extras'])) {
                $extras = collect($data['extras'])->map(function ($item) use ($tracking) {
                    return [
                        'tracking_id' => $tracking->id,
                        'extra_id' => $item['extra_id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unidad' => $item['precio_unidad'],
                        'subtotal' => $item['subtotal'],
                        'currency_id' => $item['currency_id'],
                        'created_at' => now(),
                    ];
                });
                TrackingDetalleExtras::insert($extras->toArray());
            }

            // activity
            $activityData = $data['activity'];
            $activityData['tracking_id'] = $tracking->id;
            TrackingActivity::create($activityData);

            // feedback
            TrackingFeedback::create([
                'tracking_id' => $tracking->id,
                'empleado_id' => $tracking->vendedor_id,
                'situacion_id' => $situacion_no_formalizado->id,
                'comentario' =>
                'Se ha realizado una cotización con el folio #' .
                    $tracking->folio .
                    '. Notas: ' .
                    ($data['notas'] ?? 'N/A')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tracking guardado correctamente',
                'data' => $tracking->load(['detalles', 'activities', 'extras'])
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar tracking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Tracking $tracking)
    {
        $tracking->load([
            'cliente',
            'origen',
            'vendedor',
            'sucursal',
            'certeza',
            'categoria',
            'condicionPago',
            'currency',
            'activities',
            'detalles.producto.currency',
        ]);
        return $this->respond(
            $tracking,
            'Detalle del seguimiento'
        );
    }

    public function update(TrackingRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $tracking = Tracking::findOrFail($id);
            $data = $request->validated();

            // actualizar tracking
            $tracking->update(
                collect($data)->except(['detalles', 'activity'])->toArray()
            );

            // actualizar detalles
            if (isset($data['detalles'])) {
                //borrar los actuales
                TrackingDetalle::where('tracking_id', $tracking->id)->delete();

                // insertar nuevos
                $detalles = collect($data['detalles'])->map(function ($item) use ($tracking) {
                    return [
                        'tracking_id' => $tracking->id,
                        'product_id' => $item['producto_id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unidad' => $item['precio_unidad'],
                        'subtotal' => $item['subtotal'],
                        'created_at' => now(),
                    ];
                });
                TrackingDetalle::insert($detalles->toArray());
            }

            // extras
            if (!empty($data['extras'])) {
                //borrar los actuales
                TrackingDetalleExtras::where('tracking_id', $tracking->id)->delete();
                $extras = collect($data['extras'])->map(function ($item) use ($tracking) {
                    return [
                        'tracking_id' => $tracking->id,
                        'extra_id' => $item['extra_id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unidad' => $item['precio_unidad'],
                        'subtotal' => $item['subtotal'],
                        'created_at' => now(),
                    ];
                });
                TrackingDetalleExtras::insert($extras->toArray());
            }

            // activity
            if (isset($data['activity'])) {
                $actividad = TrackingActivity::create([
                    ...$data['activity'],
                    'tracking_id' => $tracking->id,
                ]);

                // feedback
                TrackingFeedback::create([
                    'tracking_id' => $tracking->id,
                    'empleado_id' => $tracking->vendedor_id,
                    'situacion_id' => $tracking->situacion->id,
                    'comentario' =>
                    'Se ha registrado una actividad de seguimiento en la cotización. ' .
                        'Siguiente seguimiento: ' . $actividad->date_next_tracking .
                        '. Notas: ' .
                        $actividad->notas ?? 'N/A'
                ]);
            } else {
                // feedback
                TrackingFeedback::create([
                    'tracking_id' => $tracking->id,
                    'empleado_id' => $tracking->vendedor_id,
                    'situacion_id' => $tracking->situacion->id,
                    'comentario' =>
                    'Se ha realizado una actualización en la cotización. ' .
                        'Notas del vendedor: ' .
                        ($data['notas'] ?? 'N/A')
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tracking actualizado correctamente',
                'data' => $tracking->load(['detalles', 'activities'])
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tracking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Tracking $tracking)
    {
        DB::transaction(function () use ($tracking) {
            // borrar detalles
            $tracking->details()->delete();

            // borrar cabecera
            $tracking->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Tracking eliminado correctamente'
        ]);
    }

    public function getOptions(Request $request)
    {
        $user = Auth::user();
        $filters = $request->all();

        $data = [
            'clientes' => Cliente::query()
                ->when(!$user->hasRole('Credito'), function ($query) use ($user) {
                    // Si NO tiene rol "credito", filtra solo los clientes relacionados con su empleado
                    $query->whereHas('empleados', function ($q) use ($user) {
                        $q->where('empleados.id', $user->empleado->id);
                    });
                })->filter($filters)->get(),
            'origenes' => TrackingOrigen::all(),
            'vendedores' => Empleado::with('departamento')->get(),
            'sucursales' => Sucursal::all(),
            'deptos' => Departamento::all(),
            'certezas' => TrackingCerteza::all(),
            'categorias' => ProductCategory::with('condicionesPago')->get(),
            'condiciones_pago' => ProductCondicionPago::all(),
            'monedas' => Currency::all(),
            'productos' => Product::with('precios.condicionPago', 'contrapesos')->get(),
            'tarifa_cambio' => ExchangeRate::latest()->first()?->value ?? 0,
            'tipos_seguimiento' => TrackingTipoSeguimiento::all(),
            'prospectos' => TrackingProspecto::all(),
            'gerentes' => Empleado::whereHas('puesto', function ($query) {
                $query->whereIn('nombre', [
                    'Gerente Territorial',
                    'Director General',
                    'Dirección Comercial',
                ])->where('estatus_id', Estatus::where('nombre', 'Activo')->value('id'));
            })->with('sucursal', 'puesto')->get(),
        ];
        return $this->respond($data);
    }

    public function getOptionsActivity()
    {
        $data = [
            'certezas' => TrackingCerteza::all(),
            'monedas' => Currency::all(),
            'tipos_seguimiento' => TrackingTipoSeguimiento::all(),
            'tarifa_cambio' => ExchangeRate::latest()->first()?->value ?? 0,
        ];
        return $this->respond($data);
    }

    public function storeActivity(TrackingActivityRequest $request, $trackingId)
    {
        try {
            DB::beginTransaction();
            $tracking = Tracking::findOrFail($trackingId);

            $data = $request->validated();
            $data['tracking_id'] = $tracking->id;

            $activity = TrackingActivity::create($data);

            // cargar relaciones para frontend
            $activity->load([
                'certeza',
                'tipoSeguimiento',
                'currency'
            ]);

            // feedback
            TrackingFeedback::create([
                'tracking_id' => $tracking->id,
                'empleado_id' => $tracking->vendedor_id,
                'situacion_id' => $tracking->situacion->id,
                'comentario' =>
                'Se ha registrado una actividad de seguimiento. ' .
                    'Notas: ' .
                    ($data['notas'] ?? 'N/A')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Actividad de seguimiento creada correctamente',
                'data' => $activity
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar actividad de seguimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEstatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'estatus_id' => 'required|integer|exists:estatus,id'
            ]);

            $tracking = Tracking::findOrFail($id);

            $tracking->update([
                'estatus_id' => $request->estatus_id
            ]);

            // feedback
            TrackingFeedback::create([
                'tracking_id' => $tracking->id,
                'empleado_id' => $tracking->vendedor_id,
                'situacion_id' => $tracking->situacion->id,
                'comentario' =>
                'Se ha actualizado el estatus del seguimiento a: . ' . $tracking->estatus->nombre
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Estatus actualizado correctamente',
                'data' => $tracking
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar estatus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEstatus()
    {
        $data = [
            'estatus' => Estatus::where('tipo_estatus', 'tracking')->get(),
        ];
        return $this->respond($data);
    }

    public function updateSituacion($id, $situacion)
    {
        try {
            DB::beginTransaction();

            $situacion_id = Estatus::where('tipo_estatus', 'tracking-situacion')
                ->where('nombre', $situacion)
                ->firstOrFail()
                ->id;

            $tracking = Tracking::findOrFail($id);

            $tracking->update([
                'situacion_id' => $situacion_id,
            ]);
            if ($situacion === "Formalizado") {
                // feedback
                TrackingFeedback::create([
                    'tracking_id' => $tracking->id,
                    'empleado_id' => $tracking->vendedor_id,
                    'situacion_id' => $tracking->situacion->id,
                    'comentario' =>
                    'Se ha actualizado la situación del seguimiento a: ' . $situacion
                ]);
            }

            DB::commit();

            if ($situacion === "Formalizado") {
                $this->sendFormalizarRequest($id);
            }

            return response()->json([
                'message' => 'Estatus actualizado correctamente',
                'data' => $tracking
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar estatus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateACliente($id, $cliente_id)
    {
        $tracking = Tracking::findOrFail($id);

        $tracking->update([
            'cliente_id' => $cliente_id,
            'prospecto_id' => null,
        ]);

        return response()->json([
            'message' => 'Estatus actualizado correctamente',
            'data' => $tracking
        ]);
    }

    public function printQuote($id)
    {
        $tracking = Tracking::findOrFail($id);
        $tracking->load(
            'cliente',
            'prospecto',
            'vendedor',
            'sucursal',
            'condicionPago',
            'currency',
            'detalles.productos',
            'extras.item',
            'currency',
        );
        // $pdf = \PDF::loadView('pdf.tracking_quote', compact('data'));
        // return $this->sendResponse($data, 'SHOW PDF');
        // $pdf = App::make('dompdf.wrapper');
        // $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf = Pdf::loadView('pdf.tracking.tracking_quote', [
            'quote' => $tracking
        ]);

        return $pdf->stream('cotizacion.pdf');
    }

    public function sendFormalizarRequest($trackingId)
    {

        $tracking = Tracking::findOrFail($trackingId);

        $tracking->load(
            'cliente',
            'prospecto',
            'vendedor',
            'sucursal',
            'condicionPago',
            'currency',
            'detalles.productos',
            'extras.item'
        );

        $pdf = Pdf::loadView('pdf.tracking.tracking_quote', [
            'quote' => $tracking
        ]);

        // Obtener binario PDF
        $pdfContent = $pdf->output();

        $notificado = $tracking->notificar_a;
        $solicitante = $tracking->vendedor;

        $correo_pruebas = 'munozchristian@etbsa.com.mx';

        $correos = [
            // 'notificado' => $notificado->correo_institucional,
            // 'solicitante' => $solicitante->correo_institucional,
            $correo_pruebas
        ];

        foreach ($correos as $to_email) {
            if ($to_email) {
                Mail::to($to_email)->send(
                    new SendFormalizarRequest($tracking, $pdfContent)
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Correo enviado correctamente'
        ]);
    }

    public function customerAssigmentRequest($trackingId, $clienteId)
    {
        try {
            $tracking = Tracking::findOrFail($trackingId);

            $tracking->load(
                'cliente',
                'prospecto',
                'vendedor',
                'sucursal',
                'condicionPago',
                'currency',
                'detalles.productos',
                'extras.item'
            );

            $cliente = Cliente::findOrFail($clienteId);

            $pdf = Pdf::loadView('pdf.tracking.tracking_quote', [
                'quote' => $tracking
            ]);

            // Obtener binario PDF
            $pdfContent = $pdf->output();

            $solicitante = $tracking->vendedor;
            $sucursal_solicitante = $solicitante->sucursal;
            $gerente_solicitante = Empleado::whereHas(
                'puesto',
                function ($q) {
                    $q->where('nombre', 'Gerente Territorial');
                }
            )->where('sucursal_id', $sucursal_solicitante->id)
                ->whereHas(
                    'estatus',
                    function ($q) {
                        $q->where('nombre', 'Activo');
                    }
                )->first();

            $correo_pruebas = 'munozchristian@etbsa.com.mx';

            $correos = [
                'gerente_solicitante' => $gerente_solicitante->correo_institucional,
                // $correo_pruebas
            ];
            $cc = [
                'solicitante' => $solicitante->correo_institucional,
            ];

            foreach ($correos as $to_email) {
                if ($to_email) {
                    Mail::to($to_email)
                        ->cc($cc)
                        ->send(
                            new CustomerAssignmentRequest($tracking, $cliente, $pdfContent)
                        );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al mandar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEmpleadosAsignados($rfc)
    {
        $user = auth()->user();
        $empleado = $user->empleado;

        $empleado_actual = Empleado::with('sucursal', 'departamento')->findOrFail($empleado->id);

        // Validar empleado asociado al usuario
        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario autenticado no tiene empleado asociado.',
                'data' => null
            ], 403);
        }

        // Buscar cliente con empleados asignados
        $cliente = Cliente::with('empleados.sucursal', 'empleados.departamento', 'town')
            ->where('rfc', $rfc)
            ->first();

        // Cliente no encontrado
        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no registrado, completa la información del cliente.',
                'data' => null
            ], 404);
        }

        // obtener empleado asignado
        $empleadosAsignados = $cliente->empleados;

        if ($empleadosAsignados->isNotEmpty()) {
            $asignado = $empleadosAsignados->contains(function ($emp) use ($empleado_actual) {
                return $emp->id == $empleado_actual->id;
            }); // si no coincide pasa a falso

            if ($asignado) { //if $asignado falso
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente asignado.',
                    'cliente' => $cliente,
                    'empleados_asignados' => $empleadosAsignados,
                    'empleado_actual' => $empleado_actual,
                    'asignado' => $asignado
                ], 203);
            } else { //if $asignado false
                // checar si VENDEDOR ASIGNADO y SOLICITANTE tienen el mismo depto
                $mismo_depto = $empleadosAsignados->contains(function ($emp) use ($empleado_actual) {
                    return
                        // $emp->sucursal->id == $empleado_actual->sucursal->id
                        // &&
                        $emp->departamento->id == $empleado_actual->departamento->id;
                });
                if ($mismo_depto) { //si tienen mismo depto no se tiene acceso a cliente y se manda solicitud de asignacion
                    return response()->json([
                        'success' => true,
                        'message' => 'No tienes acceso a este cliente.',
                        'cliente' => $cliente,
                        'empleados_asignados' => $empleadosAsignados,
                        'empleado_actual' => $empleado_actual,
                        'asignado' => $asignado
                    ], 202);
                } else {
                    // checar si el solicitante y cliente tienen misma sucursal
                    $misma_sucursal = mb_strtolower($empleado_actual->sucursal->nombre) ===
                        mb_strtolower($cliente->town->name);
                    if ($misma_sucursal) {//si cliente y solicitante tienen misma sucursal/ciudad se puede formalizar
                        return response()->json([
                            'success' => true,
                            'message' => 'Cliente ya registrado, se puede formalizar.',
                            'cliente' => $cliente,
                            'empleados_asignados' => $empleadosAsignados,
                            'empleado_actual' => $empleado
                        ], 200);
                    } else {//si cliente y solicitante NO tienen misma sucursal/ciudad NO se puede formalizar
                        return response()->json([
                            'success' => true,
                            'message' => 'No tienes acceso a este cliente. Diferente sucursal',
                            'cliente' => $cliente,
                            'empleados_asignados' => $empleadosAsignados,
                            'empleado_actual' => $empleado_actual,
                            'asignado' => $asignado
                        ], 202);
                    }
                }
            }
        } else { //cliente sin vendedores asignados
            return response()->json([
                'success' => true,
                'message' => 'Cliente ya registrado, sin vendedores asignados.',
                'cliente' => $cliente,
                'empleados_asignados' => $empleadosAsignados,
                'empleado_actual' => $empleado
            ], 200);
        }
    }
}
