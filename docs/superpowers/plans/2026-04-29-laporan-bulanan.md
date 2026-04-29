# Laporan Bulanan Pendaftar Layanan — Rencana Implementasi

> **Untuk pekerja agentic:** GUNAKAN sub-skill: superpowers:subagent-driven-development (direkomendasikan) atau superpowers:executing-plans untuk implementasi. Langkah menggunakan sintaks checkbox (`- [ ]`).

**Goal:** Membangun modul laporan bulanan pendaftar layanan dengan preview interaktif (Livewire + Flux UI Pro) dan export Excel/PDF.

**Architecture:** Livewire 4 component `LaporanBulanan` menjadi halaman utama dengan filter bulan/tahun, preview data di browser, dan tombol export. Seluruh agregasi laporan dipusatkan di `App\Support\Reports\LaporanBulananReportBuilder` agar preview Livewire, export Excel multi-sheet, dan PDF memakai dataset yang sama. Label bulan, hari, dan tanggal harus memakai locale Indonesia secara eksplisit karena locale default aplikasi masih `en`, dan akses halaman tetap mengikuti middleware `auth`, `verified`, dan role laporan yang sudah ada.

**Tech Stack:** Laravel 12, Livewire 4, Flux UI Pro 2, Tailwind CSS 4, maatwebsite/excel, barryvdh/laravel-dompdf, Pest 4

---

### Task 1: Install Package Export

**Files:**
- Modify: `composer.json`
- Run: `composer require` commands

- [ ] **Step 1: Install `maatwebsite/excel` dan `barryvdh/laravel-dompdf`**

```bash
composer require maatwebsite/excel barryvdh/laravel-dompdf --no-interaction
```

- [ ] **Step 2: Verifikasi package terinstall**

```bash
composer show maatwebsite/excel barryvdh/laravel-dompdf
```

Expected: Menampilkan versi kedua package.

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "feat: tambah package maatwebsite/excel dan barryvdh/laravel-dompdf untuk export laporan"
```

---

### Task 2: Tulis Failing Test Akses, Filter, Locale, dan Export (RED)

**Files:**
- Create: `tests/Feature/Reports/LaporanBulananTest.php`

- [ ] **Step 1: Tulis failing test untuk akses halaman, filter, locale, dan export**

```php
<?php

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Exports\LaporanBulananExport;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use App\Support\Reports\LaporanBulananReportBuilder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('admin dapat mengakses halaman laporan bulanan', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    actingAs($user)
        ->get('/laporan/bulanan')
        ->assertOk();
});

test('monitor dapat mengakses halaman laporan bulanan', function () {
    $user = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    actingAs($user)
        ->get('/laporan/bulanan')
        ->assertOk();
});

test('frontdesk tidak dapat mengakses halaman laporan bulanan', function () {
    $user = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);

    actingAs($user)
        ->get('/laporan/bulanan')
        ->assertForbidden();
});

test('report builder menggunakan label hari bahasa indonesia', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);

    expect(collect($report['per_hari'])->firstWhere('date', '2026-04-14')['nama_hari'])->toBe('Sel');
});

test('export excel menghasilkan file xlsx yang valid', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->count(3)->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);
    $export = new LaporanBulananExport($report);

    $response = \Maatwebsite\Excel\Facades\Excel::download($export, 'test.xlsx');

    expect($response->getFile()->getExtension())->toBe('xlsx');
});

