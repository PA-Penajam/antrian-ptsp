# Code Quality Refactor — DRY, KISS, SOLID Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Perbaiki kualitas kode antrian-ptsp dengan mengatasi 30 issue dari code review: DRY violations, N+1 queries, route duplication, hardcoded values, SRP violations.

**Architecture:** Refactor dilakukan secara incremental per fase — mulai dari critical fixes (route, N+1), lalu DRY extraction ke Model scopes/methods, kemudian SRP split dan KISS simplification. Setiap task harus backward-compatible.

**Tech Stack:** Laravel 12, PHP 8.3+, Eloquent ORM, PHPUnit

---

## Phase 1: Critical Fixes (Immediate)

### Task 1: Hapus Route Duplikasi di `routes/web.php`

**Files:**
- Modify: `routes/web.php:64-78`

**Step 1: Identifikasi dan hapus route duplikasi**

Route `/admin/loket` dideklarasikan 3x. Hapus 2 blok duplikasi, sisakan hanya yang pertama:

```php
// SEBELUM (routes/web.php baris 60-78) — 3 blok identik
// Admin - Loket (Counters)
Route::get('/admin/loket', [CounterManagementController::class, 'index'])->name('admin.loket.index');
Route::post('/admin/loket', [CounterManagementController::class, 'store'])->name('admin.loket.store');
Route::put('/admin/loket/{counter}', [CounterManagementController::class, 'update'])->name('admin.loket.update');
Route::delete('/admin/loket/{counter}', [CounterManagementController::class, 'destroy'])->name('admin.loket.destroy');
// HAPUS semua baris di bawah ini sampai komentar "// Admin - Users":
Route::get('/admin/loket', ...  // DUPLIKAT 1
Route::put('/admin/loket/...    // DUPLIKAT 1
Route::delete('/admin/loket/... // DUPLIKAT 1
Route::get('/admin/loket', ...  // DUPLIKAT 2
Route::put('/admin/loket/...    // DUPLIKAT 2
Route::delete('/admin/loket/... // DUPLIKAT 2
```

```php
// SESUDAH — hanya 1 blok
// Admin - Loket (Counters)
Route::get('/admin/loket', [CounterManagementController::class, 'index'])->name('admin.loket.index');
Route::post('/admin/loket', [CounterManagementController::class, 'store'])->name('admin.loket.store');
Route::put('/admin/loket/{counter}', [CounterManagementController::class, 'update'])->name('admin.loket.update');
Route::delete('/admin/loket/{counter}', [CounterManagementController::class, 'destroy'])->name('admin.loket.destroy');
```

**Step 2: Verifikasi route list**

Run: `php artisan route:list --path=admin/loket`
Expected: Hanya 4 route (GET, POST, PUT, DELETE), tidak ada duplikat.

**Step 3: Commit**

```bash
git add routes/web.php
git commit -m "fix: remove duplicate /admin/loket route declarations"
```

---

### Task 2: Fix N+1 Query di QueueTicket — Tambah `getQueuePosition()` ke Model

**Files:**
- Modify: `app/Models/QueueTicket.php`
- Modify: `app/Http/Resources/QueueTicketResource.php`

**Step 1: Tambah method `getQueuePosition()` di QueueTicket Model**

```php
// app/Models/QueueTicket.php — tambah method baru

/**
 * Hitung posisi antrian tiket ini di antara tiket waiting pada hari yang sama.
 * Mengembalikan null jika status bukan Waiting.
 */
public function getQueuePosition(): ?int
{
    if ($this->status !== QueueStatus::Waiting) {
        return null;
    }

    return static::query()
        ->where('queue_pool_id', $this->queue_pool_id)
        ->whereDate('service_date', $this->service_date)
        ->where('status', QueueStatus::Waiting)
        ->where('sequence_number', '<', $this->sequence_number)
        ->count() + 1;
}
```

**Step 2: Update QueueTicketResource — gunakan Model method**

