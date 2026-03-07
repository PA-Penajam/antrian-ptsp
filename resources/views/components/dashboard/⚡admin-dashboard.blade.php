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
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
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
