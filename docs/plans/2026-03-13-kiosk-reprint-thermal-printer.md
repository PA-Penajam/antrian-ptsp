# Kiosk Ticket Reprint + Epson Thermal Printer Integration

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Tambahkan fitur cetak ulang tiket di kiosk berdasarkan KTP/HP, dan integrasikan pencetakan thermal langsung ke printer Epson TM-M30II via ePOS SDK JavaScript (ESC/POS native, bukan HTML).

**Architecture:** Fitur dibagi 2 bagian independen: (1) Reprint flow di KioskBooking Livewire component — menambah mode pencarian tiket berdasarkan `visitor_identifier` atau `visitor_phone` untuk tiket hari ini, (2) Thermal printer service — JavaScript module yang menggunakan Epson ePOS SDK untuk kirim perintah ESC/POS langsung ke printer via WebSocket port 8043. Kedua bagian terhubung: saat tiket ditemukan (reprint) atau dibuat (step 4), auto-print ke thermal.

**Tech Stack:** Laravel 12, Livewire 4, Pest 4, Epson ePOS SDK for JavaScript v2.27.0, ESC/POS commands, Alpine.js

---

## Phase 1: Konfigurasi Printer

### Task 1: Tambah konfigurasi thermal printer

**Files:**
- Modify: `config/services.php`
- Modify: `.env.example` (jika ada, atau dokumentasikan saja)

**Step 1: Tambah konfigurasi printer di config/services.php**

Tambahkan di akhir array, sebelum `];`:

```php
'thermal_printer' => [
    'enabled' => (bool) env('THERMAL_PRINTER_ENABLED', false),
    'ip' => env('THERMAL_PRINTER_IP', '192.168.1.100'),
    'port' => (int) env('THERMAL_PRINTER_PORT', 8043),
    'device_id' => env('THERMAL_PRINTER_DEVICE_ID', 'local_printer'),
],
```

**Step 2: Tambah env variables di `.env`**

```
THERMAL_PRINTER_ENABLED=true
THERMAL_PRINTER_IP=192.168.1.100
THERMAL_PRINTER_PORT=8043
THERMAL_PRINTER_DEVICE_ID=local_printer
```

**Step 3: Commit**

```bash
git add config/services.php .env.example
git commit -m "config: add thermal printer configuration for Epson TM-M30II"
```

---

## Phase 2: Epson ePOS SDK Setup

### Task 2: Tambah Epson ePOS SDK dan buat thermal printer JS module

**Files:**
- Create: `public/vendor/epson/epos-2.27.0.js` (download dari Epson)
- Create: `resources/js/thermal-printer.js`
- Modify: `resources/views/layouts/kiosk.blade.php`

**Step 1: Download dan tempatkan Epson ePOS SDK**

Download SDK dari portal Epson dan copy file `epos-2.27.0.js` ke `public/vendor/epson/`. Karena SDK ini tidak kompatibel dengan strict mode / ES modules, harus di-load via `<script>` tag biasa (bukan via Vite).

Jika file SDK belum tersedia, buat placeholder:
```bash
mkdir -p public/vendor/epson
touch public/vendor/epson/epos-2.27.0.js
```

**Step 2: Buat thermal printer JavaScript module**

File: `resources/js/thermal-printer.js`

