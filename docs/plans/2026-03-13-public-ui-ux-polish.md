# Public UI/UX Polish — Target 9.5/10 Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Menaikkan skor UI/UX semua halaman publik dari rata-rata 7.2 ke target 9.5 dengan memperbaiki inkonsistensi komponen, menghapus redundansi, dan menyeragamkan design language.

**Architecture:** Semua halaman publik menggunakan layout `layouts/public.blade.php` dengan Flux UI components. Perbaikan bersifat view-layer only (blade + controller view data), tidak mengubah business logic. Enum `QueueStatus` sudah punya `label()` dan `color()` yang harus dimanfaatkan di view.

**Tech Stack:** Laravel 12, Livewire 4, Flux UI Pro v2, Tailwind CSS v4, Pest 4

---

## Phase 1: Lookup Page Redesign (5/10 -> 9.5)

Halaman ini paling tertinggal. Perlu redesign total agar konsisten dengan halaman lain.

### Task 1: Update test assertions untuk lookup page redesign

**Files:**
- Modify: `tests/Feature/Public/PublicQueueLookupPageTest.php`

**Step 1: Update test assertions agar sesuai UI baru**

Test lama mengecek `'Detail Tiket:'` dan `'Menunggu'`. Setelah redesign, teks berubah.

```php
<?php

use App\Enums\QueueStatus;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;

test('public user can open queue lookup page', function () {
    $response = $this->get('/antrian/cek');

    $response->assertOk()
        ->assertSee('Cek Status Antrian')
        ->assertSee('Nomor Antrian')
        ->assertSee('Tanggal Layanan')
        ->assertSee('Cari Tiket');
});

test('public user can lookup ticket by ticket number and service date', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $ticket = QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'UMUM-0012',
        'service_date' => '2026-03-10',
        'status' => 'waiting',
    ]);

    $response = $this->get('/antrian/cek?ticket_number=UMUM-0012&service_date=2026-03-10');

    $response->assertOk()
        ->assertSee('UMUM-0012')
        ->assertSee($ticket->visitor_name)
        ->assertSee($service->name)
        ->assertSee(QueueStatus::Waiting->label());
});

test('lookup page shows not found message for invalid ticket', function () {
    $response = $this->get('/antrian/cek?ticket_number=INVALID&service_date=2026-03-10');

    $response->assertOk()
        ->assertSee('Tiket Tidak Ditemukan');
});

test('lookup page shows status-specific guidance for each queue status', function () {
    $pool = QueuePool::factory()->create(['code' => 'TST']);
    $service = Service::factory()->for($pool)->create();

    $ticket = QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'TST-0001',
        'service_date' => '2026-03-10',
        'status' => QueueStatus::Called,
    ]);

    $response = $this->get('/antrian/cek?ticket_number=TST-0001&service_date=2026-03-10');

    $response->assertOk()
        ->assertSee(QueueStatus::Called->label());
});
```

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=PublicQueueLookupPageTest`
Expected: Beberapa test FAIL karena view belum diupdate.

**Step 3: Commit**

```bash
git add tests/Feature/Public/PublicQueueLookupPageTest.php
git commit -m "test: update lookup page test assertions for UI redesign"
```

---

### Task 2: Redesign lookup page view dengan Flux components

**Files:**
- Modify: `resources/views/pages/public/antrian/lookup.blade.php`

**Reference:**
- Style pattern dari `welcome.blade.php` (badge, card, heading hierarchy)
- `QueueStatus` enum punya `->label()` dan `->color()` — gunakan di view

**Step 1: Rewrite lookup.blade.php**

```blade
<x-layouts::public :title="'Cek Status Antrian'">
    <flux:main container>
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-8 py-6 sm:gap-10 sm:py-8">
            {{-- Hero Section --}}
            <div class="space-y-3 text-center">
                <flux:badge color="cyan" rounded icon="magnifying-glass">Cek Status</flux:badge>
                <flux:heading size="xl" level="1" class="text-slate-900">Cek Status Antrian</flux:heading>
                <flux:subheading class="mx-auto max-w-2xl text-base leading-7 text-slate-600">
                    Masukkan nomor antrian dan tanggal layanan untuk melihat status tiket Anda.
                </flux:subheading>
            </div>

            {{-- Search Form --}}
            <flux:card class="mx-auto w-full max-w-lg border-cyan-200 bg-white p-6 shadow-[0_24px_60px_-48px_rgba(8,145,178,0.45)] sm:p-8">
                <form method="GET" action="{{ url('/antrian/cek') }}" class="space-y-5">
                    <flux:field>
                        <flux:label>Nomor Antrian</flux:label>
                        <flux:input type="text" name="ticket_number" value="{{ request('ticket_number') }}" required placeholder="Contoh: A0001" icon="ticket" />
                        <flux:description>Nomor yang tertera pada tiket antrian Anda</flux:description>
                        <flux:error name="ticket_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Layanan</flux:label>
                        <flux:input type="date" name="service_date" value="{{ request('service_date') }}" required icon="calendar-days" />
                        <flux:error name="service_date" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" icon="magnifying-glass" class="w-full justify-center">
                        Cari Tiket
                    </flux:button>
                </form>
            </flux:card>

            {{-- Results --}}
            @if (request()->filled('ticket_number') && request()->filled('service_date'))
                @if ($ticket)
                    <flux:card class="mx-auto w-full max-w-lg overflow-hidden border-cyan-100 bg-white p-0 shadow-[0_24px_60px_-48px_rgba(14,116,144,0.4)]">
                        {{-- Ticket Header --}}
                        <div class="bg-gradient-to-r from-cyan-600 to-cyan-700 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="ticket" class="text-white" />
                                    <flux:heading size="lg" class="text-white">{{ $ticket->ticket_number }}</flux:heading>
                                </div>
                                <flux:badge :color="$ticket->status->color()">
                                    {{ $ticket->status->label() }}
                                </flux:badge>
                            </div>
                        </div>

                        {{-- Ticket Body --}}
                        <div class="space-y-5 p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nama</flux:text>
                                    <flux:text class="font-semibold text-slate-900">{{ $ticket->visitor_name }}</flux:text>
                                </div>
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Layanan</flux:text>
                                    <flux:text class="font-semibold text-slate-900">{{ $ticket->service->name ?? '-' }}</flux:text>
                                </div>
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Tanggal</flux:text>
                                    <flux:text class="font-semibold text-slate-900">{{ $ticket->service_date->format('d M Y') }}</flux:text>
                                </div>
                                @if ($ticket->counter)
                                    <div class="space-y-1">
                                        <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Loket</flux:text>
                                        <flux:text class="font-semibold text-slate-900">{{ $ticket->counter->name }}</flux:text>
                                    </div>
                                @endif
                            </div>

                            <flux:separator />

                            {{-- Status-specific guidance --}}
                            @php
                                $statusConfig = match ($ticket->status) {
                                    \App\Enums\QueueStatus::Waiting => [
                                        'bg' => 'bg-amber-50 border-amber-200',
                                        'icon_color' => 'text-amber-600',
                                        'text_color' => 'text-amber-800',
                                        'icon' => 'clock',
                                        'message' => 'Posisi antrian Anda: ' . ($queuePosition ?? 0),
                                    ],
                                    \App\Enums\QueueStatus::Called => [
                                        'bg' => 'bg-purple-50 border-purple-200',
                                        'icon_color' => 'text-purple-600',
                                        'text_color' => 'text-purple-800',
                                        'icon' => 'speaker-wave',
                                        'message' => 'Silakan segera menuju ' . ($ticket->counter->name ?? 'loket yang ditunjuk'),
                                    ],
                                    \App\Enums\QueueStatus::Completed => [
                                        'bg' => 'bg-emerald-50 border-emerald-200',
                                        'icon_color' => 'text-emerald-600',
                                        'text_color' => 'text-emerald-800',
                                        'icon' => 'check-circle',
                                        'message' => 'Layanan telah selesai',
                                    ],
                                    \App\Enums\QueueStatus::Booked => [
                                        'bg' => 'bg-blue-50 border-blue-200',
                                        'icon_color' => 'text-blue-600',
                                        'text_color' => 'text-blue-800',
                                        'icon' => 'calendar-days',
                                        'message' => 'Tiket terdaftar. Silakan datang dan lakukan check-in di loket',
                                    ],
                                    \App\Enums\QueueStatus::Cancelled => [
                                        'bg' => 'bg-red-50 border-red-200',
                                        'icon_color' => 'text-red-600',
                                        'text_color' => 'text-red-800',
                                        'icon' => 'x-circle',
                                        'message' => 'Tiket ini telah dibatalkan',
                                    ],
                                    \App\Enums\QueueStatus::Skipped => [
                                        'bg' => 'bg-orange-50 border-orange-200',
                                        'icon_color' => 'text-orange-600',
                                        'text_color' => 'text-orange-800',
                                        'icon' => 'arrow-uturn-right',
                                        'message' => 'Tiket ini telah dilewati. Silakan hubungi petugas untuk dipanggil ulang',
                                    ],
                                };
                            @endphp

                            <div class="flex items-start gap-3 rounded-2xl border {{ $statusConfig['bg'] }} p-4">
                                <flux:icon :name="$statusConfig['icon']" class="{{ $statusConfig['icon_color'] }} mt-0.5 shrink-0" />
                                <flux:text class="{{ $statusConfig['text_color'] }} font-medium">
                                    {{ $statusConfig['message'] }}
                                </flux:text>
                            </div>
                        </div>
                    </flux:card>
                @else
                    <flux:card class="mx-auto w-full max-w-lg border-dashed border-slate-300 bg-white p-8 text-center shadow-none">
                        <div class="flex flex-col items-center gap-4">
                            <div class="flex size-14 items-center justify-center rounded-3xl bg-red-100 text-red-600">
                                <flux:icon.magnifying-glass class="size-7" />
                            </div>
                            <div class="space-y-2">
                                <flux:heading size="lg" class="text-slate-900">Tiket Tidak Ditemukan</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600">
                                    Pastikan nomor antrian dan tanggal layanan sudah benar.
                                </flux:text>
                            </div>
                            <flux:button href="{{ url('/antrian/cek') }}" variant="subtle" icon="arrow-path">
                                Periksa Ulang
                            </flux:button>
                        </div>
                    </flux:card>
                @endif
            @else
                {{-- Empty state sebelum search --}}
                <div class="mx-auto max-w-lg text-center">
                    <flux:card class="border-dashed border-slate-200 bg-slate-50/50 p-8 shadow-none">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-600">
                                <flux:icon.ticket class="size-6" />
                            </div>
                            <flux:text class="text-sm leading-6 text-slate-500">
                                Masukkan nomor tiket dan tanggal layanan pada form di atas untuk melihat status antrian Anda.
                            </flux:text>
                        </div>
                    </flux:card>
                </div>
            @endif
        </div>
    </flux:main>
