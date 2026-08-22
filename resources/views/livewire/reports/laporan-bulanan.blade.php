<div class="space-y-6">
    {{-- Print-Only Formal Header --}}
    <div class="hidden print:block mb-6 border-b-2 border-zinc-900 pb-4 text-center">
        <h1 class="text-xl font-bold uppercase tracking-wider text-zinc-900">{{ config('institution.name', 'Pengadilan Agama') }}</h1>
        <h2 class="text-base font-semibold uppercase tracking-wide text-zinc-800 mt-1">Laporan Bulanan Rekapitulasi Pendaftar Layanan PTSP</h2>
        <p class="text-xs text-zinc-600 mt-1">
            Periode: {{ Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM YYYY') }}
            &bull; Dicetak: {{ now()->translatedFormat('d/m/Y H:i') }}
        </p>
    </div>

    {{-- Breadcrumbs & Header --}}
    <div class="animate-fade-in-up flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
        <div class="space-y-1">
            <flux:breadcrumbs class="mb-1">
                <flux:breadcrumbs.item :href="route('dashboard')" icon="home" aria-label="Beranda" />
                <flux:breadcrumbs.item>Laporan</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Bulanan</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1" class="font-extrabold tracking-tight">Laporan Bulanan</flux:heading>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-bold text-cyan-800 ring-1 ring-cyan-200/60 dark:bg-cyan-950/50 dark:text-cyan-300 dark:ring-cyan-800/40">
                    {{ Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM YYYY') }}
                </span>
            </div>
            <flux:subheading class="text-zinc-600 dark:text-zinc-400">
                Sintesis kinerja pelayanan bulanan, distribusi per jenis layanan, kanal registrasi, dan tren harian.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            @if ($report['ringkasan']['total'] > 0)
                <flux:button 
                    icon="document-text" 
                    variant="primary" 
                    wire:click="downloadExcel" 
                    wire:loading.attr="disabled" 
                    wire:target="downloadExcel" 
                    class="font-semibold text-xs shadow-2xs bg-emerald-700 hover:bg-emerald-600 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white cursor-pointer" 
                    aria-label="Unduh Laporan Excel"
                >
                    <span wire:loading.remove wire:target="downloadExcel" class="flex items-center gap-1.5">
                        <flux:icon.arrow-down-tray class="size-3.5" />
                        Ekspor Excel
                    </span>
                    <span wire:loading wire:target="downloadExcel" class="flex items-center gap-1.5">
                        <svg class="size-3.5 animate-spin motion-reduce:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyiapkan...
                    </span>
                </flux:button>

                <flux:button 
                    icon="printer" 
                    variant="filled" 
                    wire:click="downloadPdf" 
                    wire:loading.attr="disabled" 
                    wire:target="downloadPdf" 
                    class="font-semibold text-xs shadow-2xs cursor-pointer" 
                    aria-label="Unduh Laporan PDF"
                >
                    <span wire:loading.remove wire:target="downloadPdf" class="flex items-center gap-1.5">
                        <flux:icon.printer class="size-3.5" />
                        Cetak PDF
                    </span>
                    <span wire:loading wire:target="downloadPdf" class="flex items-center gap-1.5">
                        <svg class="size-3.5 animate-spin motion-reduce:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mencetak...
                    </span>
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Filter Bulan & Tahun Card --}}
    <div class="animate-fade-in-up print:hidden" style="animation-delay: 75ms;">
        <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/70 dark:text-cyan-300">
                        <flux:icon.funnel class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold">Filter Periode Bulan</flux:heading>
                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Pilih periode kalender untuk mengkalkulasi agregat bulanan.</flux:text>
                    </div>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:w-auto sm:grid-cols-2 md:flex md:items-end">
                    <flux:field class="w-full sm:w-48">
                        <flux:label class="text-xs font-semibold">Bulan</flux:label>
                        <flux:select wire:model.live="bulan">
                            @foreach (range(1, 12) as $m)
                                <flux:select.option wire:key="bulan-{{ $m }}" value="{{ $m }}">
                                    {{ Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field class="w-full sm:w-36">
                        <flux:label class="text-xs font-semibold">Tahun</flux:label>
                        <flux:select wire:model.live="tahun">
                            @foreach (range(today()->year, today()->subYears(5)->year) as $th)
                                <flux:select.option wire:key="tahun-{{ $th }}" value="{{ $th }}">{{ $th }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            </div>
        </flux:card>
    </div>

    @if ($report['ringkasan']['total'] > 0)
        @php
            $tot = $report['ringkasan']['total'];
            $comp = $report['ringkasan']['completed'];
            $wait = $report['ringkasan']['waiting'];
            $canc = $report['ringkasan']['cancelled'];
            $compRate = $tot > 0 ? round(($comp / $tot) * 100) : 0;
            $cancRate = $tot > 0 ? round(($canc / $tot) * 100) : 0;
        @endphp

        {{-- Executive KPI Metrics --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-fade-in-up print:grid-cols-4 print:gap-3" style="animation-delay: 150ms;">
            {{-- Total Pendaftar --}}
            <flux:card class="admin-stat-total admin-card-elevated rounded-3xl p-5 relative overflow-hidden print:shadow-none print:border print:border-zinc-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Total Pendaftar</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-sky-200/70 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300 print:hidden">
                        <flux:icon.ticket class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($tot) }}</span>
                    <span class="text-xs font-medium text-sky-800 dark:text-sky-300">tiket terdaftar</span>
                </div>
            </flux:card>

            {{-- Selesai Dilayani --}}
            <flux:card class="admin-stat-success admin-card-elevated rounded-3xl p-5 relative overflow-hidden print:shadow-none print:border print:border-zinc-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Selesai Dilayani</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-emerald-200/70 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300 print:hidden">
                        <flux:icon.check-circle class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($comp) }}</span>
                    <span class="text-xs font-medium text-emerald-800 dark:text-emerald-300">({{ $compRate }}% rasio)</span>
                </div>
            </flux:card>

            {{-- Menunggu / Berjalan --}}
            <flux:card class="admin-stat-warning admin-card-elevated rounded-3xl p-5 relative overflow-hidden print:shadow-none print:border print:border-zinc-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Menunggu</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-amber-200/70 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300 print:hidden">
                        <flux:icon.clock class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($wait) }}</span>
                    <span class="text-xs font-medium text-amber-800 dark:text-amber-300">dalam antrian</span>
                </div>
            </flux:card>

            {{-- Dibatalkan --}}
            <flux:card class="admin-stat-danger admin-card-elevated rounded-3xl p-5 relative overflow-hidden print:shadow-none print:border print:border-zinc-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-red-800 dark:text-red-300">Dibatalkan</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-red-200/70 text-red-700 dark:bg-red-900/60 dark:text-red-300 print:hidden">
                        <flux:icon.x-circle class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($canc) }}</span>
                    <span class="text-xs font-medium text-red-800 dark:text-red-300">({{ $cancRate }}% rasio)</span>
                </div>
            </flux:card>
        </div>

        {{-- Breakdown Grid: Per Layanan & Per Kanal --}}
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-12 print:grid-cols-1 print:gap-4">
            {{-- Per Layanan (8 Cols) --}}
            <div class="lg:col-span-8 animate-fade-in-up print:break-inside-avoid" style="animation-delay: 225ms;">
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-7 dark:border-zinc-800 dark:bg-zinc-900 h-full flex flex-col justify-between print:shadow-none print:border-zinc-300">
                    <div>
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800 print:border-zinc-300">
                            <div class="admin-icon-box bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 print:hidden">
                                <flux:icon.clipboard-document-list class="size-5" />
                            </div>
                            <div>
                                <flux:heading size="lg" class="font-bold">Rekapitulasi Per Layanan</flux:heading>
                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Distribusi volume pendaftar per jenis layanan pengadilan.</flux:text>
                            </div>
                        </div>

                        @if (count($report['per_layanan']) > 0)
                            <div class="admin-table-scroll mt-4 overflow-x-auto">
                                <flux:table aria-label="Tabel rekapitulasi bulanan per layanan">
                                    <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40 print:bg-transparent print:table-header-group">
                                        <flux:table.column class="text-xs font-bold uppercase tracking-wider">Nama Layanan</flux:table.column>
                                        <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Total</flux:table.column>
                                        <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Selesai</flux:table.column>
                                        <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Dibatalkan</flux:table.column>
                                    </flux:table.columns>
                                    <flux:table.rows class="admin-row-stagger">
                                        @foreach ($report['per_layanan'] as $item)
                                            <flux:table.row wire:key="layanan-{{ $item['id'] }}" class="admin-row-enter transition-colors hover:bg-emerald-50/30 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                                <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                                    {{ $item['name'] }}
                                                </flux:table.cell>
                                                <flux:table.cell class="text-right whitespace-nowrap">
                                                    <flux:badge size="sm" color="zinc" class="font-bold font-mono">{{ $item['total'] }}</flux:badge>
                                                </flux:table.cell>
                                                <flux:table.cell class="text-right whitespace-nowrap">
                                                    <flux:badge size="sm" color="emerald" class="font-bold font-mono">{{ $item['completed'] }}</flux:badge>
                                                </flux:table.cell>
                                                <flux:table.cell class="text-right whitespace-nowrap">
                                                    <flux:badge size="sm" color="red" class="font-bold font-mono">{{ $item['cancelled'] }}</flux:badge>
                                                </flux:table.cell>
                                            </flux:table.row>
                                        @endforeach
                                    </flux:table.rows>
                                </flux:table>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-10 text-center">
                                <div class="admin-empty-icon flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    <flux:icon.inbox class="size-6" />
                                </div>
                                <p class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Tidak ada data layanan</p>
                                <p class="mt-1 text-xs text-zinc-500">Belum ada layanan yang mencatat antrian pada bulan ini.</p>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>

            {{-- Per Kanal Pendaftaran (4 Cols) --}}
            <div class="lg:col-span-4 animate-fade-in-up print:break-inside-avoid" style="animation-delay: 300ms;">
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-7 dark:border-zinc-800 dark:bg-zinc-900 h-full flex flex-col justify-between print:shadow-none print:border-zinc-300">
                    <div>
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800 print:border-zinc-300">
                            <div class="admin-icon-box bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-950/70 dark:text-fuchsia-300 print:hidden">
                                <flux:icon.signal class="size-5" />
                            </div>
                            <div>
                                <flux:heading size="lg" class="font-bold">Kanal Registrasi</flux:heading>
                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Asal registrasi permohonan antrian.</flux:text>
                            </div>
                        </div>

                        <div class="admin-table-scroll mt-4 overflow-x-auto">
                            <flux:table aria-label="Tabel distribusi kanal registrasi">
                                <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40 print:bg-transparent print:table-header-group">
                                    <flux:table.column class="text-xs font-bold uppercase tracking-wider">Kanal</flux:table.column>
                                    <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Total</flux:table.column>
                                    <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Rasio</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows class="admin-row-stagger">
                                    @foreach ($report['per_channel'] as $item)
                                        <flux:table.row wire:key="channel-{{ $item['channel'] }}" class="admin-row-enter transition-colors hover:bg-fuchsia-50/30 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                            <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                                {{ $item['channel'] }}
                                            </flux:table.cell>
                                            <flux:table.cell class="text-right whitespace-nowrap">
                                                <flux:badge size="sm" color="fuchsia" class="font-bold font-mono">{{ $item['total'] }}</flux:badge>
                                            </flux:table.cell>
                                            <flux:table.cell class="text-right whitespace-nowrap font-mono text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                                {{ $item['persen'] }}%
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Per Hari Timeline Breakdown --}}
        <div class="animate-fade-in-up print:break-inside-avoid" style="animation-delay: 375ms;">
            <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-7 dark:border-zinc-800 dark:bg-zinc-900 print:shadow-none print:border-zinc-300">
                <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800 print:border-zinc-300">
                    <div class="admin-icon-box bg-indigo-100 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 print:hidden">
                        <flux:icon.calendar-days class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold">Aktivitas Harian</flux:heading>
                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Rincian beban antrian per tanggal kalender sepanjang bulan.</flux:text>
                    </div>
                </div>

                <div class="admin-table-scroll mt-4 overflow-x-auto">
                    <flux:table aria-label="Tabel rincian aktivitas antrian per hari">
                        <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40 print:bg-transparent print:table-header-group">
                            <flux:table.column class="text-xs font-bold uppercase tracking-wider">Tanggal</flux:table.column>
                            <flux:table.column class="text-xs font-bold uppercase tracking-wider">Hari</flux:table.column>
                            <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Total</flux:table.column>
                            <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Online</flux:table.column>
                            <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Kiosk</flux:table.column>
                            <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Petugas</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows class="admin-row-stagger">
                            @foreach ($report['per_hari'] as $item)
                                @php
                                    $isWeekend = in_array($item['nama_hari'], ['Sab', 'Min', 'Sabtu', 'Minggu']);
                                    $hasTraffic = $item['total'] > 0;
                                @endphp
                                <flux:table.row wire:key="hari-{{ $item['date'] }}" class="admin-row-enter transition-colors {{ $hasTraffic ? 'hover:bg-indigo-50/30 dark:hover:bg-zinc-800/60' : 'opacity-60 bg-zinc-50/30 dark:bg-zinc-800/20' }}" style="--stagger-i: {{ $loop->index }}">
                                    <flux:table.cell class="whitespace-nowrap font-mono font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                                        {{ $item['date'] }}
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap font-medium text-xs {{ $isWeekend ? 'text-amber-800 dark:text-amber-400 font-bold' : 'text-zinc-700 dark:text-zinc-300' }}">
                                        {{ $item['nama_hari'] }}
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right whitespace-nowrap">
                                        @if ($hasTraffic)
                                            <flux:badge size="sm" color="indigo" class="font-bold font-mono">{{ $item['total'] }}</flux:badge>
                                        @else
                                            <span class="text-xs font-mono text-zinc-400">-</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right whitespace-nowrap font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                        {{ $item['online'] > 0 ? $item['online'] : '-' }}
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right whitespace-nowrap font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                        {{ $item['kiosk'] > 0 ? $item['kiosk'] : '-' }}
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right whitespace-nowrap font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                        {{ $item['assisted'] > 0 ? $item['assisted'] : '-' }}
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>
    @else
        {{-- Empty State --}}
        <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-12 text-center dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="admin-empty-icon flex size-16 items-center justify-center rounded-3xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon.inbox class="size-8" />
                </div>
                <div>
                    <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-zinc-100">Tidak ada data pendaftar untuk bulan ini.</flux:heading>
                    <flux:text class="mt-2 text-xs text-zinc-500 max-w-sm mx-auto">
                        Silakan pilih bulan dan tahun lain untuk melihat laporan.
                    </flux:text>
                </div>
            </div>
        </flux:card>
    @endif
</div>