test('export pdf menghasilkan file pdf yang valid', function () {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-bulanan', [
        'judulBulan' => 'April 2026',
        'ringkasan' => ['total' => 1, 'completed' => 1, 'waiting' => 0, 'cancelled' => 0],
        'perLayanan' => [['name' => 'Layanan Uji', 'total' => 1, 'completed' => 1, 'cancelled' => 0]],
        'perHari' => [['date' => '2026-04-14', 'hari' => 14, 'nama_hari' => 'Sel', 'total' => 1, 'online' => 1, 'kiosk' => 0, 'assisted' => 0]],
        'perChannel' => [['channel' => 'Online Booking', 'total' => 1, 'persen' => 100.0]],
    ]);

    $response = $pdf->download('test.pdf');

    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('filter bulan menampilkan data yang sesuai', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create();
    $serviceJanuari = Service::factory()->for($pool)->create(['name' => 'Layanan Januari']);
    $serviceMaret = Service::factory()->for($pool)->create(['name' => 'Layanan Maret']);

    QueueTicket::factory()->for($serviceJanuari)->for($pool)->create([
        'service_date' => '2026-01-15',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-01-15 10:00:00',
    ]);

    QueueTicket::factory()->for($serviceMaret)->for($pool)->create([
        'service_date' => '2026-03-20',
        'status' => QueueStatus::Waiting,
        'channel' => 'walk_in_kiosk',
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Reports\LaporanBulanan::class)
        ->set('tahun', 2026)
        ->set('bulan', 1)
        ->assertSee('Layanan Januari')
        ->assertDontSee('Layanan Maret')
        ->set('bulan', 3)
        ->assertSee('Layanan Maret')
        ->assertDontSee('Layanan Januari')
        ->set('bulan', 2)
        ->assertSee('Tidak ada data pendaftar untuk bulan ini.');
});
```

- [ ] **Step 2: Jalankan test dan pastikan GAGAL**

```bash
php artisan test --compact tests/Feature/Reports/LaporanBulananTest.php
```

Expected: FAIL karena route, Livewire component, report builder, export class, dan view PDF belum ada.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Reports/LaporanBulananTest.php
git commit -m "test: tulis failing test untuk laporan bulanan (RED)"
```

---

### Task 3: Buat Shared Report Builder, Livewire Component, Route, dan View (GREEN — akses halaman dan filter)

**Files:**
- Create: `app/Support/Reports/LaporanBulananReportBuilder.php`
- Create: `app/Livewire/Reports/LaporanBulanan.php`
- Modify: `routes/web.php`
- Create: `resources/views/livewire/reports/laporan-bulanan.blade.php`

- [ ] **Step 1: Buat report builder sebagai sumber data tunggal untuk preview, Excel, dan PDF**

