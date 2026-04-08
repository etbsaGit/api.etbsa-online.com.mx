<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Tracking\TrackingRequest;
use App\Models\Empleado;
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
            'certeza',
            'categoria',
            'condicionPago',
            'currency',
            'activities',
            'details'
        ])->filter($filters)->paginate(10);

        return $this->respond(
            $trackings,
            'Lista de seguimientos cargada correctamente'
        );
    }

    public function store(TrackingRequest $request)
    {
        DB::transaction(function () use ($request) {
            $tracking = Tracking::create(
                $request->except('details')
            );
            $tracking->details()->createMany($request->details);
        });
        return response()->json(['success' => true]);
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
            'details'
        ]);
        return $this->respond(
            $tracking,
            'Detalle del seguimiento'
        );
    }

    public function update(TrackingRequest $request, Tracking $tracking) {
        DB::transaction(function () use ($request,$tracking){
            // se actualzia cabecera
            $tracking->update(
                $request->excelpt('details')
            );

            $detailsRequest = collect($request->details);
            // id que vienen del frontend
            $idsRequest = $detailsRequest
                ->pluck('id')
                ->filter()
                ->values();

            // eliminar los que ya no  vienen
            $tracking->details()
                ->whereNotIn('id',$idsRequest)
                ->delete();

            // insertar o actualizar
            $tracking->details()->upsert(
                $request->details,
                ['id'],
                ['product_id','cantidad','precion_unidad','subtotal']
            );

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento actualziado correctamente'
            ]);
        });
    }

    public function destroy(Tracking $tracking)
    {
        DB::transaction(function () use ($tracking){
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
        ];
        return $this->respond($data);
    }
}
