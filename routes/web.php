<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\CounterManagementController;
use App\Http\Controllers\Admin\ServiceManagementController;
use App\Http\Controllers\FrontdeskQueueController;
use App\Http\Controllers\OfficerQueueController;
use App\Http\Controllers\PublicQueueController;
use App\Http\Controllers\Report\QueueReportController;
use App\Models\QueueTicket;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/antrian', [PublicQueueController::class, 'booking']);
Route::post('/antrian', [PublicQueueController::class, 'storeBooking']);
Route::get('/antrian/cek', [PublicQueueController::class, 'lookup']);
Route::get('/display', function () {
    $currentCalls = QueueTicket::query()
        ->with('counter')
        ->where('status', 'called')
        ->orderByDesc('called_at')
        ->limit(5)
        ->get();

    $recentCalls = QueueTicket::query()
        ->with('counter')
        ->whereNotNull('called_at')
        ->orderByDesc('called_at')
        ->limit(10)
        ->get();

    return view('pages.display.index', [
        'currentCalls' => $currentCalls,
        'recentCalls' => $recentCalls,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:'.UserRole::Frontdesk->value])->group(function () {
    Route::get('/frontdesk/antrian', [FrontdeskQueueController::class, 'index']);
    Route::post('/frontdesk/antrian', [FrontdeskQueueController::class, 'store']);
    Route::post('/frontdesk/antrian/check-in', [FrontdeskQueueController::class, 'checkIn']);
});

Route::middleware(['auth', 'verified', 'role:'.UserRole::Officer->value])->group(function () {
    Route::get('/petugas/loket/{counter}', [OfficerQueueController::class, 'show']);
    Route::post('/petugas/loket/{counter}/call-next', [OfficerQueueController::class, 'callNext']);
    Route::post('/petugas/loket/{counter}/recall', [OfficerQueueController::class, 'recall']);
    Route::post('/petugas/loket/{counter}/skip', [OfficerQueueController::class, 'skip']);
    Route::post('/petugas/loket/{counter}/complete', [OfficerQueueController::class, 'complete']);
    Route::post('/petugas/loket/{counter}/cancel', [OfficerQueueController::class, 'cancel']);
});

Route::middleware(['auth', 'verified', 'role:'.UserRole::Monitor->value])->group(function () {
    Route::get('/laporan/antrian', [QueueReportController::class, 'index']);
});

Route::middleware(['auth', 'verified', 'role:'.UserRole::Admin->value])->group(function () {
    Route::get('/admin/layanan', [ServiceManagementController::class, 'index']);
    Route::post('/admin/layanan', [ServiceManagementController::class, 'store']);
    Route::put('/admin/layanan/{service}', [ServiceManagementController::class, 'update']);

    Route::get('/admin/loket', [CounterManagementController::class, 'index']);
    Route::put('/admin/loket/{counter}', [CounterManagementController::class, 'update']);
});

require __DIR__.'/settings.php';
