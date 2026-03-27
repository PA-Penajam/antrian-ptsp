# Desain: Printer Status Indicator untuk Kiosk Legacy

**Tanggal:** 2026-03-27
**Scope:** Hanya `kiosk-legacy` — Livewire kiosk tidak terpengaruh

---

## Latar Belakang

Kiosk legacy menggunakan server-side print proxy (sejak implementasi sebelumnya). Print berhasil atau gagal baru diketahui setelah pengunjung submit form booking. Tidak ada indikasi visual apakah printer sedang terhubung atau tidak sebelum pengunjung memulai proses.

Solusi: tambahkan status bar permanen di bagian bawah halaman kiosk-legacy yang menampilkan status koneksi printer secara real-time dengan polling periodik.

---

## Arsitektur

```
Kiosk Browser (setiap 30 detik)
    │
    │  GET /kiosk-legacy/printer-status
    ▼
KioskController::printerStatusLegacy()
    │
    ├─► CheckPrinterConnectivity   — HTTP HEAD ke printer, timeout 5 detik
    │       └─ http://{ip}:{port}/
    │
    └─► JSON: { status, ip, port, checked_at, error? }
```

**Prinsip:**
- `CheckPrinterConnectivity` independent dari `PrintTicketToEposPrinter` — SRP terjaga
- Endpoint hanya dapat diakses dengan session kiosk aktif (middleware yang sudah ada)
- Jika `THERMAL_PRINTER_ENABLED=false`, endpoint return status `disabled` tanpa konek ke printer

---

## Komponen

### 1. `app/Actions/Queue/CheckPrinterConnectivity.php` (baru)

- **Input:** tidak ada (baca langsung dari config)
- **Output:** `array{ connected: bool, error: ?string }`
- Cek `THERMAL_PRINTER_ENABLED` — jika false, return `connected: false, error: 'Printer tidak diaktifkan di konfigurasi'`
- HTTP GET ke `http://{ip}:{port}/` via Guzzle dengan timeout 5 detik
- Return `connected: true` jika **response HTTP diterima (status apapun, termasuk 4xx/5xx)** — berarti server printer reachable
- Tangkap semua exception (timeout, connection refused) — return `connected: false` dengan pesan error dari exception
- Tidak melempar exception ke luar (graceful)

### 2. `KioskController::printerStatusLegacy()` (baru)

- Inject `CheckPrinterConnectivity $checker`
- Panggil `$checker->handle()`
- Response JSON:
```json
{
  "status": "connected" | "disconnected" | "disabled",
  "ip": "192.168.10.27",
  "port": 8008,
  "checked_at": "2026-03-27T09:42:28+08:00",
  "error": null | "Connection timed out"
}
```

### 3. Route baru di `routes/web.php`

```php
Route::get('/kiosk-legacy/printer-status', [KioskController::class, 'printerStatusLegacy'])
    ->middleware('kiosk.session')
    ->name('kiosk.legacy.printer-status');
```

### 4. `resources/views/pages/kiosk/legacy.blade.php` (dimodifikasi)

**Elemen baru:** status bar di atas footer, di bawah konten utama:

```html
<div id="printerStatusBar" class="printer-status-bar bar-checking" onclick="showPrinterFlash()">
  <span id="printerDot" class="dot dot-yellow"></span>
  <span id="printerLabel">MEMERIKSA KONEKSI PRINTER...</span>
  <span class="bar-tap-hint">ketuk untuk detail</span>
</div>

<div id="printerFlash" class="printer-flash">
  <!-- diisi oleh JS -->
</div>
```

**JavaScript:**
- `checkPrinterStatus()` — AJAX GET ke route `kiosk.legacy.printer-status`, update bar + simpan data untuk flash
- `showPrinterFlash()` — tampilkan flash message berisi detail, auto-hilang 3.5 detik
- Saat page load: `checkPrinterStatus()` langsung dipanggil
- `setInterval(checkPrinterStatus, 30000)` — polling setiap 30 detik
- Saat polling dimulai: bar berubah kuning ("MEMERIKSA...")
- Saat response diterima: bar berubah hijau atau merah

---

## Visual

### Status Bar (selalu terlihat)

| State | Warna | Label |
|---|---|---|
| Memeriksa | Kuning (animasi berkedip) | `MEMERIKSA KONEKSI PRINTER...` |
| Terhubung | Hijau (animasi pulsing) | `PRINTER SIAP CETAK` |
| Tidak terhubung | Merah (statis) | `PRINTER TIDAK MERESPONS` |
| Disabled | Merah (statis) | `PRINTER TIDAK AKTIF` |

### Flash Message (muncul saat bar diklik/diketuk, hilang otomatis 3.5 detik)

**State hijau:**
```
Status      : Terhubung
Alamat      : 192.168.10.27:8008
Terakhir cek: 09:42:28 (2 detik lalu)
Cek berikut : ±28 detik lagi
```

**State merah:**
```
Status      : Tidak terhubung
Alamat      : 192.168.10.27:8008
Penyebab    : Connection timed out
Terakhir cek: 09:42:28 (2 detik lalu)
─────────────────────────────────────
Tiket tetap dibuat · Hubungi petugas untuk cetak manual
```

**State kuning:**
```
Status      : Memeriksa...
Alamat      : 192.168.10.27:8008
Timeout     : 5 detik
```

---

## Mapping Error Messages

| Kondisi | Ditampilkan di flash |
|---|---|
| Printer mati / tidak terjangkau | `Connection timed out` |
| Port tertutup | `Connection refused` |
| HTTP non-2xx dari printer | `HTTP {status} {reason}` |
| Config disabled | `Printer tidak diaktifkan di konfigurasi` |

---

## Testing

### Unit Test: `CheckPrinterConnectivity`

- Config disabled → return `connected: false`, error berisi pesan konfigurasi
- HTTP response diterima (status apapun, termasuk 4xx/5xx) → return `connected: true`, error null
- `ConnectionException` / timeout → return `connected: false`, error berisi pesan timeout
- `ConnectException` (port tertutup) → return `connected: false`, error berisi "Connection refused"

### Feature Test: `KioskController::printerStatusLegacy`

- Tanpa session kiosk → redirect ke login (middleware)
- Dengan session valid → response JSON memiliki field `status`, `ip`, `port`, `checked_at`
- Saat `CheckPrinterConnectivity` return connected → `status: 'connected'`
- Saat `CheckPrinterConnectivity` return tidak connected → `status: 'disconnected'`, ada field `error`

---

## Konfigurasi

Menggunakan config yang sudah ada di `config/services.php`:
```php
'thermal_printer' => [
    'enabled'   => env('THERMAL_PRINTER_ENABLED', false),
    'ip'        => env('THERMAL_PRINTER_IP', '192.168.1.100'),
    'port'      => env('THERMAL_PRINTER_PORT', 8008),
    'device_id' => env('THERMAL_PRINTER_DEVICE_ID', 'local_printer'),
]
```

Jika `THERMAL_PRINTER_ENABLED=false`:
- Status bar tetap tampil dengan state merah
- Flash menampilkan "Printer tidak diaktifkan di konfigurasi"
- Tidak ada request HTTP ke printer

---

## Yang Tidak Termasuk Scope

- Integrasi WhatsApp (diputuskan ditunda)
- Status indicator di halaman kiosk Livewire
- Notifikasi ke admin saat printer offline