```php
<?php

namespace App\Support\Reports;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanBulananReportBuilder
{
    private const CHANNEL_LABELS = [
        'online_booking' => 'Online Booking',
        'walk_in_kiosk' => 'Kiosk Mandiri',
        'assisted_same_day' => 'Dibantu Petugas',
    ];

    /**
     * @return array{
     *     judul_bulan:string,
     *     ringkasan:array{total:int,completed:int,waiting:int,cancelled:int},
     *     per_layanan:list<array{name:string,total:int,completed:int,cancelled:int}>,
     *     per_hari:list<array{date:string,hari:int,nama_hari:string,total:int,online:int,kiosk:int,assisted:int}>,
     *     per_channel:list<array{channel:string,total:int,persen:float}>
     * }
     */
    public function build(int $bulan, int $tahun): array
    {
        $periode = Carbon::create($tahun, $bulan, 1)->locale('id');

        return [
            'judul_bulan' => $periode->translatedFormat('F Y'),
            'ringkasan' => $this->buildRingkasan($periode),
            'per_layanan' => $this->buildPerLayanan($periode),
            'per_hari' => $this->buildPerHari($periode),
            'per_channel' => $this->buildPerChannel($periode),
        ];
    }

    /**
     * @return array{0:string,1:string}
     */
    private function periodBounds(Carbon $periode): array
    {
        return [
            $periode->copy()->startOfMonth()->toDateString(),
            $periode->copy()->endOfMonth()->toDateString(),
        ];
    }

    /**
     * @return array{total:int,completed:int,waiting:int,cancelled:int}
     */
    private function buildRingkasan(Carbon $periode): array
    {
        [$start, $end] = $this->periodBounds($periode);

        $counts = QueueTicket::query()
            ->whereBetween('service_date', [$start, $end])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'total' => (int) array_sum($counts),
            'completed' => (int) ($counts[QueueStatus::Completed->value] ?? 0),
            'waiting' => (int) (($counts[QueueStatus::Booked->value] ?? 0)
                + ($counts[QueueStatus::Waiting->value] ?? 0)
                + ($counts[QueueStatus::Called->value] ?? 0)),
            'cancelled' => (int) (($counts[QueueStatus::Cancelled->value] ?? 0)
                + ($counts[QueueStatus::Skipped->value] ?? 0)),
        ];
    }

    /**
     * @return list<array{name:string,total:int,completed:int,cancelled:int}>
     */
    private function buildPerLayanan(Carbon $periode): array
    {
        [$start, $end] = $this->periodBounds($periode);

        return QueueTicket::query()
            ->whereBetween('service_date', [$start, $end])
            ->join('services', 'queue_tickets.service_id', '=', 'services.id')
            ->selectRaw(
                'services.name as name,
                COUNT(*) as total,
                SUM(CASE WHEN queue_tickets.status = ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN queue_tickets.status IN (?, ?) THEN 1 ELSE 0 END) as cancelled',
                [
                    QueueStatus::Completed->value,
                    QueueStatus::Cancelled->value,
                    QueueStatus::Skipped->value,
                ]
            )
            ->groupBy('services.id', 'services.name')
            ->orderBy('services.name')
            ->get()
            ->map(fn (object $row): array => [
                'name' => $row->name,
                'total' => (int) $row->total,
                'completed' => (int) $row->completed,
                'cancelled' => (int) $row->cancelled,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{date:string,hari:int,nama_hari:string,total:int,online:int,kiosk:int,assisted:int}>
     */
    private function buildPerHari(Carbon $periode): array
    {
        [$start, $end] = $this->periodBounds($periode);

        $endOfMonth = $periode->copy()->endOfMonth();

        /** @var Collection<string, Collection<int, object{date:string,channel:string,total:int}>> $counts */
        $counts = QueueTicket::query()
            ->whereBetween('service_date', [$start, $end])
            ->selectRaw('DATE(service_date) as date, channel, COUNT(*) as total')
            ->groupByRaw('DATE(service_date), channel')
            ->get()
            ->groupBy('date');

        $result = [];

        for ($date = $periode->copy()->startOfMonth(); $date->lte($endOfMonth); $date->addDay()) {
            $dateString = $date->toDateString();
            $dayCounts = $counts->get($dateString, collect());

            $result[] = [
                'date' => $dateString,
                'hari' => $date->day,
                'nama_hari' => $date->copy()->locale('id')->isoFormat('ddd'),
                'total' => (int) $dayCounts->sum('total'),
                'online' => (int) ($dayCounts->firstWhere('channel', 'online_booking')?->total ?? 0),
                'kiosk' => (int) ($dayCounts->firstWhere('channel', 'walk_in_kiosk')?->total ?? 0),
                'assisted' => (int) ($dayCounts->firstWhere('channel', 'assisted_same_day')?->total ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @return list<array{channel:string,total:int,persen:float}>
     */
    private function buildPerChannel(Carbon $periode): array
    {
        [$start, $end] = $this->periodBounds($periode);

        $counts = QueueTicket::query()
            ->whereBetween('service_date', [$start, $end])
            ->selectRaw('channel, COUNT(*) as total')
            ->groupBy('channel')
            ->pluck('total', 'channel')
            ->toArray();

        $total = (int) array_sum($counts);

        return collect(self::CHANNEL_LABELS)
            ->map(function (string $label, string $channel) use ($counts, $total): array {
                $jumlah = (int) ($counts[$channel] ?? 0);

                return [
                    'channel' => $label,
                    'total' => $jumlah,
                    'persen' => $total > 0 ? round(($jumlah / $total) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->all();
    }
}
```

- [ ] **Step 2: Buat Livewire component yang memakai report builder dan export dataset bersama**

