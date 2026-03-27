# Kiosk Legacy Printer Status Indicator — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tambahkan status bar permanen di bagian bawah halaman kiosk-legacy yang menampilkan status koneksi printer (hijau/kuning/merah) secara real-time dengan polling setiap 30 detik, lengkap dengan flash message detail saat diklik.

**Architecture:** Server-side endpoint `GET /kiosk-legacy/printer-status` melakukan HTTP GET ke printer (any response = connected, exception = disconnected). JavaScript di halaman polling setiap 30 detik dan memperbarui bottom status bar. Flash message muncul saat bar diklik, hilang otomatis 3.5 detik.

**Tech Stack:** PHP 8.4 / Laravel 12, Pest 4, Guzzle HTTP (via Laravel Http facade), jQuery (sudah ada di legacy page), `Http::fake()` untuk testing.

---

## File Structure

| File | Status | Tanggung jawab |
|---|---|---|
| `app/Actions/Queue/CheckPrinterConnectivity.php` | **Baru** | HTTP GET ke printer, return `[connected, error]` |
| `tests/Unit/Actions/CheckPrinterConnectivityTest.php` | **Baru** | Unit test untuk action di atas |
| `app/Http/Controllers/KioskController.php` | **Modifikasi** | Tambah method `printerStatusLegacy()` |
| `routes/web.php` | **Modifikasi** | Tambah GET route di dalam group `module.password:kiosk-legacy` |
| `resources/views/pages/kiosk/legacy.blade.php` | **Modifikasi** | CSS + HTML status bar + JS polling + flash |
| `tests/Feature/Kiosk/KioskLegacyTest.php` | **Modifikasi** | Feature test endpoint + view test |

---

## Task 1: `CheckPrinterConnectivity` Action + Unit Tests

**Files:**
- Create: `app/Actions/Queue/CheckPrinterConnectivity.php`
- Create: `tests/Unit/Actions/CheckPrinterConnectivityTest.php`

### Konteks

Action ini mirip dengan `PrintTicketToEposPrinter` yang sudah ada tapi lebih sederhana: hanya HTTP GET ke root URL printer. Berbeda dari `PrintTicketToEposPrinter` yang mengirim SOAP XML dan return `bool`, action ini mengembalikan array dengan `connected` dan `error`. Epson ePOS adalah SOAP service — response 4xx/5xx dari root URL tetap berarti printer reachable.

- [ ] **Step 1: Buat file test**

```bash
php artisan make:test --pest --unit Actions/CheckPrinterConnectivityTest
```

Expected: `tests/Unit/Actions/CheckPrinterConnectivityTest.php` terbuat.

- [ ] **Step 2: Tulis semua failing tests**

Ganti seluruh isi `tests/Unit/Actions/CheckPrinterConnectivityTest.php` dengan:

```php
<?php

use App\Actions\Queue\CheckPrinterConnectivity;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.thermal_printer.enabled' => true,
        'services.thermal_printer.ip'      => '192.168.10.27',
        'services.thermal_printer.port'    => 8008,
    ]);
});

it('returns connected false immediately when printer is disabled in config', function () {
    config(['services.thermal_printer.enabled' => false]);
    Http::fake();

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeFalse()
        ->and($result['error'])->toContain('tidak diaktifkan');
    Http::assertNothingSent();
});

it('returns connected true when printer responds with 200', function () {
    Http::fake(['*' => Http::response('', 200)]);

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeTrue()
        ->and($result['error'])->toBeNull();
});

it('returns connected true even when printer responds with 404', function () {
    Http::fake(['*' => Http::response('Not Found', 404)]);

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeTrue()
        ->and($result['error'])->toBeNull();
});

it('returns connected false with error message when connection times out', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: Operation timed out');
    });

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeFalse()
        ->and($result['error'])->toContain('timed out');
});

it('returns connected false with error message when connection is refused', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('cURL error 7: Failed to connect');
    });

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeFalse()
        ->and($result['error'])->not->toBeNull();
});

it('sends get request to root url of printer', function () {
    Http::fake(['*' => Http::response('', 200)]);

    app(CheckPrinterConnectivity::class)->handle();

    Http::assertSent(function (Request $request) {
        return $request->url() === 'http://192.168.10.27:8008/'
            && $request->method() === 'GET';
    });
});
```

