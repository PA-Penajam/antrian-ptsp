<x-layouts::public :title="__('Display Antrian PTSP')">
    <flux:main container>
        <div class="max-w-5xl mx-auto space-y-8">
            <div class="text-center">
                <flux:heading size="xl" level="1">Display Antrian PTSP</flux:heading>
                <flux:subheading>Informasi panggilan antrian secara real-time.</flux:subheading>
            </div>

            {{-- Sedang Dipanggil --}}
            <flux:card>
                <flux:heading size="lg" icon="megaphone">Sedang Dipanggil</flux:heading>

                @if ($currentCalls->isNotEmpty())
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($currentCalls as $ticket)
                            <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-700 text-center">
                                <flux:heading size="xl" class="text-amber-700 dark:text-amber-300">{{ $ticket->ticket_number }}</flux:heading>
                                <flux:text class="mt-1">
                                    <flux:badge size="sm" color="amber">{{ $ticket->counter?->name ?? 'Loket belum ditetapkan' }}</flux:badge>
                                </flux:text>
                            </flux:card>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Tidak ada panggilan aktif saat ini.</flux:text>
                    </div>
                @endif
            </flux:card>

            {{-- Riwayat Panggilan --}}
            <flux:card>
                <flux:heading size="lg" icon="clock">Riwayat Panggilan</flux:heading>

                @if ($recentCalls->isNotEmpty())
                    <div class="mt-4">
                        <flux:table>
                            <flux:table.columns>
                                    <flux:table.column>Nomor Antrian</flux:table.column>
                                    <flux:table.column>Loket</flux:table.column>
                                    <flux:table.column>Waktu Panggilan</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($recentCalls as $ticket)
                                    <flux:table.row>
                                        <flux:table.cell>
                                            <flux:text class="font-semibold">{{ $ticket->ticket_number }}</flux:text>
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $ticket->counter?->name ?? '-' }}</flux:table.cell>
                                        <flux:table.cell>{{ $ticket->called_at?->format('H:i:s') ?? '-' }}</flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @else
                    <div class="mt-4">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Belum ada riwayat panggilan.</flux:text>
                    </div>
                @endif
            </flux:card>
        </div>
    </flux:main>
</x-layouts::public>