```javascript
/**
 * Modul thermal printer untuk Epson TM-M30II via ePOS SDK.
 * Menggunakan ESC/POS native commands, bukan HTML rendering.
 */
window.ThermalPrinter = function (config) {
    return {
        ePosDev: null,
        printer: null,
        connected: false,
        ip: config.ip,
        port: config.port,
        deviceId: config.deviceId,
        enabled: config.enabled,
        institutionName: config.institutionName || 'PTSP',

        init() {
            if (!this.enabled || typeof epson === 'undefined') {
                console.warn('[ThermalPrinter] Printer tidak aktif atau SDK belum dimuat.');
                return;
            }
            this.connect();
        },

        connect() {
            this.ePosDev = new epson.ePOSDevice();
            this.ePosDev.connect(this.ip, this.port, (code) => {
                if (code === 'OK' || code === 'SSL_CONNECT_OK') {
                    this.ePosDev.createDevice(
                        this.deviceId,
                        this.ePosDev.DEVICE_TYPE_PRINTER,
                        { crypto: false, buffer: false },
                        (deviceObj, code) => {
                            if (code === 'OK') {
                                this.printer = deviceObj;
                                this.connected = true;
                                console.log('[ThermalPrinter] Terhubung ke printer.');
                            } else {
                                console.error('[ThermalPrinter] createDevice gagal:', code);
                            }
                        }
                    );
                } else {
                    console.error('[ThermalPrinter] Koneksi gagal:', code);
                }
            });
        },

        /**
         * Cetak tiket antrian ke thermal printer 80mm.
         * Format ESC/POS native — 42 karakter per baris.
         *
         * @param {Object} ticket - { ticketNumber, serviceName, visitorName, serviceDate, status }
         */
        printTicket(ticket) {
            if (!this.connected || !this.printer) {
                console.warn('[ThermalPrinter] Printer tidak terhubung. Cetak dibatalkan.');
                return false;
            }

            const prn = this.printer;
            const separator = '──────────────────────────────────────────';
            const now = new Date();
            const timestamp = now.toLocaleDateString('id-ID', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            }) + ' ' + now.toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit'
            });

            prn.addTextLang('en');
            prn.addTextSmooth(true);

            // Header institusi
            prn.addTextAlign(prn.ALIGN_CENTER);
            prn.addTextSize(1, 1);
            prn.addTextStyle(false, false, true, prn.COLOR_1);
            prn.addText(this.institutionName + '\n');
            prn.addTextStyle(false, false, false, prn.COLOR_1);
            prn.addText('Sistem Pelayanan Terpadu Satu Pintu\n');
            prn.addText(separator + '\n');

            // Nomor tiket besar
            prn.addFeedLine(1);
            prn.addTextSize(3, 3);
            prn.addTextStyle(false, false, true, prn.COLOR_1);
            prn.addText(ticket.ticketNumber + '\n');
            prn.addFeedLine(1);

            // Detail tiket
            prn.addTextSize(1, 1);
            prn.addTextStyle(false, false, false, prn.COLOR_1);
            prn.addText(separator + '\n');
            prn.addTextAlign(prn.ALIGN_LEFT);
            prn.addText('Layanan : ' + ticket.serviceName + '\n');
            prn.addText('Nama    : ' + ticket.visitorName + '\n');
            prn.addText('Tanggal : ' + ticket.serviceDate + '\n');
            prn.addText('Status  : ' + ticket.status + '\n');

            // Barcode
            prn.addTextAlign(prn.ALIGN_CENTER);
            prn.addText(separator + '\n');
            prn.addFeedLine(1);
            prn.addBarcode(
                ticket.ticketNumber,
                prn.BARCODE_CODE128,
                prn.HRI_BELOW,
                prn.FONT_A,
                2,
                80
            );

            // Instruksi
            prn.addFeedLine(1);
            prn.addText(separator + '\n');
            prn.addText('Silakan tunggu panggilan nomor\n');
            prn.addText('antrian Anda di area tunggu.\n');
            prn.addFeedLine(1);
            prn.addTextSize(1, 1);
            prn.addText('Dicetak: ' + timestamp + '\n');

            // Cut
            prn.addFeedLine(3);
            prn.addCut(prn.CUT_FEED);

            // Kirim ke printer
            prn.send();

            return true;
        },

        disconnect() {
            if (this.ePosDev) {
                this.ePosDev.disconnect();
                this.connected = false;
                this.printer = null;
            }
        },
    };
};
```

**Step 3: Muat SDK dan module di kiosk layout**

Modify `resources/views/layouts/kiosk.blade.php` — tambahkan sebelum `@fluxScripts`:

```blade
        {{-- Epson ePOS SDK untuk thermal printing --}}
        @if (config('services.thermal_printer.enabled'))
            <script src="{{ asset('vendor/epson/epos-2.27.0.js') }}"></script>
            @vite(['resources/js/thermal-printer.js'])
        @endif
```

**Step 4: Commit**

```bash
git add public/vendor/epson/ resources/js/thermal-printer.js resources/views/layouts/kiosk.blade.php
git commit -m "feat: add Epson ePOS SDK and thermal printer JS module

- Load SDK only on kiosk layout when enabled
- ESC/POS native format for 80mm thermal paper
- Connect via WebSocket to printer IP:port"
```

---

## Phase 3: Fitur Cetak Ulang Tiket (Backend)

