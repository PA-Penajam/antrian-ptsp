<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\CounterManagementController;
use App\Http\Controllers\Admin\ServiceManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WilayahSettingController;
use App\Http\Controllers\FrontdeskQueueController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\OfficerQueueController;
use App\Http\Controllers\PublicQueueController;
use App\Http\Controllers\Report\QueueReportController;
use App\Http\Controllers\TvDisplayController;
use App\Http\Controllers\TvDisplayTtsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicQueueController::class, 'index'])->name('home');
Route::get('/antrian', [PublicQueueController::class, 'booking']);
Route::post('/antrian', [PublicQueueController::class, 'storeBooking'])->middleware('throttle:10,1');
Route::get('/antrian/cek', [PublicQueueController::class, 'lookup'])->name('queue.cek')->middleware('throttle:30,1');
Route::get('/antrian/konfirmasi/{ticket}', [PublicQueueController::class, 'confirmation'])->name('queue.confirmation')->middleware('signed');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (\Illuminate\Http\Request $request) {
        return view('dashboard', [
            'activeRole' => $request->user()?->role,
        ]);
    })->name('dashboard');

    Route::get('workstation', function () {
        return view('dashboard', [
            'activeRole' => 'officer',
        ]);
    })->name('workstation');
});

Route::middleware(['auth', 'verified', 'role:'.UserRole::Frontdesk->value.','.UserRole::Admin->value])->group(function () {
    Route::get('/frontdesk/antrian', [FrontdeskQueueController::class, 'index'])->name('frontdesk.queue.index');
    Route::post('/frontdesk/antrian', [FrontdeskQueueController::class, 'store'])->name('frontdesk.queue.store');
    Route::post('/frontdesk/antrian/check-in', [FrontdeskQueueController::class, 'checkIn'])->name('frontdesk.queue.check-in');
});

Route::middleware(['auth', 'verified', 'role:'.UserRole::Officer->value])->group(function () {
    Route::get('/petugas/loket/{counter}', [OfficerQueueController::class, 'show'])->name('officer.counter.show');
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
    // Admin - Layanan (Services)
    Route::get('/admin/layanan', [ServiceManagementController::class, 'index'])->name('admin.layanan.index');
    Route::post('/admin/layanan', [ServiceManagementController::class, 'store'])->name('admin.layanan.store');
    Route::put('/admin/layanan/{service}', [ServiceManagementController::class, 'update'])->name('admin.layanan.update');
    Route::delete('/admin/layanan/{service}', [ServiceManagementController::class, 'destroy'])->name('admin.layanan.destroy');

    // Admin - Loket (Counters)
    Route::get('/admin/loket', [CounterManagementController::class, 'index'])->name('admin.loket.index');
    Route::post('/admin/loket', [CounterManagementController::class, 'store'])->name('admin.loket.store');
    Route::put('/admin/loket/{counter}', [CounterManagementController::class, 'update'])->name('admin.loket.update');
    Route::delete('/admin/loket/{counter}', [CounterManagementController::class, 'destroy'])->name('admin.loket.destroy');

    // Admin - Users
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

    // Admin - Wilayah Scope
    Route::get('/admin/wilayah', [WilayahSettingController::class, 'index'])->name('admin.wilayah.index');
    Route::put('/admin/wilayah', [WilayahSettingController::class, 'update'])->name('admin.wilayah.update');

    // Admin - Roles & Permissions
    Route::get('/admin/roles', fn () => redirect()->route('admin.users.index', ['tab' => 'roles'], 301));
    Route::get('/admin/izin-layanan', fn () => redirect()->route('admin.users.index', ['tab' => 'roles'], 301));
});

// Kiosk routes (no auth - uses own password system)
Route::get('/kiosk/login', [KioskController::class, 'showLogin'])->name('kiosk.login');
Route::post('/kiosk/login', [KioskController::class, 'login'])->name('kiosk.authenticate')->middleware('throttle:5,1');
Route::post('/kiosk/logout', [KioskController::class, 'logout'])->name('kiosk.logout');
Route::middleware('module.password:kiosk')->group(function () {
    Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
});

// TV Display routes (no auth - uses own password system)
Route::get('/tv-display/login', [TvDisplayController::class, 'showLogin'])->name('tv-display.login');
Route::post('/tv-display/login', [TvDisplayController::class, 'login'])->name('tv-display.authenticate')->middleware('throttle:5,1');
Route::post('/tv-display/logout', [TvDisplayController::class, 'logout'])->name('tv-display.logout');
Route::middleware('module.password:tv-display')->group(function () {
    Route::get('/tv-display', [TvDisplayController::class, 'index'])->name('tv-display.index');
    Route::get('/tv-display/tts/announcement', [TvDisplayTtsController::class, 'announcement'])->name('tv-display.tts.announcement');
    Route::get('/tv-display/tts/audio/{cacheKey}', [TvDisplayTtsController::class, 'audio'])->name('tv-display.tts.audio');
});

require __DIR__.'/settings.php';
