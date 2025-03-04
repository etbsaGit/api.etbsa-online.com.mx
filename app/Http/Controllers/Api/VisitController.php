<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Visit;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Models\Intranet\Cultivo;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Visit\StoreRequest;
use App\Models\Prospect;
use App\Models\Sucursal;
use PDF;

class VisitController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();
        $sc = Sucursal::where('nombre', 'Corporativo')->first(); // Cambiar get() por first()

        if ($user->hasRole('Admin') || ($sc && $user->empleado->sucursal_id == $sc->id)) {
            $visits = Visit::filter($filters)
                ->with(['empleado', 'prospect'])
                ->orderBy('dia', 'desc')
                ->paginate(10);
        } else {
            $visits = Visit::filter($filters)
                ->where('empleado_id', $user->empleado->id)
                ->with(['empleado', 'prospect'])
                ->orderBy('dia', 'desc')
                ->paginate(10);
        }

        return $this->respond($visits);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $visit = Visit::create($request->validated());

        return $this->respondCreated($visit);
    }

    /**
     * Display the specified resource.
     */
    public function show(Visit $visit)
    {
        return $this->respond($visit->load('empleado', 'prospect'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Visit $visit)
    {
        $visit->update($request->validated());

        return $this->respond($visit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Visit $visit)
    {
        $visit->delete();

        return $this->respondSuccess();
    }

    public function getforms()
    {
        $user = Auth::user();
        $sc = Sucursal::where('nombre', 'Corporativo')->first(); // Cambiar get() por first()

        if ($user->hasRole('Admin') || ($sc && $user->empleado->sucursal_id == $sc->id)) {
            $prospects = Prospect::with(['empleado'])->get();
        } else {
            $prospects = Prospect::where('empleado_id', $user->empleado->id)->with(['empleado'])->get();
        }

        $data = [
            'empleados' => Empleado::where('estatus_id', 5)->orderBy('apellido_paterno')->get(),
            'prospects' => $prospects,
        ];

        return $this->respond($data);
    }

    public function getVisitCalendar($date)
    {
        // Convierte la fecha proporcionada en un objeto Carbon para manejar el mes y año
        $parsedDate = Carbon::parse($date);
        $month = $parsedDate->month;
        $year = $parsedDate->year;
        $user = Auth::user();
        $sc = Sucursal::where('nombre', 'Corporativo')->first();

        if ($user->hasRole('Admin') || ($sc && $user->empleado->sucursal_id == $sc->id)) {
            // Consulta con Eloquent para filtrar los registros e incluir la relación
            $visits = Visit::with('empleado', 'prospect') // Incluye la relación 'empleado'
                ->where(function ($query) use ($month, $year) {
                    $query->where(function ($subQuery) use ($month, $year) {
                        $subQuery->whereMonth('dia', $month)
                            ->whereYear('dia', $year);
                    });
                })
                ->get();
        } else {
            // Consulta con Eloquent para filtrar los registros e incluir la relación
            $visits = Visit::with('empleado', 'prospect') // Incluye la relación 'empleado'
                ->where('empleado_id', $user->empleado->id)
                ->where(function ($query) use ($month, $year) {
                    $query->where(function ($subQuery) use ($month, $year) {
                        $subQuery->whereMonth('dia', $month)
                            ->whereYear('dia', $year);
                    });
                })
                ->get();
        }

        return $this->respond($visits);
    }

    public function getEmployeesWithVisits(Request $request)
    {
        // Validar que el año sea obligatorio
        $request->validate([
            'year' => 'required|integer'
        ]);

        $year = $request->year;

        // Obtener empleados que tengan al menos una visita en el año dado
        $employees = Empleado::whereHas('visits', function ($query) use ($year) {
            $query->whereYear('dia', $year);
        })->with(['visits' => function ($query) use ($year) {
            $query->whereYear('dia', $year)->select('id', 'dia', 'ubicacion', 'comentarios', 'retroalimentacion', 'prospect_id', 'empleado_id');
        }])->get();

        return response()->json($employees);
    }

    public function getFormReport()
    {
        $empleados = Empleado::whereHas('visits')->get();

        return $this->respond($empleados);
    }

    public function getReport(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $empleadoId = $request->empleado_id;

        $empleado = Empleado::with(['sucursal', 'prospects' => function ($query) use ($year, $month, $empleadoId) {
            $query->whereHas('visits', function ($visitQuery) use ($year, $month, $empleadoId) {
                $visitQuery->whereYear('dia', $year)
                    ->whereMonth('dia', $month)
                    ->where('empleado_id', $empleadoId);
            })->with([
                'visits' => function ($visitQuery) use ($year, $month, $empleadoId) {
                    $visitQuery->whereYear('dia', $year)
                        ->whereMonth('dia', $month)
                        ->where('empleado_id', $empleadoId);
                },
                'prospectCultivo.cultivo',
                'prospectCultivo.tipoCultivo',

                'prospectRiego.riego',

                'prospectDistribucion',

                'prospectMaquina.marca',
                'prospectMaquina.condicion',
                'prospectMaquina.clasEquipo',
                'prospectMaquina.tipoEquipo',

                'prospectAgp',
                'prospectServicio'
            ]);
        }])->find($empleadoId);

        if (!$empleado) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        return $this->respond($empleado);
    }

    public function getReportPdf(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $empleadoId = $request->empleado_id;

        $empleado = Empleado::with(['sucursal', 'prospects' => function ($query) use ($year, $month, $empleadoId) {
            $query->whereHas('visits', function ($visitQuery) use ($year, $month, $empleadoId) {
                $visitQuery->whereYear('dia', $year)
                    ->whereMonth('dia', $month)
                    ->where('empleado_id', $empleadoId);
            })->with([
                'visits' => function ($visitQuery) use ($year, $month, $empleadoId) {
                    $visitQuery->whereYear('dia', $year)
                        ->whereMonth('dia', $month)
                        ->where('empleado_id', $empleadoId);
                },
                'prospectCultivo.cultivo',
                'prospectCultivo.tipoCultivo',
                'prospectRiego.riego',
                'prospectDistribucion',
                'prospectMaquina.marca',
                'prospectMaquina.condicion',
                'prospectMaquina.clasEquipo',
                'prospectMaquina.tipoEquipo',
                'prospectAgp',
                'prospectServicio'
            ]);
        }])->find($empleadoId);

        if (!$empleado) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        // Formatear los números de teléfono de los prospectos
        foreach ($empleado->prospects as $prospect) {
            $prospect->telefono = $this->formatPhoneNumber($prospect->telefono);
        }

        // Generar PDF en horizontal
        $pdf = Pdf::loadView('pdf.visit.reportVisitEmployee', ['empleado' => $empleado])
            ->setPaper('a4', 'landscape'); // 'a4' es el tamaño y 'landscape' lo pone en horizontal

        // Descargar el PDF
        // return $pdf->download('reporte_empleado_' . $empleado->id . '.pdf');

        // Obtener el contenido del PDF como cadena binaria
        $pdfContent = $pdf->output();

        // Convertir el contenido a Base64
        $pdfBase64 = base64_encode($pdfContent);

        // Retornar el PDF en Base64
        return $this->respond($pdfBase64);
    }

    public function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/\D/', '', $phone); // Eliminar caracteres no numéricos
        if (strlen($phone) === 10) {
            return "(" . substr($phone, 0, 3) . ") " . substr($phone, 3, 3) . "-" . substr($phone, 6, 2) . "-" . substr($phone, 8, 2);
        }
        return $phone; // Si no tiene 10 dígitos, devolver tal cual
    }
}
