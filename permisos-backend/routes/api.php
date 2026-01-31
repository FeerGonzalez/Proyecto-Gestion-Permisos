<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermisoController;

//Autenticación
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

//Permisos de Empleado
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/permisos', [PermisoController::class, 'store']);
    Route::get('/permisos/mis-permisos', [PermisoController::class, 'misPermisos']);
    Route::get('/permisos/{permiso}', [PermisoController::class, 'show']);
    Route::put('/permisos/{permiso}', [PermisoController::class, 'update']);
    Route::delete('/permisos/{permiso}', [PermisoController::class, 'destroy']);
});

//Aprobaciones del Supervisor/RRHH
Route::middleware(['auth:sanctum', 'role:supervisor,rrhh'])->group(function () {
    Route::get('/permisos', [PermisoController::class, 'index']);
    Route::get('/permisos/pendientes', [PermisoController::class, 'pendientes']);
    Route::post('/permisos/{permiso}/aprobar', [PermisoController::class, 'aprobar']);
    Route::post('/permisos/{permiso}/rechazar', [PermisoController::class, 'rechazar']);
});
