<?php

use App\Support\Dashboard\AdminStats;
use Livewire\Component;

new class extends Component
{
    /**
     * @var array{
     *     booking_success_today:int,
     *     booking_failed_today:int,
     *     tickets_created_today:int,
     *     tickets_cancelled_today:int,
     *     tickets_completed_today:int,
     *     failure_summary:array{cancelled:int,skipped:int},
     *     public_activity:array<string,int>
     * }
     */
    public array $stats = [
        'booking_success_today' => 0,
        'booking_failed_today' => 0,
        'tickets_created_today' => 0,
        'tickets_cancelled_today' => 0,
        'tickets_completed_today' => 0,
        'failure_summary' => [
            'cancelled' => 0,
            'skipped' => 0,
        ],
        'public_activity' => [],
    ];

    public array $trendData = [];

    public array $serviceDistribution = [];

    public function mount(AdminStats $adminStats): void
    {
        $this->stats = $adminStats->build();
        $this->trendData = $adminStats->getTrendData();
        $this->serviceDistribution = $adminStats->getServiceDistribution();
    }

    public function refreshStats(AdminStats $adminStats): void
    {
        $this->stats = $adminStats->build();
        $this->trendData = $adminStats->getTrendData();
        $this->serviceDistribution = $adminStats->getServiceDistribution();
    }
};
?>