- [ ] **Step 3: Jalankan tests — pastikan FAIL**

```bash
php artisan test --compact --filter CheckPrinterConnectivity
```

Expected: 6 failed dengan "Class not found".

- [ ] **Step 4: Buat action `CheckPrinterConnectivity`**

```bash
php artisan make:class app/Actions/Queue/CheckPrinterConnectivity
```

Ganti seluruh isi `app/Actions/Queue/CheckPrinterConnectivity.php` dengan:

```php
<?php

namespace App\Actions\Queue;

use Illuminate\Support\Facades\Http;

class CheckPrinterConnectivity
{
    /**
     * Periksa apakah printer thermal dapat dijangkau dari server.
     *
     * @return array{ connected: bool, error: ?string }
     */
    public function handle(): array
    {
        if (! config('services.thermal_printer.enabled')) {
            return ['connected' => false, 'error' => 'Printer tidak diaktifkan di konfigurasi'];
        }

        $ip   = config('services.thermal_printer.ip');
        $port = config('services.thermal_printer.port');
        $url  = "http://{$ip}:{$port}/";

        try {
            Http::timeout(5)->get($url);

            return ['connected' => true, 'error' => null];
        } catch (\Throwable $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }
}
```

- [ ] **Step 5: Jalankan tests — pastikan PASS**

```bash
php artisan test --compact --filter CheckPrinterConnectivity
```

Expected: `Tests: 6 passed`.

- [ ] **Step 6: Jalankan full test suite — pastikan tidak ada regresi**

```bash
php artisan test --compact
```

Expected: semua tests pass.

- [ ] **Step 7: Format dan commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Queue/CheckPrinterConnectivity.php tests/Unit/Actions/CheckPrinterConnectivityTest.php
git commit -m "feat(kiosk-legacy): tambah CheckPrinterConnectivity action"
```

---

## Task 2: Route + Controller Method + Feature Tests

**Files:**
- Modify: `app/Http/Controllers/KioskController.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/Kiosk/KioskLegacyTest.php`

### Konteks

Route baru `kiosk.legacy.printer-status` harus berada di dalam group `module.password:kiosk-legacy` yang sudah ada di `routes/web.php` baris 101–104. Controller method inject `CheckPrinterConnectivity` dan menentukan status `connected` | `disconnected` | `disabled`. Status `disabled` ditentukan dari config di controller, bukan dari action.

Pola mock di test mengikuti test `KioskLegacyTest.php` yang sudah ada (sudah ada helper `kioskLegacySession()` dan import `MockInterface`).

- [ ] **Step 1: Tulis failing feature tests**

Tambahkan di akhir `tests/Feature/Kiosk/KioskLegacyTest.php`:

```php
// ── Printer Status Endpoint ───────────────────────────────────────────────

it('printer status endpoint requires kiosk session', function () {
    $this->get(route('kiosk.legacy.printer-status'))
        ->assertRedirect(route('kiosk.legacy.login'));
});

it('printer status returns json with required fields when connected', function () {
    config([
        'services.thermal_printer.enabled' => true,
        'services.thermal_printer.ip'      => '192.168.10.27',
        'services.thermal_printer.port'    => 8008,
    ]);

    $this->mock(CheckPrinterConnectivity::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->once()->andReturn(['connected' => true, 'error' => null]);
    });

    withSession(kioskLegacySession())
        ->get(route('kiosk.legacy.printer-status'))
        ->assertOk()
        ->assertJsonStructure(['status', 'ip', 'port', 'checked_at', 'error'])
        ->assertJson([
            'status' => 'connected',
            'ip'     => '192.168.10.27',
            'port'   => 8008,
            'error'  => null,
        ]);
});