```php
<?php

namespace App\Livewire\Reports;

use App\Exports\LaporanBulananExport;
use App\Support\Reports\LaporanBulananReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Title('Laporan Bulanan Pendaftar Layanan')]
class LaporanBulanan extends Component
{
    public int $bulan;

    public int $tahun;

    protected LaporanBulananReportBuilder $reportBuilder;

    public function boot(LaporanBulananReportBuilder $reportBuilder): void
    {
        $this->reportBuilder = $reportBuilder;
    }

    public function mount(): void
    {
        $this->bulan = (int) now()->month;
        $this->tahun = (int) now()->year;
    }

    #[Computed]
    public function report(): array
    {
        return $this->reportBuilder->build($this->bulan, $this->tahun);
    }

    public function downloadExcel(): BinaryFileResponse
    {
        $report = $this->report;

        return Excel::download(
            new LaporanBulananExport($report),
            $this->filenamePrefix($report).'.xlsx'
        );
    }

    public function downloadPdf(): Response
    {
        $report = $this->report;

        $pdf = Pdf::loadView('pdf.laporan-bulanan', [
            'judulBulan' => $report['judul_bulan'],
            'ringkasan' => $report['ringkasan'],
            'perLayanan' => $report['per_layanan'],
            'perHari' => $report['per_hari'],
            'perChannel' => $report['per_channel'],
        ]);

        return $pdf->download($this->filenamePrefix($report).'.pdf');
    }

    /**
     * @param array{judul_bulan:string} $report
     */
    private function filenamePrefix(array $report): string
    {
        return 'Laporan_Bulanan_'.(string) str($report['judul_bulan'])->replace(' ', '_');
    }

    public function render(): View
    {
        return view('livewire.reports.laporan-bulanan');
    }
}
```

- [ ] **Step 3: Tambah route di dalam group laporan yang sudah ada di `routes/web.php`**

Tambahkan route ini di dalam group middleware laporan yang sudah ada. Admin tetap lolos karena middleware `EnsureUserHasRole` proyek ini memang mengizinkan admin melewati guard role monitor.

```php
Route::middleware(['auth', 'verified', 'role:'.UserRole::Monitor->value])->group(function () {
    Route::get('/laporan/antrian', [QueueReportController::class, 'index']);
    Route::get('/laporan/audit', [App\Http\Controllers\Report\AuditTrailController::class, 'index'])->name('laporan.audit');
    Route::get('/laporan/bulanan', \App\Livewire\Reports\LaporanBulanan::class)->name('laporan.bulanan');
});
```

- [ ] **Step 4: Buat view blade yang membaca seluruh data dari computed property `report`**

