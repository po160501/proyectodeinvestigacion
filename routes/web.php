<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\MonitoreoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ConfiguracionController;

// ── Auth ──
Route::get('/', fn() => redirect('/login'));
Route::get('/about',     fn() => view('about'))->name('about');
Route::get('/login',     [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register']);

// ── Rutas protegidas ──
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Monitoreo
    Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo.index');
    Route::get('/monitoreo/datos', [MonitoreoController::class, 'datos'])->name('monitoreo.datos');

    // Alertas
    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::patch('/alertas/{alerta}/estado', [AlertaController::class, 'updateEstado'])->name('alertas.estado');

    // Reportes
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/generar', [ReporteController::class, 'generar'])->name('reportes.generar');

    // Sensores
    Route::get('/sensores', [SensorController::class, 'index'])->name('sensores.index');
    Route::post('/sensores', [SensorController::class, 'store'])->name('sensores.store');
    Route::put('/sensores/{sensor}', [SensorController::class, 'update'])->name('sensores.update');
    Route::delete('/sensores/{sensor}', [SensorController::class, 'destroy'])->name('sensores.destroy');

    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::delete('/usuarios/{user}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
});

// ── API para ESP32/Arduino (sin CSRF) ──
Route::post('/api/medicion', [MonitoreoController::class, 'recibirDato'])->name('api.medicion');
