<?php

use App\Enums\AbsenceReason;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/enums/absence-reasons', function () {

    $reasons = AbsenceReason::toArray();
    $reason = AbsenceReason::fromReason('sick_leave')->getDisplayValue();
    return compact('reasons','reason');
});

Route::get('/', function () {
    return view('welcome');
});


Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared </h1>' . $exitCode;
})->name('clear-cache');

//Reoptimized class loader:
Route::get('/optimize', function () {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>' . $exitCode;
})->name('optimize');

//Route cache:
Route::get('/route-cache', function () {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>' . $exitCode;
})->name('route-cache');

//Clear Route cache:
Route::get('/route-clear', function () {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>' . $exitCode;
})->name('route-clear');

//Clear View cache:
Route::get('/view-clear', function () {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>' . $exitCode;
})->name('view-clear');

//Clear Config cache:
Route::get('/config-cache', function () {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>' . $exitCode;
})->name('config-cache');

//Clear Config cache:
Route::get('/config-clear', function () {
    $exitCode = Artisan::call('config:clear');
    return '<h1>config:clear</h1>' . $exitCode;
})->name('config-clear');