```php
// app/Http/Resources/QueueTicketResource.php
// Ganti private method computeQueuePosition() dengan Model method

public function toArray(Request $request): array
{
    return [
        'id' => encrypt($this->id),
        'ticket_number' => $this->ticket_number,
        'service_date' => $this->service_date?->format('Y-m-d'),
        'visitor_name' => $this->visitor_name,
        'visitor_wilayah_kode' => $this->visitor_wilayah_kode,
        'status' => $this->status->value,
        'status_label' => $this->status->label(),
        'service' => $this->whenLoaded('service', fn () => new ServiceResource($this->service)),
        'queue_position' => $this->resource->getQueuePosition(), // Gunakan model method
        'counter_name' => $this->whenLoaded('counter', fn () => $this->counter?->name),
        'checked_in_at' => $this->checked_in_at?->toIso8601String(),
        'called_at' => $this->called_at?->toIso8601String(),
        'completed_at' => $this->completed_at?->toIso8601String(),
        'cancelled_at' => $this->cancelled_at?->toIso8601String(),
    ];
}

// HAPUS private method computeQueuePosition() — sudah pindah ke Model
```

**Step 3: Verifikasi tidak ada panggilan lain ke computeQueuePosition()**

Run: `grep -rn "computeQueuePosition" app/`
Expected: Tidak ada hasil (semua sudah diganti).

**Step 4: Commit**

```bash
git add app/Models/QueueTicket.php app/Http/Resources/QueueTicketResource.php
git commit -m "refactor: move queue position calculation to QueueTicket model (DRY)"
```

---

### Task 3: Fix N+1 Query di ServiceResource — Tambah `getRemainingQuota()` ke Model

**Files:**
- Modify: `app/Models/Service.php`
- Modify: `app/Http/Resources/ServiceResource.php`

**Step 1: Tambah method `getRemainingQuota()` di Service Model**

```php
// app/Models/Service.php — tambah method

/**
 * Hitung sisa kuota harian untuk tanggal tertentu.
 * Mengembalikan null jika daily_quota tidak diset.
 */
public function getRemainingQuota(?string $date = null): ?int
{
    if ($this->daily_quota === null) {
        return null;
    }

    $targetDate = $date ?? today()->toDateString();

    $usedCount = QueueTicket::query()
        ->where('service_id', $this->id)
        ->whereDate('service_date', $targetDate)
        ->whereNotIn('status', [QueueStatus::Cancelled])
        ->count();

    return max(0, $this->daily_quota - $usedCount);
}
```

Tambah juga import di atas file:
```php
use App\Enums\QueueStatus;
```

**Step 2: Update ServiceResource — gunakan Model method**

```php
// app/Http/Resources/ServiceResource.php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'code' => $this->code,
        'slug' => $this->slug,
        'description' => $this->description,
        'requirements' => $this->requirements,
        'booking_enabled' => (bool) $this->booking_enabled,
        'daily_quota' => $this->daily_quota,
        'remaining_quota' => $this->resource->getRemainingQuota(), // Gunakan model method
    ];
}

// HAPUS private method computeRemainingQuota() — sudah pindah ke Model
```

**Step 3: Commit**

```bash
git add app/Models/Service.php app/Http/Resources/ServiceResource.php
git commit -m "refactor: move remaining quota calculation to Service model (DRY)"
```

---

## Phase 2: DRY — Model Scopes & Shared Validation

### Task 4: Tambah `scopeActive()` di Service Model

**Files:**
- Modify: `app/Models/Service.php`
- Modify: `app/Http/Controllers/Api/PublicServiceController.php`
- Modify: setiap file yang pakai `->where('is_active', true)->orderBy('sort_order')->orderBy('name')`

**Step 1: Tambah scope di Service Model**

```php
// app/Models/Service.php — tambah scope

use Illuminate\Database\Eloquent\Builder;

/**
 * Scope: hanya layanan aktif, diurutkan berdasarkan sort_order lalu nama.
 */
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name');
}
```

**Step 2: Ganti semua query manual dengan scope**

```php
// SEBELUM (di beberapa controller):
Service::query()
    ->where('is_active', true)
    ->orderBy('sort_order')
    ->orderBy('name')
    ->get();

// SESUDAH:
Service::active()->get();
```

File-file yang perlu diupdate:
- `app/Http/Controllers/Api/PublicServiceController.php` — method `index()`
- `app/Http/Controllers/PublicQueueController.php` — jika ada
- `app/Http/Controllers/FrontdeskQueueController.php` — jika ada
- `app/Http/Controllers/KioskController.php` — jika ada

**Step 3: Verifikasi scope digunakan konsisten**

Run: `grep -rn "where('is_active', true)" app/`
Expected: Hanya tersisa di `scopeActive()` method itu sendiri.

**Step 4: Commit**

```bash
git add app/Models/Service.php app/Http/Controllers/
git commit -m "refactor: extract Service::scopeActive() to eliminate DRY violation"
```

---

