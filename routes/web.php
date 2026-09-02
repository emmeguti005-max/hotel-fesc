<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\EstructuraController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SolicitudController::class, 'create']);
Route::get('/solicitudes/nueva', [SolicitudController::class, 'create'])->name('solicitudes.create');
Route::post('/solicitudes', [SolicitudController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('solicitudes.store');
Route::get('/solicitudes/{solicitud:token}/gracias', [SolicitudController::class, 'gracias'])->name('solicitudes.gracias');
Route::get('/solicitudes/{solicitud:token}/comprobante', [SolicitudController::class, 'comprobante'])->name('solicitudes.comprobante');

Route::middleware('auth')->group(function () {
    Route::get('/plano', [PlanoController::class, 'index'])->name('plano.index');
    Route::get('/plano/{piso}', [PlanoController::class, 'show'])->name('plano.show');
    Route::get('/equipos/{equipo}', [EquipoController::class, 'ficha'])->name('equipos.ficha');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', [PanelController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/solicitudes/exportar', [PanelController::class, 'exportar'])->name('solicitudes.exportar');
    Route::get('/solicitudes/{solicitud}', [PanelController::class, 'show'])->name('show');
    Route::post('/solicitudes/{solicitud}/tomar', [PanelController::class, 'tomar'])->name('tomar');
    Route::post('/solicitudes/{solicitud}/comentar', [PanelController::class, 'comentar'])->name('comentar');
    Route::post('/solicitudes/{solicitud}/cerrar', [PanelController::class, 'cerrar'])->name('cerrar');

    Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');
    Route::get('/equipos/crear', [EquipoController::class, 'create'])->name('equipos.create');
    Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');
    Route::get('/equipos/{equipo}/editar', [EquipoController::class, 'edit'])->name('equipos.edit');
    Route::put('/equipos/{equipo}', [EquipoController::class, 'update'])->name('equipos.update');
    Route::middleware('admin')->delete('/equipos/{equipo}', [EquipoController::class, 'destroy'])->name('equipos.destroy');
    Route::post('/equipos/{equipo}/posicion', [EquipoController::class, 'actualizarPosicion'])->name('equipos.posicion');

    Route::middleware('admin')->get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria');

    Route::middleware('admin')->prefix('estructura')->name('estructura.')->group(function () {
        Route::get('/torres', [EstructuraController::class, 'torres'])->name('torres');
        Route::post('/torres', [EstructuraController::class, 'storeTorre'])->name('torres.store');
        Route::put('/torres/{torre}', [EstructuraController::class, 'updateTorre'])->name('torres.update');
        Route::delete('/torres/{torre}', [EstructuraController::class, 'destroyTorre'])->name('torres.destroy');

        Route::get('/torres/{torre}/pisos', [EstructuraController::class, 'pisos'])->name('pisos');
        Route::post('/torres/{torre}/pisos', [EstructuraController::class, 'storePiso'])->name('pisos.store');
        Route::delete('/torres/{torre}/pisos/{piso}', [EstructuraController::class, 'destroyPiso'])->name('pisos.destroy');
        Route::post('/torres/{torre}/pisos/{piso}/plano', [EstructuraController::class, 'storePlanoPiso'])->name('pisos.plano.store');
        Route::delete('/torres/{torre}/pisos/{piso}/plano', [EstructuraController::class, 'destroyPlanoPiso'])->name('pisos.plano.destroy');

        Route::get('/torres/{torre}/pisos/{piso}/areas', [EstructuraController::class, 'areas'])->name('areas');
        Route::post('/torres/{torre}/pisos/{piso}/areas', [EstructuraController::class, 'storeArea'])->name('areas.store');
        Route::put('/torres/{torre}/pisos/{piso}/areas/{area}', [EstructuraController::class, 'updateArea'])->name('areas.update');
        Route::delete('/torres/{torre}/pisos/{piso}/areas/{area}', [EstructuraController::class, 'destroyArea'])->name('areas.destroy');

        Route::get('/torres/{torre}/pisos/{piso}/areas/{area}/ubicaciones', [EstructuraController::class, 'ubicaciones'])->name('ubicaciones');
        Route::post('/torres/{torre}/pisos/{piso}/areas/{area}/ubicaciones', [EstructuraController::class, 'storeUbicacion'])->name('ubicaciones.store');
        Route::put('/torres/{torre}/pisos/{piso}/areas/{area}/ubicaciones/{ubicacion}', [EstructuraController::class, 'updateUbicacion'])->name('ubicaciones.update');
        Route::delete('/torres/{torre}/pisos/{piso}/areas/{area}/ubicaciones/{ubicacion}', [EstructuraController::class, 'destroyUbicacion'])->name('ubicaciones.destroy');
    });
});