</x-layouts::public>
```

**Step 2: Run tests to verify they pass**

Run: `php artisan test --compact --filter=PublicQueueLookupPageTest`
Expected: ALL PASS

**Step 3: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

**Step 4: Commit**

```bash
git add resources/views/pages/public/antrian/lookup.blade.php
git commit -m "feat(ui): redesign lookup page with Flux components and consistent design"
```

---

## Phase 2: Welcome Page Polish (8/10 -> 9.5)

### Task 3: Update landing page test assertions

**Files:**
- Modify: `tests/Feature/Public/PublicLandingPageTest.php`

**Step 1: Update test — remove assertion for deleted elements**

Test saat ini mengecek `'Lihat Papan Antrian'` (sudah dihapus) dan `'Kuota/hari: 25'` (akan dihapus karena duplikat). Update:

```php
<?php

use App\Models\Service;

use function Pest\Laravel\get;

test('public user can open landing page and see primary guidance', function () {
    $response = get('/');

    $response->assertSuccessful()
        ->assertSeeText('Sistem Antrian PTSP')
        ->assertSeeText('Ambil Nomor Antrian')
        ->assertSeeText('Cek Status Antrian')
        ->assertSeeText('Panduan Pengunjung')
        ->assertSeeTextInOrder([
            'Pilih Layanan',
            'Isi Data Diri',
            'Tunjukkan Nomor Antrian',
        ])
        ->assertSeeText(config('institution.operating_hours'));
});

