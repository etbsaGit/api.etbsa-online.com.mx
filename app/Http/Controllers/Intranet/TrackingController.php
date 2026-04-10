<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
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
use App\Models\Intranet\TrackingTipoSeguimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TrackingController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $trackings = Tracking::with([
            'cliente',
            'origen',
            'vendedor',
            'sucursal',
            'categoria',
            'condicionPago',
            'currency',
            'activities.certeza',
            'activities.tipoSeguimiento',
            'activities.currency',
            'detalles',
            'estatus',
            'depto',
            'ultimaActividad.certeza'
        ])->filter($filters)->paginate(10);

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
                $trackingData['folio'] = 'TRK-' . str_pad(Tracking::max('id') + 1, 6, '0', STR_PAD_LEFT);
            }

            // meter estatus predeterminado como ACTIVO
            $estatus_activo = Estatus::where('nombre', 'activo')
                ->where('clave', 'tracking')
                ->first();
            $trackingData['estatus_id'] = $estatus_activo->id;

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

            // activity
            $activityData = $data['activity'];
            $activityData['tracking_id'] = $tracking->id;
            TrackingActivity::create($activityData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tracking creado correctamente',
                'data' => $tracking->load(['detalles', 'activities'])
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
            unset($data['activity']);

            // actualizar tracking
            $tracking->update($data);

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
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tracking actualizado correctamente',
                'data' => $tracking->load(['detalles'])
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
            'productos' => Product::with('precios')->get(),
            'tarifa_cambio' => ExchangeRate::latest()->first()?->value ?? 0,
            'tipos_seguimiento' => TrackingTipoSeguimiento::all()
        ];
        return $this->respond($data);
    }
}
