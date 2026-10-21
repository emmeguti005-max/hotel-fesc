<?php

use App\Http\Controllers\AseoController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\EstructuraController;
use App\Http\Controllers\EvidenciaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Público (huéspedes y visitantes, sin cuenta)
|--------------------------------------------------------------------------
| El huésped escanea el QR de su habitación y desde ahí pide al restaurante,
| solicita aseo o reporta un daño. Facturas y registros de despacho se
| consultan por un token imposible de adivinar (el que va en el QR).
*/
Route::get('/', [PortalController::class, 'inicio'])->name('inicio');

Route::get('/habitacion/{habitacion:token}', [PortalController::class, 'habitacion'])->name('portal.habitacion');
Route::middleware('throttle:20,1')->group(function () {
    Route::post('/habitacion/{habitacion:token}/pedido', [PortalController::class, 'pedir'])->name('portal.pedir');
    Route::post('/habitacion/{habitacion:token}/aseo', [PortalController::class, 'solicitarAseo'])->name('portal.aseo');
    Route::post('/habitacion/{habitacion:token}/reporte', [PortalController::class, 'reportar'])->name('portal.reportar');
});

Route::get('/pedidos/{pedido:token}', [PortalController::class, 'pedido'])->name('pedidos.publico');
Route::get('/facturas/{factura:token}', [FacturaController::class, 'publica'])->name('facturas.publica');