### Task 3: Tulis test untuk fitur pencarian tiket cetak ulang

**Files:**
- Modify: `tests/Feature/Kiosk/KioskBookingTest.php`

**Step 1: Tambahkan test cases untuk reprint**

Tambahkan di akhir file test:

```php
it('shows reprint search form when entering reprint mode', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->assertSet('step', 0)
        ->assertSee('Cetak Ulang Tiket');
});

it('finds ticket by visitor identifier for today', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $ticket = \App\Models\QueueTicket::factory()->for($service)->create([
        'visitor_identifier' => '3507XXXXXXXXXXXX',
        'service_date' => today(),
        'status' => 'waiting',
    ]);

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->set('reprintQuery', '3507XXXXXXXXXXXX')
        ->call('searchTicketForReprint')
        ->assertSet('reprintTicket.id', $ticket->id)
        ->assertSee($ticket->ticket_number);
});

it('finds ticket by visitor phone for today', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $ticket = \App\Models\QueueTicket::factory()->for($service)->create([
        'visitor_phone' => '081234567890',
        'service_date' => today(),
        'status' => 'waiting',
    ]);

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->set('reprintQuery', '081234567890')
        ->call('searchTicketForReprint')
        ->assertSet('reprintTicket.id', $ticket->id);
});

it('shows not found when no ticket matches reprint query', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->set('reprintQuery', '0000000000000000')
        ->call('searchTicketForReprint')
        ->assertSet('reprintTicket', null)
        ->assertSee('Tiket Tidak Ditemukan');
});

it('ignores tickets from other dates in reprint search', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    \App\Models\QueueTicket::factory()->for($service)->create([
        'visitor_identifier' => '3507XXXXXXXXXXXX',
        'service_date' => today()->subDay(),
        'status' => 'waiting',
    ]);

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->set('reprintQuery', '3507XXXXXXXXXXXX')
        ->call('searchTicketForReprint')
        ->assertSet('reprintTicket', null);
});

it('returns to step 1 when exiting reprint mode', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->assertSet('step', 0)
        ->call('exitReprintMode')
        ->assertSet('step', 1)
        ->assertSet('reprintQuery', '')
        ->assertSet('reprintTicket', null);
});
```

**Step 2: Run tests — verify they FAIL**

Run: `php artisan test --compact --filter=KioskBookingTest`
Expected: FAIL — methods `enterReprintMode`, `searchTicketForReprint`, `exitReprintMode` dan properties `reprintQuery`, `reprintTicket` belum ada.

**Step 3: Commit**

```bash
git add tests/Feature/Kiosk/KioskBookingTest.php
git commit -m "test: add reprint ticket search tests for kiosk"
```

---

### Task 4: Implementasi reprint logic di KioskBooking component

**Files:**
- Modify: `app/Livewire/KioskBooking.php`

**Step 1: Tambah properties dan methods untuk reprint**

Tambahkan properties baru setelah property `barcodeSvg`:

```php
public string $reprintQuery = '';

public ?QueueTicket $reprintTicket = null;

public string $reprintBarcodeSvg = '';
```

Tambahkan methods baru sebelum `render()`:

```php
public function enterReprintMode(): void
{
    $this->step = 0;
    $this->reprintQuery = '';
    $this->reprintTicket = null;
    $this->reprintBarcodeSvg = '';
}

public function exitReprintMode(): void
{
    $this->step = 1;
    $this->reprintQuery = '';
    $this->reprintTicket = null;
    $this->reprintBarcodeSvg = '';
}

public function searchTicketForReprint(): void
{
    $this->validate([
        'reprintQuery' => ['required', 'string', 'min:3'],
    ]);

    $this->reprintTicket = QueueTicket::query()
        ->with('service')
        ->whereDate('service_date', today())
        ->whereIn('status', [
            QueueStatus::Booked,
            QueueStatus::Waiting,
            QueueStatus::Called,
        ])
        ->where(function ($query) {
            $query->where('visitor_identifier', $this->reprintQuery)
                ->orWhere('visitor_phone', $this->reprintQuery);
        })
        ->latest()
        ->first();

    if ($this->reprintTicket) {
        $this->reprintBarcodeSvg = $this->generateBarcodeSvg($this->reprintTicket->ticket_number);
    }
}
```

Tambahkan import `QueueStatus` di atas file jika belum ada:
```php
use App\Enums\QueueStatus;
```