<div class="space-y-6">
    <div wire:loading.remove wire:target="refreshStats" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <x-dashboard.stat-card
            :value="$stats['booking_success_today']"
            label="Booking Berhasil"
            icon="check-circle"
            color="green"
        />
        <x-dashboard.stat-card
            :value="$stats['booking_failed_today']"
            label="Booking Gagal"
            icon="x-circle"
            color="red"
        />
        <x-dashboard.stat-card
            :value="$stats['tickets_created_today']"
            label="Tiket Dibuat"
            icon="ticket"
            color="blue"
        />
        <x-dashboard.stat-card
            :value="$stats['tickets_cancelled_today']"
            label="Tiket Batal"
            icon="minus-circle"
            color="amber"
        />
        <x-dashboard.stat-card
            :value="$stats['tickets_completed_today']"
            label="Tiket Selesai"
            icon="check-badge"
            color="green"
        />
    </div>
    <div wire:loading wire:target="refreshStats" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @for ($i = 0; $i < 5; $i++)
            <flux:skeleton class="h-24 rounded-xl" />
        @endfor
    </div>

    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">Tren 7 Hari Terakhir</flux:heading>
            <flux:subheading>Perbandingan total tiket dan tiket selesai per hari.</flux:subheading>
        </div>

        @if (collect($trendData)->isEmpty() || (collect($trendData)->sum('total') === 0 && collect($trendData)->sum('completed') === 0))
            <flux:callout icon="information-circle">
                <flux:callout.heading>Belum ada data</flux:callout.heading>
                <flux:callout.text>Belum ada data tren untuk 7 hari terakhir.</flux:callout.text>
            </flux:callout>
        @else
            <flux:chart wire:model="trendData" class="w-full">
                <flux:chart.viewport class="aspect-[3/1] min-h-72">
                    <flux:chart.svg>
                        <flux:chart.group>
                            <flux:chart.bar field="total" class="text-blue-500 dark:text-blue-400" />
                            <flux:chart.bar field="completed" class="text-green-500 dark:text-green-400" />
                        </flux:chart.group>
                        <flux:chart.axis axis="x" field="date">
                            <flux:chart.axis.tick />
                            <flux:chart.axis.line />
                        </flux:chart.axis>
                        <flux:chart.axis axis="y">
                            <flux:chart.axis.grid />
                            <flux:chart.axis.tick />
                        </flux:chart.axis>
                        <flux:chart.cursor type="area" />
                    </flux:chart.svg>
                </flux:chart.viewport>

                <flux:chart.tooltip>
                    <flux:chart.tooltip.heading field="date" />
                    <flux:chart.tooltip.value field="total" label="Total Tiket" />
                    <flux:chart.tooltip.value field="completed" label="Selesai" />
                </flux:chart.tooltip>

                <div class="flex flex-wrap justify-center gap-4 pt-2">
                    <flux:chart.legend label="Total Tiket">
                        <flux:chart.legend.indicator class="bg-blue-500 dark:bg-blue-400" />
                    </flux:chart.legend>
                    <flux:chart.legend label="Selesai">
                        <flux:chart.legend.indicator class="bg-green-500 dark:bg-green-400" />
                    </flux:chart.legend>
                </div>
            </flux:chart>
        @endif
    </flux:card>

    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">Distribusi per Layanan</flux:heading>
            <flux:subheading>Komposisi tiket hari ini berdasarkan layanan yang paling aktif.</flux:subheading>
        </div>

        @if (collect($serviceDistribution)->isEmpty() || collect($serviceDistribution)->sum('count') === 0)
            <flux:callout icon="information-circle">
                <flux:callout.heading>Belum ada distribusi layanan hari ini</flux:callout.heading>
            </flux:callout>
        @else
            @php
                $serviceColors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#a855f7', '#71717a'];
                $serviceTotal = collect($serviceDistribution)->sum('count');
                $radius = 60;
                $circumference = 2 * M_PI * $radius;
                $segmentOffset = 0;
            @endphp

            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex justify-center lg:w-64 lg:shrink-0">
                    <svg viewBox="0 0 160 160" class="h-44 w-44 overflow-visible">
                        <circle
                            cx="80"
                            cy="80"
                            r="{{ $radius }}"
                            fill="none"
                            stroke="rgb(228 228 231 / 0.7)"
                            stroke-width="20"
                        />

                        @foreach ($serviceDistribution as $index => $service)
                            @php
                                $segmentLength = ($service['percentage'] / 100) * $circumference;
                                $dashOffset = $circumference - $segmentOffset;
                                $segmentOffset += $segmentLength;
                            @endphp

                            <circle
                                cx="80"
                                cy="80"
                                r="{{ $radius }}"
                                fill="none"
                                stroke="{{ $serviceColors[$index] ?? '#71717a' }}"
                                stroke-width="20"
                                stroke-linecap="butt"
                                stroke-dasharray="{{ $segmentLength }} {{ max($circumference - $segmentLength, 0) }}"
                                stroke-dashoffset="{{ $dashOffset }}"
                                transform="rotate(-90 80 80)"
                            />
                        @endforeach

                        <text
                            x="80"
                            y="74"
                            text-anchor="middle"
                            class="fill-zinc-900 text-2xl font-bold dark:fill-zinc-100"
                        >
                            {{ $serviceTotal }}
                        </text>
                        <text
                            x="80"
                            y="96"
                            text-anchor="middle"
                            class="fill-zinc-500 text-xs font-medium dark:fill-zinc-400"
                        >
                            tiket hari ini
                        </text>
                    </svg>
                </div>

                <div class="space-y-3 lg:flex-1">
                    @foreach ($serviceDistribution as $index => $service)
                        <div class="flex items-center gap-3 rounded-xl border border-zinc-200/70 bg-zinc-50/80 px-3 py-2 dark:border-zinc-800 dark:bg-zinc-900/60">
                            <svg class="h-3 w-3 shrink-0" viewBox="0 0 12 12" aria-hidden="true">
                                <circle cx="6" cy="6" r="6" fill="{{ $serviceColors[$index] ?? '#71717a' }}" />
                            </svg>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $service['name'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format($service['count']) }} tiket</p>
                            </div>

                            <p class="text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                                {{ number_format($service['percentage'], 1) }}%
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </flux:card>

    <div class="space-y-3">
        <flux:heading size="lg">Akses Cepat</flux:heading>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a
                href="/admin/layanan"
                wire:navigate
                class="group rounded-xl border border-blue-200 bg-blue-50 p-5 transition-all duration-200 hover:scale-[1.02] hover:shadow-md dark:border-blue-800 dark:bg-blue-900/20"
            >
                <flux:icon name="cog-6-tooth" class="mb-3 size-8 text-blue-600 dark:text-blue-400" />
                <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Layanan</p>
            </a>

            <a
                href="/admin/loket"
                wire:navigate
                class="group rounded-xl border border-green-200 bg-green-50 p-5 transition-all duration-200 hover:scale-[1.02] hover:shadow-md dark:border-green-800 dark:bg-green-900/20"
            >
                <flux:icon name="computer-desktop" class="mb-3 size-8 text-green-600 dark:text-green-400" />
                <p class="text-sm font-medium text-green-700 dark:text-green-300">Loket</p>
            </a>

            <a
                href="/admin/users"
                wire:navigate
                class="group rounded-xl border border-amber-200 bg-amber-50 p-5 transition-all duration-200 hover:scale-[1.02] hover:shadow-md dark:border-amber-800 dark:bg-amber-900/20"
            >
                <flux:icon name="users" class="mb-3 size-8 text-amber-600 dark:text-amber-400" />
                <p class="text-sm font-medium text-amber-700 dark:text-amber-300">Users</p>
            </a>

            <a
                href="/admin/roles"
                wire:navigate
                class="group rounded-xl border border-purple-200 bg-purple-50 p-5 transition-all duration-200 hover:scale-[1.02] hover:shadow-md dark:border-purple-800 dark:bg-purple-900/20"
            >
                <flux:icon name="shield-check" class="mb-3 size-8 text-purple-600 dark:text-purple-400" />
                <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Roles</p>
            </a>

            <a
                href="/admin/izin-layanan"
                wire:navigate
                class="group rounded-xl border border-red-200 bg-red-50 p-5 transition-all duration-200 hover:scale-[1.02] hover:shadow-md dark:border-red-800 dark:bg-red-900/20"
            >
                <flux:icon name="key" class="mb-3 size-8 text-red-600 dark:text-red-400" />
                <p class="text-sm font-medium text-red-700 dark:text-red-300">Izin Layanan</p>
            </a>
        </div>
    </div>
</div>
