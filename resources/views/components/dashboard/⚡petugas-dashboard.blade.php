<?php

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use App\Support\Dashboard\PetugasStats;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?QueueTicket $activeTicket = null;

    /**
     * @var array<int,array{id:int,ticket_number:string,service_name:string}>
     */
    public array $skippedTickets = [];

    /**
     * @var array{
     *     served_today:int,
     *     action_counts:array{skipped:int,recalled:int,completed:int},
     *     service_distribution:array<string,int>
     * }
     */
    public array $stats = [
        'served_today' => 0,
        'action_counts' => [
            'skipped' => 0,
            'recalled' => 0,
            'completed' => 0,
        ],
        'service_distribution' => [],
    ];

    public function mount(PetugasStats $petugasStats): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $today = now()->toDateString();
        $allowedServiceIds = $user->services()->pluck('services.id');
        if ($allowedServiceIds->isEmpty()) {
            return;
        }

        $this->activeTicket = QueueTicket::query()
            ->with('service')
            ->whereIn('service_id', $allowedServiceIds)
            ->whereDate('service_date', $today)
            ->where('status', QueueStatus::Called)
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->first();

        $this->skippedTickets = QueueTicket::query()
            ->with('service')
            ->whereIn('service_id', $allowedServiceIds)
            ->whereDate('service_date', $today)
            ->where('status', QueueStatus::Skipped)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(fn (QueueTicket $ticket): array => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'service_name' => $ticket->service?->name ?? '-',
            ])
            ->toArray();

        $this->stats = $petugasStats->build($user, $today);
    }

    #[Computed]
    public function hasActiveTicket(): bool
    {
        return $this->activeTicket !== null;
    }
};
?>

<div class="space-y-6">
    <flux:card class="space-y-3">
        <flux:heading size="lg">Tiket Aktif</flux:heading>

        @if ($this->hasActiveTicket)
            <div class="space-y-1">
                <flux:badge color="blue" size="lg">{{ $activeTicket?->ticket_number }}</flux:badge>
                <flux:text>{{ $activeTicket?->service?->name }}</flux:text>
            </div>
        @else
            <flux:callout icon="information-circle" color="zinc">
                Tidak ada tiket aktif saat ini.
            </flux:callout>
        @endif

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
            <flux:button variant="primary">Panggil Berikutnya</flux:button>
            <flux:button variant="filled">Proses Layanan</flux:button>
            <flux:button variant="ghost">Panggil Ulang</flux:button>
            <flux:button variant="ghost">Lewati</flux:button>
            <flux:button variant="filled">Selesai</flux:button>
        </div>
    </flux:card>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="space-y-3">
            <flux:heading size="lg">Daftar Skip Layanan</flux:heading>
            @if (count($skippedTickets) === 0)
                <flux:text class="text-zinc-500">Belum ada tiket skip hari ini.</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Tiket</flux:table.column>
                        <flux:table.column>Layanan</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($skippedTickets as $ticket)
                            <flux:table.row>
                                <flux:table.cell>{{ $ticket['ticket_number'] }}</flux:table.cell>
                                <flux:table.cell>{{ $ticket['service_name'] }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">Jumlah pihak yang dilayani hari ini</flux:heading>
            <flux:heading size="xl">{{ $stats['served_today'] }}</flux:heading>
            <flux:separator />
            <div class="grid grid-cols-3 gap-2 text-sm">
                <div>Skip: <strong>{{ $stats['action_counts']['skipped'] }}</strong></div>
                <div>Recall: <strong>{{ $stats['action_counts']['recalled'] }}</strong></div>
                <div>Selesai: <strong>{{ $stats['action_counts']['completed'] }}</strong></div>
            </div>
        </flux:card>
    </div>
</div>