Update `resetWizard()` — tambahkan reset reprint state:
```php
$this->reprintQuery = '';
$this->reprintTicket = null;
$this->reprintBarcodeSvg = '';
```

**Step 2: Run tests — verify they PASS**

Run: `php artisan test --compact --filter=KioskBookingTest`
Expected: ALL PASS

**Step 3: Commit**

```bash
git add app/Livewire/KioskBooking.php
git commit -m "feat: add reprint ticket search logic to kiosk component

- enterReprintMode() sets step to 0 (reprint mode)
- searchTicketForReprint() finds ticket by KTP or phone for today
- exitReprintMode() returns to step 1
- Only searches active tickets (booked/waiting/called)"
```

---

## Phase 4: Fitur Cetak Ulang Tiket (Frontend)

### Task 5: Update kiosk view — tombol cetak ulang di step 1 dan UI reprint

**Files:**
- Modify: `resources/views/livewire/kiosk-booking.blade.php`

**Step 1: Tambah tombol "Cetak Ulang Tiket" di step 1**

Di bagian step 1 (setelah helper text "Ketuk kartu layanan..."), tambahkan:

```blade
                    {{-- Tombol Cetak Ulang --}}
                    <div class="flex items-center justify-center pt-2">
                        <flux:button wire:click="enterReprintMode" variant="outline" icon="printer" class="h-14 rounded-2xl border-2 border-slate-300 px-8 text-lg font-semibold text-slate-700 transition-colors hover:border-cyan-400 hover:bg-cyan-50">
                            Cetak Ulang Tiket
                        </flux:button>
                    </div>
```

**Step 2: Tambah section step 0 (reprint mode) SEBELUM step 1**

Tambahkan sebelum `@if ($step === 1)`:

```blade
            {{-- Step 0: Reprint / Cetak Ulang Tiket --}}
            @if ($step === 0)
                <div wire:key="kiosk-step-reprint" class="mx-auto max-w-xl space-y-8">
                    {{-- Header --}}
                    <div class="space-y-4">
                        <div class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-2 shadow-lg">
                            <flux:icon.printer class="size-5 text-white" />
                            <span class="ml-2 text-base font-semibold text-white">Cetak Ulang Tiket</span>
                        </div>
                        <flux:heading level="1" size="3xl" class="bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-4xl font-black text-transparent">
                            Cari Tiket Anda
                        </flux:heading>
                        <flux:text class="text-xl text-slate-600">
                            Masukkan nomor KTP atau nomor HP yang digunakan saat mendaftar
                        </flux:text>
                    </div>

                    {{-- Form Pencarian --}}
                    <div class="space-y-6 rounded-3xl bg-white p-8 shadow-lg">
                        <flux:field>
                            <flux:label class="flex items-center gap-2 text-left text-xl font-semibold text-slate-800">
                                <flux:icon.magnifying-glass class="size-5 text-cyan-600" />
                                Nomor KTP atau Nomor HP
                            </flux:label>
                            <flux:input
                                wire:model="reprintQuery"
                                wire:keydown.enter="searchTicketForReprint"
                                size="lg"
                                placeholder="Masukkan nomor KTP atau nomor HP"
                                autofocus
                                class="mt-3 h-16 text-xl [&_[data-flux-control]]:h-16 [&_[data-flux-control]]:rounded-2xl [&_[data-flux-control]]:border-2 [&_[data-flux-control]]:border-slate-200 [&_[data-flux-control]]:text-xl [&_[data-flux-control]]:focus:border-cyan-500"
                            />
                            <flux:error name="reprintQuery" />
                        </flux:field>

                        <flux:button wire:click="searchTicketForReprint" variant="primary" icon="magnifying-glass" class="h-16 w-full rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-600 text-xl font-bold shadow-lg shadow-cyan-500/25" wire:loading.attr="disabled" wire:target="searchTicketForReprint">
                            <span wire:loading.remove wire:target="searchTicketForReprint">Cari Tiket</span>
                            <span wire:loading wire:target="searchTicketForReprint" class="inline-flex items-center gap-2">
                                <svg class="h-6 w-6 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Mencari...
                            </span>
                        </flux:button>
                    </div>

                    {{-- Hasil Pencarian: Tiket Ditemukan --}}
                    @if ($reprintTicket)
                        <div class="relative overflow-hidden rounded-3xl border-2 border-emerald-200 bg-white p-8 shadow-2xl">
                            <div class="absolute left-0 right-0 top-0 h-2 bg-gradient-to-r from-emerald-500 via-cyan-500 to-blue-500"></div>

                            <div class="space-y-6">
                                {{-- Success badge --}}
                                <div class="flex justify-center">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-emerald-700">
                                        <flux:icon.check-circle class="size-5" />
                                        <span class="text-base font-semibold">Tiket Ditemukan</span>
                                    </div>
                                </div>

                                {{-- Nomor Tiket --}}
                                <div class="text-center">
                                    <div class="bg-gradient-to-br from-cyan-600 via-blue-600 to-violet-600 bg-clip-text text-8xl font-black tracking-wider text-transparent">
                                        {{ $reprintTicket->ticket_number }}
                                    </div>
                                </div>

                                {{-- Detail --}}
                                <div class="rounded-2xl bg-slate-50 p-5 text-center">
                                    <div class="text-sm font-medium uppercase tracking-wide text-slate-500">Layanan</div>
                                    <div class="mt-1 text-xl font-bold text-slate-800">{{ $reprintTicket->service?->name }}</div>
                                    <div class="mt-2 text-base text-slate-500">{{ $reprintTicket->visitor_name }}</div>
                                </div>

                                {{-- Barcode --}}
                                @if ($reprintBarcodeSvg)
                                    <div class="flex justify-center rounded-2xl bg-slate-50 py-4">
                                        <div class="barcode-container">{!! $reprintBarcodeSvg !!}</div>
                                    </div>
                                @endif

                                {{-- Tombol cetak --}}
                                <flux:button
                                    variant="primary"
                                    icon="printer"
                                    class="h-16 w-full rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-xl font-bold shadow-lg shadow-emerald-500/25"
                                    x-on:click="$dispatch('print-ticket', {
                                        ticketNumber: '{{ $reprintTicket->ticket_number }}',
                                        serviceName: '{{ $reprintTicket->service?->name }}',
                                        visitorName: '{{ $reprintTicket->visitor_name }}',
                                        serviceDate: '{{ $reprintTicket->service_date?->format('d/m/Y') }}',
                                        status: '{{ $reprintTicket->status->label() }}'
                                    })"
                                >
                                    Cetak Ulang
                                </flux:button>
                            </div>
                        </div>
                    @elseif ($reprintQuery !== '')
                        {{-- Tidak ditemukan --}}
                        <div class="rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50/50 p-10 text-center">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
                                <flux:icon.magnifying-glass class="size-10 text-slate-400" />
                            </div>
                            <flux:heading level="2" size="xl" class="mt-6 text-slate-700">Tiket Tidak Ditemukan</flux:heading>
                            <flux:text class="mt-2 text-lg text-slate-500">
                                Tidak ada tiket aktif hari ini untuk nomor tersebut. Pastikan nomor KTP atau HP sudah benar.
                            </flux:text>
                        </div>
                    @endif

                    {{-- Tombol Kembali --}}
                    <flux:button wire:click="exitReprintMode" variant="outline" icon="arrow-left" class="h-16 w-full rounded-2xl border-2 border-slate-300 text-xl font-semibold text-slate-700">
                        Kembali ke Menu Utama
                    </flux:button>
                </div>
            @endif
```

**Step 3: Run tests**

Run: `php artisan test --compact --filter=KioskBookingTest`
Expected: ALL PASS

**Step 4: Commit**

```bash
git add resources/views/livewire/kiosk-booking.blade.php
git commit -m "feat(ui): add reprint ticket UI to kiosk

- Add 'Cetak Ulang Tiket' button on step 1
- Add step 0 (reprint mode) with search form
- Show ticket card with barcode when found
- Show not-found state when no match"
```

---

## Phase 5: Wire Up Thermal Printing

### Task 6: Integrasikan thermal printer di kiosk view

**Files:**
- Modify: `resources/views/livewire/kiosk-booking.blade.php`

**Step 1: Tambah Alpine.js thermal printer integration**

Di dalam `<div>` paling luar dari kiosk-booking view (elemen pertama), tambahkan wrapper Alpine `x-data` dan event listener:

Wrap seluruh konten kiosk dalam:

