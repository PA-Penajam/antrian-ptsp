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

    public function mount(AdminStats $adminStats): void
    {
        $this->stats = $adminStats->build();
    }
};
?>

<div class="space-y-6" wire:poll.30s.visible="refreshStats">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="relative flex size-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex size-2 rounded-full bg-green-500"></span>
            </span>
            <span class="text-xs font-medium text-green-600 dark:text-green-400">Live</span>
        </div>
        <flux:text class="text-xs text-zinc-500">Diperbarui setiap 30 detik</flux:text>
    </div>

    <div wire:loading.remove wire:target="refreshStats" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <flux:card>
            <flux:subheading>Booking Berhasil Hari Ini</flux:subheading>
            <flux:heading size="lg">{{ $stats['booking_success_today'] }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:subheading>Booking Gagal Hari Ini</flux:subheading>
            <flux:heading size="lg">{{ $stats['booking_failed_today'] }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:subheading>Tiket Dibuat</flux:subheading>
            <flux:heading size="lg">{{ $stats['tickets_created_today'] }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:subheading>Tiket Batal</flux:subheading>
            <flux:heading size="lg">{{ $stats['tickets_cancelled_today'] }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:subheading>Tiket Selesai</flux:subheading>
            <flux:heading size="lg">{{ $stats['tickets_completed_today'] }}</flux:heading>
        </flux:card>
</div>
    <div wire:loading wire:target="refreshStats" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @for ($i = 0; $i < 5; $i++)
            <flux:skeleton class="h-24 rounded-xl" />
        @endfor
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="space-y-3">
            <flux:heading size="lg">Aktivitas Pengguna Layanan</flux:heading>
            @if (count($stats['public_activity']) === 0)
                <flux:text class="text-zinc-500">Belum ada aktivitas publik hari ini.</flux:text>
            @else
                @foreach ($stats['public_activity'] as $channel => $total)
                    <div class="flex items-center justify-between">
                        <flux:text>{{ $channel }}</flux:text>
                        <flux:badge>{{ $total }}</flux:badge>
                    </div>
                @endforeach
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">Ringkasan Failure Operasional</flux:heading>
            <div class="flex items-center justify-between">
                <flux:text>Cancelled</flux:text>
                <flux:badge color="red">{{ $stats['failure_summary']['cancelled'] }}</flux:badge>
            </div>
            <div class="flex items-center justify-between">
                <flux:text>Skipped</flux:text>
                <flux:badge color="amber">{{ $stats['failure_summary']['skipped'] }}</flux:badge>
            </div>
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="lg">Shortcut Manajemen</flux:heading>
        <div class="flex flex-wrap gap-2">
            <flux:button :href="url('/admin/layanan')" variant="primary">Layanan</flux:button>
            <flux:button :href="url('/admin/loket')" variant="filled">Loket</flux:button>
            <flux:button :href="url('/admin/users')" variant="ghost">Users</flux:button>
            <flux:button :href="url('/admin/roles')" variant="ghost">Roles</flux:button>
            <flux:button :href="url('/admin/izin-layanan')" variant="ghost">Izin Layanan</flux:button>
        </div>
    </flux:card>
</div>
