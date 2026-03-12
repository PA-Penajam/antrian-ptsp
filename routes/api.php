<?php

use App\Http\Controllers\Api\PublicQueueController;
use App\Http\Controllers\Api\PublicServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('institution', [PublicServiceController::class, 'institution'])->name('api.institution');
    Route::get('services', [PublicServiceController::class, 'index'])->name('api.services.index');
    Route::get('services/{slug}', [PublicServiceController::class, 'show'])->name('api.services.show');
    Route::get('queue/lookup', [PublicQueueController::class, 'lookup'])->name('api.queue.lookup');
    Route::get('queue/ticket/{ticketNumber}', [PublicQueueController::class, 'show'])->name('api.queue.show');
    Route::get('queue/ticket-by-id/{encryptedId}', [PublicQueueController::class, 'showById'])->name('api.queue.showById');
});

Route::middleware('throttle:10,1')->group(function () {
    Route::post('queue/booking', [PublicQueueController::class, 'booking'])->name('api.queue.booking');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
