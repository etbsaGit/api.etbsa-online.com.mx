<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\CreditoInterno\AutorizarCreditoInternoRequest;
use App\Http\Requests\Intranet\CreditoInterno\CreditoInternoRequest;
use App\Http\Requests\Intranet\Products\TractorContrapesoRequest;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Contrapesos;
use App\Models\Intranet\CreditoInterno\CreditoHistorialPagos;
use App\Models\Intranet\CreditoInterno\CreditoLineas;
use App\Models\Intranet\CreditoInterno\CreditoSolicitud;
use App\Models\Intranet\Currency;
use App\Models\Intranet\Product;
use App\Models\Puesto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditoInternoController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();
        $creditoSolicitudes = CreditoSolicitud::with([
            'cliente',
            'asesor',
            'notificado',
            'estatus',
            'validadoPor',
            'linea'
        ])->filter($filters)->orderBy('created_at', 'desc')->paginate(10);
        return $this->respond(
            $creditoSolicitudes,
            'Lista de solicitudes de crédito cargada correctamente'
        );
    }

    public function store(CreditoInternoRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            $user = Auth::user();
            $empleadoId = $user->empleado?->id;

            if ($empleadoId) {
                $data['asesor_id'] = $empleadoId;
            }

            // crear folio
            $data['folio'] = str_pad(
                CreditoSolicitud::max('id') + 1,
                6,
                '0',
                STR_PAD_LEFT
            );


            $estatusId = Estatus::where('nombre', 'Crédito Solicitado')->where('tipo_estatus', 'credito-interno')->first();

            $data['estatus_id'] = $estatusId->id;

            $creditoSolicitud = CreditoSolicitud::create($data);

            // Calcular el saldo pendiente inicial restando el anticipo (si existe)
            $anticipo = isset($data['anticipo']) ? (float) $data['anticipo'] : 0;
            $saldoPendienteInicial = max(0, (float) $data['monto_solicitado'] - $anticipo);

            // Guardar cada pago en la tabla credito_historial_pagos
            if (!empty($request->pagos)) {
                foreach ($request->pagos as $index => $pago) {
                    $esPrimerPago = ($index === 0) || (($pago['numero'] ?? null) == 1);

                    CreditoHistorialPagos::create([
                        'solicitud_id'    => $creditoSolicitud->id,
                        'n_pago'          => $pago['numero'] ?? ($index + 1),
                        'saldo_pendiente' => $saldoPendienteInicial,
                        'fecha_a_pagar'   => $pago['fecha'],
                        'estatus_id'      => $estatusId->id,
                    ]);
                }
            }

            DB::commit();

            return $this->respondCreated(
                $creditoSolicitud,
                'Solicitud de crédito creada'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(CreditoSolicitud $credito_solicitud, CreditoInternoRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $credito_solicitud->update($data);
            DB::commit();

            return $this->respond($credito_solicitud, 'Solicitud de crédito actualizada correctamente');
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function autorizarCredito(AutorizarCreditoInternoRequest $request, int $solicitudId, bool $autorizado)
    {
        DB::beginTransaction();

        try {
            $credito_solicitud = CreditoSolicitud::find($solicitudId);

            if ($credito_solicitud == null) {
                return $this->respondNotFound('Solicitud de crédito no encontrada');
            }

            $user = Auth::user();
            $empleadoId = $user->empleado?->id;
            $aprobadoId = Estatus::where('nombre', 'Crédito Aprobado')->where('tipo_estatus', 'credito-interno')->first();
            $data = $request->validated();
            $data['validated_by'] = $empleadoId;
            if ($autorizado) {
                $data['monto_aprobado'] = $data['monto_aprobado'];
                $data['autorizado'] = true;
                $data['estatus_id'] = $aprobadoId->id;
                $credito_solicitud->update($data);
            } else {
                $rechazadoId = Estatus::where('nombre', 'Crédito Rechazado')->where('tipo_estatus', 'credito-interno')->first();
                $data['estatus_id'] = $rechazadoId->id;
                $credito_solicitud->update($data);
            }
            DB::commit();

            return $this->respond($credito_solicitud, 'Solicitud de crédito ' . ($autorizado ? 'autorizada' : 'rechazada') . ' correctamente');
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy()
    {
        DB::beginTransaction();
        try {
        } catch (\Throwable $e) {
            DB::rollBack();
        }
    }

    public function getOptions()
    {
        $data = [
            'creditoLineas' => CreditoLineas::all(),
            'gerentes' => Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente Territorial')->first()->id)->with('sucursal')->where('estatus_id', Estatus::where('nombre', 'Activo')->where('tipo_estatus', 'empleado')->first()->id)->get()
        ];

        return $this->respond($data, 'Opciones cargadas correctamente');
    }
}