it('printer status returns disconnected with error when connectivity check fails', function () {
    config(['services.thermal_printer.enabled' => true]);

    $this->mock(CheckPrinterConnectivity::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->once()->andReturn([
            'connected' => false,
            'error'     => 'cURL error 28: Operation timed out',
        ]);
    });

    withSession(kioskLegacySession())
        ->get(route('kiosk.legacy.printer-status'))
        ->assertOk()
        ->assertJson([
            'status' => 'disconnected',
            'error'  => 'cURL error 28: Operation timed out',
        ]);
});

it('printer status returns disabled when thermal printer config is off', function () {
    config(['services.thermal_printer.enabled' => false]);

    withSession(kioskLegacySession())
        ->get(route('kiosk.legacy.printer-status'))
        ->assertOk()
        ->assertJson(['status' => 'disabled']);
});
```

Pastikan `use App\Actions\Queue\CheckPrinterConnectivity;` ada di bagian atas file. Jika belum, tambahkan.

- [ ] **Step 2: Jalankan tests — pastikan FAIL**

```bash
php artisan test --compact --filter "printer status"
```

Expected: failed dengan "Route [kiosk.legacy.printer-status] not defined".

- [ ] **Step 3: Tambah route di `routes/web.php`**

Cari blok berikut di `routes/web.php`:

```php
Route::middleware('module.password:kiosk-legacy')->group(function () {
    Route::get('/kiosk-legacy', [KioskController::class, 'legacy'])->name('kiosk.legacy');
    Route::post('/kiosk-legacy/print', [KioskController::class, 'printLegacy'])->name('kiosk.legacy.print');
});
```

Ganti dengan:

```php
Route::middleware('module.password:kiosk-legacy')->group(function () {
    Route::get('/kiosk-legacy', [KioskController::class, 'legacy'])->name('kiosk.legacy');
    Route::post('/kiosk-legacy/print', [KioskController::class, 'printLegacy'])->name('kiosk.legacy.print');
    Route::get('/kiosk-legacy/printer-status', [KioskController::class, 'printerStatusLegacy'])->name('kiosk.legacy.printer-status');
});
```

- [ ] **Step 4: Tambah method di `KioskController`**

Tambahkan import di bagian `use` pada `app/Http/Controllers/KioskController.php`:

```php
use App\Actions\Queue\CheckPrinterConnectivity;
```

Tambahkan method berikut setelah method `printLegacy()`:

```php
public function printerStatusLegacy(CheckPrinterConnectivity $checker): JsonResponse
{
    $enabled = (bool) config('services.thermal_printer.enabled');
    $result  = $checker->handle();

    if (! $enabled) {
        $status = 'disabled';
    } elseif ($result['connected']) {
        $status = 'connected';
    } else {
        $status = 'disconnected';
    }

    return response()->json([
        'status'     => $status,
        'ip'         => (string) config('services.thermal_printer.ip'),
        'port'       => (int) config('services.thermal_printer.port'),
        'checked_at' => now()->toIso8601String(),
        'error'      => $result['error'],
    ]);
}
```

- [ ] **Step 5: Jalankan tests — pastikan PASS**

```bash
php artisan test --compact --filter "printer status"
```

Expected: `Tests: 4 passed`.

- [ ] **Step 6: Jalankan full test suite — pastikan tidak ada regresi**

```bash
php artisan test --compact
```

Expected: semua tests pass.

- [ ] **Step 7: Format dan commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/web.php app/Http/Controllers/KioskController.php tests/Feature/Kiosk/KioskLegacyTest.php
git commit -m "feat(kiosk-legacy): tambah endpoint printer-status dan controller method"
```

---

## Task 3: Status Bar UI di `legacy.blade.php`

**Files:**
- Modify: `resources/views/pages/kiosk/legacy.blade.php`
- Modify: `tests/Feature/Kiosk/KioskLegacyTest.php`

### Konteks

File `legacy.blade.php` menggunakan jQuery (sudah tersedia di layout). Struktur halaman:
- `@push('styles')` berakhir di baris 453 (tepat sebelum `@endpush` pertama)
- Konten utama diakhiri dengan Alert Overlay, lalu FOOTER, lalu `#kioskLegacyConfig`
- `@push('scripts')` berakhir di baris 900