### Task 5: Tambah `isQuotaFull()` di Service Model — Eliminasi Duplikasi Quota Check

**Files:**
- Modify: `app/Models/Service.php`
- Modify: `app/Http/Requests/Api/StoreBookingRequest.php`
- Modify: `app/Http/Requests/StoreFrontdeskQueueTicketRequest.php`

**Step 1: Tambah method `isQuotaFull()` di Service Model**

```php
// app/Models/Service.php — tambah method

/**
 * Periksa apakah kuota harian sudah penuh untuk tanggal tertentu.
 * Mengembalikan false jika daily_quota null (unlimited).
 */
public function isQuotaFull(string $date): bool
{
    if ($this->daily_quota === null) {
        return false;
    }

    return $this->getRemainingQuota($date) <= 0;
}
```

**Step 2: Simplify StoreBookingRequest::withValidator()**

```php
// app/Http/Requests/Api/StoreBookingRequest.php

public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $service = Service::query()->find($this->integer('service_id'));

        if (! $service || ! $service->is_active) {
            $validator->errors()->add('service_id', 'Layanan tidak tersedia saat ini.');
            return;
        }

        if (! $service->booking_enabled) {
            $validator->errors()->add('service_id', 'Layanan ini tidak menerima pemesanan online.');
            return;
        }

        if ($service->isQuotaFull((string) $this->input('service_date'))) {
            $validator->errors()->add('service_date', 'Kuota harian untuk layanan ini sudah penuh.');
        }
    });
}
```

**Step 3: Simplify StoreFrontdeskQueueTicketRequest::withValidator()**

```php
// app/Http/Requests/StoreFrontdeskQueueTicketRequest.php

public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator): void {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $service = Service::query()->find($this->integer('service_id'));
        $channel = (string) $this->input('channel');

        if (! $service || ! $service->is_active) {
            $validator->errors()->add('service_id', 'Layanan tidak tersedia saat ini.');
            return;
        }

        if (in_array($channel, ['assisted_same_day', 'walk_in_kiosk'], true) && ! $service->walk_in_enabled) {
            $validator->errors()->add('service_id', 'Layanan ini tidak menerima antrean walk-in/frontdesk.');
            return;
        }

        if ($service->isQuotaFull((string) $this->input('service_date'))) {
            $validator->errors()->add('service_date', 'Kuota harian untuk layanan ini sudah penuh.');
        }
    });
}
```

**Step 4: Verifikasi tidak ada duplikasi quota query lagi**

Run: `grep -rn "todayCount" app/Http/Requests/`
Expected: Tidak ada hasil.

**Step 5: Commit**

```bash
git add app/Models/Service.php app/Http/Requests/
git commit -m "refactor: extract Service::isQuotaFull() to eliminate quota check duplication"
```

---

## Phase 3: Hardcoded Values & Inconsistency

### Task 6: Hapus InstitutionController Hardcoded — Gunakan config()

**Files:**
- Cari: `app/Http/Controllers/Api/InstitutionController.php` atau file yang hardcode institution data
- Referensi: `config/institution.php`

**Step 1: Periksa apakah InstitutionController terpisah dari PublicServiceController**

Run: `grep -rn "Pengadilan\|pa.penajam" app/Http/Controllers/`

Jika ditemukan di file selain `PublicServiceController`:
- Ganti dengan `return response()->json(config('institution'));`
- Atau hapus controller duplikat jika `PublicServiceController::institution()` sudah ada

Jika hanya ada di `PublicServiceController::institution()` dan sudah pakai config, maka skip task ini.

**Step 2: Verifikasi konsistensi**

Run: `grep -rn "config('institution')" app/`
Expected: Semua referensi institution menggunakan config.

**Step 3: Commit (jika ada perubahan)**

```bash
git add app/Http/Controllers/
git commit -m "fix: use config('institution') consistently, remove hardcoded data"
```

---

### Task 7: Tambah Scope `scopeNotCancelled()` di QueueTicket — Eliminasi Repeated whereNotIn

**Files:**
- Modify: `app/Models/QueueTicket.php`

**Step 1: Tambah scope**

```php
// app/Models/QueueTicket.php

/**
 * Scope: tiket yang belum dibatalkan.
 */
public function scopeNotCancelled(Builder $query): Builder
{
    return $query->whereNotIn('status', [QueueStatus::Cancelled]);
}

/**
 * Scope: tiket untuk layanan dan tanggal tertentu.
 */
public function scopeForServiceOnDate(Builder $query, int $serviceId, string $date): Builder
{
    return $query->where('service_id', $serviceId)
        ->whereDate('service_date', $date);
}
```

