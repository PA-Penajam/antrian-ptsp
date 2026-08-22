<?php

use App\Models\QueueActivity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public array $activities = [];

    public int $unreadCount = 0;

    public bool $isOpen = false;

    public function mount(): void
    {
        $this->loadActivities();
    }

    public function updatedIsOpen(bool $isOpen): void
    {
        if ($isOpen) {
            $this->markAsRead();
        }
    }

    public function loadActivities(): void
    {
        $this->activities = QueueActivity::query()
            ->with('queueTicket:id,ticket_number')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (QueueActivity $activity): array => [
                'id' => $activity->id,
                'action' => $activity->action,
                'action_label' => Str::headline($activity->action),
                'ticket_number' => $activity->queueTicket?->ticket_number,
                'created_at' => $activity->created_at?->toIso8601String(),
                'relative_time' => $activity->created_at?->locale('id')->diffForHumans() ?? 'Baru saja',
            ])
            ->all();

        $this->unreadCount = QueueActivity::query()
            ->where('created_at', '>=', CarbonImmutable::now()->subMinutes(5))
            ->count();
    }

    public function markAsRead(): void
    {
        $this->unreadCount = 0;
    }
};
?>

<flux:dropdown wire:model="isOpen" position="bottom" align="end">
    <button
        type="button"
        class="group relative inline-flex size-11 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200/80 bg-white/95 shadow-sm shadow-zinc-900/5 transition hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-md hover:shadow-sky-500/10 focus:outline-none focus:ring-2 focus:ring-sky-500/30 dark:border-zinc-700/80 dark:bg-zinc-900/95 dark:hover:border-sky-500/60 dark:hover:shadow-sky-950/30"
        aria-label="Buka notifikasi aktivitas terbaru"
    >
        <span class="absolute inset-0 bg-linear-to-br from-sky-500/12 via-cyan-500/6 to-emerald-500/12 opacity-0 transition duration-200 group-hover:opacity-100"></span>

        <flux:icon name="bell" class="relative size-5 text-zinc-700 transition group-hover:scale-105 group-hover:text-sky-600 dark:text-zinc-200 dark:group-hover:text-sky-300" />

        @if ($unreadCount > 0)
            <span class="absolute -end-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full border border-white bg-rose-500 px-1.5 text-xs font-semibold leading-none text-white shadow-sm shadow-rose-500/30 dark:border-zinc-900">
                {{ min($unreadCount, 99) }}
            </span>
        @endif
    </button>

    <flux:popover class="w-[22rem] max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-3xl border border-zinc-200/80 bg-white/96 p-0 shadow-2xl shadow-zinc-950/10 backdrop-blur dark:border-zinc-700/80 dark:bg-zinc-900/96 dark:shadow-black/40">
        <div class="border-b border-zinc-200/80 bg-linear-to-r from-sky-500/8 via-cyan-500/4 to-transparent px-5 py-4 dark:border-zinc-700/80 dark:from-sky-400/12 dark:via-cyan-400/6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <flux:heading size="lg">Aktivitas Terbaru</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        10 event terbaru dari antrian operasional.
                    </flux:text>
                </div>

                @if ($unreadCount > 0)
                    <span class="inline-flex items-center rounded-full bg-sky-500/10 px-2.5 py-1 text-xs font-semibold uppercase tracking-wider text-sky-700 dark:bg-sky-400/15 dark:text-sky-300">
                        {{ min($unreadCount, 99) }} baru
                    </span>
                @endif
            </div>
        </div>

        @if ($activities === [])
            <div class="flex flex-col items-center justify-center gap-3 px-5 py-10 text-center">
                <div class="flex size-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon name="bell" class="size-6" />
                </div>

                <div class="space-y-1">
                    <flux:heading size="sm">Belum ada aktivitas</flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        Notifikasi event antrian akan muncul di panel ini.
                    </flux:text>
                </div>
            </div>
        @else
            <div class="max-h-[26rem] overflow-y-auto px-2 py-2">
                @foreach ($activities as $activity)
                    <div
                        wire:key="queue-activity-{{ $activity['id'] }}"
                        class="group rounded-2xl border border-transparent px-3 py-3 transition hover:border-sky-200/70 hover:bg-sky-50/70 dark:hover:border-sky-800/60 dark:hover:bg-sky-500/10"
                    >
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-200">
                                <flux:icon name="sparkles" class="size-4" />
                            </div>

                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">
                                            {{ $activity['action_label'] }}
                                        </p>

                                        <p class="mt-1 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            {{ $activity['action'] }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                        {{ $activity['relative_time'] }}
                                    </span>
                                </div>

                                @if ($activity['ticket_number'])
                                    <div class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        <flux:icon name="ticket" class="size-3.5 text-emerald-600 dark:text-emerald-400" />
                                        <span>Tiket {{ $activity['ticket_number'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:popover>
</flux:dropdown>