```blade
@php($report = $this->report)

<div class="space-y-6">
    {{-- Filter Bar --}}
    <flux:card class="p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                    <flux:icon.document-text class="size-5" />
                </div>
                <div>
                    <flux:heading size="lg">Laporan Bulanan Pendaftar Layanan</flux:heading>
                    <flux:text class="text-xs text-zinc-500">Preview data {{ $report['judul_bulan'] }} sebelum export</flux:text>
                </div>
            </div>

            <div class="flex items-end gap-3">
                <flux:field>
                    <flux:label>Bulan</flux:label>
                    <flux:select wire:model.live="bulan">
                        @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $nama)
                            <option value="{{ $i + 1 }}">{{ $nama }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Tahun</flux:label>
                    <flux:select wire:model.live="tahun">
                        @for ($y = (int) now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </flux:select>
                </flux:field>

                <div class="flex gap-2">
                    <flux:button icon="document-text" variant="primary" wire:click="downloadExcel">
                        Excel
                    </flux:button>
                    <flux:button icon="printer" variant="outline" wire:click="downloadPdf">
                        PDF
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <flux:card class="p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-sky-700 uppercase dark:text-sky-300">Total</flux:text>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $report['ringkasan']['total'] }}</p>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                    <flux:icon.ticket class="size-5" />
                </div>
            </div>
        </flux:card>

        <flux:card class="p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase dark:text-emerald-300">Dilayani</flux:text>
                    <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-400">{{ $report['ringkasan']['completed'] }}</p>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                    <flux:icon.check-circle class="size-5" />
                </div>
            </div>
        </flux:card>

        <flux:card class="p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-amber-700 uppercase dark:text-amber-300">Menunggu</flux:text>
                    <p class="text-3xl font-bold text-amber-700 dark:text-amber-400">{{ $report['ringkasan']['waiting'] }}</p>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                    <flux:icon.clock class="size-5" />
                </div>
            </div>
        </flux:card>

        <flux:card class="p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-red-700 uppercase dark:text-red-300">Dibatalkan</flux:text>
                    <p class="text-3xl font-bold text-red-700 dark:text-red-400">{{ $report['ringkasan']['cancelled'] }}</p>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                    <flux:icon.x-circle class="size-5" />
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Tabel: Per Layanan --}}
    <flux:card class="p-5">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                <flux:icon.clipboard-document-list class="size-5" />
            </div>
            <flux:heading size="sm">Rekap Per Layanan</flux:heading>
        </div>

        @if (count($report['per_layanan']) > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Layanan</flux:table.column>
                    <flux:table.column>Total</flux:table.column>
                    <flux:table.column>Selesai</flux:table.column>
                    <flux:table.column>Dibatalkan</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($report['per_layanan'] as $row)
                        <flux:table.row wire:key="per-layanan-{{ $row['name'] }}">
                            <flux:table.cell class="font-medium">{{ $row['name'] }}</flux:table.cell>
                            <flux:table.cell>{{ $row['total'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="green">{{ $row['completed'] }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="red">{{ $row['cancelled'] }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="py-8 text-center text-zinc-400">
                <p>Tidak ada data pendaftar untuk bulan ini.</p>
            </div>
        @endif
    </flux:card>

    {{-- Tabel: Per Hari --}}
    <flux:card class="p-5">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                <flux:icon.calendar-days class="size-5" />
            </div>
            <flux:heading size="sm">Detail Per Hari</flux:heading>
        </div>

        @if (collect($report['per_hari'])->sum('total') > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Tanggal</flux:table.column>
                    <flux:table.column>Total</flux:table.column>
                    <flux:table.column>Online</flux:table.column>
                    <flux:table.column>Kiosk</flux:table.column>
                    <flux:table.column>Petugas</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($report['per_hari'] as $row)
                        @if ($row['total'] > 0)
                            <flux:table.row wire:key="per-hari-{{ $row['date'] }}">
                                <flux:table.cell class="font-medium">
                                    {{ $row['hari'] }}
                                    <span class="ml-1 text-xs text-zinc-400">{{ $row['nama_hari'] }}</span>
                                </flux:table.cell>
                                <flux:table.cell>{{ $row['total'] }}</flux:table.cell>
                                <flux:table.cell>{{ $row['online'] }}</flux:table.cell>
                                <flux:table.cell>{{ $row['kiosk'] }}</flux:table.cell>
                                <flux:table.cell>{{ $row['assisted'] }}</flux:table.cell>
                            </flux:table.row>
                        @endif
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="py-8 text-center text-zinc-400">
                <p>Tidak ada data pendaftar untuk bulan ini.</p>
            </div>
        @endif
    </flux:card>

    {{-- Tabel: Per Channel --}}
    <flux:card class="p-5">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-900/50 dark:text-fuchsia-400">
                <flux:icon.signal class="size-5" />
            </div>
            <flux:heading size="sm">Distribusi Channel</flux:heading>
        </div>

        @if (collect($report['per_channel'])->sum('total') > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Channel</flux:table.column>
                    <flux:table.column>Jumlah</flux:table.column>
                    <flux:table.column>Persentase</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($report['per_channel'] as $row)
                        <flux:table.row wire:key="per-channel-{{ $row['channel'] }}">
                            <flux:table.cell class="font-medium">{{ $row['channel'] }}</flux:table.cell>
                            <flux:table.cell>{{ $row['total'] }}</flux:table.cell>
                            <flux:table.cell>{{ $row['persen'] }}%</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="py-8 text-center text-zinc-400">
                <p>Tidak ada data pendaftar untuk bulan ini.</p>
            </div>
        @endif
    </flux:card>
</div>
```

- [ ] **Step 5: Commit**

```bash
git add app/Support/Reports/LaporanBulananReportBuilder.php app/Livewire/Reports/LaporanBulanan.php routes/web.php resources/views/livewire/reports/laporan-bulanan.blade.php
git commit -m "feat: buat halaman laporan bulanan dengan shared report builder"
```

---

### Task 4: Buat Excel Export Classes yang Mengonsumsi Dataset Builder (GREEN untuk export test)

