<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Intranet\SaleController;
use App\Http\Controllers\Intranet\SaleTractorController;
use App\Http\Controllers\Intranet\TownController;
use App\Http\Controllers\Intranet\FincaController;
use App\Http\Controllers\Intranet\MarcaController;
use App\Http\Controllers\Intranet\RiegoController;
use App\Http\Controllers\Intranet\EgresoController;
use App\Http\Controllers\Intranet\GanadoController;
use App\Http\Controllers\Intranet\TacticController;
use App\Http\Controllers\Intranet\ClienteController;
use App\Http\Controllers\Intranet\CultivoController;
use App\Http\Controllers\Intranet\IngresoController;
use App\Http\Controllers\Intranet\InvItemController;
use App\Http\Controllers\Intranet\KinshipController;
use App\Http\Controllers\Intranet\MachineController;
use App\Http\Controllers\Intranet\FincaDocController;
use App\Http\Controllers\Intranet\InvGroupController;
use App\Http\Controllers\Intranet\InvModelController;
use App\Http\Controllers\Intranet\AnaliticaController;
use App\Http\Controllers\Intranet\CondicionController;
use App\Http\Controllers\Intranet\ClasEquipoController;
use App\Http\Controllers\Intranet\IngresoDocController;
use App\Http\Controllers\Intranet\InvFactoryController;
use App\Http\Controllers\Intranet\InvItemDocController;
use App\Http\Controllers\Intranet\ReferenciaController;
use App\Http\Controllers\Intranet\TipoEquipoController;
use App\Http\Controllers\Intranet\ClientesDocController;
use App\Http\Controllers\Intranet\InvCategoryController;
use App\Http\Controllers\Intranet\StateEntityController;
use App\Http\Controllers\Intranet\TipoCultivoController;
use App\Http\Controllers\Intranet\AnaliticaDocController;
use App\Http\Controllers\Intranet\ClienteRiegoController;
use App\Http\Controllers\Intranet\DistribucionController;
use App\Http\Controllers\Intranet\SegmentationController;
use App\Http\Controllers\Intranet\RepresentanteController;
use App\Http\Controllers\Intranet\AbastecimientoController;
use App\Http\Controllers\Intranet\ClienteCultivoController;
use App\Http\Controllers\Intranet\ClassificationsController;
use App\Http\Controllers\Intranet\NuevaTecnologiaController;
use App\Http\Controllers\Intranet\InvConfigurationController;
use App\Http\Controllers\Intranet\AgricolaInversionController;
use App\Http\Controllers\Intranet\ClienteTechnologyController;
use App\Http\Controllers\Intranet\GanaderaInversionController;
use App\Http\Controllers\Intranet\InversionesAgricolaController;
use App\Http\Controllers\Intranet\InversionesGanaderaController;
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

    /**
     * ────────────────────────────────
     *  📦 RUTAS AGRUPADAS POR CONTROLADOR
     * ────────────────────────────────
     */

    // 🔹 Abastecimiento
    Route::post('abastecimientos', [AbastecimientoController::class, 'index']);
    Route::apiResource('abastecimiento', AbastecimientoController::class);

    // 🔹 Agricola ingresos
    Route::get('agricolaInversion/cliente/{cliente}/{year}', [AgricolaInversionController::class, 'getPerCliente']);
    Route::get('agricolaInversion/options', [AgricolaInversionController::class, 'getOptions']);
    Route::apiResource('agricolaInversion', AgricolaInversionController::class);

    // 🔹 Agricola Inversión
    Route::get('inversionesAgricola/cliente/{cliente}/{year}', [InversionesAgricolaController::class, 'getPerCliente']);
    Route::get('inversionesAgricola/options', [InversionesAgricolaController::class, 'getOptions']);
    Route::apiResource('inversionesAgricola', InversionesAgricolaController::class);

    // 🔹 Analitica
    Route::get('analitica/cliente/{cliente}', [AnaliticaController::class, 'getPerCliente']);
    Route::get('analitica/report/{analitica}', [AnaliticaController::class, 'getReport']);
    Route::get('analitica/report/pdf/{analitica}', [AnaliticaController::class, 'exportReportPdfBase64']);
    Route::apiResource('analitica', AnaliticaController::class);

    // 🔹 AnaliticaDoc
    Route::get('analiticaDoc/status/{analiticaDoc}/{status}', [AnaliticaDocController::class, 'changeEstatus']);
    Route::apiResource('analiticaDoc', AnaliticaDocController::class);

    // 🔹 Classifications
    Route::post('classifications', [ClassificationsController::class, 'index']);
    Route::apiResource('classification', ClassificationsController::class);

    // 🔹 ClasEquipo
    Route::post('clasEquipos', [ClasEquipoController::class, 'index']);
    Route::apiResource('clasEquipo', ClasEquipoController::class);

    // 🔹 Cliente
    Route::post('clientes', [ClienteController::class, 'index']);
    Route::post('clientes/excel', [ClienteController::class, 'insetExcel']);
    Route::post('cliente/add/capTech/{cliente}', [ClienteController::class, 'addCapTech']);
    Route::get('cliente/get/capTech/{cliente}', [ClienteController::class, 'getCapTech']);
    Route::get('cliente/options', [ClienteController::class, 'getOptions']);
    Route::get('cliente/mail/{cliente}', [ClienteController::class, 'enviarNotificacionActualizacion']);
    Route::get('cliente/rfc/{rfc}', [ClienteController::class, 'findByRfc']);
    Route::post('clientes/clientes', [ClienteController::class, 'getClientes']);
    Route::post('clientes/empleados', [ClienteController::class, 'getEmpleados']);
    Route::post('clientes/empleados/async', [ClienteController::class, 'syncEmpleadoClientes']);
    Route::apiResource('cliente', ClienteController::class);

    // 🔹 Cliente Abastecimiento
    Route::get('clienteAbastecimiento/cliente/form', [ClienteAbastecimientoController::class, 'getOptions']);
    Route::get('clienteAbastecimiento/cliente/{cliente}', [ClienteAbastecimientoController::class, 'getPerCliente']);
    Route::apiResource('clienteAbastecimiento', ClienteAbastecimientoController::class);

    // 🔹 Cliente Cultivo
    Route::get('clienteCultivo/cliente/{cliente}', [ClienteCultivoController::class, 'getPerCliente']);
    Route::get('clienteCultivo/options', [ClienteCultivoController::class, 'getOptions']);
    Route::apiResource('clienteCultivo', ClienteCultivoController::class);

    // 🔹 Cliente Riego
    Route::get('clienteRiego/cliente/form', [ClienteRiegoController::class, 'getOptions']);
    Route::get('clienteRiego/cliente/{cliente}', [ClienteRiegoController::class, 'getPerCliente']);
    Route::apiResource('clienteRiego', ClienteRiegoController::class);

    // 🔹 Cliente Technology
    Route::get('clienteTechnology/cliente/{cliente}', [ClienteTechnologyController::class, 'getPerCliente']);
    Route::get('nt/cliente/form', [ClienteTechnologyController::class, 'getOptions']);
    Route::post('nt/clientes', [ClienteTechnologyController::class, 'getClientesNT']);
    Route::post('nt/clientes/xls', [ClienteTechnologyController::class, 'getClientesNTxls']);
    Route::apiResource('clienteTechnology', ClienteTechnologyController::class);

    // 🔹 ClientesDoc
    Route::get('clientesDoc/cliente/{cliente}', [ClientesDocController::class, 'getPerCliente']);
    Route::apiResource('clientesDoc', ClientesDocController::class);

    // 🔹 Condicion
    Route::post('condiciones', [CondicionController::class, 'index']);
    Route::apiResource('condicion', CondicionController::class);

    // 🔹 Construction Classification
    Route::post('constructionClassifications', [ConstructionClassificationsController::class, 'index']);
    Route::apiResource('constructionClassification', ConstructionClassificationsController::class);

    // 🔹 Cultivo
    Route::post('cultivos', [CultivoController::class, 'index']);
    Route::apiResource('cultivo', CultivoController::class);

    // 🔹 Distribución
    Route::get('distribucion/cliente/{cliente}', [DistribucionController::class, 'getPerCliente']);
    Route::apiResource('distribucion', DistribucionController::class);

    // 🔹 Egresos
    Route::get('egreso/cliente/{cliente}/{year}', [EgresoController::class, 'getPerCliente']);
    Route::apiResource('egreso', EgresoController::class);

    // 🔹 Finca
    Route::get('finca/cliente/{cliente}', [FincaController::class, 'getPerCliente']);
    Route::get('finca/options', [FincaController::class, 'getOptions']);
    Route::apiResource('finca', FincaController::class);

    // 🔹 FincaDoc
    Route::apiResource('fincaDoc', FincaDocController::class);

    // 🔹 Ganado
    Route::post('ganados', [GanadoController::class, 'index']);
    Route::apiResource('ganado', GanadoController::class);

    // 🔹 Ganadera ingresos
    Route::get('ganaderaInversion/cliente/{cliente}/{year}', [GanaderaInversionController::class, 'getPerCliente']);
    Route::get('ganaderaInversion/options', [GanaderaInversionController::class, 'getOptions']);
    Route::apiResource('ganaderaInversion', GanaderaInversionController::class);

    // 🔹 Ganadera Inversión
    Route::get('inversionesGanadera/cliente/{cliente}/{year}', [InversionesGanaderaController::class, 'getPerCliente']);
    Route::get('inversionesGanadera/options', [InversionesGanaderaController::class, 'getOptions']);
    Route::apiResource('inversionesGanadera', InversionesGanaderaController::class);

    // 🔹 Ingreso
    Route::get('ingreso/cliente/{cliente}/{year}', [IngresoController::class, 'getPerCliente']);
    Route::apiResource('ingreso', IngresoController::class);

    // 🔹 IngresoDoc
    Route::apiResource('ingresoDoc', IngresoDocController::class);

    // 🔹 InvGroup
    Route::post('invFactories', [InvFactoryController::class, 'index']);
    Route::apiResource('invFactory', InvFactoryController::class);

    // 🔹 InvGroup
    Route::post('invGroups', [InvGroupController::class, 'index']);
    Route::apiResource('invGroup', InvGroupController::class);

    // 🔹 InvCategory
    Route::post('invCategories', [InvCategoryController::class, 'index']);
    Route::get('invCategory/forms', [InvCategoryController::class, 'getForms']);
    Route::apiResource('invCategory', InvCategoryController::class);

    // 🔹 InvConfiguration
    Route::post('invConfigurations', [InvConfigurationController::class, 'index']);
    Route::get('invConfiguration/forms', [InvConfigurationController::class, 'getForms']);
    Route::apiResource('invConfiguration', InvConfigurationController::class);

    // 🔹 InvItem
    Route::post('invItems', [InvItemController::class, 'index']);
    Route::get('invItem/invModel/{invModel}', [InvItemController::class, 'getModels']);
    Route::get('invItem/forms', [InvItemController::class, 'getForms']);
    Route::apiResource('invItem', InvItemController::class);

    // 🔹 InvItemDoc
    Route::apiResource('invItemDoc', InvItemDocController::class);

    // 🔹 InvModel
    Route::post('invModels', [InvModelController::class, 'index']);
    Route::get('invModel/category', [InvModelController::class, 'getForms']);
    Route::get('invModel/report', [InvModelController::class, 'getAllExport']);
    Route::get('invModel/configurations/{invCategory}', [InvModelController::class, 'perCategory']);
    Route::apiResource('invModel', InvModelController::class);

    // 🔹 Kinship
    Route::post('kinships', [KinshipController::class, 'index']);
    Route::apiResource('kinship', KinshipController::class);

    // 🔹 Marca
    Route::post('marcas', [MarcaController::class, 'index']);
    Route::apiResource('marca', MarcaController::class);

    // 🔹 Machine
    Route::get('machine/cliente/{cliente}', [MachineController::class, 'getPerCliente']);
    Route::get('machine/options', [MachineController::class, 'getOptions']);
    Route::apiResource('machine', MachineController::class);

    // 🔹 Nueva Tecnología
    Route::post('nuevaTecnologiums', [NuevaTecnologiaController::class, 'index']);
    Route::apiResource('nuevaTecnologium', NuevaTecnologiaController::class);

    // 🔹 Referencia
    Route::get('referencia/cliente/form', [ReferenciaController::class, 'getOptions']);
    Route::get('referencia/cliente/{cliente}', [ReferenciaController::class, 'getPerCliente']);
    Route::put('referencia/{referencia}', [ReferenciaController::class, 'update']);
    Route::delete('referencia/{referencia}', [ReferenciaController::class, 'destroy']);
    Route::apiResource('referencia', ReferenciaController::class);

    // 🔹 Referencia Comercial
    Route::get('referenciaComercial/cliente/{cliente}', [ReferenciaComercialController::class, 'getPerCliente']);
    Route::apiResource('referenciaComercial', ReferenciaComercialController::class);

    // 🔹 Representante
    Route::get('representante/cliente/{cliente}', [RepresentanteController::class, 'getPerCliente']);
    Route::apiResource('representante', RepresentanteController::class);

    // 🔹 Riego
    Route::post('riegos', [RiegoController::class, 'index']);
    Route::apiResource('riego', RiegoController::class);

    // 🔹 Sale
    Route::post('sales', [SaleController::class, 'index']);
    Route::get('sale/options', [SaleController::class, 'getOptions']);
    Route::get('sale/validated', [SaleController::class, 'getForValidate']);
    Route::post('sale/post/validated', [SaleController::class, 'postValidate']);
    Route::apiResource('sale', SaleController::class);

    // 🔹 Sale Tractor
    Route::get('sales-tractors', [SaleTractorController::class, 'index']);
    Route::apiResource('sales-tractors', SaleTractorController::class);

    // 🔹 Segmentation
    Route::post('segmentations', [SegmentationController::class, 'index']);
    Route::apiResource('segmentation', SegmentationController::class);

    // 🔹 State Entity
    Route::apiResource('stateEntity', StateEntityController::class);

    // 🔹 Tactic
    Route::post('tactics', [TacticController::class, 'index']);
    Route::apiResource('tactic', TacticController::class);

    // 🔹 Technological Capability
    Route::post('technologicalCapabilities', [TechnologicalCapabilityController::class, 'index']);
    Route::apiResource('technologicalCapability', TechnologicalCapabilityController::class);

    // 🔹 Tipo Cultivo
    Route::post('tipoCultivos', [TipoCultivoController::class, 'index']);
    Route::apiResource('tipoCultivo', TipoCultivoController::class);

    // 🔹 Tipo Equipo
    Route::post('tipoEquipos', [TipoEquipoController::class, 'index']);
    Route::apiResource('tipoEquipo', TipoEquipoController::class);

    // 🔹 Town
    Route::get('town/state/{id}', [TownController::class, 'getPerState']);
    Route::apiResource('town', TownController::class);
});
