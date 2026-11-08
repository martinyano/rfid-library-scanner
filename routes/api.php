<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RfidScanController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/rfid/scan', RfidScanController::class)
    ->middleware(['rfid.key', 'throttle:60,1'])
    ->name('api.rfid.scan');