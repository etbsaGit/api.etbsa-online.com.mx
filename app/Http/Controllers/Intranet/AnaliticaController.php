<?php

namespace App\Http\Controllers\Intranet;

use App\Models\User;
use App\Models\Estatus;
use Illuminate\Http\Request;
use App\Models\Intranet\Finca;
use App\Traits\UploadableFile;
use App\Models\Intranet\Egreso;
use App\Mail\NuevaAnaliticaMail;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Ingreso;
use App\Models\Intranet\Analitica;
use Illuminate\Support\Facades\Mail;
use App\Models\Intranet\AnaliticaDoc;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\AgricolaInversion;
use App\Models\Intranet\GanaderaInversion;
use App\Models\Intranet\InversionesAgricola;
use App\Models\Intranet\InversionesGanadera;
use App\Http\Requests\Intranet\Analitica\StoreRequest;

class AnaliticaController extends ApiController
{
    use UploadableFile;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Analitica::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $analitica = Analitica::create($request->validated());
        $docs = $request->base64;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $sa = AnaliticaDoc::create([
                    "name" => $doc['name'],
                    "analitica_id" => $analitica->id
                ]);
                $relativePath  = $this->saveDoc($doc['base64'], $sa->default_path_folder);
                $sa->update(['path' => $relativePath]);
            }
        }
        // 3️⃣ Enviar notificación por correo usando el método privado
        $this->sendAnaliticaNotification($analitica, 'creada');
        return $this->respondCreated($analitica);
    }

    /**
     * Display the specified resource.
     */
    public function show(Analitica $analitica)
    {
        return $this->respond($analitica);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Analitica $analitica)
    {
        $analitica->update($request->validated());
        $docs = $request->base64;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $sa = AnaliticaDoc::create([
                    "name" => $doc['name'],
                    "analitica_id" => $analitica->id,
                ]);
                $relativePath  = $this->saveDoc($doc['base64'], $sa->default_path_folder);
                $sa->update(['path' => $relativePath]);
            }
        }
        $this->sendAnaliticaNotification($analitica, 'actualizada');

        return $this->respond($analitica);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Analitica $analitica)
    {
        $analitica->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $analiticas = Analitica::where('cliente_id', $cliente->id)->with('cliente', 'analiticaDocs')->get();

        $data = [
            'analiticas' => $analiticas,
        ];

        return $this->respond($data);
    }

    public function getReport(Analitica $analitica)
    {
        $analitica->load($this->relationsToLoad());

        $cliente = $analitica->cliente;
        $anioAnalitica = \Carbon\Carbon::parse($analitica->fecha)->year;

        // -------------------------------------------------------------------------
        // 🔹 Calcular secciones
        // -------------------------------------------------------------------------
        $activosFijos = $this->calcularActivosFijos($cliente);
        $activosCirculantes = $this->calcularActivosCirculantes($analitica, $cliente, $anioAnalitica);
        $pasivos = $this->calcularPasivos($cliente, $anioAnalitica);
        $ingresos = $this->obtenerIngresosAnuales($cliente, $anioAnalitica);
        $ingresosDirectos = $this->obtenerIngresosDirectos($cliente, $anioAnalitica);
        $otrosGastos = $this->obtenerOtrosGastos($analitica);


        $totalGeneral = $activosFijos['totalActivosFijos']
            + $activosCirculantes['totalActivosCirculantes']
            - $pasivos['total_pasivos'];

        // -------------------------------------------------------------------------
        // 🔹 Respuesta final
        // -------------------------------------------------------------------------
        $data = [
            'cliente' => $cliente,
            'activos_fijos' => $activosFijos,
            'activos_circulantes' => $activosCirculantes,
            'pasivos' => $pasivos,
            'ingresos' => $ingresos, // ✅ agregado aquí
            'ingresosDirectos' => $ingresosDirectos,
            'otros_gastos' => $otrosGastos,
            'total_general' => $totalGeneral,
        ];

        return $this->respond($data);
    }

    # ============================================================
    # 🔹 MÉTODOS PRIVADOS
    # ============================================================

    /**
     * Relaciones que se deben cargar con el modelo Analitica.
     */
    private function relationsToLoad(): array
    {
        return [
            'cliente.stateEntity',
            'cliente.town',
            'cliente.referencia.kinship',
            'cliente.referenciaComercial',
            'cliente.clienteDoc.status',
            'cliente.machine.marca',
            'cliente.machine.condicion',
            'cliente.machine.clasEquipo',
            'cliente.machine.tipoEquipo',
            'cliente.fincas',
        ];
    }

    /**
     * Calcula los activos fijos (máquinas + fincas)
     */
    private function calcularActivosFijos($cliente): array
    {
        $machines = $cliente->machine;
        $fincas = $cliente->fincas->whereNotNull('valor')->values();

        $totalMachines = $machines->sum('valor');
        $totalFincas = $fincas->sum('valor');
        $totalActivosFijos = $totalMachines + $totalFincas;

        return [
            'machines' => [
                'items' => $machines,
                'totalMachines' => $totalMachines,
            ],
            'fincas' => [
                'items' => $fincas,
                'totalFincas' => $totalFincas,
            ],
            'totalActivosFijos' => $totalActivosFijos,
        ];
    }

    /**
     * Calcula los activos circulantes (base + inversiones agrícolas y ganaderas)
     */
    private function calcularActivosCirculantes($analitica, $cliente, int $anio): array
    {
        $base = [
            'efectivo'     => $analitica->efectivo ?? 0,
            'caja'         => $analitica->caja ?? 0,
            'documentospc' => $analitica->documentospc ?? 0,
            'mercancias'   => $analitica->mercancias ?? 0,
        ];

        $inversionesAgricolas = InversionesAgricola::with('cultivo')
            ->where('cliente_id', $cliente->id)
            ->where('year', $anio)
            ->get();

        $totalAgricolas = $inversionesAgricolas->sum(fn($item) => $item->total ?? 0);

        $inversionesGanaderas = InversionesGanadera::with('ganado')
            ->where('cliente_id', $cliente->id)
            ->where('year', $anio)
            ->get();

        $totalGanaderas = $inversionesGanaderas->sum(fn($item) => $item->total ?? 0);

        $totalInversiones = $totalAgricolas + $totalGanaderas;
        $totalActivosCirculantes = array_sum($base) + $totalInversiones;

        return [
            'base' => $base,
            'inversiones' => [
                'agricolas' => [
                    'items' => $inversionesAgricolas,
                    'totalAgricolas' => $totalAgricolas,
                ],
                'ganaderas' => [
                    'items' => $inversionesGanaderas,
                    'totalGanaderas' => $totalGanaderas,
                ],
                'totalInversiones' => $totalInversiones,
            ],
            'totalActivosCirculantes' => $totalActivosCirculantes,
        ];
    }

    /**
     * Calcula los pasivos (egresos de corto y largo plazo)
     */
    private function calcularPasivos($cliente, int $anio): array
    {
        $egresos = Egreso::where('cliente_id', $cliente->id)
            ->where('year', $anio)
            ->get();

        $pasivoCorto = 0;
        $pasivoLargo = 0;

        foreach ($egresos as $egreso) {
            $totalDeuda = $egreso->pago * $egreso->months;
            $corto = min($egreso->pago * $egreso->type, $totalDeuda);
            $largo = $totalDeuda - $corto;

            $pasivoCorto += $corto;
            $pasivoLargo += $largo;
        }

        return [
            'items' => $egresos,
            'corto_plazo' => $pasivoCorto,
            'largo_plazo' => $pasivoLargo,
            'total_pasivos' => $pasivoCorto + $pasivoLargo,
        ];
    }

    /**
     * Obtiene las inversiones agrícolas y ganaderas del cliente,
     * separadas por tipo y agrupadas por año (año anterior y año actual).
     */
    private function obtenerIngresosAnuales(Cliente $cliente, int $anioAnalitica): array
    {
        $anioAnterior = $anioAnalitica - 1;

        // ============================================================
        // 🔹 GANADERAS
        // ============================================================
        $ganaderasActual = GanaderaInversion::where('cliente_id', $cliente->id)
            ->where('year', $anioAnalitica)
            ->with('ganado')
            ->get();

        $ganaderasAnterior = GanaderaInversion::where('cliente_id', $cliente->id)
            ->where('year', $anioAnterior)
            ->with('ganado')
            ->get();

        $ganaderas = [
            $anioAnterior => [
                'items' => $ganaderasAnterior,
                'totales' => [
                    'total'    => $ganaderasAnterior->sum('total'),
                    'costo'    => $ganaderasAnterior->sum('costo'),
                    'precio'   => $ganaderasAnterior->sum('precio'),
                    'ingreso'  => $ganaderasAnterior->sum('ingreso'),
                    'utilidad' => $ganaderasAnterior->sum('utilidad'),
                ],
            ],
            $anioAnalitica => [
                'items' => $ganaderasActual,
                'totales' => [
                    'total'    => $ganaderasActual->sum('total'),
                    'costo'    => $ganaderasActual->sum('costo'),
                    'precio'   => $ganaderasActual->sum('precio'),
                    'ingreso'  => $ganaderasActual->sum('ingreso'),
                    'utilidad' => $ganaderasActual->sum('utilidad'),
                ],
            ],
        ];

        // ============================================================
        // 🔹 AGRÍCOLAS
        // ============================================================
        $agricolasActual = AgricolaInversion::where('cliente_id', $cliente->id)
            ->where('year', $anioAnalitica)
            ->with('cultivo')
            ->get();

        $agricolasAnterior = AgricolaInversion::where('cliente_id', $cliente->id)
            ->where('year', $anioAnterior)
            ->with('cultivo')
            ->get();

        $agricolas = [
            $anioAnterior => [
                'items' => $agricolasAnterior,
                'totales' => [
                    'total'    => $agricolasAnterior->sum('total'),
                    'costo'    => $agricolasAnterior->sum('costo'),
                    'precio'   => $agricolasAnterior->sum('precio'),
                    'ingreso'  => $agricolasAnterior->sum('ingreso'),
                    'utilidad' => $agricolasAnterior->sum('utilidad'),
                ],
            ],
            $anioAnalitica => [
                'items' => $agricolasActual,
                'totales' => [
                    'total'    => $agricolasActual->sum('total'),
                    'costo'    => $agricolasActual->sum('costo'),
                    'precio'   => $agricolasActual->sum('precio'),
                    'ingreso'  => $agricolasActual->sum('ingreso'),
                    'utilidad' => $agricolasActual->sum('utilidad'),
                ],
            ],
        ];

        // ============================================================
        // 🔹 RESULTADO FINAL AGRUPADO POR TIPO
        // ============================================================
        return [
            'ganaderas' => $ganaderas,
            'agricolas' => $agricolas,
        ];
    }

    /**
     * Obtiene los ingresos directos (modelo Ingresos) del cliente
     * correspondientes al año de la analítica
     */
    private function obtenerIngresosDirectos(Cliente $cliente, int $anioAnalitica): array
    {
        $ingresosDirectos = Ingreso::where('cliente_id', $cliente->id)
            ->where('year', $anioAnalitica)
            ->get();

        $totales = [
            'total'   => $ingresosDirectos->sum('total'),
            'neto'    => $ingresosDirectos->sum('neto'),
            'costos'  => $ingresosDirectos->sum('costos'),
        ];

        return [
            'anio' => $anioAnalitica,
            'items' => $ingresosDirectos,
            'totales' => $totales,
        ];
    }

    /**
     * Obtiene los otros gastos del cliente:
     * - Los costos de sus fincas (campo costo no nulo)
     * - El valor de gastos registrado en la analítica
     */
    private function obtenerOtrosGastos(Analitica $analitica): array
    {
        $cliente = $analitica->cliente;

        // 🔹 Fincas con costo
        $fincasConCosto = Finca::where('cliente_id', $cliente->id)
            ->whereNotNull('costo')
            ->get();

        $totalCostosFincas = $fincasConCosto->sum('costo');

        // 🔹 Gastos generales registrados en la analítica
        $gastosAnalitica = $analitica->gastos ?? 0;

        // 🔹 Estructura final
        return [
            'fincas' => [
                'items' => $fincasConCosto,
                'total_costos_fincas' => $totalCostosFincas,
            ],
            'analitica' => [
                'gastos' => $gastosAnalitica,
            ],
            'total_otros_gastos' => $totalCostosFincas + $gastosAnalitica,
        ];
    }

    private function sendAnaliticaNotification(Analitica $analitica, string $accion = 'creada')
    {
        // 🟢 1. Correo del empleado asignado
        $correoEmpleado = optional($analitica->empleado)->correo_institucional;

        // 🟢 2. Correos de todos los usuarios con rol "Credito" usando hasRole
        $correosCredito = User::all()
            ->filter(fn($user) => $user->hasRole('Credito'))
            ->pluck('email')
            ->filter() // elimina nulos
            ->toArray();

        // 🟢 3. Combinar destinatarios
        $destinatarios = collect([$correoEmpleado])
            ->merge($correosCredito)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // 🟢 4. Enviar correo individualmente
        foreach ($destinatarios as $correo) {
            Mail::to($correo)->send(
                new NuevaAnaliticaMail(
                    $analitica,
                    $analitica->cliente,
                    $analitica->empleado,
                    $accion
                )
            );
        }
    }
}
