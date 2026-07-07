<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Tracking\TrackingAsignacionSerieRequest;
use App\Http\Requests\Intranet\Tracking\TrackingFeedbackRequest;
use App\Mail\MailToAsignacionSerie;
use App\Mail\MailToCreditoCobranza;
use App\Mail\SendAsignacionSerie;
use App\Mail\SendAutorizacionDecision;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\InvItem;
use App\Models\Intranet\Tracking;
use App\Models\Intranet\TrackingAsignacionSerie;
use App\Models\Intranet\TrackingFeedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

use function PHPUnit\Framework\isEmpty;

class TrackingAutorizacionController extends ApiController
{
    public function index(Request $request, $situacion, $situacion2 = null)
    {
        $filters = $request->all();
        $user = Auth::user();

        $situacionId = Estatus::where('nombre', $situacion)
            ->where('tipo_estatus', 'tracking-situacion')
            ->value('id');

        $situacion2Id = null;
        if ($situacion2) {
            $situacion2Id = Estatus::where('nombre', $situacion2)
                ->where('tipo_estatus', 'tractor-estatus')
                ->value('id');
        }

        $situaciones = array_filter([
            $situacionId,
            $situacion2Id
        ]);

        // si es admin ve todas las cotizaciones, si no sólo las que se les notificó
        $trackings = Tracking::
        query()
            ->when(!$user->hasRole('Admin') && $situacion == "Formalizado", function ($query) use ($user) {
                $query->whereHas('notificado', function ($q) use ($user) {
                    $q->where('id', $user->empleado->id);
                });
            })
            ->
            with([
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
                'notificado',
                'asignacion.invItem.invModel',
                'asignacion.invItem.sucursal',
                'asignacion.empleado',
                'historial' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                },
                'historial.situacion',
                'historial.empleado'
            ])
            ->whereIn('situacion_id', $situaciones)
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->respond(
            $trackings,
            'Lista de seguimientos cargada correctamente'
        );
    }

    public function autorizarPedido(TrackingFeedbackRequest $request, $trackingId, $situacion)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $empleadoId = $user->empleado?->id;

            $situacionId = Estatus::where('nombre', $situacion)
                ->where('tipo_estatus', 'tracking-situacion')
                ->firstOrFail()
                ->id;

            $data = $request->validated();

            $data['tracking_id'] = $trackingId;
            $data['situacion_id'] = $situacionId;
            if ($empleadoId) {
                $data['empleado_id'] = $empleadoId;
            }

            // feedback informativo
            $feedback = TrackingFeedback::create([
                'tracking_id' => $trackingId,
                'empleado_id' => $empleadoId,
                'situacion_id' => $situacionId,
                'comentario' =>
                'Se ha actualizado la situación del pedido a ' . $situacion .
                    '. Notas: ' . ($data['comentario'] ?? 'N/A')
            ]);

            // feedback descriptivo
            // $feedback = TrackingFeedback::create($data);

            $tracking = Tracking::findOrFail($trackingId);

            $tracking->update([
                'situacion_id' => $situacionId
            ]);


            DB::commit();

            if ($situacion === 'Para Asignar') {
                $this->mailToAsignacionSerie($trackingId);
            }

            $this->sendAutorizacionDecision($trackingId);

            return $this->respondCreated($feedback->load(['tracking']), 'Pedido Actualizado Correctamente');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendAutorizacionDecision($trackingId)
    {
        try {
            $tracking = Tracking::findOrFail($trackingId);

            $tracking->load(
                'cliente',
                'notificado',
                'historial.empleado',
            );

            $pdf = Pdf::loadView('pdf.tracking.tracking_quote', [
                'quote' => $tracking
            ]);

            $pdfContent = $pdf->output();

            // $solicitante = $tracking->vendedor;
            // $notificado = $tracking->notificar_a;

            $correo_pruebas = 'munozchristian@etbsa.com.mx';

            $correos = [
                // 'notificado' => $notificado->correo_institucional,
                // 'solicitante' => $solicitante->correo_institucional,
                $correo_pruebas
            ];

            foreach ($correos as $to_email) {
                if ($to_email) {
                    Mail::to($to_email)->send(
                        new SendAutorizacionDecision($tracking, $pdfContent)
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al mandar correo de Solicitud de Asignación de Serie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function mailToAsignacionSerie($trackingId)
    {
        try {
            $tracking = Tracking::findOrFail($trackingId);

            $tracking->load(
                'cliente',
                'notificado',
                'historial',
            );

            $pdf = Pdf::loadView('pdf.tracking.tracking_quote', [
                'quote' => $tracking
            ]);

            $pdfContent = $pdf->output();

            $correos = Empleado::whereHas('user.roles', function ($query) {
                $query->where('name', 'crm.asignacion_serie');
            })
                ->pluck('correo_institucional')
                ->filter()
                ->unique();

            foreach ($correos as $to_email) {
                if ($to_email) {
                    Mail::to($to_email)->send(
                        new MailToAsignacionSerie($tracking, $pdfContent)
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al asignar mandar correo de Solicitud de Asignación de Serie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function asignacionSerie(TrackingAsignacionSerieRequest $request, $trackingId)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $empleadoId = $user->empleado?->id;

            $data = $request->validated();

            $tracking = Tracking::findOrFail($trackingId);

            // ESTATUS TRACKING
            $situacionId = Estatus::where('nombre', 'Tractor Asignado')
                ->where('clave', 'tracking')
                ->value('id');

            // ESTATUS INVENTARIO
            $estatusInventarioId = Estatus::where('nombre', 'En Inventario')
                ->where('clave', 'tractor')
                ->where('tipo_estatus', 'tractor-estatus')
                ->value('id');

            $estatusAsignadoId = Estatus::where('nombre', 'Asignado')
                ->where('clave', 'tractor')
                ->where('tipo_estatus', 'tractor-estatus')
                ->value('id');

            // buscar asignación actual
            $asignacionActual = TrackingAsignacionSerie::where(
                'tracking_id',
                $trackingId
            )->first();

            // SI EXISTE Y CAMBIÓ EL TRACTOR
            if (
                $asignacionActual &&
                $asignacionActual->inv_item_id != $data['inv_item_id']
            ) {

                // regresar tractor anterior a inventario
                InvItem::where(
                    'id',
                    $asignacionActual->inv_item_id
                )->update([
                    'shipping_status' => $estatusInventarioId
                ]);
            }

            // actualizar nuevo tractor a asignado
            InvItem::where(
                'id',
                $data['inv_item_id']
            )->update([
                'shipping_status' => $estatusAsignadoId
            ]);

            // update/create asignación
            $asignacion = TrackingAsignacionSerie::updateOrCreate(
                [
                    'tracking_id' => $trackingId
                ],
                [
                    'inv_item_id' => $data['inv_item_id'],
                    'comentarios' => $data['comentarios'] ?? null,
                    'asignado_por' => $empleadoId
                ]
            );

            // actualizar tracking
            $tracking->update([
                'situacion_id' => $situacionId
            ]);

            // feedback
            TrackingFeedback::create([
                'tracking_id' => $trackingId,
                'empleado_id' => $empleadoId,
                'situacion_id' => $situacionId,
                'comentario' =>
                'Se asignó/reasignó tractor' .
                    ' al seguimiento. Comentarios adicionales: ' .
                    ($data['comentarios'] ?? 'N/A')
            ]);

            DB::commit();

            $this->sendAsignacionSerie($trackingId);

            return $this->respondCreated(
                $tracking->fresh(),
                'Número de serie asignado correctamente'
            );
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error al asignar número de serie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendAsignacionSerie($trackingId)
    {
        try {
            $tracking = Tracking::findOrFail($trackingId);

            $tracking->load(
                'cliente',
                'notificado',
                'historial',
                'asignacion.invItem.invModel',
                'asignacion.invItem.sucursal',
                'asignacion.empleado',
            );

            $pdf = Pdf::loadView('pdf.tracking.tracking_quote', [
                'quote' => $tracking
            ]);

            $pdfContent = $pdf->output();

            // $solicitante = $tracking->vendedor;
            // $notificado = $tracking->notificar_a;

            $correo_pruebas = 'munozchristian@etbsa.com.mx';

            $correos = [
                // 'notificado' => $notificado->correo_institucional,
                // 'solicitante' => $solicitante->correo_institucional,
                $correo_pruebas
            ];

            foreach ($correos as $to_email) {
                if ($to_email) {
                    Mail::to($to_email)->send(
                        new SendAsignacionSerie($tracking, $pdfContent)
                    );
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al mandar correo de Asignación de Serie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function mailToCreditoCobranza($trackingId)
    {
        try {
            $tracking = Tracking::findOrFail($trackingId);

            $tracking->load(
                'cliente',
                'notificado',
                'historial',
                'asignacion.invItem.invModel',
                'asignacion.invItem.sucursal',
                'asignacion.empleado',
            );

            $pdf = Pdf::loadView('pdf.tracking.tracking_quote', [
                'quote' => $tracking
            ]);

            $pdfContent = $pdf->output();

            $correos = Empleado::whereHas('user.roles', function ($query) {
                $query->where('name', 'crm.credito');
            })
                ->pluck('correo_institucional')
                ->filter()
                ->unique();

            foreach ($correos as $to_email) {
                if ($to_email) {
                    Mail::to($to_email)->send(
                        new MailToCreditoCobranza($tracking, $pdfContent)
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al mandar correo de Asignación de Serie',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
