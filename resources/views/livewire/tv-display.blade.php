<div wire:poll.5s class="min-h-screen bg-zinc-950 text-white p-6 space-y-8 relative">
    {{-- Connection Status Indicator --}}
    <div x-data="{ connected: true }"
         x-on:livewire:connecting.window="connected = false"
         x-on:livewire:connected.window="connected = true"
         x-show="!connected"
         x-transition
         class="fixed top-2 left-1/2 -translate-x-1/2 bg-red-900 text-red-200 text-sm px-4 py-2 rounded-full z-50 shadow-lg">
        ⚠ Koneksi terputus, menghubungkan ulang...
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-4xl font-bold">Monitor Antrian PTSP</h1>
        <span x-data="{ time: '' }"
              x-init="setInterval(() => { time = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'}) }, 1000); time = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'})"
              x-text="time"
              class="text-zinc-400 text-lg font-mono"></span>
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
        @if ($recentCalls->isEmpty())
            <div class="rounded-lg bg-zinc-900 p-6 text-center text-zinc-500">
                Belum ada riwayat hari ini
            </div>
        @else
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
        @endif
    </div>

    {{-- Hidden logout --}}
    <div class="fixed bottom-2 right-2 opacity-20 hover:opacity-100 transition-opacity">
        <form method="POST" action="{{ route('tv-display.logout') }}">
            @csrf
            <button type="submit" class="text-zinc-600 text-xs px-2 py-1 rounded border border-zinc-800 hover:text-white hover:border-zinc-600">Logout</button>
        </form>
    </div>
</div>
