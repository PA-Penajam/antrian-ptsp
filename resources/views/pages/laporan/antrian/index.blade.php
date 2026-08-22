<x-layouts::app :title="__('Laporan Antrian')">
    <div class="w-full space-y-6">
        {{-- Breadcrumbs & Header --}}
        <div class="animate-fade-in-up flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" aria-label="Beranda" />
                    <flux:breadcrumbs.item>Laporan</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Antrian</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <flux:heading size="xl" level="1" class="font-extrabold tracking-tight">Laporan Antrian</flux:heading>
                <flux:subheading class="text-zinc-600 dark:text-zinc-400">
                    Rekapitulasi aktivitas layanan antrian per layanan, loket, petugas, dan status operasional.
                </flux:subheading>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <flux:button 
                    type="button" 
                    variant="filled" 
                    icon="printer" 
                    class="font-semibold text-xs shadow-2xs"
                    onclick="window.print()"
                    aria-label="Cetak atau ekspor laporan ke PDF"
                >
                    Cetak Laporan
                </flux:button>
            </div>
        </div>

        {{-- Filter Tanggal & Quick Presets --}}
        <div class="animate-fade-in-up" style="animation-delay: 75ms;">
            <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900" x-data="{
                from: '{{ $from }}',
                to: '{{ $to }}',
                filtering: false,
                setPreset(days) {
                    const today = new Date();
                    const toStr = today.toISOString().split('T')[0];
                    let fromDate = new Date();
                    
                    if (days === 0) {
                        fromDate = today;
                    } else if (days === 'month') {
                        fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    } else {
                        fromDate.setDate(today.getDate() - days);
                    }
                    
                    this.from = fromDate.toISOString().split('T')[0];
                    this.to = toStr;
                    this.$nextTick(() => this.$refs.filterForm.submit());
                }
            }">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/70 dark:text-cyan-300">
                            <flux:icon.funnel class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold">Filter Periode Laporan</flux:heading>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-zinc-600 dark:text-zinc-400">Rentang aktif:</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-bold text-cyan-800 ring-1 ring-cyan-200/60 dark:bg-cyan-950/50 dark:text-cyan-300 dark:ring-cyan-800/40">
                                    {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form 
                        x-ref="filterForm"
                        method="GET" 
                        action="{{ url('/laporan/antrian') }}" 
                        class="flex flex-col gap-3 sm:flex-row sm:items-end"
                        x-bind:aria-busy="filtering"
                        @submit="filtering = true"
                    >
                        <flux:field class="w-full sm:w-40">
                            <flux:label class="text-xs font-semibold">Dari Tanggal</flux:label>
                            <flux:input type="date" name="from" x-model="from" required class="w-full" />
                        </flux:field>

                        <flux:field class="w-full sm:w-40">
                            <flux:label class="text-xs font-semibold">Sampai Tanggal</flux:label>
                            <flux:input type="date" name="to" x-model="to" required class="w-full" />
                        </flux:field>

                        <flux:button 
                            type="submit" 
                            variant="primary" 
                            class="w-full sm:w-auto bg-cyan-700 font-bold text-white shadow-md shadow-cyan-700/20 hover:bg-cyan-600 dark:bg-cyan-700 dark:text-white dark:hover:bg-cyan-600 px-5"
                            x-bind:disabled="filtering"
                        >
                            <span x-show="!filtering" class="flex items-center gap-1.5">
                                <flux:icon.funnel class="size-4" />
                                Terapkan
                            </span>
                            <span x-show="filtering" class="flex items-center gap-1.5" style="display: none;">
                                <svg class="size-4 animate-spin motion-reduce:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memuat...
                            </span>
                        </flux:button>
                    </form>
                </div>

                {{-- Quick Filter Presets --}}
                <div class="mt-4 pt-3.5 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-zinc-500 font-medium mr-1">Preset Cepat:</span>
                    @if ($from === now()->toDateString() && $to === now()->toDateString())
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-cyan-700 text-white dark:bg-cyan-600 dark:text-white shadow-2xs" @click="setPreset(0)">
                            Hari Ini
                        </button>
                    @else
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" @click="setPreset(0)">
                            Hari Ini
                        </button>
                    @endif

                    @if ($from === now()->subDays(6)->toDateString() && $to === now()->toDateString())
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-cyan-700 text-white dark:bg-cyan-600 dark:text-white shadow-2xs" @click="setPreset(6)">
                            7 Hari Terakhir
                        </button>
                    @else
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" @click="setPreset(6)">
                            7 Hari Terakhir
                        </button>
                    @endif

                    @if ($from === now()->subDays(29)->toDateString() && $to === now()->toDateString())
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-cyan-700 text-white dark:bg-cyan-600 dark:text-white shadow-2xs" @click="setPreset(29)">
                            30 Hari Terakhir
                        </button>
                    @else
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" @click="setPreset(29)">
                            30 Hari Terakhir
                        </button>
                    @endif

                    @if ($from === now()->startOfMonth()->toDateString() && $to === now()->toDateString())
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-cyan-700 text-white dark:bg-cyan-600 dark:text-white shadow-2xs" @click="setPreset('month')">
                            Bulan Ini
                        </button>
                    @else
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700" @click="setPreset('month')">
                            Bulan Ini
                        </button>
                    @endif
                </div>
            </flux:card>
        </div>

        @php
            $totalTickets = array_sum($report['by_service']);
            $completedTickets = $report['by_status']['completed'] ?? 0;
            $activeServicesCount = count($report['by_service']);
            $activeCountersCount = count($report['by_counter']);
            $activeOfficersCount = count($report['by_officer']);
        @endphp

        {{-- Executive KPI Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 animate-fade-in-up" style="animation-delay: 150ms;">
            {{-- Total Antrian --}}
            <flux:card class="admin-stat-total admin-card-elevated rounded-3xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Total Tiket</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-sky-200/70 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300">
                        <flux:icon.ticket class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($totalTickets) }}</span>
                    <span class="text-xs font-medium text-sky-800 dark:text-sky-300">tiket teregister</span>
                </div>
            </flux:card>

            {{-- Tiket Selesai --}}
            <flux:card class="admin-stat-success admin-card-elevated rounded-3xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Tiket Selesai</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-emerald-200/70 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                        <flux:icon.check-circle class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($completedTickets) }}</span>
                    <span class="text-xs font-medium text-emerald-800 dark:text-emerald-300">
                        @if ($totalTickets > 0)
                            ({{ round(($completedTickets / $totalTickets) * 100) }}% rate)
                        @else
                            (0%)
                        @endif
                    </span>
                </div>
            </flux:card>

            {{-- Layanan Terdaftar --}}
            <flux:card class="admin-stat-warning admin-card-elevated rounded-3xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Layanan Aktif</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-amber-200/70 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300">
                        <flux:icon.clipboard-document-list class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ $activeServicesCount }}</span>
                    <span class="text-xs font-medium text-amber-800 dark:text-amber-300">jenis layanan</span>
                </div>
            </flux:card>

            {{-- Petugas Bertugas --}}
            <flux:card class="admin-stat-info admin-card-elevated rounded-3xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-violet-800 dark:text-violet-300">Petugas Bertugas</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-violet-200/70 text-violet-700 dark:bg-violet-900/60 dark:text-violet-300">
                        <flux:icon.user-group class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ $activeOfficersCount }}</span>
                    <span class="text-xs font-medium text-violet-800 dark:text-violet-300">petugas aktif</span>
                </div>
            </flux:card>
        </div>

        {{-- Laporan Breakdown Grid --}}
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
            {{-- By Service --}}
            <div class="animate-fade-in-up" style="animation-delay: 200ms;">
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 h-full flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                            <div class="admin-icon-box bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300">
                                <flux:icon.clipboard-document-list class="size-5" />
                            </div>
                            <div>
                                <flux:heading size="lg" class="font-bold">Berdasarkan Layanan</flux:heading>
                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Distribusi volume antrian menurut unit layanan.</flux:text>
                            </div>
                        </div>

                        @if (count($report['by_service']) > 0)
                            <div class="admin-table-scroll mt-4 overflow-x-auto">
                                <flux:table>
                                    <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40">
                                        <flux:table.column class="text-xs font-bold uppercase tracking-wider">Nama Layanan</flux:table.column>
                                        <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Jumlah</flux:table.column>
                                    </flux:table.columns>
                                    <flux:table.rows class="admin-row-stagger">
                                        @foreach ($report['by_service'] as $name => $count)
                                            @php
                                                $pct = $totalTickets > 0 ? round(($count / $totalTickets) * 100) : 0;
                                            @endphp
                                            <flux:table.row class="admin-row-enter transition-colors hover:bg-emerald-50/40 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                                <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                                    {{ $name }}
                                                </flux:table.cell>
                                                <flux:table.cell class="text-right whitespace-nowrap">
                                                    <div class="inline-flex items-center gap-2">
                                                        <span class="text-xs text-zinc-500 font-mono">{{ $pct }}%</span>
                                                        <flux:badge size="sm" color="emerald" class="font-bold font-mono">{{ $count }}</flux:badge>
                                                    </div>
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
                                <p class="mt-1 text-xs text-zinc-500">Belum ada antrian pada rentang tanggal ini.</p>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>

            {{-- By Counter --}}
            <div class="animate-fade-in-up" style="animation-delay: 250ms;">
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 h-full flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                            <div class="admin-icon-box bg-amber-100 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300">
                                <flux:icon.building-office class="size-5" />
                            </div>
                            <div>
                                <flux:heading size="lg" class="font-bold">Berdasarkan Loket</flux:heading>
                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Jumlah antrian yang diarahkan ke loket fisik.</flux:text>
                            </div>
                        </div>

                        @if (count($report['by_counter']) > 0)
                            <div class="admin-table-scroll mt-4 overflow-x-auto">
                                <flux:table>
                                    <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40">
                                        <flux:table.column class="text-xs font-bold uppercase tracking-wider">Loket Pelayanan</flux:table.column>
                                        <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Jumlah</flux:table.column>
                                    </flux:table.columns>
                                    <flux:table.rows class="admin-row-stagger">
                                        @foreach ($report['by_counter'] as $name => $count)
                                            <flux:table.row class="admin-row-enter transition-colors hover:bg-amber-50/40 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                                <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                                    {{ $name }}
                                                </flux:table.cell>
                                                <flux:table.cell class="text-right whitespace-nowrap">
                                                    <flux:badge size="sm" color="amber" class="font-bold font-mono">{{ $count }}</flux:badge>
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
                                <p class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Tidak ada data loket</p>
                                <p class="mt-1 text-xs text-zinc-500">Belum ada pemanggilan tiket di loket pada rentang ini.</p>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>

            {{-- By Officer --}}
            <div class="animate-fade-in-up" style="animation-delay: 300ms;">
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 h-full flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                            <div class="admin-icon-box bg-violet-100 text-violet-700 dark:bg-violet-950/70 dark:text-violet-300">
                                <flux:icon.user class="size-5" />
                            </div>
                            <div>
                                <flux:heading size="lg" class="font-bold">Berdasarkan Petugas</flux:heading>
                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Total penyelesaian tiket per petugas bertugas.</flux:text>
                            </div>
                        </div>

                        @if (count($report['by_officer']) > 0)
                            <div class="admin-table-scroll mt-4 overflow-x-auto">
                                <flux:table>
                                    <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40">
                                        <flux:table.column class="text-xs font-bold uppercase tracking-wider">Nama Petugas</flux:table.column>
                                        <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Tiket Selesai</flux:table.column>
                                    </flux:table.columns>
                                    <flux:table.rows class="admin-row-stagger">
                                        @foreach ($report['by_officer'] as $name => $count)
                                            <flux:table.row class="admin-row-enter transition-colors hover:bg-violet-50/40 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                                <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                                    {{ $name }}
                                                </flux:table.cell>
                                                <flux:table.cell class="text-right whitespace-nowrap">
                                                    <flux:badge size="sm" color="violet" class="font-bold font-mono">{{ $count }}</flux:badge>
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
                                <p class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Tidak ada data petugas</p>
                                <p class="mt-1 text-xs text-zinc-500">Belum ada penyelesaian tiket oleh petugas.</p>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>

            {{-- By Status --}}
            <div class="animate-fade-in-up" style="animation-delay: 350ms;">
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 h-full flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                            <div class="admin-icon-box bg-sky-100 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300">
                                <flux:icon.signal class="size-5" />
                            </div>
                            <div>
                                <flux:heading size="lg" class="font-bold">Berdasarkan Status</flux:heading>
                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Kondisi akhir tiket dalam siklus antrian.</flux:text>
                            </div>
                        </div>

                        @if (count($report['by_status']) > 0)
                            <div class="admin-table-scroll mt-4 overflow-x-auto">
                                <flux:table>
                                    <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40">
                                        <flux:table.column class="text-xs font-bold uppercase tracking-wider">Status Tiket</flux:table.column>
                                        <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Jumlah</flux:table.column>
                                    </flux:table.columns>
                                    <flux:table.rows class="admin-row-stagger">
                                        @foreach ($report['by_status'] as $status => $count)
                                            @php
                                                $statusEnum = \App\Enums\QueueStatus::tryFrom($status);
                                                $color = $statusEnum?->color() ?? 'zinc';
                                                $label = $statusEnum?->label() ?? ucfirst($status);
                                            @endphp
                                            <flux:table.row class="admin-row-enter transition-colors hover:bg-sky-50/40 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                                <flux:table.cell class="whitespace-nowrap">
                                                    <flux:badge size="sm" :color="$color" class="font-bold">{{ $label }}</flux:badge>
                                                </flux:table.cell>
                                                <flux:table.cell class="text-right whitespace-nowrap">
                                                    <flux:badge size="sm" color="zinc" class="font-bold font-mono">{{ $count }}</flux:badge>
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
                                <p class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Tidak ada data status</p>
                                <p class="mt-1 text-xs text-zinc-500">Belum ada riwayat status antrian.</p>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>

            {{-- Officer x Service Distribution --}}
            <div class="lg:col-span-2 animate-fade-in-up" style="animation-delay: 400ms;">
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-7 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div class="admin-icon-box bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-950/70 dark:text-fuchsia-300">
                            <flux:icon.chart-bar class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold">Distribusi Petugas x Layanan</flux:heading>
                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Rincian spesifik jumlah layanan yang diselesaikan oleh masing-masing petugas.</flux:text>
                        </div>
                    </div>

                    @if (count($report['officer_service_distribution'] ?? []) > 0)
                        <div class="admin-table-scroll mt-4 overflow-x-auto">
                            <flux:table>
                                <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40">
                                    <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Petugas</flux:table.column>
                                    <flux:table.column class="text-xs font-bold uppercase tracking-wider">Distribusi Layanan Selesai</flux:table.column>
                                    <flux:table.column class="text-right text-xs font-bold uppercase tracking-wider">Total Selesai</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows class="admin-row-stagger">
                                    @foreach (($report['officer_service_distribution'] ?? []) as $officer => $services)
                                        @php
                                            $officerTotal = array_sum($services);
                                        @endphp
                                        <flux:table.row class="admin-row-enter transition-colors hover:bg-fuchsia-50/30 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                            <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex size-7 items-center justify-center rounded-lg bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-950/60 dark:text-fuchsia-300 font-bold text-xs">
                                                        {{ strtoupper(substr($officer, 0, 1)) }}
                                                    </div>
                                                    <span>{{ $officer }}</span>
                                                </div>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <div class="flex flex-wrap gap-1.5 py-1">
                                                    @foreach ($services as $service => $count)
                                                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                                            <span>{{ $service }}</span>
                                                            <span class="rounded-md bg-white px-1.5 py-0.5 text-xs font-bold font-mono text-fuchsia-700 shadow-2xs dark:bg-zinc-900 dark:text-fuchsia-300">{{ $count }}</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </flux:table.cell>
                                            <flux:table.cell class="text-right whitespace-nowrap">
                                                <flux:badge size="sm" color="fuchsia" class="font-bold font-mono">{{ $officerTotal }}</flux:badge>
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
                            <p class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Tidak ada distribusi</p>
                            <p class="mt-1 text-xs text-zinc-500">Belum ada aktivitas penyelesaian layanan pada rentang tanggal ini.</p>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts::app>

