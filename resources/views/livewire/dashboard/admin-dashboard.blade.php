<div class="space-y-6">
    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <flux:card class="admin-stat-total admin-card-elevated p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-sky-700 uppercase dark:text-sky-300">Total Hari Ini</flux:text>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $this->todayTotal }}</p>
                </div>
                <div class="admin-icon-box bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                    <flux:icon.ticket class="size-5" />
                </div>
            </div>
        </flux:card>

        <flux:card class="admin-stat-success admin-card-elevated p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase dark:text-emerald-300">Sudah Dilayani</flux:text>
                    <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-400">{{ $this->todayServed }}</p>
                </div>
                <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                    <flux:icon.check-circle class="size-5" />
                </div>
            </div>
        </flux:card>

        <flux:card class="admin-stat-warning admin-card-elevated p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-amber-700 uppercase dark:text-amber-300">Menunggu</flux:text>
                    <p class="text-3xl font-bold text-amber-700 dark:text-amber-400">{{ $this->todayWaiting }}</p>
                </div>
                <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                    <flux:icon.clock class="size-5" />
                </div>
            </div>
        </flux:card>

        <flux:card class="admin-stat-info admin-card-elevated p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-violet-700 uppercase dark:text-violet-300">Rata-rata Tunggu</flux:text>
                    <p class="text-3xl font-bold text-violet-700 dark:text-violet-400">{{ $this->todayAvgWaitMinutes }}<span class="ml-1 text-base font-medium">mnt</span></p>
                </div>
                <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                    <flux:icon.chart-bar class="size-5" />
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Date Range Filter --}}
    <flux:card class="p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon.funnel class="size-5" />
                </div>
                <div>
                    <flux:heading size="sm">Filter Periode</flux:heading>
                    <flux:text class="text-xs text-zinc-500">Pilih rentang tanggal untuk analisis data</flux:text>
                </div>
            </div>
            <div class="flex items-end gap-3">
                <flux:field>
                    <flux:label>Dari</flux:label>
                    <flux:input type="date" wire:model.live="startDate" />
                </flux:field>
                <flux:field>
                    <flux:label>Sampai</flux:label>
                    <flux:input type="date" wire:model.live="endDate" />
                </flux:field>
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

    <div id="charts-placeholder" class="space-y-4">
        <flux:card class="admin-card-elevated p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="admin-icon-box bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                    <flux:icon.chart-bar class="size-5" />
                </div>
                <flux:heading size="sm">Tren 7 Hari Terakhir</flux:heading>
            </div>

            @if (count($this->trendData) > 0 && collect($this->trendData)->sum('total') > 0)
                <flux:chart :value="$this->trendData" class="w-full">
                    <flux:chart.viewport class="h-48">
                        <flux:chart.svg>
                            <flux:chart.line field="total" class="text-sky-500 dark:text-sky-400" />
                            <flux:chart.point field="total" class="text-sky-500 dark:text-sky-400" />

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
                <div class="flex h-48 items-center justify-center text-sm text-zinc-400 dark:text-zinc-500">
                    <p>Belum ada data untuk ditampilkan</p>
                </div>
            @endif
        </flux:card>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <flux:card class="admin-card-elevated p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <flux:icon.clipboard-document-list class="size-5" />
                    </div>
                    <flux:heading size="sm">Per Layanan</flux:heading>
                </div>

                @if (count($serviceData) > 0 && collect($serviceData)->sum('count') > 0)
                    <flux:chart :value="$serviceData" class="w-full">
                        <flux:chart.viewport class="h-48">
                            <flux:chart.svg>
                                <flux:chart.bar field="count" class="text-emerald-500 dark:text-emerald-400" width="70%" />

                                <flux:chart.axis axis="x" field="name">
                                    <flux:chart.axis.tick class="text-xs" />
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
                    <div class="flex h-48 items-center justify-center text-sm text-zinc-400 dark:text-zinc-500">
                        <p>Belum ada data untuk ditampilkan</p>
                    </div>
                @endif
            </flux:card>

            <flux:card class="admin-card-elevated p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                        <flux:icon.building-office class="size-5" />
                    </div>
                    <flux:heading size="sm">Per Loket</flux:heading>
                </div>

                @if (count($counterData) > 0 && collect($counterData)->sum('count') > 0)
                    <flux:chart :value="$counterData" class="w-full">
                        <flux:chart.viewport class="h-48">
                            <flux:chart.svg>
                                <flux:chart.bar field="count" class="text-amber-500 dark:text-amber-400" width="70%" />

                                <flux:chart.axis axis="x" field="name">
                                    <flux:chart.axis.tick class="text-xs" />
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
                    <div class="flex h-48 items-center justify-center text-sm text-zinc-400 dark:text-zinc-500">
                        <p>Belum ada data untuk ditampilkan</p>
                    </div>
                @endif
            </flux:card>
        </div>

        <flux:card class="admin-card-elevated p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="admin-icon-box bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-900/50 dark:text-fuchsia-400">
                    <flux:icon.signal class="size-5" />
                </div>
                <flux:heading size="sm">Distribusi Kanal</flux:heading>
            </div>

            @if (count($channelData) > 0 && collect($channelData)->sum('total') > 0)
                <flux:chart :value="$channelData" class="w-full">
                    <flux:chart.viewport class="h-48">
                        <flux:chart.svg>
                            <flux:chart.bar field="total" class="text-fuchsia-500 dark:text-fuchsia-400" width="55%" />

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
                <div class="flex h-48 items-center justify-center text-sm text-zinc-400 dark:text-zinc-500">
                    <p>Belum ada data untuk ditampilkan</p>
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Activity log (auto-updates every 30s) --}}
    <flux:card class="admin-card-elevated p-5" wire:poll.30s>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                    <flux:icon.clock class="size-5" />
                </div>
                <div>
                    <flux:heading size="sm">Aktivitas Terkini</flux:heading>
                    <flux:text class="text-xs text-zinc-500">Auto-refresh setiap 30 detik</flux:text>
                </div>
            </div>
            <flux:badge size="sm" color="green" variant="pill">Live</flux:badge>
        </div>

        @if($this->recentActivities->isEmpty())
            <div class="py-8 text-center text-zinc-400">
                <p>Belum ada aktivitas</p>
            </div>
        @else
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
                    <flux:table.row>
                        <flux:table.cell>{{ $activity->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $this->actionColor($activity->action) }}">
                                {{ $this->actionLabel($activity->action) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $activity->queueTicket?->ticket_number ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $activity->queueTicket?->service?->name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $activity->user?->name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $activity->counter?->name ?? '-' }}</flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Ringkasan Operasional --}}
    <div class="grid grid-cols-2 gap-4">
        <flux:card class="admin-stat-success p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase dark:text-emerald-300">Booking Berhasil</flux:text>
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $this->bookingSuccess }}</p>
                    <flux:text class="text-xs text-emerald-600/70 dark:text-emerald-400/70">Hari ini</flux:text>
                </div>
                <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                    <flux:icon.check class="size-5" />
                </div>
            </div>
        </flux:card>

        <flux:card class="admin-stat-danger p-5">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-red-700 uppercase dark:text-red-300">Booking Gagal</flux:text>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $this->bookingFailed }}</p>
                    <flux:text class="text-xs text-red-600/70 dark:text-red-400/70">Hari ini</flux:text>
                </div>
                <div class="admin-icon-box bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                    <flux:icon.x-circle class="size-5" />
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Shortcut Manajemen --}}
    <flux:card class="p-5">
        <div class="flex items-center gap-3 mb-4">
            <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                <flux:icon.squares-2x2 class="size-5" />
            </div>
            <flux:heading size="sm">Shortcut Manajemen</flux:heading>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <flux:button :href="route('admin.layanan.index')" variant="filled" icon="clipboard-document-list" class="justify-center">Layanan</flux:button>
            <flux:button :href="route('admin.loket.index')" variant="filled" icon="building-office" class="justify-center">Loket</flux:button>
            <flux:button :href="route('admin.users.index')" variant="filled" icon="users" class="justify-center">Users</flux:button>
            <flux:button :href="route('admin.wilayah.index')" variant="filled" icon="map" class="justify-center">Wilayah</flux:button>
            <flux:button :href="url('/frontdesk/antrian')" variant="filled" icon="ticket" class="justify-center">Frontdesk</flux:button>
        </div>
    </flux:card>
</div>
