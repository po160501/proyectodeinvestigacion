<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\AlertaController;
use App\Http\Controllers\MonitoreoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;

use App\Http\Controllers\ObraController;
use App\Http\Controllers\TrabajadorController;

// ── Auth ──
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    if ($token = request()->cookie('worker_token')) {
        return redirect()->route('trabajador.medidor', $token);
    }
    return redirect('/login');
});
Route::get('/about', fn() => view('about'))->name('about');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register']);

// ── Rutas protegidas ──
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/api', [DashboardController::class, 'api'])->name('dashboard.api');
    Route::get('/dashboard/api/terc', [DashboardController::class, 'apiTerc'])->name('dashboard.api.terc');
    Route::get('/pdr', [DashboardController::class, 'pdr'])->name('pdr.index');
    Route::get('/etag', [DashboardController::class, 'etag'])->name('etag.index');
    Route::get('/terc', [DashboardController::class, 'terc'])->name('terc.index');


    Route::post('/dashboard/pdr', [DashboardController::class, 'storePdr'])->name('dashboard.pdr');
    Route::post('/dashboard/etag', [DashboardController::class, 'storeEtag'])->name('dashboard.etag');
    Route::post('/dashboard/terc', [DashboardController::class, 'storeTerc'])->name('dashboard.terc');
    Route::post('/pdr/ajax-update', [DashboardController::class, 'ajaxUpdatePdr'])->name('pdr.ajax-update');

    // Monitoreo
    Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo.index');
    Route::get('/monitoreo/datos', [MonitoreoController::class, 'datos'])->name('monitoreo.datos');

    // Alertas
    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::patch('/alertas/{alerta}/estado', [AlertaController::class, 'updateEstado'])->name('alertas.estado');

    // Reportes
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/generar', [ReporteController::class, 'generar'])->name('reportes.generar');


    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::delete('/usuarios/{user}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Obras / Áreas
    Route::get('/obras', [ObraController::class, 'index'])->name('obras.index');
    Route::post('/obras', [ObraController::class, 'store'])->name('obras.store');
    Route::delete('/obras/{obra}', [ObraController::class, 'destroy'])->name('obras.destroy');
    Route::post('/obras/{obra}/token', [ObraController::class, 'generarToken'])->name('obras.token');
    Route::patch('/trabajadores/{trabajador}/area', [ObraController::class, 'asignarArea'])->name('trabajadores.area');
    Route::delete('/trabajadores/{trabajador}', [TrabajadorController::class, 'destroy'])->name('trabajadores.destroy');

});


// ── Trabajador (acceso por token, sin auth) ──
Route::get('/trabajador/{token}', [TrabajadorController::class, 'medidor'])->name('trabajador.medidor');
Route::post('/trabajador/{token}/registrar', [TrabajadorController::class, 'registrar'])->name('trabajador.registrar');
Route::post('/trabajador/{token}/medicion', [TrabajadorController::class, 'guardarMedicion'])->name('trabajador.medicion');
