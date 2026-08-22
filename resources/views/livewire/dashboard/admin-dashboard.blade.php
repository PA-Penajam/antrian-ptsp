<div class="space-y-6">

    {{-- Page Header with brand ambient --}}
    <div class="animate-fade-in-up relative overflow-hidden rounded-3xl border border-cyan-100/80 bg-gradient-to-br from-cyan-50/90 via-white to-teal-50/60 p-5 shadow-[0_24px_60px_-36px_rgba(14,116,144,0.22)] sm:px-6 sm:py-5 dark:border-cyan-900/30 dark:from-cyan-950/40 dark:via-zinc-900 dark:to-teal-950/30">
        {{-- Ambient radial glow with gentle shimmer --}}
        <div aria-hidden="true" class="animate-glow-shimmer pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-[radial-gradient(circle,_rgba(14,116,144,0.14),_transparent_70%)]"></div>
        <div aria-hidden="true" class="animate-glow-shimmer pointer-events-none absolute -bottom-8 left-32 size-48 rounded-full bg-[radial-gradient(circle,_rgba(15,118,110,0.10),_transparent_70%)]" style="animation-delay: 1.5s;"></div>
        <div class="relative flex flex-col gap-3.5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase dark:text-cyan-400">Panel Monitoring</p>
                <h1 class="mt-0.5 text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">Health Aplikasi</h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm dark:text-zinc-400">Observabilitas operasional dan akses manajemen inti.</p>
            </div>
            <div class="flex items-center gap-2.5 self-start rounded-2xl border border-emerald-200/80 bg-emerald-50/90 px-3.5 py-2 shadow-xs transition-transform duration-200 hover:scale-102 dark:border-emerald-800/40 dark:bg-emerald-950/50">
                <span class="relative flex size-2.5 shrink-0">
                    <span class="animate-pulse-radar absolute inline-flex h-full w-full rounded-full bg-emerald-500"></span>
                    <span class="relative inline-flex size-2.5 rounded-full bg-emerald-500"></span>
                </span>
                <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-300">Sistem Aktif</span>
            </div>
        </div>
    </div>

    {{-- Stat Cards with Staggered Entrance & Interactive Physics --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Hari Ini --}}
        <div class="animate-fade-in-up anim-delay-75 admin-stat-total admin-card-elevated group relative cursor-default overflow-hidden rounded-2xl border p-5">
            <div aria-hidden="true" class="pointer-events-none absolute right-0 top-0 size-24 rounded-full bg-[radial-gradient(circle,_rgba(14,116,144,0.12),_transparent_70%)] transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between">
                <div class="space-y-1.5">
                    <p class="text-xs font-semibold tracking-[0.16em] text-sky-700 uppercase dark:text-sky-300">Total Hari Ini</p>
                    <p class="font-tabular-nums text-3xl font-bold tabular-nums text-slate-900 dark:text-white sm:text-4xl">{{ $this->todayTotal }}</p>
                </div>
                <div class="admin-icon-box bg-sky-100 text-sky-600 group-hover:scale-110 group-hover:rotate-3 dark:bg-sky-900/60 dark:text-sky-300">
                    <flux:icon.ticket class="size-5 transition-transform duration-300 group-hover:scale-110" />
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5">
                <div class="h-1 flex-1 overflow-hidden rounded-full bg-sky-100/80 dark:bg-sky-950/60">
                    <div class="h-full rounded-full bg-gradient-to-r from-sky-400 to-cyan-500 transition-all duration-1000 ease-out" style="width:{{ $this->todayTotal > 0 ? '100' : '0' }}%"></div>
                </div>
                <p class="text-[10px] font-semibold text-sky-700 dark:text-sky-300">tiket masuk</p>
            </div>
        </div>

        {{-- Sudah Dilayani --}}
        <div class="animate-fade-in-up anim-delay-150 admin-stat-success admin-card-elevated group relative cursor-default overflow-hidden rounded-2xl border p-5">
            <div aria-hidden="true" class="pointer-events-none absolute right-0 top-0 size-24 rounded-full bg-[radial-gradient(circle,_rgba(16,185,129,0.14),_transparent_70%)] transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between">
                <div class="space-y-1.5">
                    <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase dark:text-emerald-300">Sudah Dilayani</p>
                    <p class="text-3xl font-bold tabular-nums text-emerald-700 dark:text-emerald-200 sm:text-4xl">{{ $this->todayServed }}</p>
                </div>
                <div class="admin-icon-box bg-emerald-100 text-emerald-600 group-hover:scale-110 group-hover:rotate-3 dark:bg-emerald-900/60 dark:text-emerald-300">
                    <flux:icon.check-circle class="size-5 transition-transform duration-300 group-hover:scale-110" />
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5">
                <div class="h-1 flex-1 overflow-hidden rounded-full bg-emerald-100/80 dark:bg-emerald-950/60">
                    @php $servedRate = $this->todayTotal > 0 ? round(($this->todayServed / $this->todayTotal) * 100) : 0; @endphp
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 transition-all duration-1000 ease-out" style="width:{{ $servedRate }}%"></div>
                </div>
                <p class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-300">{{ $servedRate }}% selesai</p>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="animate-fade-in-up anim-delay-225 admin-stat-warning admin-card-elevated group relative cursor-default overflow-hidden rounded-2xl border p-5">
            <div aria-hidden="true" class="pointer-events-none absolute right-0 top-0 size-24 rounded-full bg-[radial-gradient(circle,_rgba(245,158,11,0.14),_transparent_70%)] transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between">
                <div class="space-y-1.5">
                    <p class="text-xs font-semibold tracking-[0.16em] text-amber-700 uppercase dark:text-amber-300">Menunggu</p>
                    <p class="text-3xl font-bold tabular-nums text-amber-700 dark:text-amber-200 sm:text-4xl">{{ $this->todayWaiting }}</p>
                </div>
                <div class="admin-icon-box bg-amber-100 text-amber-600 group-hover:scale-110 group-hover:rotate-3 dark:bg-amber-900/60 dark:text-amber-300">
                    <flux:icon.clock class="size-5 transition-transform duration-300 group-hover:scale-110" />
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5">
                @php $waitingRate = $this->todayTotal > 0 ? round(($this->todayWaiting / $this->todayTotal) * 100) : 0; @endphp
                <div class="h-1 flex-1 overflow-hidden rounded-full bg-amber-100/80 dark:bg-amber-950/60">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-400 transition-all duration-1000 ease-out" style="width:{{ $waitingRate }}%"></div>
                </div>
                <p class="text-[10px] font-semibold text-amber-700 dark:text-amber-300">{{ $waitingRate }}% antri</p>
            </div>
        </div>

        {{-- Rata-rata Tunggu --}}
        <div class="animate-fade-in-up anim-delay-300 admin-stat-info admin-card-elevated group relative cursor-default overflow-hidden rounded-2xl border p-5">
            <div aria-hidden="true" class="pointer-events-none absolute right-0 top-0 size-24 rounded-full bg-[radial-gradient(circle,_rgba(168,85,247,0.14),_transparent_70%)] transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between">
                <div class="space-y-1.5">
                    <p class="text-xs font-semibold tracking-[0.16em] text-purple-700 uppercase dark:text-purple-300">Rata-rata Tunggu</p>
                    <p class="text-3xl font-bold tabular-nums text-purple-700 dark:text-purple-200 sm:text-4xl">{{ $this->todayAvgWaitMinutes }}<span class="ml-1 text-base font-medium">mnt</span></p>
                </div>
                <div class="admin-icon-box bg-purple-100 text-purple-600 group-hover:scale-110 group-hover:rotate-3 dark:bg-purple-900/60 dark:text-purple-300">
                    <flux:icon.chart-bar class="size-5 transition-transform duration-300 group-hover:scale-110" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-[10px] font-semibold text-purple-700 dark:text-purple-300">rata-rata per tiket</p>
            </div>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <flux:card class="animate-fade-in-up anim-delay-150 admin-card-elevated p-4 sm:p-5 transition-shadow hover:shadow-md">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400">
                    <flux:icon.funnel class="size-5" />
                </div>
                <div>
                    <flux:heading size="sm">Filter Periode</flux:heading>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Pilih rentang tanggal atau gunakan preset cepat</flux:text>
                </div>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0" role="group" aria-label="Preset rentang tanggal">
                    <flux:button size="sm" variant="{{ $startDate === today()->toDateString() && $endDate === today()->toDateString() ? 'primary' : 'subtle' }}" wire:click="setPreset('today')" class="transition-transform active:scale-95">Hari Ini</flux:button>
                    <flux:button size="sm" variant="{{ $startDate === today()->subDays(6)->toDateString() && $endDate === today()->toDateString() ? 'primary' : 'subtle' }}" wire:click="setPreset('7days')" class="transition-transform active:scale-95">7 Hari</flux:button>
                    <flux:button size="sm" variant="{{ $startDate === today()->startOfMonth()->toDateString() && $endDate === today()->toDateString() ? 'primary' : 'subtle' }}" wire:click="setPreset('month')" class="transition-transform active:scale-95">Bulan Ini</flux:button>
                </div>
                <div class="grid w-full grid-cols-1 gap-3 sm:w-auto sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Dari</flux:label>
                        <flux:input type="date" wire:model="startDate" wire:change="filterByDate" class="w-full transition-shadow focus:shadow-xs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Sampai</flux:label>
                        <flux:input type="date" wire:model="endDate" wire:change="filterByDate" class="w-full transition-shadow focus:shadow-xs" />
                    </flux:field>
                </div>
            </div>
        </div>
    </flux:card>

    @php
        $serviceData = collect($this->byService)
            ->map(fn ($count, $name) => ['name' => $name, 'count' => $count])
            ->values()
            ->toArray();

        $counterData = collect($this->byCounter)
            ->map(fn ($count, $name) => ['name' => $name, 'count' => $count])
            ->values()
            ->toArray();

        $channelData = collect($this->byChannel)
            ->map(fn ($total, $channel) => [
                'channel' => \Illuminate\Support\Str::of($channel)->replace('_', ' ')->headline()->value(),
                'total' => $total,
            ])
            ->values()
            ->toArray();
    @endphp

    <div id="charts-placeholder" class="animate-fade-in-up anim-delay-225 space-y-4">
        {{-- Trend chart --}}
        <flux:card class="admin-card-elevated group p-5 transition-all duration-300 hover:border-cyan-200 dark:hover:border-cyan-800/50" role="region" aria-label="Grafik Tren Antrean 7 Hari Terakhir">
            <div class="mb-4 flex items-center gap-3">
                <div class="admin-icon-box bg-sky-100 text-sky-600 transition-transform duration-300 group-hover:scale-105 dark:bg-sky-900/50 dark:text-sky-400">
                    <flux:icon.chart-bar class="size-5" />
                </div>
                <div>
                    <flux:heading size="sm">Tren 7 Hari Terakhir</flux:heading>
                    <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">Volume tiket masuk per hari</flux:text>
                </div>
            </div>

            @if (count($this->trendData) > 0 && collect($this->trendData)->sum('total') > 0)
                <flux:chart :value="$this->trendData" class="w-full">
                    <flux:chart.viewport class="h-48">
                        <flux:chart.svg>
                            <flux:chart.line field="total" class="text-cyan-500 transition-all duration-500 dark:text-cyan-400" />
                            <flux:chart.point field="total" class="text-cyan-500 transition-transform duration-200 hover:scale-125 dark:text-cyan-400" />
                            <flux:chart.axis axis="x" field="date" :format="['day' => 'numeric', 'month' => 'short']">
                                <flux:chart.axis.tick />
                                <flux:chart.axis.line />
                            </flux:chart.axis>
                            <flux:chart.axis axis="y" :format="['useGrouping' => true]">
                                <flux:chart.axis.grid />
                                <flux:chart.axis.tick />
                            </flux:chart.axis>
                            <flux:chart.cursor />
                        </flux:chart.svg>
                    </flux:chart.viewport>
                    <flux:chart.tooltip>
                        <flux:chart.tooltip.heading field="date" :format="['year' => 'numeric', 'month' => 'short', 'day' => 'numeric']" />
                        <flux:chart.tooltip.value field="total" label="Total" :format="['useGrouping' => true]" />
                    </flux:chart.tooltip>
                </flux:chart>
            @else
                <div class="flex h-48 flex-col items-center justify-center gap-3">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-400 transition-transform duration-300 hover:scale-110 dark:bg-sky-950/40 dark:text-sky-400">
                        <flux:icon.chart-bar class="size-6" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-slate-600 dark:text-zinc-300">Belum ada data tren</p>
                        <p class="text-xs text-slate-400 dark:text-zinc-500">Data akan tampil setelah ada tiket masuk hari ini</p>
                    </div>
                </div>
            @endif
        </flux:card>

        {{-- Per Layanan + Per Loket --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <flux:card class="admin-card-elevated group p-5 transition-all duration-300 hover:border-emerald-200 dark:hover:border-emerald-800/50" role="region" aria-label="Grafik Distribusi Tiket per Layanan">
                <div class="mb-4 flex items-center gap-3">
                    <div class="admin-icon-box bg-emerald-100 text-emerald-600 transition-transform duration-300 group-hover:scale-105 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <flux:icon.clipboard-document-list class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="sm">Per Layanan</flux:heading>
                        <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">Distribusi tiket berdasarkan layanan</flux:text>
                    </div>
                </div>
                @if (count($serviceData) > 0 && collect($serviceData)->sum('count') > 0)
                    <flux:chart :value="$serviceData" class="w-full">
                        <flux:chart.viewport class="h-48">
                            <flux:chart.svg>
                                <flux:chart.bar field="count" class="text-emerald-500 transition-all duration-500 dark:text-emerald-400" width="55%" />
                                <flux:chart.axis axis="x" field="name">
                                    <flux:chart.axis.tick class="text-xs" />
                                    <flux:chart.axis.line />
                                </flux:chart.axis>
                                <flux:chart.axis axis="y" :format="['useGrouping' => true]">
                                    <flux:chart.axis.grid />
                                    <flux:chart.axis.tick />
                                </flux:chart.axis>
                                <flux:chart.cursor />
                            </flux:chart.svg>
                        </flux:chart.viewport>
                        <flux:chart.tooltip>
                            <flux:chart.tooltip.heading field="name" />
                            <flux:chart.tooltip.value field="count" label="Total" :format="['useGrouping' => true]" />
                        </flux:chart.tooltip>
                    </flux:chart>
                @else
                    <div class="flex h-48 flex-col items-center justify-center gap-3">
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-400 transition-transform duration-300 hover:scale-110 dark:bg-emerald-950/40 dark:text-emerald-400">
                            <flux:icon.clipboard-document-list class="size-6" />
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-medium text-slate-600 dark:text-zinc-300">Belum ada data layanan</p>
                            <p class="text-xs text-slate-400 dark:text-zinc-500">Tambahkan layanan untuk mulai mencatat distribusi</p>
                        </div>
                    </div>
                @endif
            </flux:card>

            <flux:card class="admin-card-elevated group p-5 transition-all duration-300 hover:border-amber-200 dark:hover:border-amber-800/50" role="region" aria-label="Grafik Distribusi Tiket per Loket">
                <div class="mb-4 flex items-center gap-3">
                    <div class="admin-icon-box bg-amber-100 text-amber-600 transition-transform duration-300 group-hover:scale-105 dark:bg-amber-900/50 dark:text-amber-400">
                        <flux:icon.building-office class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="sm">Per Loket</flux:heading>
                        <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">Distribusi tiket berdasarkan loket aktif</flux:text>
                    </div>
                </div>
                @if (count($counterData) > 0 && collect($counterData)->sum('count') > 0)
                    <flux:chart :value="$counterData" class="w-full">
                        <flux:chart.viewport class="h-48">
                            <flux:chart.svg>
                                <flux:chart.bar field="count" class="text-amber-500 transition-all duration-500 dark:text-amber-400" width="55%" />
                                <flux:chart.axis axis="x" field="name">
                                    <flux:chart.axis.tick class="text-xs" />
                                    <flux:chart.axis.line />
                                </flux:chart.axis>
                                <flux:chart.axis axis="y" :format="['useGrouping' => true]">
                                    <flux:chart.axis.grid />
                                    <flux:chart.axis.tick />
                                </flux:chart.axis>
                                <flux:chart.cursor />
                            </flux:chart.svg>
                        </flux:chart.viewport>
                        <flux:chart.tooltip>
                            <flux:chart.tooltip.heading field="name" />
                            <flux:chart.tooltip.value field="count" label="Total" :format="['useGrouping' => true]" />
                        </flux:chart.tooltip>
                    </flux:chart>
                @else
                    <div class="flex h-48 flex-col items-center justify-center gap-3">
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-400 transition-transform duration-300 hover:scale-110 dark:bg-amber-950/40 dark:text-amber-400">
                            <flux:icon.building-office class="size-6" />
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-medium text-slate-600 dark:text-zinc-300">Belum ada data loket</p>
                            <p class="text-xs text-slate-400 dark:text-zinc-500">Aktifkan loket untuk mulai melihat distribusi</p>
                        </div>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Distribusi Kanal --}}
        <flux:card class="admin-card-elevated group p-5 transition-all duration-300 hover:border-indigo-200 dark:hover:border-indigo-800/50" role="region" aria-label="Grafik Distribusi Tiket per Kanal">
            <div class="mb-4 flex items-center gap-3">
                <div class="admin-icon-box bg-indigo-100 text-indigo-600 transition-transform duration-300 group-hover:scale-105 dark:bg-indigo-900/50 dark:text-indigo-400">
                    <flux:icon.signal class="size-5" />
                </div>
                <div>
                    <flux:heading size="sm">Distribusi Kanal</flux:heading>
                    <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">Tiket dari kiosk, frontdesk, dan booking online</flux:text>
                </div>
            </div>
            @if (count($channelData) > 0 && collect($channelData)->sum('total') > 0)
                <flux:chart :value="$channelData" class="w-full">
                    <flux:chart.viewport class="h-48">
                        <flux:chart.svg>
                            <flux:chart.bar field="total" class="text-indigo-500 transition-all duration-500 dark:text-indigo-400" width="55%" />
                            <flux:chart.axis axis="x" field="channel">
                                <flux:chart.axis.tick class="text-xs" />
                                <flux:chart.axis.line />
                            </flux:chart.axis>
                            <flux:chart.axis axis="y" :format="['useGrouping' => true]">
                                <flux:chart.axis.grid />
                                <flux:chart.axis.tick />
                            </flux:chart.axis>
                            <flux:chart.cursor />
                        </flux:chart.svg>
                    </flux:chart.viewport>
                    <flux:chart.tooltip>
                        <flux:chart.tooltip.heading field="channel" />
                        <flux:chart.tooltip.value field="total" label="Total" :format="['useGrouping' => true]" />
                    </flux:chart.tooltip>
                </flux:chart>
            @else
                <div class="flex h-48 flex-col items-center justify-center gap-3">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-400 transition-transform duration-300 hover:scale-110 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <flux:icon.signal class="size-6" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-slate-600 dark:text-zinc-300">Belum ada data kanal</p>
                        <p class="text-xs text-slate-400 dark:text-zinc-500">Data kanal akan tampil setelah tiket pertama masuk</p>
                    </div>
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Activity log (auto-updates every 30s) --}}
    <flux:card class="animate-fade-in-up anim-delay-300 admin-card-elevated p-4 sm:p-5" wire:poll.30s>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box relative bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                    <flux:icon.clock class="size-5" />
                    {{-- Live pulse radar beacon --}}
                    <span aria-hidden="true" class="absolute -right-0.5 -top-0.5 flex size-3 items-center justify-center">
                        <span class="animate-pulse-radar absolute inline-flex h-full w-full rounded-full bg-emerald-500"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <flux:heading size="sm">Aktivitas Terkini</flux:heading>
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/80 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold tracking-wider text-emerald-700 uppercase dark:border-emerald-800/40 dark:bg-emerald-950/40 dark:text-emerald-300">Live</span>
                    </div>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Auto-refresh setiap 30 detik</flux:text>
                </div>
            </div>
            <div class="w-full sm:w-72">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari tiket, layanan, petugas..."
                    size="sm"
                    icon="magnifying-glass"
                    clearable
                    class="w-full transition-shadow focus:shadow-xs"
                />
            </div>
        </div>

        <div wire:loading.class="opacity-60 transition-opacity duration-200">
            @if($this->recentActivities->isEmpty())
                <div class="flex flex-col items-center justify-center gap-3 py-10">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 transition-transform duration-300 hover:scale-105 dark:bg-zinc-800 dark:text-zinc-600">
                        <flux:icon.clock class="size-7" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-slate-600 dark:text-zinc-300">Belum ada aktivitas</p>
                        <p class="text-xs text-slate-400 dark:text-zinc-500">Aktivitas akan muncul saat tiket dipanggil atau diproses</p>
                    </div>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Waktu</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                            <flux:table.column>No. Antrian</flux:table.column>
                            <flux:table.column>Layanan</flux:table.column>
                            <flux:table.column>Petugas</flux:table.column>
                            <flux:table.column>Loket</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($this->recentActivities as $activity)
                            <flux:table.row class="transition-colors duration-150 hover:bg-slate-50/75 dark:hover:bg-zinc-800/50">
                                <flux:table.cell class="whitespace-nowrap text-xs text-slate-500 dark:text-zinc-400">{{ $activity->created_at->diffForHumans() }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="{{ $this->actionColor($activity->action) }}">
                                        {{ $this->actionLabel($activity->action) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap font-semibold tabular-nums text-slate-900 dark:text-white">{{ $activity->queueTicket?->ticket_number ?? '-' }}</flux:table.cell>
                                <flux:table.cell class="text-sm dark:text-zinc-200">{{ $activity->queueTicket?->service?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell class="text-sm dark:text-zinc-200">{{ $activity->user?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap text-sm dark:text-zinc-200">{{ $activity->counter?->name ?? '-' }}</flux:table.cell>
                            </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        </div>
    </flux:card>

    {{-- Ringkasan Operasional --}}
    <div class="animate-fade-in-up anim-delay-375 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="admin-stat-success admin-card-elevated group relative cursor-default overflow-hidden rounded-2xl border p-5">
            <div aria-hidden="true" class="pointer-events-none absolute right-0 top-0 size-24 rounded-full bg-[radial-gradient(circle,_rgba(16,185,129,0.14),_transparent_70%)] transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase dark:text-emerald-300">Booking Berhasil</p>
                    <p class="text-3xl font-bold tabular-nums text-emerald-700 dark:text-emerald-200">{{ $this->bookingSuccess }}</p>
                    <p class="text-xs text-emerald-600/80 dark:text-emerald-300/80">Hari ini</p>
                </div>
                <div class="admin-icon-box bg-emerald-100 text-emerald-600 group-hover:scale-110 group-hover:rotate-3 dark:bg-emerald-900/60 dark:text-emerald-300">
                    <flux:icon.check class="size-5 transition-transform duration-300 group-hover:scale-110" />
                </div>
            </div>
        </div>

        <div class="admin-stat-danger admin-card-elevated group relative cursor-default overflow-hidden rounded-2xl border p-5">
            <div aria-hidden="true" class="pointer-events-none absolute right-0 top-0 size-24 rounded-full bg-[radial-gradient(circle,_rgba(239,68,68,0.14),_transparent_70%)] transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold tracking-[0.16em] text-red-700 uppercase dark:text-red-300">Booking Gagal</p>
                    <p class="text-3xl font-bold tabular-nums text-red-700 dark:text-red-200">{{ $this->bookingFailed }}</p>
                    <p class="text-xs text-red-600/80 dark:text-red-300/80">Hari ini</p>
                </div>
                <div class="admin-icon-box bg-red-100 text-red-600 group-hover:scale-110 group-hover:rotate-3 dark:bg-red-900/60 dark:text-red-300">
                    <flux:icon.x-circle class="size-5 transition-transform duration-300 group-hover:scale-110" />
                </div>
            </div>
        </div>
    </div>

    {{-- Shortcut Manajemen --}}
    <flux:card class="animate-fade-in-up anim-delay-375 admin-card-elevated p-4 sm:p-5">
        <div class="mb-4 flex items-center gap-3">
            <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                <flux:icon.squares-2x2 class="size-5" />
            </div>
            <flux:heading size="sm">Shortcut Manajemen</flux:heading>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <a href="{{ route('admin.layanan.index') }}" wire:navigate class="group flex flex-col items-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50/80 px-3 py-4 text-emerald-800 transition-all duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:bg-emerald-100 hover:shadow-[0_8px_20px_-6px_rgba(16,185,129,0.30)] active:scale-95 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300 dark:hover:bg-emerald-950/50">
                <flux:icon.clipboard-document-list class="size-5 transition-transform duration-300 group-hover:scale-115 group-hover:-translate-y-0.5" />
                <span class="text-xs font-semibold">Layanan</span>
            </a>
            <a href="{{ route('admin.loket.index') }}" wire:navigate class="group flex flex-col items-center gap-2 rounded-2xl border border-cyan-100 bg-cyan-50/80 px-3 py-4 text-cyan-800 transition-all duration-200 hover:-translate-y-1 hover:border-cyan-200 hover:bg-cyan-100 hover:shadow-[0_8px_20px_-6px_rgba(14,116,144,0.28)] active:scale-95 dark:border-cyan-900/40 dark:bg-cyan-950/30 dark:text-cyan-300 dark:hover:bg-cyan-950/50">
                <flux:icon.building-office class="size-5 transition-transform duration-300 group-hover:scale-115 group-hover:-translate-y-0.5" />
                <span class="text-xs font-semibold">Loket</span>
            </a>
            <a href="{{ route('admin.users.index') }}" wire:navigate class="group flex flex-col items-center gap-2 rounded-2xl border border-indigo-100 bg-indigo-50/80 px-3 py-4 text-indigo-800 transition-all duration-200 hover:-translate-y-1 hover:border-indigo-200 hover:bg-indigo-100 hover:shadow-[0_8px_20px_-6px_rgba(99,102,241,0.25)] active:scale-95 dark:border-indigo-900/40 dark:bg-indigo-950/30 dark:text-indigo-300 dark:hover:bg-indigo-950/50">
                <flux:icon.users class="size-5 transition-transform duration-300 group-hover:scale-115 group-hover:-translate-y-0.5" />
                <span class="text-xs font-semibold">Users</span>
            </a>
            <a href="{{ route('admin.wilayah.index') }}" wire:navigate class="group flex flex-col items-center gap-2 rounded-2xl border border-teal-100 bg-teal-50/80 px-3 py-4 text-teal-800 transition-all duration-200 hover:-translate-y-1 hover:border-teal-200 hover:bg-teal-100 hover:shadow-[0_8px_20px_-6px_rgba(13,148,136,0.26)] active:scale-95 dark:border-teal-900/40 dark:bg-teal-950/30 dark:text-teal-300 dark:hover:bg-teal-950/50">
                <flux:icon.map class="size-5 transition-transform duration-300 group-hover:scale-115 group-hover:-translate-y-0.5" />
                <span class="text-xs font-semibold">Wilayah</span>
            </a>
            <a href="{{ url('/frontdesk/antrian') }}" wire:navigate class="group col-span-2 flex flex-col items-center gap-2 rounded-2xl border border-sky-100 bg-sky-50/80 px-3 py-4 text-sky-800 transition-all duration-200 hover:-translate-y-1 hover:border-sky-200 hover:bg-sky-100 hover:shadow-[0_8px_20px_-6px_rgba(14,165,233,0.26)] active:scale-95 sm:col-span-1 dark:border-sky-900/40 dark:bg-sky-950/30 dark:text-sky-300 dark:hover:bg-sky-950/50">
                <flux:icon.ticket class="size-5 transition-transform duration-300 group-hover:scale-115 group-hover:-translate-y-0.5" />
                <span class="text-xs font-semibold">Frontdesk</span>
            </a>
        </div>
    </flux:card>
</div>
