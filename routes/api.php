<?php

use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public endpoints for antrian-public
Route::get('/institution', [InstitutionController::class, 'index']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{slug}', [ServiceController::class, 'show']);

Route::post('/queue/booking', [QueueController::class, 'booking']);
Route::get('/queue/lookup', [QueueController::class, 'lookup']);
Route::get('/queue/ticket/{ticketNumber}', [QueueController::class, 'showTicket']);
