<?php

use App\Support\Dashboard\MonitorStats;
use Livewire\Component;

new class extends Component
{
    /**
     * @var array{
     *     total_served_today:int,
     *     throughput_today:int,
     *     backlog_by_service:array<string,int>,
     *     served_by_officer:array<string,int>,
     *     officer_service_matrix:array<string,array<string,int>>
     * }
     */
    public array $stats = [
        'total_served_today' => 0,
        'throughput_today' => 0,
        'backlog_by_service' => [],
        'served_by_officer' => [],
        'officer_service_matrix' => [],
    ];

    public function mount(MonitorStats $monitorStats): void
    {
        $this->stats = $monitorStats->build();
    }
};
?>

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <flux:subheading>Total Dilayani Hari Ini</flux:subheading>
            <flux:heading size="xl">{{ $stats['total_served_today'] }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:subheading>Throughput Hari Ini</flux:subheading>
            <flux:heading size="xl">{{ $stats['throughput_today'] }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="space-y-3">
            <flux:heading size="lg">Backlog per Layanan</flux:heading>
            @if (count($stats['backlog_by_service']) === 0)
                <flux:text class="text-zinc-500">Belum ada backlog hari ini.</flux:text>
            @else
                @foreach ($stats['backlog_by_service'] as $service => $total)
                    <div class="flex items-center justify-between">
                        <flux:text>{{ $service }}</flux:text>
                        <flux:badge>{{ $total }}</flux:badge>
                    </div>
                @endforeach
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">Dilayani per Petugas</flux:heading>
            @if (count($stats['served_by_officer']) === 0)
                <flux:text class="text-zinc-500">Belum ada aktivitas petugas.</flux:text>
            @else
                @foreach ($stats['served_by_officer'] as $officer => $total)
                    <div class="flex items-center justify-between">
                        <flux:text>{{ $officer }}</flux:text>
                        <flux:badge color="green">{{ $total }}</flux:badge>
                    </div>
                @endforeach
            @endif
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="lg">Distribusi Petugas x Layanan</flux:heading>
        @if (count($stats['officer_service_matrix']) === 0)
            <flux:text class="text-zinc-500">Belum ada aktivitas hari ini.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Petugas</flux:table.column>
                    <flux:table.column>Distribusi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($stats['officer_service_matrix'] as $officer => $services)
                        <flux:table.row>
                            <flux:table.cell>{{ $officer }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($services as $service => $total)
                                        <flux:badge size="sm">{{ $service }}: {{ $total }}</flux:badge>
                                    @endforeach
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