Status bar disisipkan **antara Alert Overlay dan FOOTER**. Flash message menggunakan `position: fixed` agar muncul di atas seluruh konten. JS variabel baru ditambahkan di awal blok `@push('scripts')`.

**Catatan keamanan:** Semua nilai dinamis di `buildPrinterFlashHtml()` (IP, error message) di-escape dengan fungsi `escPrinterHtml()` sebelum dimasukkan ke `innerHTML`, untuk mencegah XSS.

- [ ] **Step 1: Tulis failing view test**

Tambahkan di akhir `tests/Feature/Kiosk/KioskLegacyTest.php`:

```php
// ── Status Bar View ───────────────────────────────────────────────────────

it('renders printer status bar with polling js in legacy page', function () {
    Service::factory()->create(['is_active' => true, 'walk_in_enabled' => true]);

    withSession(kioskLegacySession())
        ->get(route('kiosk.legacy'))
        ->assertOk()
        ->assertSee('printerStatusBar', false)
        ->assertSee('checkPrinterStatus', false)
        ->assertSee('showPrinterFlash', false)
        ->assertSee('kiosk.legacy.printer-status', false);
});
```

- [ ] **Step 2: Jalankan test — pastikan FAIL**

```bash
php artisan test --compact --filter "renders printer status bar"
```

Expected: FAIL karena `printerStatusBar` belum ada di view.

- [ ] **Step 3: Tambah CSS di blok `@push('styles')`**

Tambahkan CSS berikut **tepat sebelum** `@endpush` pertama (baris 453) di `legacy.blade.php`:

```css
    /* ═══ PRINTER STATUS BAR ═══ */
    .printer-status-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 7px 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-top: 1px solid rgba(255,255,255,0.07);
        cursor: pointer;
        user-select: none;
        flex-shrink: 0;
        transition: filter 0.15s;
    }
    .printer-status-bar:hover  { filter: brightness(1.2); }
    .printer-status-bar:active { filter: brightness(0.85); }
    .printer-status-bar.bar-checking { background: rgba(213,216,61,0.10); color: #c8cb30; }
    .printer-status-bar.bar-ok       { background: rgba(53,210,154,0.10); color: #35D29A; }
    .printer-status-bar.bar-err      { background: rgba(249,102,110,0.13); color: #F9666E; }

    .printer-status-bar .ps-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .printer-status-bar.bar-checking .ps-dot { background: #c8cb30; animation: psDotY 0.9s infinite; }
    .printer-status-bar.bar-ok       .ps-dot { background: #35D29A; animation: psDotG 2s infinite; }
    .printer-status-bar.bar-err      .ps-dot { background: #F9666E; }

    @keyframes psDotG { 0%,100%{box-shadow:0 0 0 0 rgba(53,210,154,0.5)} 50%{box-shadow:0 0 0 5px rgba(53,210,154,0)} }
    @keyframes psDotY { 0%,100%{box-shadow:0 0 0 0 rgba(213,216,61,0.5)} 50%{box-shadow:0 0 0 5px rgba(213,216,61,0)} }

    .ps-tap-hint { font-size: 8px; font-weight: 500; opacity: 0.32; margin-left: 4px; }

    .printer-flash {
        position: fixed;
        bottom: 46px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(18,18,38,0.97);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 10px;
        color: #fff;
        white-space: nowrap;
        z-index: 9998;
        display: none;
        min-width: 260px;
        pointer-events: none;
    }
    .printer-flash .pf-row { display: flex; align-items: baseline; gap: 8px; margin-bottom: 4px; }
    .printer-flash .pf-row:last-child { margin-bottom: 0; }
    .printer-flash .pf-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.4; min-width: 72px; }
    .printer-flash .pf-val   { font-weight: 600; font-size: 10px; }
    .printer-flash .pf-val.ok   { color: #35D29A; }
    .printer-flash .pf-val.warn { color: #c8cb30; }
    .printer-flash .pf-val.err  { color: #F9666E; }
    .printer-flash .pf-hr  { border: none; border-top: 1px solid rgba(255,255,255,0.08); margin: 5px 0; }
    .printer-flash .pf-hint { font-size: 8.5px; color: rgba(255,165,90,0.85); }
```

