<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\CounterManagementController;
use App\Http\Controllers\Admin\ServiceManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\FrontdeskQueueController;
use App\Http\Controllers\OfficerQueueController;
use App\Http\Controllers\PublicQueueController;
use App\Http\Controllers\Report\QueueReportController;
use App\Livewire\QueueDisplay;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicQueueController::class, 'index'])->name('home');
Route::get('/antrian', [PublicQueueController::class, 'booking']);
Route::post('/antrian', [PublicQueueController::class, 'storeBooking']);
Route::get('/antrian/cek', [PublicQueueController::class, 'lookup'])->name('queue.cek');
Route::get('/antrian/konfirmasi/{ticket}', [PublicQueueController::class, 'confirmation'])->name('queue.confirmation');
Route::get('/display', QueueDisplay::class)->name('queue.display');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard', [
            'activeRole' => auth()->user()?->role,
        ]);
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
    // Admin - Layanan (Services)
    Route::get('/admin/layanan', [ServiceManagementController::class, 'index'])->name('admin.layanan.index');
    Route::post('/admin/layanan', [ServiceManagementController::class, 'store'])->name('admin.layanan.store');
    Route::put('/admin/layanan/{service}', [ServiceManagementController::class, 'update'])->name('admin.layanan.update');
    Route::delete('/admin/layanan/{service}', [ServiceManagementController::class, 'destroy'])->name('admin.layanan.destroy');

    // Admin - Loket (Counters)
    Route::get('/admin/loket', [CounterManagementController::class, 'index'])->name('admin.loket.index');
    Route::put('/admin/loket/{counter}', [CounterManagementController::class, 'update'])->name('admin.loket.update');
    Route::delete('/admin/loket/{counter}', [CounterManagementController::class, 'destroy'])->name('admin.loket.destroy');

    // Admin - Users
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

    // Admin - Roles & Permissions
    Route::get('/admin/roles', [UserManagementController::class, 'roles']);
    Route::get('/admin/izin-layanan', [UserManagementController::class, 'servicePermissions']);
});

// Kiosk routes (no auth - uses own password system)
Route::get('/kiosk', fn () => view('pages.kiosk.index'))->name('kiosk.index');
Route::get('/kiosk/login', fn () => view('pages.kiosk.login'))->name('kiosk.login');
Route::post('/kiosk/login', fn () => redirect('/kiosk'))->name('kiosk.authenticate');
Route::post('/kiosk/logout', fn () => redirect('/kiosk/login'))->name('kiosk.logout');

// TV Display routes (no auth - uses own password system)
Route::get('/tv-display', fn () => view('pages.tv-display.index'))->name('tv-display.index');
Route::get('/tv-display/login', fn () => view('pages.tv-display.login'))->name('tv-display.login');
Route::post('/tv-display/login', fn () => redirect('/tv-display'))->name('tv-display.authenticate');
Route::post('/tv-display/logout', fn () => redirect('/tv-display/login'))->name('tv-display.logout');

require __DIR__.'/settings.php';