Route::get('/solicitudes/nueva', [SolicitudController::class, 'create'])->name('solicitudes.create');
Route::post('/solicitudes', [SolicitudController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('solicitudes.store');
Route::get('/solicitudes/{solicitud:token}/gracias', [SolicitudController::class, 'gracias'])->name('solicitudes.gracias');
Route::get('/solicitudes/{solicitud:token}/comprobante', [SolicitudController::class, 'comprobante'])->name('solicitudes.comprobante');

/*
|--------------------------------------------------------------------------
| Acceso del personal
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/plano', [PlanoController::class, 'index'])->name('plano.index');
    Route::get('/plano/{piso}', [PlanoController::class, 'show'])->name('plano.show');
    Route::get('/equipos/{equipo}', [EquipoController::class, 'ficha'])->name('equipos.ficha');
});

/*
|--------------------------------------------------------------------------
| Panel interno — cada sección limitada a los roles que la usan
|--------------------------------------------------------------------------
| admin: todo · recepcion: estadías, facturas, tablero · aseo: limpiezas ·
| restaurante: cocina y menú · mantenimiento: daños y activos.
*/
Route::middleware('auth')->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', [DashboardController::class, 'inicio'])->name('inicio');
    Route::middleware('rol:recepcion')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Habitaciones: todo el personal ve el tablero; recepción cambia estados; admin las crea.
    Route::get('/habitaciones', [HabitacionController::class, 'index'])->name('habitaciones.index');
    Route::middleware('admin')->group(function () {
        Route::get('/habitaciones/crear', [HabitacionController::class, 'create'])->name('habitaciones.create');
        Route::post('/habitaciones', [HabitacionController::class, 'store'])->name('habitaciones.store');
        Route::get('/habitaciones/{habitacion}/editar', [HabitacionController::class, 'edit'])->name('habitaciones.edit');
        Route::put('/habitaciones/{habitacion}', [HabitacionController::class, 'update'])->name('habitaciones.update');
        Route::delete('/habitaciones/{habitacion}', [HabitacionController::class, 'destroy'])->name('habitaciones.destroy');
    });
    Route::get('/habitaciones/{habitacion}', [HabitacionController::class, 'show'])->name('habitaciones.show');
    Route::middleware('rol:recepcion,mantenimiento')->post('/habitaciones/{habitacion}/estado', [HabitacionController::class, 'cambiarEstado'])->name('habitaciones.estado');

    Route::middleware('rol:recepcion')->group(function () {
        Route::get('/reservas', [ReservaController::class, 'index'])->name('reservas.index');
        Route::get('/reservas/crear', [ReservaController::class, 'create'])->name('reservas.create');
        Route::post('/reservas', [ReservaController::class, 'store'])->name('reservas.store');
        Route::get('/reservas/{reserva}', [ReservaController::class, 'show'])->name('reservas.show');
        Route::post('/reservas/{reserva}/checkin', [ReservaController::class, 'checkin'])->name('reservas.checkin');
        Route::post('/reservas/{reserva}/checkout', [ReservaController::class, 'checkout'])->name('reservas.checkout');
        Route::post('/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');
        Route::get('/huespedes', [ReservaController::class, 'huespedes'])->name('huespedes.index');

        Route::get('/facturas', [FacturaController::class, 'index'])->name('facturas.index');
        Route::get('/facturas/{factura}', [FacturaController::class, 'show'])->name('facturas.show');
        Route::middleware('admin')->post('/facturas/{factura}/anular', [FacturaController::class, 'anular'])->name('facturas.anular');
    });

    Route::middleware('rol:aseo,recepcion')->group(function () {
        Route::get('/aseo', [AseoController::class, 'index'])->name('aseo.index');
        Route::post('/aseo/{tarea}/iniciar', [AseoController::class, 'iniciar'])->name('aseo.iniciar');
        Route::post('/aseo/{tarea}/terminar', [AseoController::class, 'terminar'])->name('aseo.terminar');
    });
    Route::middleware('rol:recepcion')->group(function () {
        Route::post('/aseo', [AseoController::class, 'store'])->name('aseo.store');
        Route::post('/aseo/{tarea}/asignar', [AseoController::class, 'asignar'])->name('aseo.asignar');
        Route::get('/aseo/personal', [AseoController::class, 'personal'])->name('aseo.personal');
    });

    Route::middleware('rol:restaurante,recepcion')->group(function () {
        Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/crear', [PedidoController::class, 'create'])->name('pedidos.create');
        Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
        Route::get('/pedidos/historial', [PedidoController::class, 'historial'])->name('pedidos.historial');
        Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
        Route::post('/pedidos/{pedido}/avanzar', [PedidoController::class, 'avanzar'])->name('pedidos.avanzar');
        Route::post('/pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    });
    Route::middleware('rol:restaurante')->group(function () {
        Route::get('/menu', [ProductoController::class, 'index'])->name('productos.index');
        Route::post('/menu', [ProductoController::class, 'store'])->name('productos.store');
        Route::put('/menu/{producto}', [ProductoController::class, 'update'])->name('productos.update');
        Route::post('/menu/{producto}/disponible', [ProductoController::class, 'alternar'])->name('productos.alternar');
    });

    Route::middleware('rol:mantenimiento,recepcion')->group(function () {
        Route::get('/mantenimiento', [MantenimientoController::class, 'index'])->name('mantenimiento.index');
        Route::get('/mantenimiento/exportar', [MantenimientoController::class, 'exportar'])->name('mantenimiento.exportar');
        Route::get('/mantenimiento/{solicitud}', [MantenimientoController::class, 'show'])->name('mantenimiento.show');
        Route::post('/mantenimiento/{solicitud}/comentar', [MantenimientoController::class, 'comentar'])->name('mantenimiento.comentar');
        Route::post('/mantenimiento/{solicitud}/evidencias', [EvidenciaController::class, 'store'])->name('evidencias.store');
    });
    Route::middleware('rol:mantenimiento')->group(function () {
        Route::post('/mantenimiento/{solicitud}/tomar', [MantenimientoController::class, 'tomar'])->name('mantenimiento.tomar');
        Route::post('/mantenimiento/{solicitud}/cerrar', [MantenimientoController::class, 'cerrar'])->name('mantenimiento.cerrar');
        Route::delete('/evidencias/{evidencia}', [EvidenciaController::class, 'destroy'])->name('evidencias.destroy');

        Route::get('/activos', [EquipoController::class, 'index'])->name('equipos.index');
        Route::get('/activos/crear', [EquipoController::class, 'create'])->name('equipos.create');
        Route::post('/activos', [EquipoController::class, 'store'])->name('equipos.store');
        Route::get('/activos/{equipo}/editar', [EquipoController::class, 'edit'])->name('equipos.edit');
        Route::put('/activos/{equipo}', [EquipoController::class, 'update'])->name('equipos.update');
        Route::post('/activos/{equipo}/posicion', [EquipoController::class, 'actualizarPosicion'])->name('equipos.posicion');
    });

    Route::middleware('admin')->group(function () {
        Route::delete('/activos/{equipo}', [EquipoController::class, 'destroy'])->name('equipos.destroy');

        Route::get('/personal', [PersonalController::class, 'index'])->name('personal.index');
        Route::get('/personal/crear', [PersonalController::class, 'create'])->name('personal.create');
        Route::post('/personal', [PersonalController::class, 'store'])->name('personal.store');
        Route::get('/personal/{usuario}/editar', [PersonalController::class, 'edit'])->name('personal.edit');
        Route::put('/personal/{usuario}', [PersonalController::class, 'update'])->name('personal.update');

        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria');
    });

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
