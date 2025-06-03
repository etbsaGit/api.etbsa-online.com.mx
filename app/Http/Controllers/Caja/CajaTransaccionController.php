<?php

namespace App\Http\Controllers\Caja;

use PDF;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Models\Caja\CajaPago;
use App\Models\Caja\CajaCorte;
use App\Models\Intranet\Marca;
use App\Models\Caja\CajaCuenta;
use App\Models\Caja\CajaCliente;
use App\Models\Intranet\Cliente;
use App\Models\Caja\CajaCategoria;
use App\Models\Caja\CajaTiposPago;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Caja\CajaTransaccion;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Caja\CajaDenominacion;
use App\Models\Caja\CajaTiposFactura;
use App\Exports\CajaTransaccionExport;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaTransaccion\PutRequest;
use App\Http\Requests\Caja\CajaTransaccion\StoreRequest;

class CajaTransaccionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $cajaTransaccion = CajaTransaccion::filter($filters)
            ->with('tipoFactura', 'cliente', 'user.empleado', 'pagos.sucursal', 'cuenta.cajaBanco', 'pagos.categoria', 'pagos.marca', 'tipoPago')
            ->paginate(10);

        return $this->respond($cajaTransaccion);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // Crear la transacción
            $cajaTransaccion = CajaTransaccion::create([
                'factura' => $data['factura'],
                'fecha_pago' => $data['fecha_pago'],
                'folio' => $data['folio'],
                'serie' => $data['serie'],
                'uuid' => $data['uuid'],
                'comentarios' => $data['comentarios'],
                'validado' => $data['validado'],
                'iva' => $data['iva'],
                'cliente_id' => $data['cliente_id'],
                'user_id' => $data['user_id'],
                'tipo_factura_id' => $data['tipo_factura_id'],
                'cuenta_id' => $data['cuenta_id'],
                'tipo_pago_id' => $data['tipo_pago_id'],
            ]);

            // Crear los pagos asociados
            foreach ($data['pagos'] as $pago) {
                CajaPago::create([
                    'transaccion_id' => $cajaTransaccion->id,
                    'monto' => $pago['monto'],
                    'descripcion' => $pago['descripcion'],
                    'serie' => $pago['serie'],
                    'marca_id' => $pago['marca_id'],
                    'sucursal_id' => $pago['sucursal_id'],
                    'categoria_id' => $pago['categoria_id'],
                ]);
            }

            DB::commit();

            return $this->respondCreated($cajaTransaccion);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaTransaccion $cajaTransaccion)
    {
        return $this->respond($cajaTransaccion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaTransaccion $cajaTransaccion)
    {
        DB::transaction(function () use ($request, $cajaTransaccion) {
            // Actualiza la transacción principal
            $cajaTransaccion->update($request->validated());

            // Procesa los pagos
            foreach ($request->input('pagos') as $pagoData) {
                if (!empty($pagoData['id'])) {
                    // Si tiene ID, actualizar
                    $pago = CajaPago::find($pagoData['id']);
                    if ($pago) {
                        $pago->update($pagoData);
                    }
                } else {
                    // Si no tiene ID, crear nuevo y asociarlo
                    $cajaTransaccion->pagos()->create($pagoData);
                }
            }
        });

        return $this->respond($cajaTransaccion->fresh('pagos'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaTransaccion $cajaTransaccion)
    {
        $cajaTransaccion->delete();
        return $this->respondSuccess();
    }

    public function getPerDay()
    {
        $user = auth()->user();
        // $sucursalId = $user->empleado->sucursal_id;
        $data = CajaTransaccion::whereDate('created_at', now())
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->with('tipoFactura', 'cliente', 'user.empleado', 'pagos.sucursal', 'cuenta.cajaBanco', 'pagos.categoria', 'tipoPago','pagos.marca')
            ->get();

        return $this->respond($data);
    }

    public function getforms()
    {
        $data = [
            'sucursales' => Sucursal::all(),
            'categorias' => CajaCategoria::all(),
            'cuentas' => CajaCuenta::with('cajaBanco', 'sucursal', 'categoria')->get(),
            'tipos_pago' => CajaTiposPago::all(),
            'tipos_factura' => CajaTiposFactura::all(),
            'clientes' => CajaCliente::all(),
            'users' => User::has('cajaTransaccion')->get(),
            'marcas' => Marca::all()
        ];
        return $this->respond($data);
    }

    public function getReportPerDay($fecha)
    {
        $fecha = $fecha ? Carbon::parse($fecha)->toDateString() : Carbon::today()->toDateString();

        $data = CajaTiposPago::with(['transaccion' => function ($query) use ($fecha) {
            $query->whereDate('created_at', $fecha)->with('pagos.sucursal', 'pagos.categoria', 'cuenta.cajaBanco','pagos.marca');
        }])->get();

        // Calcular el total por tipo de pago, protegiendo en caso de que 'transaccion' sea null
        $data = $data->map(function ($item) {
            $item->total = $item->transaccion->sum(fn($trans) => $trans->total->total_con_iva ?? 0);
            return $item;
        })->filter(fn($item) => $item->total > 0)->values();


        $denominaciones = CajaDenominacion::all();

        // Obtener el corte de caja con esa fecha
        $cajaCorte = CajaCorte::whereDate('fecha_corte', $fecha)->with('detalleEfectivo.denominacion')->first();

        $res = [
            'data' => $data,
            'denominaciones' => $denominaciones,
            'corte' => $cajaCorte
        ];

        return $this->respond($res);
    }

    public function getReportExcelPerDay($fecha)
    {
        $user = Auth::user();
        $emailLimpio = str_replace(['@', '.'], '_', $user->email); // Para evitar caracteres inválidos
        $nombreArchivo = 'reporte_caja_' . $fecha . '_' . $emailLimpio . '.xlsx';

        $export = new CajaTransaccionExport($fecha, $user->id);

        $data = $export->collection();

        // Verificar si no hay datos para exportar
        if ($data->isEmpty()) {
            return response()->json(['error' => 'No hay datos para exportar.']);
        }

        // Exportar el archivo en formato XLSX con los filtros aplicados
        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        // Convertir el contenido del archivo a Base64
        $base64 = base64_encode($fileContent);

        return response()->json([
            'file_name' => $nombreArchivo,
            'file_base64' => $base64,
        ]);
    }

    public function getReportPerDayCategory(Request $request)
    {
        // Validar que category_ids exista y no esté vacío
        $categoryIds = $request->input('category_ids', []);
        if (empty($categoryIds)) {
            return response()->json([
                'message' => 'Debe proporcionar al menos una categoría.'
            ], 422);
        }

        // Obtener la fecha o usar la de hoy
        $fecha = $request->input('fecha')
            ? Carbon::parse($request->input('fecha'))->toDateString()
            : Carbon::today()->toDateString();

        // Obtener categorías con sus pagos del día y relaciones necesarias
        $categorias = CajaCategoria::whereIn('id', $categoryIds)
            ->with(['pagos' => function ($query) use ($fecha) {
                $query->whereDate('created_at', $fecha)
                    ->with(['sucursal', 'transaccion.cliente']);
            }])
            ->get();

        // Calcular totales y filtrar categorías sin pagos
        $categorias = $categorias->map(function ($item) {
            $item->monto = optional($item->pagos)->sum('monto') ?? 0;
            return $item;
        })->filter(function ($item) {
            return $item->monto > 0;
        })->values();

        return $this->respond($categorias);
    }


    public function getReportPdfPerDayCategory(Request $request)
    {
        // Validar que category_ids exista y no esté vacío
        $categoryIds = $request->input('category_ids', []);
        if (empty($categoryIds)) {
            return response()->json([
                'message' => 'Debe proporcionar al menos una categoría.'
            ], 422);
        }

        // Obtener la fecha o usar la de hoy
        $fecha = $request->input('fecha')
            ? Carbon::parse($request->input('fecha'))->toDateString()
            : Carbon::today()->toDateString();

        // Obtener categorías con sus pagos del día y relaciones necesarias
        $categorias = CajaCategoria::whereIn('id', $categoryIds)
            ->with(['pagos' => function ($query) use ($fecha) {
                $query->whereDate('created_at', $fecha)
                    ->with(['sucursal', 'transaccion.cliente']);
            }])
            ->get();

        // Calcular totales y filtrar categorías sin pagos
        $categorias = $categorias->map(function ($item) {
            $item->monto = optional($item->pagos)->sum('monto') ?? 0;
            return $item;
        })->filter(function ($item) {
            return $item->monto > 0;
        })->values();

        // Cargar la vista y pasarle los datos
        $pdf = PDF::loadView('pdf.caja.reportPerDay', ['data' => $categorias]);

        // // Descargar el PDF generado comentar para produccion se utiliza para postman
        // return $pdf->download('document.pdf');

        // Obtener el contenido del PDF como cadena binaria
        $pdfContent = $pdf->output();

        // Convertir el contenido a Base64
        $pdfBase64 = base64_encode($pdfContent);

        // Retornar el PDF en Base64
        return $this->respond($pdfBase64);
    }

    public function changeStatus(CajaTransaccion $cajaTransaccion)
    {
        if ($cajaTransaccion->validado == 1) {
            $cajaTransaccion->validado = 0;
        } elseif ($cajaTransaccion->validado == 0) {
            $cajaTransaccion->validado = 1;
        }

        $cajaTransaccion->save();

        return $this->respondSuccess();
    }
}
