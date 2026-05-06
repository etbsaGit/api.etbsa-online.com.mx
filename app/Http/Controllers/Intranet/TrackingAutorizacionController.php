<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Tracking;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

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
}