- [ ] **Step 4: Tambah HTML status bar dan flash**

Cari blok berikut di `legacy.blade.php`:

```blade
    {{-- FOOTER --}}
    <div class="py-7 text-center flex-shrink-0">
```

Tambahkan HTML berikut **tepat sebelum** blok itu:

```blade
    {{-- PRINTER STATUS BAR --}}
    <div id="printerFlash" class="printer-flash"></div>
    <div id="printerStatusBar" class="printer-status-bar bar-checking" onclick="showPrinterFlash()">
        <span class="ps-dot"></span>
        <span id="printerLabel">MEMERIKSA KONEKSI PRINTER...</span>
        <span class="ps-tap-hint">ketuk untuk detail</span>
    </div>
```

- [ ] **Step 5: Tambah `data-status-url` ke `#kioskLegacyConfig`**

Cari:

```blade
    <div id="kioskLegacyConfig"
         class="d-none"
         data-print-url="{{ route('kiosk.legacy.print') }}"></div>
```

Ganti dengan:

```blade
    <div id="kioskLegacyConfig"
         class="d-none"
         data-print-url="{{ route('kiosk.legacy.print') }}"
         data-status-url="{{ route('kiosk.legacy.printer-status') }}"></div>
```

- [ ] **Step 6: Tambah variabel JS di awal `@push('scripts')`**

Cari baris pertama di dalam `@push('scripts')`:

```javascript
    var cdInterval = null;
```

Tambahkan tepat **sebelum** baris itu:

```javascript
    var kioskStatusUrl     = kioskLegacyConfig ? kioskLegacyConfig.dataset.statusUrl : '';
    var printerLastData    = null;
    var printerNextCheckAt = null;
    var printerCurrentState = 'checking';
    var printerFlashTimer  = null;
```

- [ ] **Step 7: Tambah fungsi-fungsi JS printer**

Cari fungsi `updateKioskClock` di dalam `@push('scripts')`:

```javascript
    function updateKioskClock() {
```

Tambahkan fungsi-fungsi berikut **tepat sebelum** fungsi itu:

