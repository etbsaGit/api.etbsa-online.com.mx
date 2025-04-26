<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Caja\CajaBancoController;
use App\Http\Controllers\Caja\CajaCuentaController;
use App\Http\Controllers\Caja\CajaCategoriaController;
use App\Http\Controllers\Caja\CajaTiposPagosController;
use App\Http\Controllers\Caja\CajaDenominacionController;
use App\Http\Controllers\Caja\CajaTiposFacturaController;


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
    //--------------------CajaCategoria--------------------
    Route::post('cajaCategorias', [CajaCategoriaController::class, 'index']);
    Route::apiResource('cajaCategoria', CajaCategoriaController::class);

    //--------------------CajaTiposPago--------------------
    Route::post('cajaTiposPagos', [CajaTiposPagosController::class, 'index']);
    Route::apiResource('cajaTiposPago', CajaTiposPagosController::class);

    //--------------------CajaTiposFactura--------------------
    Route::post('cajaTiposFacturas', [CajaTiposFacturaController::class, 'index']);
    Route::apiResource('cajaTiposFactura', CajaTiposFacturaController::class);

    //--------------------CajaBanco--------------------
    Route::post('cajaBancos', [CajaBancoController::class, 'index']);
    Route::apiResource('cajaBanco', CajaBancoController::class);

    //--------------------CajaCuenta--------------------
    Route::post('cajaCuentas', [CajaCuentaController::class, 'index']);
    Route::get('cajaCuenta/forms', [CajaCuentaController::class, 'getforms']);
    Route::apiResource('cajaCuenta', CajaCuentaController::class);

    //--------------------CajaDenominacion--------------------
    Route::post('cajaDenominaciones', [CajaDenominacionController::class, 'index']);
    Route::apiResource('cajaDenominacion', CajaDenominacionController::class);
});
