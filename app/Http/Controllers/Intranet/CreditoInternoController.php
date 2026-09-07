<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\CreditoInterno\AutorizarCreditoInternoRequest;
use App\Http\Requests\Intranet\CreditoInterno\CreditoInternoRequest;
use App\Http\Requests\Intranet\Products\TractorContrapesoRequest;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\CreditoInterno\CreditoDocs;
use App\Models\Intranet\CreditoInterno\CreditoHistorialPagos;
use App\Models\Intranet\CreditoInterno\CreditoHistorical;
use App\Models\Intranet\CreditoInterno\CreditoLineas;
use App\Models\Intranet\CreditoInterno\CreditoSolicitud;
use App\Models\Puesto;
use App\Models\Sucursal;
use App\Traits\UploadableFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditoInternoController extends ApiController
{
    use UploadableFile;
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();
        // si es admin o director administrativo puede ver todas las cotizaciones, si no sólo las que se le notificó al usuario
        $creditoSolicitudes = CreditoSolicitud::query()
            ->when(!$user->hasRole('Admin') && !$user->hasRole('Credito') && $user->empleado->puesto_id !== Puesto::where('nombre', 'Director Administrativo')->first()->id, function ($query) use ($user) {
                $query->whereHas('notificado', function ($q) use ($user) {
                    $q->where('id', $user->empleado->id);
                });
            })->with([
                'cliente',
                'asesor',
                'notificado',
                'estatus',
                'validadoPor',
                'linea',
                'pagos.estatus',
                'historial.estatus',
                'sucursal',
                'pagos',
                'documentacion'
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

            $estatusPagoPendiente = Estatus::where('nombre', 'Pago Pendiente')->where('tipo_estatus', 'credito-interno')->first();

            // Guardar cada pago en la tabla credito_historial_pagos
            if (!empty($request->pagos)) {
                foreach ($request->pagos as $index => $pago) {
                    // $esPrimerPago = ($index === 0) || (($pago['numero'] ?? null) == 1);

                    CreditoHistorialPagos::create([
                        'solicitud_id'    => $creditoSolicitud->id,
                        'n_pago'          => $pago['numero'] ?? ($index + 1),
                        'saldo_pendiente' => $data['monto_solicitado'],
                        'fecha_a_pagar'   => $pago['fecha'],
                        'estatus_id'      => $estatusPagoPendiente->id,
                    ]);
                }
            }

            if (!empty($request->archivos)) {
                $this->guardarArchivosSolicitud($creditoSolicitud, $request->archivos);
            }
            $user = Auth::user();
            $empleadoId = $user->empleado?->id;
            CreditoHistorical::create([
                'solicitud_id' => $creditoSolicitud->id,
                'estatus_id' => $estatusId->id,
                'descripcion' => "Solicitud de crédito creada. Monto: " . ($request->monto_solicitado ?? 'N/A') . ' Motivo: ' . ($request->motivo ?? 'N/A') . ' Notas adicionales: ' . ($request->notas ?? 'N/A'),
                'empleado_id' => $empleadoId
            ]);

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
        $estatuses = ['Crédito Solicitado', 'Crédito Aprobado', 'Crédito Rechazado', 'Crédito en Proceso', 'Crédito Pagado'];
        $data = [
            'creditoLineas' => CreditoLineas::all(),
            'gerentes' => Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente Territorial')->first()->id)->with('sucursal')->where('estatus_id', Estatus::where('nombre', 'Activo')->where('tipo_estatus', 'empleado')->first()->id)->get(),
            'estatuses' => Estatus::whereIn('nombre', $estatuses)->where('tipo_estatus', 'credito-interno')->get(),
            'sucursales' => Sucursal::all(),
            'empleados' => Empleado::where('estatus_id', Estatus::where('nombre', 'Activo')->first()->id)->get(),
        ];

        return $this->respond($data, 'Opciones cargadas correctamente');
    }

    public function guardarArchivosSolicitud(CreditoSolicitud $solicitud, array $archivos)
    {
        $user = Auth::user();
        $empleadoId = $user->empleado?->id;
        $folder = "intranet/credito_interno/folio_" . ($solicitud->folio ?? $solicitud->id);
        $guardados = [];
        foreach ($archivos as $archivo) {
            if (!empty($archivo['base64'])) {
                // Guarda el archivo en S3 usando el Trait UploadableFile
                $relativePath = $this->saveDoc($archivo['base64'], $folder);
                $guardados[] = CreditoDocs::create([
                    'solicitud_id' => $solicitud->id,
                    'archivo'      => $archivo['tipo'] ?? 'Documento',
                    'path'         => $relativePath,
                    'extension'    => $archivo['extension'] ?? pathinfo($relativePath, PATHINFO_EXTENSION),
                    'uploaded_by'  => $empleadoId,
                ]);

                CreditoHistorical::create([
                    'solicitud_id' => $solicitud->id,
                    'estatus_id' => Estatus::where('nombre', 'Documento Adjuntado')->where('tipo_estatus', 'credito-interno')->first()->id,
                    'descripcion' => "Documento: " . ($archivo['tipo'] ?? 'Documento') . ' adjuntado.',
                    'empleado_id' => $empleadoId
                ]);
            }
        }
        return $guardados;
    }
}
