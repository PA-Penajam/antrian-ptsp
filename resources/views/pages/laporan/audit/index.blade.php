<x-layouts::app :title="__('Audit Trail')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-3">
                <flux:badge color="blue" rounded>Laporan</flux:badge>
                <div>
                    <flux:heading size="xl" level="1">Audit Trail</flux:heading>
                    <flux:subheading class="mt-1">Log aktivitas antrian, pemanggilan, dan aksi pengguna.</flux:subheading>
                </div>
            </div>
        </div>

        <flux:card class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.clock class="size-5" />
                    </div>
                    <flux:heading size="lg">Log Aktivitas</flux:heading>
                </div>

                <form method="GET" action="{{ url('/laporan/audit') }}" class="flex flex-wrap items-end gap-3 sm:flex-nowrap">
                    <flux:field>
                        <flux:label>Tanggal</flux:label>
                        <flux:input type="date" name="date" value="{{ $date }}" />
                    </flux:field>
                    <flux:field class="grow sm:w-64">
                        <flux:label class="sr-only">Pencarian</flux:label>
                        <flux:input
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari tiket, user, atau loket..."
                            icon="magnifying-glass"
                        />
                    </flux:field>
                    <flux:button type="submit" variant="primary">Filter</flux:button>
                </form>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Waktu</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                    <flux:table.column>Tiket</flux:table.column>
                    <flux:table.column>Layanan / Loket</flux:table.column>
                    <flux:table.column>Pelaku</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($activities as $activity)
                        <flux:table.row>
                            <flux:table.cell class="whitespace-nowrap text-xs">
                                {{ $activity->created_at->format('H:i:s') }}
                                <div class="text-zinc-500">{{ $activity->created_at->format('d/m/Y') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $actionColor = match($activity->action) {
                                        'created' => 'blue',
                                        'called' => 'amber',
                                        'completed' => 'emerald',
                                        'skipped' => 'zinc',
                                        'cancelled' => 'red',
                                        'recalled' => 'cyan',
                                        'transferred' => 'violet',
                                        default => 'slate',
                                    };
                                    $actionLabel = match($activity->action) {
                                        'created' => 'Dibuat',
                                        'called' => 'Dipanggil',
                                        'completed' => 'Selesai',
                                        'skipped' => 'Dilewati',
                                        'cancelled' => 'Dibatalkan',
                                        'recalled' => 'Dipanggil Ulang',
                                        'transferred' => 'Ditransfer',
                                        default => Str::title($activity->action),
                                    };
                                @endphp
                                <flux:badge size="sm" color="{{ $actionColor }}">{{ $actionLabel }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($activity->queueTicket)
                                    <span class="font-medium">{{ $activity->queueTicket->ticket_number }}</span>
                                @else
                                    <span class="text-zinc-500">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="text-sm">
                                    {{ $activity->queueTicket?->service?->name ?? '-' }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $activity->counter?->name ?? 'Sistem / Kiosk' }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($activity->user)
                                    <div class="flex items-center gap-2">
                                        <flux:icon.user class="size-4 text-zinc-400" />
                                        <span>{{ $activity->user->name }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 text-zinc-500">
                                        <flux:icon.cpu-chip class="size-4" />
                                        <span>Sistem / Pengunjung</span>
                                    </div>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5">
                                <div class="flex flex-col items-center justify-center py-8 text-center">
                                    <flux:icon name="inbox" class="h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                    <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Tidak ada log aktivitas</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Belum ada catatan aktivitas pada tanggal atau kata kunci tersebut.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if ($activities->hasPages())
                <div class="mt-4">
                    {{ $activities->links() }}
                </div>
            @endif
        </flux:card>
    </div>
</x-layouts::app>
