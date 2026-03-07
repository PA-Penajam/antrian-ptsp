<flux:main container>
    <div wire:poll.5000ms class="mx-auto max-w-5xl space-y-8">
        <div class="text-center">
            <flux:heading size="xl" level="1">Display Antrian PTSP</flux:heading>
            <flux:text class="mt-2 text-base text-slate-600">Informasi panggilan antrian {{ config('institution.name') }} yang diperbarui otomatis setiap 5 detik.</flux:text>
        </div>

        <flux:card class="space-y-4 border-amber-200 bg-amber-50/60">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg" icon="megaphone">Sedang Dipanggil</flux:heading>
                <flux:badge color="amber" inset="top bottom">Live</flux:badge>
            </div>

            @if ($currentCalls->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($currentCalls as $ticket)
                        <flux:card wire:key="current-call-{{ $ticket->id }}" class="space-y-3 border-amber-300 bg-white text-center shadow-sm">
                            <flux:heading size="xl" class="text-amber-700">{{ $ticket->ticket_number }}</flux:heading>
                            <flux:text class="text-sm text-slate-600">Nomor antrian aktif</flux:text>
                            <div class="flex justify-center">
                                <flux:badge size="sm" color="amber">{{ $ticket->counter?->name ?? 'Loket belum ditetapkan' }}</flux:badge>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @else
                <flux:text class="text-slate-500">Tidak ada panggilan aktif saat ini.</flux:text>
            @endif
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg" icon="clock">Riwayat Panggilan</flux:heading>

            @if ($recentCalls->isNotEmpty())
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nomor Antrian</flux:table.column>
                        <flux:table.column>Loket</flux:table.column>
                        <flux:table.column>Waktu Panggilan</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($recentCalls as $ticket)
                            <flux:table.row wire:key="recent-call-{{ $ticket->id }}">
                                <flux:table.cell>
                                    <flux:text class="font-semibold text-slate-900">{{ $ticket->ticket_number }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>{{ $ticket->counter?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $ticket->called_at?->format('H:i:s') ?? '-' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <flux:text class="text-slate-500">Belum ada riwayat panggilan.</flux:text>
            @endif
        </flux:card>
    </div>
</flux:main>