Tambah import `Builder`:
```php
use Illuminate\Database\Eloquent\Builder;
```

**Step 2: Update Service::getRemainingQuota() untuk pakai scope**

```php
// app/Models/Service.php — update method

public function getRemainingQuota(?string $date = null): ?int
{
    if ($this->daily_quota === null) {
        return null;
    }

    $targetDate = $date ?? today()->toDateString();

    $usedCount = QueueTicket::forServiceOnDate($this->id, $targetDate)
        ->notCancelled()
        ->count();

    return max(0, $this->daily_quota - $usedCount);
}
```

**Step 3: Commit**

```bash
git add app/Models/QueueTicket.php app/Models/Service.php
git commit -m "refactor: add QueueTicket scopes (notCancelled, forServiceOnDate)"
```

---

## Phase 4: Session Constants & Minor Cleanup

### Task 8: Ekstrak Session Keys ke Constants

**Files:**
- Create: `app/Enums/ModuleSession.php`
- Modify: `app/Http/Controllers/KioskController.php`
- Modify: `app/Http/Controllers/TvDisplayController.php`

**Step 1: Buat enum untuk session keys**

```php
<?php

namespace App\Enums;

/**
 * Konstanta session key untuk modul non-auth (kiosk, tv display).
 */
enum ModuleSession: string
{
    case KioskAuthenticated = 'kiosk_authenticated';
    case KioskAuthenticatedAt = 'kiosk_authenticated_at';
    case TvDisplayAuthenticated = 'tv_display_authenticated';
    case TvDisplayAuthenticatedAt = 'tv_display_authenticated_at';
}
```

**Step 2: Ganti hardcoded strings di KioskController**

```php
// SEBELUM:
session(['kiosk_authenticated' => true, 'kiosk_authenticated_at' => now()->timestamp]);

// SESUDAH:
session([
    ModuleSession::KioskAuthenticated->value => true,
    ModuleSession::KioskAuthenticatedAt->value => now()->timestamp,
]);
```

**Step 3: Ganti hardcoded strings di TvDisplayController (sama)**

**Step 4: Commit**

```bash
git add app/Enums/ModuleSession.php app/Http/Controllers/KioskController.php app/Http/Controllers/TvDisplayController.php
git commit -m "refactor: extract session keys to ModuleSession enum"
```

---

### Task 9: Cleanup — Hapus Import yang Tidak Digunakan & Konsistensi

**Files:**
- Semua file yang dimodifikasi di task sebelumnya

**Step 1: Jalankan Laravel Pint untuk format dan cleanup**

Run: `./vendor/bin/pint`

**Step 2: Verifikasi tidak ada error**

Run: `php artisan route:list --compact` (pastikan semua route masih valid)

**Step 3: Commit**

```bash
git add -A
git commit -m "chore: run pint, cleanup imports and formatting"
```

---

## Phase 5: Ringkasan & Verifikasi Akhir

### Task 10: Verifikasi Akhir

**Step 1: Jalankan semua test (jika ada)**

Run: `php artisan test`

**Step 2: Periksa route list lengkap**

Run: `php artisan route:list`

**Step 3: Periksa tidak ada error di build**

Run: `php artisan config:cache && php artisan route:cache`

**Step 4: Commit final tag**

```bash
git tag -a v1.1.0-refactor -m "Code quality refactor: DRY, KISS, SOLID improvements"
```

---

## Checklist Ringkasan

| # | Task | Severity | Principle | Status |
|---|------|----------|-----------|--------|
| 1 | Hapus route duplikasi | Critical | DRY | [ ] |
| 2 | QueueTicket::getQueuePosition() | Critical | DRY, N+1 | [ ] |
| 3 | Service::getRemainingQuota() | Critical | DRY, N+1 | [ ] |
| 4 | Service::scopeActive() | High | DRY | [ ] |
| 5 | Service::isQuotaFull() | High | DRY, KISS | [ ] |
| 6 | Hapus hardcoded institution | Medium | DIP | [ ] |
| 7 | QueueTicket scopes | Medium | DRY | [ ] |
| 8 | ModuleSession enum | Low | DRY, DIP | [ ] |
| 9 | Pint cleanup | Low | KISS | [ ] |
| 10 | Verifikasi akhir | - | - | [ ] |

**Estimasi total task: 10 tasks, masing-masing 2-5 menit**