**Files:**
- Create: `app/Exports/LaporanBulananExport.php`
- Create: `app/Exports/Sheets/PerLayananSheet.php`
- Create: `app/Exports/Sheets/PerHariSheet.php`
- Create: `app/Exports/Sheets/PerChannelSheet.php`

- [ ] **Step 1: Buat Sheet Per Layanan dari array hasil builder**

```php
<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerLayananSheet implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param list<array{name:string,total:int,completed:int,cancelled:int}> $rows
     */
    public function __construct(
        private array $rows,
    ) {}

    public function array(): array
    {
        return array_map(fn (array $row): array => [
            $row['name'],
            $row['total'],
            $row['completed'],
            $row['cancelled'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return ['Layanan', 'Total', 'Selesai', 'Dibatalkan'];
    }

    public function title(): string
    {
        return 'Per Layanan';
    }
}
```

- [ ] **Step 2: Buat Sheet Per Hari dari array hasil builder dengan label hari Indonesia**

```php
<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerHariSheet implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param list<array{date:string,hari:int,nama_hari:string,total:int,online:int,kiosk:int,assisted:int}> $rows
     */
    public function __construct(
        private array $rows,
    ) {}

    public function array(): array
    {
        return array_map(fn (array $row): array => [
            Carbon::parse($row['date'])->format('d/m/Y'),
            $row['nama_hari'],
            $row['total'],
            $row['online'],
            $row['kiosk'],
            $row['assisted'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return ['Tanggal', 'Hari', 'Total', 'Online', 'Kiosk', 'Petugas'];
    }

    public function title(): string
    {
        return 'Per Hari';
    }
}
```

- [ ] **Step 3: Buat Sheet Per Channel dari array hasil builder**

```php
<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerChannelSheet implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param list<array{channel:string,total:int,persen:float}> $rows
     */
    public function __construct(
        private array $rows,
    ) {}

    public function array(): array
    {
        return array_map(fn (array $row): array => [
            $row['channel'],
            $row['total'],
            $row['persen'].'%',
        ], $this->rows);
    }

    public function headings(): array
    {
        return ['Channel', 'Jumlah', 'Persentase'];
    }

    public function title(): string
    {
        return 'Per Channel';
    }
}
```

- [ ] **Step 4: Buat export class utama yang meneruskan dataset builder ke tiap sheet**

```php
<?php

namespace App\Exports;

use App\Exports\Sheets\PerChannelSheet;
use App\Exports\Sheets\PerHariSheet;
use App\Exports\Sheets\PerLayananSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanBulananExport implements WithMultipleSheets
{
    /**
     * @param array{
     *     judul_bulan:string,
     *     ringkasan:array{total:int,completed:int,waiting:int,cancelled:int},
     *     per_layanan:list<array{name:string,total:int,completed:int,cancelled:int}>,
     *     per_hari:list<array{date:string,hari:int,nama_hari:string,total:int,online:int,kiosk:int,assisted:int}>,
     *     per_channel:list<array{channel:string,total:int,persen:float}>
     * } $report
     */
    public function __construct(
        private array $report,
    ) {}

    public function sheets(): array
    {
        return [
            new PerLayananSheet($this->report['per_layanan']),
            new PerHariSheet($this->report['per_hari']),
            new PerChannelSheet($this->report['per_channel']),
        ];
    }
}
```

- [ ] **Step 5: Commit**

```bash
git add app/Exports/LaporanBulananExport.php app/Exports/Sheets/PerLayananSheet.php app/Exports/Sheets/PerHariSheet.php app/Exports/Sheets/PerChannelSheet.php
git commit -m "feat: buat Excel export multi-sheet untuk laporan bulanan"
```

---

### Task 5: Buat PDF Template (GREEN untuk PDF test)

**Files:**
- Create: `resources/views/pdf/laporan-bulanan.blade.php`

