<?php

namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Festivo;
use App\Models\Empleado;
use App\Models\VacationDay;
use App\Models\VacationDiaCuenta;
use App\Models\VacationDiaCuentaBitacora;
use App\Services\VacationDiaCuentaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VacationDiaCuentaServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected VacationDiaCuentaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(VacationDiaCuentaService::class);
    }

    public function test_calcular_fecha_regreso_evita_domingos_y_festivos()
    {
        // 2026-12-12 es sábado. Domingo es 2026-12-13.
        // Si 2026-12-14 (lunes) fuera festivo, el regreso debería ser 2026-12-15 (martes).
        Festivo::firstOrCreate(['fecha' => '2026-12-14'], ['nombre' => 'Día de Prueba Festivo']);

        $service = new VacationDiaCuentaService();
        $fechaRegreso = $service->calcularFechaRegreso(Carbon::parse('2026-12-12'));

        $this->assertEquals('2026-12-15', $fechaRegreso);
    }

    public function test_saldo_insuficiente_no_se_registra()
    {
        $empleado = Empleado::where('estatus_id', 5)->first();
        $this->assertNotNull($empleado);

        $dia = VacationDiaCuenta::firstOrCreate([
            'fecha' => '2026-11-20',
        ], [
            'nombre' => 'Día de prueba saldo',
        ]);

        // Crear una solicitud ficticia que agote todo el subtotal disponible del empleado
        $a = $empleado->aniosVacaciones;
        $diasDisponibles = $a['subtotal'] ?? 0;

        if ($diasDisponibles > 0) {
            VacationDay::create([
                'empleado_id' => $empleado->id,
                'sucursal_id' => $empleado->sucursal_id,
                'puesto_id' => $empleado->puesto_id,
                'departamento_id' => $empleado->departamento_id,
                'periodo_correspondiente' => $empleado->vacationPeriod,
                'anios_cumplidos' => $a['cumplidos'],
                'dias_periodo' => $a['correspondientes'],
                'subtotal_dias' => $diasDisponibles,
                'dias_disfrute' => $diasDisponibles,
                'dias_pendientes' => 0,
                'fecha_inicio' => Carbon::parse($a['periodo']['inicio'])->toDateString(),
                'fecha_termino' => Carbon::parse($a['periodo']['inicio'])->toDateString(),
                'fecha_regreso' => Carbon::parse($a['periodo']['inicio'])->addDay()->toDateString(),
                'validated' => 1,
            ]);
        }

        // Ahora intentamos registrar el día a cuenta: debe ser rechazado por saldo insuficiente
        $motivo = null;
        $resultado = $this->service->registrarDia($empleado, $dia, false, $motivo);

        $this->assertNull($resultado);
        $this->assertStringContainsString('Saldo insuficiente', $motivo);
    }

    public function test_idempotencia_no_duplica_solicitudes()
    {
        $empleado = Empleado::where('estatus_id', 5)->first();
        $dia = VacationDiaCuenta::firstOrCreate([
            'fecha' => '2026-11-25',
        ], [
            'nombre' => 'Día de prueba idempotencia',
        ]);

        // Registrar la primera vez (si tiene saldo)
        $a = $empleado->aniosVacaciones;
        if (($a['subtotal'] ?? 0) >= 1) {
            $motivo = null;
            $res1 = $this->service->registrarDia($empleado, $dia, false, $motivo);
            $this->assertNotNull($res1);

            // Segunda vez con el mismo día: debe omitirse
            $res2 = $this->service->registrarDia($empleado, $dia, false, $motivo);
            $this->assertNull($res2);
            $this->assertStringContainsString('Ya tiene registrada', $motivo);
        } else {
            $this->markTestSkipped('Empleado sin saldo disponible para la prueba de idempotencia.');
        }
    }

    public function test_aplicar_por_aniversario_ejemplo_usuario()
    {
        // Encontrar empleado activo con suficiente antigüedad
        $empleado = Empleado::where('estatus_id', 5)
            ->whereYear('fecha_de_ingreso', '<', 2026)
            ->first();

        $this->assertNotNull($empleado);
        $fechaIngreso = Carbon::parse($empleado->fecha_de_ingreso);
        $fechaSimulada = Carbon::create(2026, $fechaIngreso->month, $fechaIngreso->day)->toDateString();

        // Días de prueba específicos: dos dentro del periodo y uno fuera (14 meses después)
        $dia1 = VacationDiaCuenta::create(['fecha' => Carbon::parse($fechaSimulada)->addMonths(2)->toDateString(), 'nombre' => 'Test Dia 1 Periodo']);
        $dia2 = VacationDiaCuenta::create(['fecha' => Carbon::parse($fechaSimulada)->addMonths(5)->toDateString(), 'nombre' => 'Test Dia 2 Periodo']);
        $dia3 = VacationDiaCuenta::create(['fecha' => Carbon::parse($fechaSimulada)->addMonths(14)->toDateString(), 'nombre' => 'Test Dia 3 Fuera']);

        // Ejecutar aplicación por aniversario para la fecha simulada
        $resumen = $this->service->aplicarPorAniversario($fechaSimulada, false, $empleado->id);

        $this->assertNotEmpty($resumen['detalles']);
        $detalle = $resumen['detalles'][0];

        // Verificar que se crearon los días 1 y 2 (dentro del periodo)
        $creadosIds = collect($detalle['creados'])->pluck('dia_cuenta_id')->toArray();
        $this->assertContains($dia1->id, $creadosIds);
        $this->assertContains($dia2->id, $creadosIds);

        // El día 3 está fuera del periodo
        $this->assertNotContains($dia3->id, $creadosIds);

        // Verificar en BD que las solicitudes creadas tengan los campos esperados
        $vacation1 = VacationDay::where('empleado_id', $empleado->id)
            ->where('vacation_dia_cuenta_id', $dia1->id)
            ->first();

        $this->assertNotNull($vacation1);
        $this->assertEquals(1, $vacation1->validated);
        $this->assertNull($vacation1->cubre);
        $this->assertStringContainsString('automático', $vacation1->comentarios);
    }

    public function test_fase_2_aplicar_y_retirar_dia()
    {
        $dia = VacationDiaCuenta::create([
            'nombre' => 'Día Festivo Especial Fase 2',
            'fecha' => '2026-11-18',
        ]);

        $stats = $this->service->aplicarDia($dia);
        $this->assertGreaterThan(0, $stats['evaluados']);

        $countGeneradas = VacationDay::where('vacation_dia_cuenta_id', $dia->id)->count();

        // Retirar el día: debe soft-deletear las que no han sido modificadas
        $eliminadas = $this->service->retirarDia($dia);
        $this->assertEquals($countGeneradas, $eliminadas);

        // En BD ya no deben estar activas (deleted_at no es null)
        $activas = VacationDay::where('vacation_dia_cuenta_id', $dia->id)->whereNull('deleted_at')->count();
        $this->assertEquals(0, $activas);
    }

    public function test_soft_delete_vacation_dia_cuenta()
    {
        $dia = VacationDiaCuenta::create([
            'nombre' => 'Día a Eliminar Soft Delete',
            'fecha' => '2026-11-25',
        ]);

        $id = $dia->id;
        $this->assertNull($dia->deleted_at);

        // Al eliminarlo, Eloquent debe asignar deleted_at (soft delete)
        $dia->delete();

        $this->assertTrue($dia->trashed());
        $this->assertNotNull($dia->deleted_at);

        // Las consultas normales (como las del frontend VacationDiaCuentaController::index) lo excluyen
        $this->assertNull(VacationDiaCuenta::find($id));

        // Pero el registro sigue existiendo en base de datos con withTrashed()
        $diaEnBD = VacationDiaCuenta::withTrashed()->find($id);
        $this->assertNotNull($diaEnBD);
        $this->assertNotNull($diaEnBD->deleted_at);
        $this->assertEquals('Día a Eliminar Soft Delete', $diaEnBD->nombre);
    }

    public function test_registro_en_bitacora()
    {
        $dia = VacationDiaCuenta::create([
            'nombre' => 'Día Test Bitácora',
            'fecha' => '2026-11-26',
        ]);

        // Aplicar día
        $this->service->aplicarDia($dia);

        // Verificar que existan registros de Info en la bitácora
        $infoInicio = VacationDiaCuentaBitacora::where('dia_cuenta_id', $dia->id)
            ->where('comentario', 'like', '%Se registró un día a cuenta%')
            ->first();
        $this->assertNotNull($infoInicio);
        $this->assertEquals(VacationDiaCuentaBitacora::getEstatusId(VacationDiaCuentaBitacora::ESTATUS_INFO), $infoInicio->estatus_id);

        $infoConteo = VacationDiaCuentaBitacora::where('dia_cuenta_id', $dia->id)
            ->where('comentario', 'like', '%Se les va a descontar el día a cuenta a%')
            ->first();
        $this->assertNotNull($infoConteo);

        // Probar aplicación por aniversario
        $resumen = $this->service->aplicarPorAniversario('2026-10-09', false);
        $infoAniv = VacationDiaCuentaBitacora::where('comentario', 'like', '%Se hace el proceso de buscar a los empleados con aniversario%')
            ->first();
        $this->assertNotNull($infoAniv);
    }
}
