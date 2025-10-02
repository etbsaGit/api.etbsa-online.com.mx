<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Intranet\SaleController;
use App\Http\Controllers\Intranet\TownController;
use App\Http\Controllers\Intranet\FincaController;
use App\Http\Controllers\Intranet\MarcaController;
use App\Http\Controllers\Intranet\RiegoController;
use App\Http\Controllers\Intranet\GanadoController;
use App\Http\Controllers\Intranet\TacticController;
use App\Http\Controllers\Intranet\ClienteController;
use App\Http\Controllers\Intranet\CultivoController;
use App\Http\Controllers\Intranet\IngresoController;
use App\Http\Controllers\Intranet\KinshipController;
use App\Http\Controllers\Intranet\MachineController;
use App\Http\Controllers\Intranet\AnaliticaController;
use App\Http\Controllers\Intranet\CondicionController;
use App\Http\Controllers\Intranet\ClasEquipoController;
use App\Http\Controllers\Intranet\IngresoDocController;
use App\Http\Controllers\Intranet\ReferenciaController;
use App\Http\Controllers\Intranet\TipoEquipoController;
use App\Http\Controllers\Intranet\ClientesDocController;
use App\Http\Controllers\Intranet\StateEntityController;
use App\Http\Controllers\Intranet\TipoCultivoController;
use App\Http\Controllers\Intranet\ClienteRiegoController;
use App\Http\Controllers\Intranet\DistribucionController;
use App\Http\Controllers\Intranet\SegmentationController;
use App\Http\Controllers\Intranet\RepresentanteController;
use App\Http\Controllers\Intranet\AbastecimientoController;
use App\Http\Controllers\Intranet\ClienteCultivoController;
use App\Http\Controllers\Intranet\ClassificationsController;
use App\Http\Controllers\Intranet\NuevaTecnologiaController;
use App\Http\Controllers\Intranet\AgricolaInversionController;
use App\Http\Controllers\Intranet\ClienteTechnologyController;
use App\Http\Controllers\Intranet\GanaderaInversionController;
use App\Http\Controllers\Intranet\ReferenciaComercialController;
use App\Http\Controllers\Intranet\ClienteAbastecimientoController;
use App\Http\Controllers\Intranet\TechnologicalCapabilityController;
use App\Http\Controllers\Intranet\ConstructionClassificationsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'cors'])->group(function () {

    Route::get('town/state/{id}', [TownController::class, 'getPerState']);
    Route::get('cliente/options', [ClienteController::class, 'getOptions']);
    Route::get('referencia/cliente/{cliente}', [ReferenciaController::class, 'getPerCliente']);
    Route::put('referencia/{referencia}', [ReferenciaController::class, 'update']);
    Route::delete('referencia/{referencia}', [ReferenciaController::class, 'destroy']);
    Route::get('representante/cliente/{cliente}', [RepresentanteController::class, 'getPerCliente']);
    Route::get('machine/cliente/{cliente}', [MachineController::class, 'getPerCliente']);
    Route::get('machine/options', [MachineController::class, 'getOptions']);
    Route::get('clienteTechnology/cliente/{cliente}', [ClienteTechnologyController::class, 'getPerCliente']);
    Route::get('distribucion/cliente/{cliente}', [DistribucionController::class, 'getPerCliente']);
    Route::get('clienteCultivo/cliente/{cliente}', [ClienteCultivoController::class, 'getPerCliente']);
    Route::get('clienteCultivo/options', [ClienteCultivoController::class, 'getOptions']);
    Route::get('clienteRiego/cliente/{cliente}', [ClienteRiegoController::class, 'getPerCliente']);
    Route::get('clienteAbastecimiento/cliente/{cliente}', [ClienteAbastecimientoController::class, 'getPerCliente']);
    Route::post('clientes', [ClienteController::class, 'index']);
    Route::post('cliente/add/capTech/{cliente}', [ClienteController::class, 'addCapTech']);
    Route::get('cliente/get/capTech/{cliente}', [ClienteController::class, 'getCapTech']);
    Route::post('clientes/excel', [ClienteController::class, 'insetExcel']);
    Route::get('clientesDoc/cliente/{cliente}', [ClientesDocController::class, 'getPerCliente']);
    Route::post('sales', [SaleController::class, 'index']);
    Route::get('sale/options', [SaleController::class, 'getOptions']);
    Route::get('sale/validated', [SaleController::class, 'getForValidate']);
    Route::post('sale/post/validated', [SaleController::class, 'postValidate']);
    Route::get('referenciaComercial/cliente/{cliente}', [ReferenciaComercialController::class, 'getPerCliente']);
    Route::get('agricolaInversion/cliente/{cliente}/{year}', [AgricolaInversionController::class, 'getPerCliente']);
    Route::get('agricolaInversion/options', [AgricolaInversionController::class, 'getOptions']);
    Route::get('ganaderaInversion/cliente/{cliente}/{year}', [GanaderaInversionController::class, 'getPerCliente']);
    Route::get('ganaderaInversion/options', [GanaderaInversionController::class, 'getOptions']);
    Route::get('finca/cliente/{cliente}', [FincaController::class, 'getPerCliente']);
    Route::get('finca/options', [FincaController::class, 'getOptions']);
    Route::get('analitica/cliente/{cliente}', [AnaliticaController::class, 'getPerCliente']);
    Route::get('analitica/report/{analitica}', [AnaliticaController::class, 'getReport']);

    Route::get('ingreso/cliente/{cliente}', [IngresoController::class, 'getPerCliente']);


    Route::apiResource('construction-classification', ConstructionClassificationsController::class);
    Route::apiResource('tactic', TacticController::class);
    Route::apiResource('technological-capability', TechnologicalCapabilityController::class);
    Route::apiResource('segmentation', SegmentationController::class);
    Route::apiResource('classification', ClassificationsController::class);
    Route::apiResource('stateEntity', StateEntityController::class);
    Route::apiResource('town', TownController::class);
    Route::apiResource('kinship', KinshipController::class);
    Route::apiResource('abastecimiento', AbastecimientoController::class);
    Route::apiResource('condicion', CondicionController::class);
    Route::apiResource('cultivo', CultivoController::class);
    Route::apiResource('tipoCultivo', TipoCultivoController::class);
    Route::apiResource('marca', MarcaController::class);
    Route::apiResource('nuevaTecnologia', NuevaTecnologiaController::class);
    Route::apiResource('riego', RiegoController::class);
    Route::apiResource('tipoEquipo', TipoEquipoController::class);
    Route::apiResource('clasEquipo', ClasEquipoController::class);
    Route::apiResource('cliente', ClienteController::class);
    Route::apiResource('referencia', ReferenciaController::class);
    Route::apiResource('representante', RepresentanteController::class);
    Route::apiResource('machine', MachineController::class);
    Route::apiResource('clienteTechnology', ClienteTechnologyController::class);
    Route::apiResource('distribucion', DistribucionController::class);
    Route::apiResource('clienteCultivo', ClienteCultivoController::class);
    Route::apiResource('clienteRiego', ClienteRiegoController::class);
    Route::apiResource('clienteAbastecimiento', ClienteAbastecimientoController::class);
    Route::apiResource('clientesDoc', ClientesDocController::class);
    Route::apiResource('sale', SaleController::class);
    Route::apiResource('referenciaComercial', ReferenciaComercialController::class);
    Route::apiResource('agricolaInversion', AgricolaInversionController::class);
    Route::apiResource('ganado', GanadoController::class);
    Route::apiResource('ganaderaInversion', GanaderaInversionController::class);
    Route::apiResource('finca', FincaController::class);
    Route::apiResource('analitica', AnaliticaController::class);
    Route::apiResource('ingreso', IngresoController::class);
    Route::apiResource('ingresoDoc', IngresoDocController::class);

    Route::post('nt/clientes', [ClienteTechnologyController::class, 'getClientesNT']);
    Route::post('nt/clientes/xls', [ClienteTechnologyController::class, 'getClientesNTxls']);
});
