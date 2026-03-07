<?php
use Livewire\Volt\Component;
use App\Models\QueueTicket;

new class extends \Livewire\Component {
    public array $trendData = [];

    public function mount()
    {
        $this->trendData = collect(range(6, 0))->map(function ($days) {
            $date = now()->subDays($days)->toDateString();
            return [
                'date' => now()->subDays($days)->format('d M'),
                'total' => QueueTicket::query()->whereDate('service_date', $date)->count(),
                'completed' => QueueTicket::query()->whereDate('service_date', $date)->where('status', 'completed')->count(),
            ];
        })->values()->toArray();
    }

    public function with(): array
    {
        $today = now()->toDateString();
        $summary = [
            'waiting' => QueueTicket::query()->whereDate('service_date', $today)->where('status', 'waiting')->count(),
            'called' => QueueTicket::query()->whereDate('service_date', $today)->where('status', 'called')->count(),
            'completed' => QueueTicket::query()->whereDate('service_date', $today)->where('status', 'completed')->count(),
            'cancelled' => QueueTicket::query()->whereDate('service_date', $today)->where('status', 'cancelled')->count(),
        ];
        $summary['total'] = array_sum($summary);

        return [
            'summary' => $summary,
        ];
    }
}; ?>

<div class="space-y-8">
    <div>
        <flux:heading size="xl" level="1">Ringkasan PTSP Hari Ini</flux:heading>
        <flux:subheading>Pantau antrian secara real-time</flux:subheading>
    </div>

    <div class="grid gap-6 md:grid-cols-5">
        <flux:card class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
            <flux:subheading class="text-blue-600 dark:text-blue-400">Menunggu</flux:subheading>
            <flux:heading size="3xl" class="mt-2 text-blue-700 dark:text-blue-300">{{ $summary['waiting'] ?? 0 }}</flux:heading>
        </flux:card>
        <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
            <flux:subheading class="text-amber-600 dark:text-amber-400">Dipanggil</flux:subheading>
            <flux:heading size="3xl" class="mt-2 text-amber-700 dark:text-amber-300">{{ $summary['called'] ?? 0 }}</flux:heading>
        </flux:card>
        <flux:card class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
            <flux:subheading class="text-green-600 dark:text-green-400">Selesai</flux:subheading>
            <flux:heading size="3xl" class="mt-2 text-green-700 dark:text-green-300">{{ $summary['completed'] ?? 0 }}</flux:heading>
        </flux:card>
        <flux:card class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
            <flux:subheading class="text-red-600 dark:text-red-400">Batal</flux:subheading>
            <flux:heading size="3xl" class="mt-2 text-red-700 dark:text-red-300">{{ $summary['cancelled'] ?? 0 }}</flux:heading>
        </flux:card>
        <flux:card class="bg-zinc-50 dark:bg-zinc-900/20 border-zinc-200 dark:border-zinc-800">
            <flux:subheading class="text-zinc-600 dark:text-zinc-400">Total</flux:subheading>
            <flux:heading size="3xl" class="mt-2 text-zinc-700 dark:text-zinc-300">{{ $summary['total'] ?? 0 }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <flux:card>
            <flux:heading size="lg" class="mb-4">Tren Antrian 7 Hari Terakhir</flux:heading>
            <flux:chart wire:model="trendData" class="aspect-[3/1]">
                <flux:chart.svg>
                    <flux:chart.group>
                        <flux:chart.bar field="total" class="text-blue-200 dark:text-blue-900" />
                        <flux:chart.bar field="completed" class="text-green-500" />
                    </flux:chart.group>
                    <flux:chart.axis axis="x" field="date">
                        <flux:chart.axis.tick />
                    </flux:chart.axis>
                    <flux:chart.axis axis="y">
                        <flux:chart.axis.grid />
                        <flux:chart.axis.tick />
                    </flux:chart.axis>
                </flux:chart.svg>
                <flux:chart.tooltip>
                    <flux:chart.tooltip.heading field="date" />
                    <flux:chart.tooltip.value field="total" label="Total" />
                    <flux:chart.tooltip.value field="completed" label="Selesai" />
                </flux:chart.tooltip>
                <div class="flex justify-center gap-4 pt-4">
                    <flux:chart.legend label="Total Antrian">
                        <flux:chart.legend.indicator class="bg-blue-200 dark:bg-blue-900" />
                    </flux:chart.legend>
                    <flux:chart.legend label="Selesai Dilayani">
                        <flux:chart.legend.indicator class="bg-green-500" />
                    </flux:chart.legend>
                </div>
            </flux:chart>
        </flux:card>
    </div>
</div>