- [ ] **Step 1: Buat template PDF yang memakai locale Indonesia dan dataset builder yang sama**

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan Pendaftar Layanan</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0 0 4px 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 11px; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background-color: #f0f0f0; font-size: 10px; font-weight: bold; }
        td { font-size: 10px; }
        .text-center { text-align: center; }
        .section-title { font-size: 12px; font-weight: bold; margin: 20px 0 8px 0; }
        .footer { margin-top: 40px; text-align: right; }
        .footer .signature { display: inline-block; margin-top: 60px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('institution.name') }}</h2>
        @if (config('institution.address'))
            <p>{{ config('institution.address') }}</p>
        @endif
        <p>Jam Operasional: {{ config('institution.operating_hours') }}</p>
    </div>

    <div class="title">
        LAPORAN BULANAN PENDAFTAR LAYANAN<br>
        {{ $judulBulan }}
    </div>

    <div class="section-title">A. Ringkasan Statistik</div>
    <table>
        <tr>
            <th>Keterangan</th>
            <th class="text-center">Jumlah</th>
        </tr>
        <tr><td>Total Pendaftar</td><td class="text-center">{{ $ringkasan['total'] }}</td></tr>
        <tr><td>Selesai Dilayani</td><td class="text-center">{{ $ringkasan['completed'] }}</td></tr>
        <tr><td>Menunggu / Dalam Proses</td><td class="text-center">{{ $ringkasan['waiting'] }}</td></tr>
        <tr><td>Dibatalkan / Dilewati</td><td class="text-center">{{ $ringkasan['cancelled'] }}</td></tr>
    </table>

    <div class="section-title">B. Rekap Per Layanan</div>
    <table>
        <tr>
            <th>Layanan</th>
            <th class="text-center">Total</th>
            <th class="text-center">Selesai</th>
            <th class="text-center">Dibatalkan</th>
        </tr>
        @foreach ($perLayanan as $row)
        <tr>
            <td>{{ $row['name'] }}</td>
            <td class="text-center">{{ $row['total'] }}</td>
            <td class="text-center">{{ $row['completed'] }}</td>
            <td class="text-center">{{ $row['cancelled'] }}</td>
        </tr>
        @endforeach
    </table>

    <div class="section-title">C. Detail Per Hari</div>
    <table>
        <tr>
            <th>Tanggal</th>
            <th>Hari</th>
            <th class="text-center">Total</th>
            <th class="text-center">Online</th>
            <th class="text-center">Kiosk</th>
            <th class="text-center">Petugas</th>
        </tr>
        @foreach ($perHari as $row)
        @if ($row['total'] > 0)
        <tr>
            <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m') }}</td>
            <td>{{ $row['nama_hari'] }}</td>
            <td class="text-center">{{ $row['total'] }}</td>
            <td class="text-center">{{ $row['online'] }}</td>
            <td class="text-center">{{ $row['kiosk'] }}</td>
            <td class="text-center">{{ $row['assisted'] }}</td>
        </tr>
        @endif
        @endforeach
    </table>

    <div class="section-title">D. Distribusi Channel</div>
    <table>
        <tr>
            <th>Channel</th>
            <th class="text-center">Jumlah</th>
            <th class="text-center">Persentase</th>
        </tr>
        @foreach ($perChannel as $row)
        <tr>
            <td>{{ $row['channel'] }}</td>
            <td class="text-center">{{ $row['total'] }}</td>
            <td class="text-center">{{ $row['persen'] }}%</td>
        </tr>
        @endforeach
    </table>

    <div class="footer">
        <p>{{ config('institution.name') }}, {{ now()->locale('id')->translatedFormat('d F Y') }}</p>
        <div class="signature">
            <p>(_____________________________)</p>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/pdf/laporan-bulanan.blade.php
