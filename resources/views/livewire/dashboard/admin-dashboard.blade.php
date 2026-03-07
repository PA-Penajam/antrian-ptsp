<div class="space-y-6">
    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card class="p-4">
            <flux:heading size="sm">Total Hari Ini</flux:heading>
            <p class="text-3xl font-bold mt-2">{{ $this->todayTotal }}</p>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading size="sm">Sudah Dilayani</flux:heading>
            <p class="text-3xl font-bold mt-2 text-green-600">{{ $this->todayServed }}</p>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading size="sm">Menunggu</flux:heading>
            <p class="text-3xl font-bold mt-2 text-orange-600">{{ $this->todayWaiting }}</p>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading size="sm">Rata-rata Tunggu (menit)</flux:heading>
            <p class="text-3xl font-bold mt-2 text-blue-600">{{ $this->todayAvgWaitMinutes }}</p>
        </flux:card>
    </div>

    {{-- Date Range Filter --}}
    <flux:card class="p-4">
        <div class="flex gap-4 items-end">
            <flux:field>
                <flux:label>Dari Tanggal</flux:label>
                <flux:input type="date" wire:model.live="startDate" />
            </flux:field>

            <flux:field>
                <flux:label>Sampai Tanggal</flux:label>
                <flux:input type="date" wire:model.live="endDate" />
            </flux:field>
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
        <flux:card class="p-4">
            <flux:heading size="sm">Tren 7 Hari Terakhir</flux:heading>

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
            <flux:card class="p-4">
                <flux:heading size="sm">Per Layanan</flux:heading>

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

            <flux:card class="p-4">
                <flux:heading size="sm">Per Loket</flux:heading>

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

        <flux:card class="p-4">
            <flux:heading size="sm">Distribusi Kanal</flux:heading>

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
    <flux:card class="p-4" wire:poll.30s>
        <flux:heading size="sm">Aktivitas Terkini</flux:heading>

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
</div>
