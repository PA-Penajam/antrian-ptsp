<div wire:poll.5s class="min-h-screen bg-zinc-950 text-white p-6 space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-4xl font-bold">Monitor Antrian PTSP</h1>
        <span class="text-zinc-400 text-lg">{{ now()->format('H:i:s') }}</span>
    </div>

    {{-- Currently Called --}}
    <div class="space-y-4">
        <h2 class="text-2xl font-semibold text-amber-400">Sedang Dipanggil</h2>
        @if ($currentCalls->isEmpty())
            <div class="rounded-lg bg-zinc-900 p-8 text-center text-zinc-500 text-xl">
                Belum ada panggilan
            </div>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($currentCalls as $ticket)
                    <div wire:key="current-call-{{ $ticket->id }}" class="rounded-xl bg-amber-500 text-zinc-950 p-6 text-center">
                        <div class="text-5xl font-black">{{ $ticket->ticket_number }}</div>
                        <div class="text-xl mt-2">{{ $ticket->counter?->name ?? '-' }}</div>
                        <div class="text-sm mt-1 opacity-75">{{ $ticket->service?->name ?? '-' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recent Calls Table --}}
    <div class="space-y-3">
        <h2 class="text-xl font-semibold text-zinc-400">Riwayat Panggilan</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-zinc-500 border-b border-zinc-800">
                        <th class="text-left py-2">No. Antrian</th>
                        <th class="text-left py-2">Loket</th>
                        <th class="text-left py-2">Layanan</th>
                        <th class="text-left py-2">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentCalls as $ticket)
                        <tr wire:key="recent-call-{{ $ticket->id }}" class="border-b border-zinc-900 {{ $ticket->status === \App\Enums\QueueStatus::Called ? 'text-amber-400' : 'text-zinc-400' }}">
                            <td class="py-2 font-mono text-lg">{{ $ticket->ticket_number }}</td>
                            <td class="py-2">{{ $ticket->counter?->name ?? '-' }}</td>
                            <td class="py-2">{{ $ticket->service?->name ?? '-' }}</td>
                            <td class="py-2">{{ $ticket->called_at?->format('H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Hidden logout --}}
    <div class="fixed bottom-2 right-2 opacity-20 hover:opacity-100 transition-opacity">
        <form method="POST" action="{{ route('tv-display.logout') }}">
            @csrf
            <button type="submit" class="text-zinc-600 text-xs px-2 py-1 rounded border border-zinc-800 hover:text-white hover:border-zinc-600">Logout</button>
        </form>
    </div>
</div>