test('landing page renders service catalog details when services are available', function () {
    $service = new Service([
        'name' => 'Konsultasi Informasi',
        'description' => 'Pendampingan awal untuk kebutuhan administrasi pengunjung.',
        'requirements' => "KTP\nDokumen pendukung",
        'daily_quota' => 25,
        'booking_enabled' => true,
        'walk_in_enabled' => true,
    ]);

    $html = view('welcome', [
        'services' => collect([$service]),
    ])->render();

    expect(strip_tags($html))
        ->toContain('Katalog Layanan')
        ->toContain($service->name)
        ->toContain($service->description)
        ->toContain('KTP')
        ->toContain('Dokumen pendukung')
        ->toContain('Online')
        ->toContain('Walk-in');
});
```

**Step 2: Run tests**

Run: `php artisan test --compact --filter=PublicLandingPageTest`
Expected: FAIL karena `'Lihat Papan Antrian'` sudah dihapus tapi assertion mungkin masih ada di view teks.

**Step 3: Commit**

```bash
git add tests/Feature/Public/PublicLandingPageTest.php
git commit -m "test: update landing page assertions, remove display antrian references"
```

---

### Task 4: Clean up welcome page — remove redundancies

**Files:**
- Modify: `resources/views/welcome.blade.php`

**Perubahan yang dilakukan:**

1. **Hapus teks stale "display antrian"** (baris 43) — ganti dengan teks tanpa referensi display
2. **Hapus section "Akses Cepat"** (baris 82-126) — redundan dengan hero CTA
3. **Hapus duplikasi kuota** — baris 170-172 (`flux:text` kuota) sudah ada sebagai badge
4. **Simplify section "Akun & Akses"** — pindahkan login ke link kecil, bukan card besar

**Step 1: Edit welcome.blade.php**

Perubahan detail:

a) Baris 43 — ganti teks yang menyebut display:
```
OLD: Gunakan katalog layanan untuk menyiapkan berkas, cek tiket tanpa antre ulang, lalu pantau panggilan melalui display antrian saat tiba di PTSP.
NEW: Gunakan katalog layanan untuk menyiapkan berkas dan cek tiket tanpa perlu antre ulang di kantor.
```

b) Hapus seluruh section "Akses Cepat" (baris 82-126) — section `<section class="space-y-4">` yang berisi grid 2 kolom "Ambil Antrian" dan "Cek Status Tiket"

c) Hapus duplikasi kuota di service cards — baris 170-172:
```blade
{{-- HAPUS baris ini --}}
@if ($service->daily_quota)
    <flux:text class="text-xs leading-5 text-slate-500">Kuota/hari: {{ $service->daily_quota }}</flux:text>