```blade
<div
    x-data="ThermalPrinter({
        enabled: {{ config('services.thermal_printer.enabled') ? 'true' : 'false' }},
        ip: '{{ config('services.thermal_printer.ip') }}',
        port: {{ config('services.thermal_printer.port', 8043) }},
        deviceId: '{{ config('services.thermal_printer.device_id', 'local_printer') }}',
        institutionName: '{{ config('institution.name') }}',
    })"
    x-init="init()"
    x-on:print-ticket.window="printTicket($event.detail)"
    class="flex min-h-screen flex-col justify-between gap-6 p-4 sm:p-6 lg:p-8 {{ $fontSize === 'large' ? 'text-lg' : 'text-base' }}"
>
```

Ini menggantikan `<div class="flex min-h-screen...">` yang sudah ada di baris 1.

**Step 2: Tambah auto-print di step 4 (tiket baru)**

Di dalam step 4 (`@if ($step === 4 && $ticket)`), pada div `x-init`, tambahkan dispatch event:

```blade
x-init="
    setInterval(() => { countdown--; if(countdown <= 0) $wire.resetWizard(); }, 1000);
    $dispatch('print-ticket', {
        ticketNumber: '{{ $ticket->ticket_number }}',
        serviceName: '{{ $ticket->service?->name }}',
        visitorName: '{{ $ticket->visitor_name }}',
        serviceDate: '{{ $ticket->service_date?->format('d/m/Y') }}',
        status: '{{ $ticket->status->label() }}'
    });
"
```

**Step 3: Run tests**

Run: `php artisan test --compact --filter=KioskBookingTest`
Expected: ALL PASS (JS tidak dieksekusi di Livewire test)

**Step 4: Commit**

```bash
git add resources/views/livewire/kiosk-booking.blade.php
git commit -m "feat: wire up thermal printer for kiosk auto-print

- Initialize ThermalPrinter Alpine component on kiosk
- Auto-print on step 4 (new ticket created)
- Print on reprint button click via x-dispatch
- Config-driven: only active when THERMAL_PRINTER_ENABLED=true"
```

---

## Phase 6: Final Verification

### Task 7: Run full test suite dan manual test

**Step 1: Run semua test**

Run: `php artisan test --compact`
Expected: ALL PASS

**Step 2: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

**Step 3: Manual test checklist**

Buka `/kiosk` di browser:

- [ ] Step 1: tampil tombol "Cetak Ulang Tiket" di bawah kartu layanan
- [ ] Klik "Cetak Ulang Tiket" → masuk step 0 dengan form pencarian
- [ ] Masukkan KTP valid → tiket ditemukan, tampil kartu + barcode
- [ ] Masukkan HP valid → tiket ditemukan
- [ ] Masukkan nomor tidak valid → "Tiket Tidak Ditemukan"
- [ ] Klik "Kembali ke Menu Utama" → kembali ke step 1
- [ ] Buat tiket baru → step 4, auto-print ke thermal (jika printer terhubung)
- [ ] Console browser: `[ThermalPrinter] Terhubung ke printer.` (jika printer aktif)

**Step 4: Commit jika ada Pint fixes**

```bash
git add -A
git commit -m "style: apply Pint formatting"
```

---

## Summary

| Phase | Task | File Utama | Deskripsi |
|---|---|---|---|
| 1 | Task 1 | `config/services.php` | Konfigurasi IP printer |
| 2 | Task 2 | `thermal-printer.js`, layout | SDK + JS module |
| 3 | Task 3 | Test file | Test reprint search |
| 4 | Task 4 | `KioskBooking.php` | Logic reprint backend |
| 4 | Task 5 | `kiosk-booking.blade.php` | UI reprint frontend |
| 5 | Task 6 | `kiosk-booking.blade.php` | Wire up thermal print |
| 6 | Task 7 | All | Final verification |

Total: **7 tasks**, **6 phases**

### Catatan Penting: Epson ePOS SDK

File `epos-2.27.0.js` harus didownload manual dari [Epson Developer Portal](https://download.ebz.epson.net/dsc/du/02/DriverDownloadInfo.RAW?OSC=WS64) dan ditempatkan di `public/vendor/epson/`. File ini **tidak tersedia via npm** dan berukuran ~200KB. Tanpa file ini, printer tidak akan terhubung tapi aplikasi tetap berfungsi normal (fitur cetak ulang tetap bekerja, hanya auto-print thermal yang tidak aktif).
