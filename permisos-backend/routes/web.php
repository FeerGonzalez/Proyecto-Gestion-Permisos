<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'API Sistema de Gestión de Permisos OK'
    ]);
});
