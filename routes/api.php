<?php

use App\Http\Controllers\Api\RfidCirculationController;
use App\Http\Controllers\Api\RfidScanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/rfid/scan', RfidScanController::class)
    ->middleware(['rfid.key', 'throttle:60,1'])
    ->name('api.rfid.scan');

Route::post('/rfid/circulation', RfidCirculationController::class)
    ->middleware(['rfid.key', 'throttle:120,1'])
    ->name('api.rfid.circulation');