@endif
```

d) Simplify "Akun & Akses" section — ganti card besar dengan inline link di section Panduan:
- Hapus seluruh `<flux:card>` yang berisi "Akun & Akses" (baris 246-281)
- Tambahkan tombol login sebagai elemen kecil di akhir section Panduan (di dalam card yang sudah ada)
- Ubah layout grid section terakhir dari `lg:grid-cols-[...]` menjadi single column

**Step 2: Run tests**

Run: `php artisan test --compact --filter=PublicLandingPageTest`
Expected: ALL PASS

**Step 3: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

**Step 4: Commit**

```bash
git add resources/views/welcome.blade.php
git commit -m "feat(ui): clean up welcome page — remove redundancies, stale display text"
```

---

## Phase 3: Booking Page Simplification (7/10 -> 9.5)

### Task 5: Simplify booking hero section

**Files:**
- Modify: `resources/views/pages/public/antrian/booking.blade.php`

**Perubahan:**

1. **Ganti hero besar** (baris 205-303, card gradient + 3 mini-cards + sidebar "Yang perlu disiapkan") dengan hero compact: hanya heading + subheading + badge
2. **Hapus sidebar "Petunjuk: Alur cepat pengunjung"** (baris 620-642) — redundan dengan step indicator
3. **Pertahankan sidebar "Ringkasan aktif"** — ini berguna

**Step 1: Replace hero section**

Ganti card besar `flux:card class="overflow-hidden border-cyan-200 bg-[linear-gradient..."` (baris 205-303) dengan:

```blade
<div class="space-y-3 text-center">
    <div class="flex flex-wrap items-center justify-center gap-3">
        <flux:badge color="cyan" rounded icon="sparkles">Booking Online PTSP</flux:badge>
        <flux:badge color="sky" rounded icon="rectangle-group">3 Langkah Cepat</flux:badge>
    </div>

    <flux:heading size="xl" level="1" class="text-balance text-slate-900">
        Ambil Antrian PTSP
    </flux:heading>

    <flux:subheading class="mx-auto max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">
        Pilih layanan, lengkapi data kunjungan, lalu periksa ulang ringkasan sebelum mengirim booking Anda.
    </flux:subheading>
