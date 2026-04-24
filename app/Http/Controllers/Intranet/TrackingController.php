<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Tracking\TrackingActivityRequest;
use App\Http\Requests\Intranet\Tracking\TrackingRequest;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Currency;
use App\Models\Intranet\Product;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductCondicionPago;
use App\Models\Intranet\Tracking;
use App\Models\Intranet\TrackingCerteza;
use App\Models\Intranet\TrackingDepto;
use App\Models\Intranet\TrackingOrigen;
use App\Models\Sucursal;
use App\Models\Intranet\ExchangeRate;
use App\Models\Intranet\TrackingActivity;
use App\Models\Intranet\TrackingDetalle;
use App\Models\Intranet\TrackingDetalleExtras;
use App\Models\Intranet\TrackingProspecto;
use App\Models\Intranet\TrackingTipoSeguimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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

            $trackingData = $data;

            // crear tracking
            if (empty($trackingdata['folio'])) {
                $trackingData['folio'] = str_pad(Tracking::max('id') + 1, 6, '0', STR_PAD_LEFT);
            }

            // meter estatus_id predeterminado como ACTIVO
            $estatus_activo = Estatus::where('nombre', 'activo')
                ->where('tipo_estatus', 'tracking')
                ->first();
            $trackingData['estatus_id'] = $estatus_activo->id;

            // meter situacion_id como formalizado
            $situacion_formalizado = Estatus::where('nombre', 'formalizado')
                ->where('tipo_estatus', 'tracking-situacion')
                ->first();
            $trackingData['situacion_id'] = $situacion_formalizado->id;

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
                        'created_at' => now(),
                    ];
                });
                TrackingDetalleExtras::insert($extras->toArray());
            }

            // activity
            $activityData = $data['activity'];
            $activityData['tracking_id'] = $tracking->id;
            TrackingActivity::create($activityData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tracking creado correctamente',
                'data' => $tracking->load(['detalles', 'activities', 'extras'])
            ], 201);

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
                TrackingActivity::create([
                    ...$data['activity'],
                    'tracking_id' => $tracking->id,
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

    public function getOptions()
    {
        $data = [
            'clientes' => Cliente::all(),
            'origenes' => TrackingOrigen::all(),
            'vendedores' => Empleado::all(),
            'sucursales' => Sucursal::all(),
            'deptos' => TrackingDepto::all(),
            'certezas' => TrackingCerteza::all(),
            'categorias' => ProductCategory::with('condicionesPago')->get(),
            'condiciones_pago' => ProductCondicionPago::all(),
            'monedas' => Currency::all(),
            'productos' => Product::with('precios', 'contrapesos')->get(),
            'tarifa_cambio' => ExchangeRate::latest()->first()?->value ?? 0,
            'tipos_seguimiento' => TrackingTipoSeguimiento::all(),
            'prospectos' => TrackingProspecto::all(),
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

            return response()->json([
                'success' => true,
                'message' => 'Actividad de seguimiento creada correctamente',
                'data' => $activity
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar actividad de seguimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEstatus(Request $request, $id)
    {
        $request->validate([
            'estatus_id' => 'required|integer|exists:estatus,id'
        ]);

        $tracking = Tracking::findOrFail($id);

        $tracking->update([
            'estatus_id' => $request->estatus_id
        ]);

        return response()->json([
            'message' => 'Estatus actualizado correctamente',
            'data' => $tracking
        ]);
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
        $situacion_id = Estatus::where('tipo_estatus', 'tracking-situacion')
            ->where('nombre', $situacion)
            ->firstOrFail()
            ->id;

        $tracking = Tracking::findOrFail($id);

        $tracking->update([
            'situacion_id' => $situacion_id,
        ]);

        return response()->json([
            'message' => 'Estatus actualizado correctamente',
            'data' => $tracking
        ]);
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

        return $pdf->download('cotizacion.pdf');
    }
}
