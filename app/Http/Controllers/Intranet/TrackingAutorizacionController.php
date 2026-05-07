<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Tracking\TrackingFeedbackRequest;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Tracking;
use App\Models\Intranet\TrackingFeedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

use function PHPUnit\Framework\isEmpty;

class TrackingAutorizacionController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();

        $situacionId = Estatus::where('nombre', 'Formalizado')
            ->where('tipo_estatus', 'tracking-situacion')
            ->value('id');

        // si es admin ve todas las cotizaciones, si no sólo las que se les notificó
        $trackings = Tracking::query()->when(!$user->hasRole('Admin'), function ($query) use ($user) {
            $query->whereHas('notificado', function ($q) use ($user) {
                $q->where('id', $user->empleado->id);
            });
        })
            ->with([
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
                'notificado'
            ])->where('situacion_id', $situacionId)
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
        DB::beginTransaction();
        try {
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

            $feedback = TrackingFeedback::create($data);

            $tracking = Tracking::findOrFail($trackingId);

            $tracking->update([
                'situacion_id' => $situacionId
            ]);

            DB::commit();

            return $this->respondCreated($feedback->load(['tracking']), 'Pedido Actualizado Correctamente');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