</div>
```

**Step 2: Hapus sidebar "Petunjuk"**

Hapus card kedua di sidebar (baris 620-642) yang berisi "Alur cepat pengunjung" dengan 3 numbered boxes. Pertahankan hanya card "Ringkasan aktif".

**Step 3: Run tests**

Run: `php artisan test --compact --filter=PublicQueueBookingPageTest`
Expected: ALL PASS

**Step 4: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

**Step 5: Commit**

```bash
git add resources/views/pages/public/antrian/booking.blade.php
git commit -m "feat(ui): simplify booking page — compact hero, remove redundant sidebar"
```

---

## Phase 4: Confirmation Page Consistency (7.5/10 -> 9.5)

### Task 6: Update confirmation page test assertions

**Files:**
- Modify: `tests/Feature/Public/PublicQueueConfirmationTest.php`

**Step 1: Add more thorough test**

```php
<?php

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('confirmation page displays ticket details', function () {
    $service = Service::factory()->create();
    $ticket = QueueTicket::factory()->create([
        'service_id' => $service->id,
        'ticket_number' => 'ABC123',
        'status' => QueueStatus::Waiting,
        'visitor_name' => 'John Doe',
        'sequence_number' => 5,
    ]);

    $url = route('queue.confirmation', $ticket);
    $response = $this->get($url);

    $response->assertOk()
        ->assertSee('Konfirmasi Antrian')
        ->assertSee('ABC123')
        ->assertSee('John Doe')
        ->assertSee($service->name)
        ->assertSee($ticket->status->label());
});

test('confirmation page has print button and navigation links', function () {
    $service = Service::factory()->create();
    $ticket = QueueTicket::factory()->create([
        'service_id' => $service->id,
        'status' => QueueStatus::Booked,
    ]);

    $response = $this->get(route('queue.confirmation', $ticket));

    $response->assertOk()
        ->assertSee('Cetak Tiket')
        ->assertSee('Cek Status Antrian')
        ->assertSee('Kembali ke Beranda');
});
```

**Step 2: Run tests**

Run: `php artisan test --compact --filter=PublicQueueConfirmationTest`
Expected: Should PASS or FAIL depending on exact text.

**Step 3: Commit**

```bash
git add tests/Feature/Public/PublicQueueConfirmationTest.php
git commit -m "test: expand confirmation page test coverage"
```

---

### Task 7: Refactor confirmation page to use Flux components

**Files:**
- Modify: `resources/views/pages/public/antrian/confirmation.blade.php`

**Perubahan:**
1. Ganti `<h1>`, `<h2>`, `<p>` dengan `flux:heading`, `flux:text`
2. Ganti link navigasi raw dengan `flux:button` variant subtle
3. Pertahankan print CSS dan card structure (sudah bagus)

**Step 1: Update view**

Perubahan spesifik:

a) Header section (baris 43-46):
```blade
{{-- OLD --}}
<div class="text-center mb-8 no-print">
    <h1 class="text-3xl font-bold text-slate-900 mb-2">Konfirmasi Antrian</h1>
    <p class="text-slate-600">Terima kasih telah melakukan pendaftaran antrian</p>
</div>

{{-- NEW --}}
<div class="space-y-2 text-center mb-8 no-print">
    <flux:badge color="emerald" rounded icon="check-circle">Berhasil</flux:badge>
    <flux:heading size="xl" level="1" class="text-slate-900">Konfirmasi Antrian</flux:heading>
    <flux:subheading>Terima kasih telah melakukan pendaftaran antrian</flux:subheading>
</div>
```

b) Ticket header (baris 55):
```blade
{{-- OLD --}}
<h2 class="text-white font-semibold text-lg">Detail Tiket Antrian</h2>

{{-- NEW --}}
<flux:heading size="lg" class="text-white">Detail Tiket Antrian</flux:heading>
```

c) Ticket body labels (baris 67-68, 74-75, 78-79, 85-86):
```blade
{{-- OLD pattern --}}
<p class="text-slate-500 text-sm font-medium mb-1">Nomor Tiket</p>
<p class="text-6xl font-bold text-slate-900">{{ $ticket->ticket_number }}</p>

{{-- NEW pattern --}}
<flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nomor Tiket</flux:text>
<flux:heading size="3xl" class="text-slate-900">{{ $ticket->ticket_number }}</flux:heading>
```

d) Ticket footer labels (baris 117-122):
```blade
{{-- OLD --}}
<div class="text-xs text-slate-500">...</div>

{{-- NEW --}}
<flux:text class="text-xs text-slate-500">...</flux:text>
```

e) Navigation links (baris 135-142):
```blade
{{-- OLD --}}
<div class="flex flex-col gap-2 text-center no-print">
    <a href="{{ route('queue.cek') }}" class="text-cyan-600 hover:text-cyan-700 font-medium text-sm no-print">
        Cek Status Antrian
    </a>
    <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-700 font-medium text-sm no-print">
        Kembali ke Beranda
    </a>
