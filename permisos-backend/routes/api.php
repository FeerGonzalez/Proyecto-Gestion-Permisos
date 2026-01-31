<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\UserController;

//Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

//Perfil usuario autenticado
Route::middleware('auth:sanctum')->prefix('me')->group(function () {
    Route::get('/', [AuthController::class, 'me']);
    Route::put('/', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'changePassword']);
});

//Permisos de Empleado
Route::middleware('auth:sanctum')->prefix('permisos')->group(function () {
    Route::post('/', [PermisoController::class, 'store']);
    Route::get('/horas-disponibles', [UserController::class, 'horasDisponibles']);
    Route::get('/mis-permisos', [PermisoController::class, 'misPermisos']);
    Route::get('/{permiso}', [PermisoController::class, 'show'])->whereNumber('permiso');
    Route::put('/{permiso}', [PermisoController::class, 'update'])->whereNumber('permiso');
    Route::delete('/{permiso}', [PermisoController::class, 'destroy'])->whereNumber('permiso');
    Route::post('/{permiso}/cancelar', [PermisoController::class, 'cancelar'])->whereNumber('permiso');
});

//Aprobaciones del Supervisor/RRHH
Route::middleware(['auth:sanctum', 'role:supervisor,rrhh'])->prefix('permisos')->group(function () {
    Route::get('/', [PermisoController::class, 'index']);
    Route::get('/pendientes', [PermisoController::class, 'pendientes']);
    Route::post('/{permiso}/aprobar', [PermisoController::class, 'aprobar']);
    Route::post('/{permiso}/rechazar', [PermisoController::class, 'rechazar']);
});

//Registro de Usuarios (RRHH)
Route::middleware(['auth:sanctum', 'role:rrhh'])->prefix('usuarios')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{user}', [UserController::class, 'show']);
    Route::put('/{user}', [UserController::class, 'update']);
    Route::delete('/{user}', [UserController::class, 'destroy']);
});


