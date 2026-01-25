<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Ruta principal (puedes redirigir a clientes)
Route::get('/', function () {
    return redirect()->route('clients.index');
});

// Rutas automáticas para el CRUD completo de ambas entidades
Route::resource('clients', ClientController::class);
Route::resource('orders', OrderController::class);