git commit -m "feat: buat template PDF untuk laporan bulanan"
```

---

### Task 6: Jalankan Test Laporan Bulanan (VERIFIKASI GREEN)

- [ ] **Step 1: Jalankan test laporan bulanan**

```bash
php artisan test --compact tests/Feature/Reports/LaporanBulananTest.php
```

Expected: PASS (7/7).

Jika ada test yang gagal, perbaiki lalu jalankan ulang test yang sama.

- [ ] **Step 2: Commit perbaikan jika ada**

```bash
git add -A
git commit -m "fix: perbaiki test laporan bulanan"
```

---

### Task 7: Tambah Navigasi ke Laporan Bulanan dan Coverage Test

**Files:**
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Modify: `resources/views/layouts/app/header.blade.php`
- Modify: `tests/Feature/Navigation/PtspNavigationTest.php`

- [ ] **Step 1: Tambah link di sidebar desktop**

Di `resources/views/layouts/app/sidebar.blade.php`, di dalam blok monitor setelah item `Audit Trail`, tambahkan:

```blade
                        <flux:sidebar.item icon="chart-bar" :href="route('laporan.bulanan')" :current="request()->routeIs('laporan.bulanan')" wire:navigate>
                            {{ __('Laporan Bulanan') }}
                        </flux:sidebar.item>
```

Link ini tetap berada di dalam blok role monitor yang sudah ada agar mengikuti perilaku `activeRole()` untuk admin.

- [ ] **Step 2: Tambah link di header desktop dan menu mobile**

Di `resources/views/layouts/app/header.blade.php`, di blok desktop `flux:navbar` setelah link `Laporan`, tambahkan:

```blade
                        <flux:navbar.item icon="chart-bar" :href="route('laporan.bulanan')" :current="request()->routeIs('laporan.bulanan')" wire:navigate>
                            {{ __('Laporan Bulanan') }}
                        </flux:navbar.item>
```

Di blok mobile `flux:sidebar.group` setelah item `Audit Trail`, tambahkan:

```blade
                        <flux:sidebar.item icon="chart-bar" :href="route('laporan.bulanan')" :current="request()->routeIs('laporan.bulanan')" wire:navigate>
                            {{ __('Laporan Bulanan') }}
                        </flux:sidebar.item>
```

Keduanya diletakkan di blok `@if` yang sama dengan link laporan monitor yang sudah ada.

- [ ] **Step 3: Perluas test navigasi agar link baru ter-cover**

Perbarui `tests/Feature/Navigation/PtspNavigationTest.php` menjadi:

```php
<?php

use App\Enums\UserRole;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('public landing page exposes PTSP public links', function () {
    $response = get('/');

    $response->assertOk()
        ->assertSee('/antrian')
        ->assertSee('/antrian/cek');
});

test('dashboard shows role-aware navigation links', function () {
    $frontdesk = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);
    $monitor = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    actingAs($frontdesk);

    get('/dashboard')
        ->assertOk()
        ->assertSee('/frontdesk/antrian')
        ->assertSee('Modul Panggilan Petugas')
        ->assertDontSee('/laporan/bulanan')
        ->assertDontSee('/admin/layanan');

    actingAs($monitor);

    get('/dashboard')
        ->assertOk()
        ->assertSee('/laporan/antrian')
        ->assertSee('/laporan/bulanan')
        ->assertSee('Ringkasan Monitoring')
        ->assertDontSee('/admin/layanan');

    actingAs($admin);

    get('/dashboard')
        ->assertOk()
        ->assertSee('/admin/layanan')
        ->assertSee('/laporan/bulanan')
        ->assertSee('Health Aplikasi')
        ->assertSee('Shortcut Manajemen');
});
```

- [ ] **Step 4: Jalankan test laporan bulanan dan navigasi**

```bash
php artisan test --compact tests/Feature/Reports/LaporanBulananTest.php tests/Feature/Navigation/PtspNavigationTest.php
```

Expected: PASS untuk kedua file test.

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/app/sidebar.blade.php resources/views/layouts/app/header.blade.php tests/Feature/Navigation/PtspNavigationTest.php
git commit -m "feat: tambah navigasi ke laporan bulanan di sidebar dan header"
```

---

### Task 8: Format Kode dan Verifikasi Final

**Files:** Semua file PHP yang diubah.

- [ ] **Step 1: Jalankan Laravel Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 2: Jalankan seluruh test suite**

```bash
php artisan test --compact
```

Expected: Semua test PASS.

- [ ] **Step 3: Commit jika ada perubahan format**

```bash
git add -A
git commit -m "style: format kode dengan pint"
```

Jika tidak ada perubahan setelah Pint, lewati commit ini.

---