</div>

{{-- NEW --}}
<div class="flex flex-col gap-2 text-center no-print">
    <flux:button href="{{ route('queue.cek') }}" variant="subtle" icon="magnifying-glass" class="justify-center">
        Cek Status Antrian
    </flux:button>
    <flux:button href="{{ route('home') }}" variant="ghost" icon="home" class="justify-center">
        Kembali ke Beranda
    </flux:button>
</div>
```

**Step 2: Run tests**

Run: `php artisan test --compact --filter=PublicQueueConfirmationTest`
Expected: ALL PASS

**Step 3: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

**Step 4: Commit**

```bash
git add resources/views/pages/public/antrian/confirmation.blade.php
git commit -m "feat(ui): refactor confirmation page to Flux components for consistency"
```

---

## Phase 5: Layout & Navbar Polish (8/10 -> 9.5)

### Task 8: Fix navbar label "Daftar Antrian" -> "Ambil Antrian"

**Files:**
- Modify: `resources/views/layouts/public.blade.php`

**Step 1: Update navbar labels**

Ganti semua "Daftar Antrian" menjadi "Ambil Antrian" di:
- Desktop navbar (baris 61-62)
- Mobile menu (baris 89-90)

```blade
{{-- OLD --}}
<flux:navbar.item href="{{ url('/antrian') }}" :current="request()->is('antrian')" wire:navigate>
    Daftar Antrian
</flux:navbar.item>

{{-- NEW --}}
<flux:navbar.item href="{{ url('/antrian') }}" :current="request()->is('antrian')" wire:navigate>
    Ambil Antrian
</flux:navbar.item>
```

Sama untuk mobile navlist:
```blade
{{-- OLD --}}
<flux:navlist.item href="{{ url('/antrian') }}" :current="request()->is('antrian')" icon="ticket" wire:navigate>
    Daftar Antrian
</flux:navlist.item>

{{-- NEW --}}
<flux:navlist.item href="{{ url('/antrian') }}" :current="request()->is('antrian')" icon="ticket" wire:navigate>
    Ambil Antrian
</flux:navlist.item>
```

**Step 2: Run all public tests**

Run: `php artisan test --compact --filter=Public`
Expected: ALL PASS

**Step 3: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

**Step 4: Commit**

```bash
git add resources/views/layouts/public.blade.php
git commit -m "feat(ui): rename navbar 'Daftar Antrian' to 'Ambil Antrian' for clarity"
```

---

## Phase 6: Final Verification

### Task 9: Run full test suite and visual check

**Step 1: Run all tests**

Run: `php artisan test --compact`
Expected: ALL PASS, zero failures

**Step 2: Run Pint on all modified files**

Run: `vendor/bin/pint --dirty --format agent`

**Step 3: Visual checklist (manual)**

Buka di browser dan verifikasi:
- [ ] `/` — Beranda: hero + katalog layanan, tidak ada section "Akses Cepat", tidak ada "display antrian"
- [ ] `/antrian` — Booking: hero compact, wizard clear, sidebar hanya "Ringkasan aktif"
- [ ] `/antrian/cek` — Lookup: hero + form + empty state, hasil pencarian dengan Flux components
- [ ] `/antrian/konfirmasi/{ticket}` — Konfirmasi: Flux components, print only tiket card
- [ ] Navbar: label "Ambil Antrian" (bukan "Daftar Antrian")
- [ ] Mobile menu: sama dengan desktop

**Step 4: Commit if any Pint fixes**

```bash
git add -A
git commit -m "style: apply Pint formatting to all modified views"
```

---

## Summary

| Phase | Task | File Utama | Target |
|---|---|---|---|
| 1 | Task 1-2 | lookup.blade.php | 5/10 -> 9.5 |
| 2 | Task 3-4 | welcome.blade.php | 8/10 -> 9.5 |
| 3 | Task 5 | booking.blade.php | 7/10 -> 9.5 |
| 4 | Task 6-7 | confirmation.blade.php | 7.5 -> 9.5 |
| 5 | Task 8 | public.blade.php (layout) | 8/10 -> 9.5 |
| 6 | Task 9 | All | Final verification |

Total: **9 tasks**, estimasi **6 phases**, setiap task 2-5 menit.
