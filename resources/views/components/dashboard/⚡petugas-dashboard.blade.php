<?php

use App\Actions\Queue\CallNextTicket;
use App\Actions\Queue\CancelTicket;
use App\Actions\Queue\CompleteTicket;
use App\Actions\Queue\RecallTicket;
use App\Actions\Queue\SkipTicket;
use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueueTicket;
use App\Models\User;
use App\Support\Dashboard\PetugasStats;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?int $lockedCounterId = null;

    public bool $fullScreen = false;

    public ?int $selectedCounterId = null;

    public ?QueueTicket $activeTicket = null;

    public int $waitingCount = 0;

    /**
     * @var array<int,array{id:int,name:string,pool_name:string,pool_id:int}>
     */
    public array $counters = [];

    public string $feedbackTone = 'zinc';

    public string $feedbackMessage = '';

    private ?array $cachedStats = null;

    private ?string $cachedStatsDate = null;

    /**
     * @var array<int,array{id:int,ticket_number:string,sequence_number:int,service_name:string,visitor_name:string}>
     */
    public array $waitingTickets = [];

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

    public function mount(PetugasStats $petugasStats, ?int $counterId = null, bool $fullScreen = false): void
    {
        $this->lockedCounterId = $counterId;
        $this->fullScreen = $fullScreen;
        $this->syncBoard($petugasStats);
    }

    public function updatedSelectedCounterId(): void
    {
        $this->refreshBoard();
    }

    public function refreshBoard(): void
    {
        $this->syncBoard(app(PetugasStats::class));
    }

    public function callNext(): void
    {
        $counter = $this->resolveSelectedCounter();
        if (! $counter) {
            $this->setFeedback('Pilih loket aktif terlebih dahulu.', 'amber');

            return;
        }

        try {
            $ticket = app(CallNextTicket::class)->handle($counter, $this->currentUserId());

            if (! $ticket) {
                $this->setFeedback('Tidak ada antrean menunggu yang sesuai layanan petugas.', 'amber');
            } else {
                $this->setFeedback("Nomor {$ticket->ticket_number} dipanggil ke {$counter->name}.", 'blue');
            }
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function recall(): void
    {
        $counter = $this->resolveSelectedCounter();
        $ticket = $this->resolveActiveTicket();

        if (! $counter || ! $ticket) {
            $this->setFeedback('Tidak ada tiket aktif untuk dipanggil ulang.', 'amber');

            return;
        }

        try {
            app(RecallTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} dipanggil ulang.", 'blue');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function skip(): void
    {
        $counter = $this->resolveSelectedCounter();
        $ticket = $this->resolveActiveTicket();

        if (! $counter || ! $ticket) {
            $this->setFeedback('Tidak ada tiket aktif untuk dilewati.', 'amber');

            return;
        }

        try {
            app(SkipTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} dilewati.", 'amber');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function complete(): void
    {
        $counter = $this->resolveSelectedCounter();
        $ticket = $this->resolveActiveTicket();

        if (! $counter || ! $ticket) {
            $this->setFeedback('Tidak ada tiket aktif untuk diselesaikan.', 'amber');

            return;
        }

        try {
            app(CompleteTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} ditandai selesai.", 'green');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function cancel(): void
    {
        $counter = $this->resolveSelectedCounter();
        $ticket = $this->resolveActiveTicket();

        if (! $counter || ! $ticket) {
            $this->setFeedback('Tidak ada tiket aktif untuk dibatalkan.', 'amber');

            return;
        }

        try {
            app(CancelTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} dibatalkan.", 'red');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    #[Computed]
    public function hasSelectedCounter(): bool
    {
        return $this->selectedCounterId !== null;
    }

    #[Computed]
    public function isCounterLocked(): bool
    {
        return $this->lockedCounterId !== null;
    }

    #[Computed]
    public function selectedCounterName(): string
    {
        if ($this->selectedCounterId === null) {
            return '-';
        }

        $counter = collect($this->counters)
            ->firstWhere('id', $this->selectedCounterId);

        return is_array($counter)
            ? "{$counter['name']} - {$counter['pool_name']}"
            : '-';
    }

    private function syncBoard(PetugasStats $petugasStats): void
    {
        $user = $this->currentUser();
        if (! $user instanceof User) {
            $this->resetBoardState();

            return;
        }

        $today = now()->toDateString();
        $isAdmin = ($user->role?->value ?? $user->role) === 'admin';
        $allowedServiceIds = $user->services()->pluck('services.id');

        if (! $isAdmin && $allowedServiceIds->isEmpty()) {
            $this->resetBoardState();
            $this->setFeedback('Akun petugas belum memiliki layanan yang diizinkan.', 'amber');

            return;
        }

        $countersQuery = Counter::query()
            ->with('queuePool:id,name')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');

        if (! $isAdmin) {
            $countersQuery->whereIn('queue_pool_id', function ($query) use ($allowedServiceIds): void {
                $query->select('queue_pool_id')
                    ->from('services')
                    ->whereIn('id', $allowedServiceIds)
                    ->whereNotNull('queue_pool_id');
            });
        }

        $availableCounters = $countersQuery->get(['id', 'name', 'queue_pool_id']);

        $this->counters = $availableCounters
            ->map(fn (Counter $counter): array => [
                'id' => $counter->id,
                'name' => $counter->name,
                'pool_name' => $counter->queuePool?->name ?? '-',
                'pool_id' => $counter->queue_pool_id,
            ])
            ->values()
            ->toArray();

        if (count($this->counters) === 0) {
            $this->selectedCounterId = null;
            $this->waitingCount = 0;
            $this->activeTicket = null;
            $this->waitingTickets = [];
            $this->skippedTickets = [];
            $this->stats = $this->resolveStatsWithCache($petugasStats, $user, $today);

            return;
        }

        $availableCounterIds = $availableCounters->pluck('id')->all();
        if ($this->lockedCounterId !== null) {
            if (! in_array($this->lockedCounterId, $availableCounterIds, true)) {
                $this->selectedCounterId = null;
                $this->waitingCount = 0;
                $this->activeTicket = null;
                $this->waitingTickets = [];
                $this->skippedTickets = [];
                $this->stats = $this->resolveStatsWithCache($petugasStats, $user, $today);
                $this->setFeedback('Anda tidak memiliki akses ke loket ini.', 'red');

                return;
            }

            $this->selectedCounterId = $this->lockedCounterId;
        }

        if (! in_array($this->selectedCounterId, $availableCounterIds, true)) {
            $this->selectedCounterId = $availableCounterIds[0];
        }

        $selectedCounter = $availableCounters->firstWhere('id', $this->selectedCounterId);
        if (! $selectedCounter instanceof Counter) {
            $this->waitingCount = 0;
            $this->activeTicket = null;
            $this->waitingTickets = [];
            $this->skippedTickets = [];
            $this->stats = $this->resolveStatsWithCache($petugasStats, $user, $today);

            return;
        }

        $queueQuery = QueueTicket::query()
            ->whereDate('service_date', $today)
            ->where('queue_pool_id', $selectedCounter->queue_pool_id);

        if (! $isAdmin) {
            $queueQuery->whereIn('service_id', $allowedServiceIds);
        }

        $this->waitingCount = (clone $queueQuery)
            ->where('status', QueueStatus::Waiting)
            ->count();

        $this->activeTicket = (clone $queueQuery)
            ->with('service')
            ->where('counter_id', $selectedCounter->id)
            ->where('status', QueueStatus::Called)
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->first();

        $this->waitingTickets = (clone $queueQuery)
            ->with('service')
            ->where('status', QueueStatus::Waiting)
            ->orderBy('sequence_number')
            ->orderBy('id')
            ->limit(8)
            ->get()
            ->map(fn (QueueTicket $ticket): array => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'sequence_number' => $ticket->sequence_number,
                'service_name' => $ticket->service?->name ?? '-',
                'visitor_name' => $ticket->visitor_name,
            ])
            ->toArray();

        $this->skippedTickets = (clone $queueQuery)
            ->with('service')
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

        $this->stats = $this->resolveStatsWithCache($petugasStats, $user, $today);
    }

    /**
     * @return array{
     *     served_today:int,
     *     action_counts:array{skipped:int,recalled:int,completed:int},
     *     service_distribution:array<string,int>
     * }
     */
    private function resolveStatsWithCache(PetugasStats $petugasStats, User $user, string $today): array
    {
        if ($this->cachedStatsDate === $today && $this->cachedStats !== null) {
            return $this->cachedStats;
        }

        $this->cachedStats = $petugasStats->build($user, $today);
        $this->cachedStatsDate = $today;

        return $this->cachedStats;
    }

    private function resolveSelectedCounter(): ?Counter
    {
        if ($this->selectedCounterId === null) {
            return null;
        }

        $cached = collect($this->counters)
            ->firstWhere('id', $this->selectedCounterId);

        if ($cached === null) {
            return null;
        }

        return Counter::query()
            ->where('is_active', true)
            ->find($this->selectedCounterId);
    }

    private function resolveActiveTicket(): ?QueueTicket
    {
        $ticketId = $this->activeTicket?->id;
        if (! $ticketId) {
            return null;
        }

        return QueueTicket::query()->find($ticketId);
    }

    private function resetBoardState(): void
    {
        $this->selectedCounterId = null;
        $this->activeTicket = null;
        $this->waitingCount = 0;
        $this->counters = [];
        $this->waitingTickets = [];
        $this->skippedTickets = [];
        $this->stats = [
            'served_today' => 0,
            'action_counts' => [
                'skipped' => 0,
                'recalled' => 0,
                'completed' => 0,
            ],
            'service_distribution' => [],
        ];
    }

    private function setFeedback(string $message, string $tone): void
    {
        $this->feedbackMessage = $message;
        $this->feedbackTone = $tone;
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    private function currentUserId(): ?int
    {
        return $this->currentUser()?->id;
    }

    #[Computed]
    public function hasActiveTicket(): bool
    {
        return $this->activeTicket !== null;
    }
};
?>

<div class="space-y-6" wire:poll.10s.visible="refreshBoard">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">Workstation Petugas</flux:heading>
            <flux:text class="text-zinc-500">Pilih loket, panggil tiket berikutnya, lalu lanjutkan status layanan.</flux:text>
        </div>
        <div class="flex items-center gap-2">
            @if (! $fullScreen && $this->hasSelectedCounter)
                <flux:button :href="route('officer.counter.show', ['counter' => $selectedCounterId])" variant="ghost" icon="arrows-pointing-out" size="sm">
                    Mode Layar Loket
                </flux:button>
            @endif
            <flux:badge color="blue">Refresh 10 detik</flux:badge>
        </div>
    </div>

    @if ($feedbackMessage !== '')
        <flux:callout icon="information-circle" color="{{ $feedbackTone }}">
            {{ $feedbackMessage }}
        </flux:callout>
    @endif

    <flux:card class="space-y-4">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,20rem)_repeat(3,minmax(0,1fr))]">
            @if ($this->isCounterLocked)
                <div class="rounded-xl border border-zinc-700 bg-zinc-900/70 p-3">
                    <flux:text class="text-xs uppercase tracking-wide text-zinc-400">Loket Aktif</flux:text>
                    <flux:heading size="sm" class="text-white">{{ $this->selectedCounterName }}</flux:heading>
                </div>
            @else
                <flux:field>
                    <flux:label>Loket Aktif</flux:label>
                    <flux:select wire:model.live="selectedCounterId">
                        @if (count($counters) === 0)
                            <flux:select.option value="">Belum ada loket aktif</flux:select.option>
                        @else
                            @foreach ($counters as $counter)
                                <flux:select.option value="{{ $counter['id'] }}">
                                    {{ $counter['name'] }} - {{ $counter['pool_name'] }}
                                </flux:select.option>
                            @endforeach
                        @endif
                    </flux:select>
                </flux:field>
            @endif

            <div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">Antrian Menunggu</flux:text>
                <flux:heading size="lg">{{ $waitingCount }}</flux:heading>
            </div>

            <div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">Tiket Aktif</flux:text>
                <flux:heading size="lg">{{ $activeTicket?->ticket_number ?? '-' }}</flux:heading>
            </div>

            <div class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">Layanan Aktif</flux:text>
                <flux:heading size="sm">{{ $activeTicket?->service?->name ?? '-' }}</flux:heading>
            </div>
        </div>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
            <flux:button
                variant="primary"
                icon="megaphone"
                wire:click="callNext"
                :disabled="! $this->hasSelectedCounter"
                wire:loading.attr="disabled"
            >
                Panggil Berikutnya
            </flux:button>
            <flux:button
                variant="ghost"
                icon="speaker-wave"
                wire:click="recall"
                :disabled="! $this->hasActiveTicket"
                wire:loading.attr="disabled"
            >
                Panggil Ulang
            </flux:button>
            <flux:button
                variant="ghost"
                icon="forward"
                x-on:click="$dispatch('open-modal', 'confirm-skip-ticket')"
                :disabled="! $this->hasActiveTicket"
                wire:loading.attr="disabled"
            >
                Lewati
            </flux:button>
            <flux:button
                variant="filled"
                icon="check-circle"
                wire:click="complete"
                :disabled="! $this->hasActiveTicket"
                wire:loading.attr="disabled"
            >
                Selesai
            </flux:button>
            <flux:button
                variant="ghost"
                icon="x-circle"
                x-on:click="$dispatch('open-modal', 'confirm-cancel-ticket')"
                :disabled="! $this->hasActiveTicket"
                wire:loading.attr="disabled"
            >
                Batalkan
            </flux:button>
        </div>
    </flux:card>

    <div class="grid gap-6 xl:grid-cols-3">
        <flux:card class="space-y-3 xl:col-span-2">
            <flux:heading size="lg">Antrean Menunggu Prioritas Panggil</flux:heading>
            @if (count($waitingTickets) === 0)
                <flux:text class="text-zinc-500">Belum ada antrean menunggu untuk loket terpilih.</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>No. Tiket</flux:table.column>
                        <flux:table.column>Urutan</flux:table.column>
                        <flux:table.column>Layanan</flux:table.column>
                        <flux:table.column>Pengunjung</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($waitingTickets as $ticket)
                            <flux:table.row>
                                <flux:table.cell>{{ $ticket['ticket_number'] }}</flux:table.cell>
                                <flux:table.cell>{{ $ticket['sequence_number'] }}</flux:table.cell>
                                <flux:table.cell>{{ $ticket['service_name'] }}</flux:table.cell>
                                <flux:table.cell>{{ $ticket['visitor_name'] }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">Kinerja Hari Ini</flux:heading>
            <div class="rounded-xl border border-green-200 bg-green-50 p-3 dark:border-green-900/60 dark:bg-green-950/30">
                <flux:text class="text-xs uppercase tracking-wide text-green-700 dark:text-green-300">Selesai Dilayani</flux:text>
                <flux:heading size="xl" class="text-green-700 dark:text-green-300">{{ $stats['served_today'] }}</flux:heading>
            </div>
            <div class="grid grid-cols-3 gap-2 text-sm">
                <div class="rounded-lg border border-zinc-200 p-2 text-center dark:border-zinc-700">
                    <div class="text-zinc-500">Skip</div>
                    <div class="font-semibold">{{ $stats['action_counts']['skipped'] }}</div>
                </div>
                <div class="rounded-lg border border-zinc-200 p-2 text-center dark:border-zinc-700">
                    <div class="text-zinc-500">Recall</div>
                    <div class="font-semibold">{{ $stats['action_counts']['recalled'] }}</div>
                </div>
                <div class="rounded-lg border border-zinc-200 p-2 text-center dark:border-zinc-700">
                    <div class="text-zinc-500">Selesai</div>
                    <div class="font-semibold">{{ $stats['action_counts']['completed'] }}</div>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="lg">Daftar Skip Layanan</flux:heading>
        @if (count($skippedTickets) === 0)
            <flux:text class="text-zinc-500">Belum ada tiket skip pada loket ini hari ini.</flux:text>
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

    <flux:modal name="confirm-skip-ticket" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Konfirmasi Lewati Tiket</flux:heading>
            <flux:text>
                Tiket aktif <strong>{{ $activeTicket?->ticket_number ?? '-' }}</strong> akan dipindahkan ke status skip.
            </flux:text>
            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" x-on:click="$dispatch('close-modal', 'confirm-skip-ticket')">
                    Batal
                </flux:button>
                <flux:button
                    type="button"
                    variant="filled"
                    color="amber"
                    wire:click="skip"
                    x-on:click="$dispatch('close-modal', 'confirm-skip-ticket')"
                    wire:loading.attr="disabled"
                >
                    Ya, Lewati
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="confirm-cancel-ticket" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">Konfirmasi Batalkan Tiket</flux:heading>
            <flux:text>
                Tiket aktif <strong>{{ $activeTicket?->ticket_number ?? '-' }}</strong> akan dibatalkan dari antrean.
            </flux:text>
            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" x-on:click="$dispatch('close-modal', 'confirm-cancel-ticket')">
                    Batal
                </flux:button>
                <flux:button
                    type="button"
                    variant="filled"
                    color="red"
                    wire:click="cancel"
                    x-on:click="$dispatch('close-modal', 'confirm-cancel-ticket')"
                    wire:loading.attr="disabled"
                >
                    Ya, Batalkan
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