```javascript
    function escPrinterHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(String(str || '')));
        return d.innerHTML;
    }

    function checkPrinterStatus() {
        setPrinterBar('checking');
        printerNextCheckAt = Date.now() + 30000;

        $.ajax({
            url: kioskStatusUrl,
            type: 'GET',
            success: function (data) {
                printerLastData = data;
                printerLastData._checkedAt = new Date();
                setPrinterBar(data.status === 'connected' ? 'connected' : 'offline');
            },
            error: function () {
                printerLastData = null;
                setPrinterBar('offline');
            }
        });
    }

    function setPrinterBar(state) {
        printerCurrentState = state;
        var bar   = document.getElementById('printerStatusBar');
        var label = document.getElementById('printerLabel');
        if (!bar || !label) { return; }

        bar.className = 'printer-status-bar';
        if (state === 'checking') {
            bar.classList.add('bar-checking');
            label.textContent = 'MEMERIKSA KONEKSI PRINTER...';
        } else if (state === 'connected') {
            bar.classList.add('bar-ok');
            label.textContent = 'PRINTER SIAP CETAK';
        } else {
            bar.classList.add('bar-err');
            var isDisabled = printerLastData && printerLastData.status === 'disabled';
            label.textContent = isDisabled ? 'PRINTER TIDAK AKTIF' : 'PRINTER TIDAK MERESPONS';
        }
    }

    function showPrinterFlash() {
        var flash = document.getElementById('printerFlash');
        if (!flash) { return; }

        var d = printerLastData;
        var html = '';

        if (printerCurrentState === 'checking' || !d) {
            var addr = d ? escPrinterHtml(d.ip + ':' + d.port) : '---';
            html = '<div class="pf-row"><span class="pf-label">Status</span><span class="pf-val warn">Memeriksa...</span></div>' +
                   '<div class="pf-row"><span class="pf-label">Alamat</span><span class="pf-val">' + addr + '</span></div>' +
                   '<div class="pf-row"><span class="pf-label">Timeout</span><span class="pf-val">5 detik</span></div>';
        } else {
            var addr      = escPrinterHtml(d.ip + ':' + d.port);
            var checkedAt = d._checkedAt ? formatPrinterTime(d._checkedAt) : '-';
            var secsAgo   = d._checkedAt ? Math.round((Date.now() - d._checkedAt.getTime()) / 1000) : 0;
            var secsLeft  = printerNextCheckAt ? Math.max(0, Math.round((printerNextCheckAt - Date.now()) / 1000)) : '-';

            if (d.status === 'connected') {
                html = '<div class="pf-row"><span class="pf-label">Status</span><span class="pf-val ok">Terhubung</span></div>' +
                       '<div class="pf-row"><span class="pf-label">Alamat</span><span class="pf-val">' + addr + '</span></div>' +
                       '<div class="pf-row"><span class="pf-label">Terakhir cek</span><span class="pf-val">' + escPrinterHtml(checkedAt) + ' (' + secsAgo + 's lalu)</span></div>' +
                       '<div class="pf-row"><span class="pf-label">Cek berikut</span><span class="pf-val">&#177;' + secsLeft + ' detik lagi</span></div>';
            } else {
                var errRow  = d.error ? '<div class="pf-row"><span class="pf-label">Penyebab</span><span class="pf-val err">' + escPrinterHtml(d.error) + '</span></div>' : '';
                var hint    = d.status !== 'disabled'
                    ? '<hr class="pf-hr"><div class="pf-hint">Tiket tetap dibuat &middot; Hubungi petugas untuk cetak manual</div>'
                    : '';
                var statVal = d.status === 'disabled' ? 'Tidak aktif' : 'Tidak terhubung';
                html = '<div class="pf-row"><span class="pf-label">Status</span><span class="pf-val err">' + statVal + '</span></div>' +
                       '<div class="pf-row"><span class="pf-label">Alamat</span><span class="pf-val">' + addr + '</span></div>' +
                       errRow +
                       '<div class="pf-row"><span class="pf-label">Terakhir cek</span><span class="pf-val">' + escPrinterHtml(checkedAt) + ' (' + secsAgo + 's lalu)</span></div>' +
                       hint;
            }
        }

        flash.innerHTML = html;
        flash.style.display = 'block';

        clearTimeout(printerFlashTimer);
        printerFlashTimer = setTimeout(function () {
            flash.style.display = 'none';
        }, 3500);
    }

    function formatPrinterTime(date) {
        return ('0' + date.getHours()).slice(-2) + ':' +
               ('0' + date.getMinutes()).slice(-2) + ':' +
               ('0' + date.getSeconds()).slice(-2);
    }
```

- [ ] **Step 8: Panggil `checkPrinterStatus` saat page load dan setup polling**

Cari di dalam `$(document).ready(function () {`:

```javascript
        updateKioskClock();
        setInterval(updateKioskClock, 1000);
```

Tambahkan **tepat setelah** baris `setInterval(updateKioskClock, 1000);`:

```javascript
        if (kioskStatusUrl) {
            checkPrinterStatus();
            setInterval(checkPrinterStatus, 30000);
        }
```

- [ ] **Step 9: Jalankan view test — pastikan PASS**

```bash
php artisan test --compact --filter "renders printer status bar"
```

Expected: `Tests: 1 passed`.

- [ ] **Step 10: Jalankan full test suite — pastikan tidak ada regresi**

```bash
php artisan test --compact
```

Expected: semua tests pass.

- [ ] **Step 11: Format dan commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/pages/kiosk/legacy.blade.php tests/Feature/Kiosk/KioskLegacyTest.php
git commit -m "feat(kiosk-legacy): tambah printer status bar dengan polling dan flash detail"
```

---

## Verifikasi Akhir

- [ ] Jalankan full test suite sekali lagi:

```bash
php artisan test --compact
```

Expected: semua tests pass, 0 failures.

- [ ] Push ke remote:

```bash
git push origin main
```
