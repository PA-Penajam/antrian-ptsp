<div
    wire:poll.10s
    class="relative overflow-hidden rounded-3xl border border-cyan-100 bg-gradient-to-b from-white via-white to-cyan-50/40 p-5 sm:p-8 shadow-[0_20px_50px_-32px_rgba(14,116,144,0.25)] transition-all duration-300 print:border-slate-300 print:shadow-none"
>
    {{-- Ambient Decorative Glow --}}
    <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-cyan-400/10 blur-3xl print:hidden"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -left-16 -bottom-16 size-64 rounded-full bg-emerald-400/10 blur-3xl print:hidden"></div>

    {{-- Header Section with Live Pulse Beacon --}}
    <div class="relative flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-0.5 sm:space-y-1">
            <div class="flex items-center gap-2">
                <flux:heading level="2" size="xl" class="font-bold text-slate-900">Pantauan Antrian Hari Ini</flux:heading>
            </div>
            <flux:text class="text-xs text-slate-600 sm:text-sm">
                Ringkasan aktivitas tiket dan panggilan loket di ruang tunggu PTSP saat ini.
            </flux:text>
        </div>

        <div class="inline-flex items-center gap-2 self-start rounded-full border border-emerald-200/90 bg-emerald-50/90 px-3.5 py-1.5 text-xs font-bold tracking-wider text-emerald-800 uppercase shadow-xs sm:self-auto print:hidden">
            <span class="relative flex size-2.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75 motion-reduce:hidden"></span>
                <span class="relative inline-flex size-2.5 rounded-full bg-emerald-600"></span>
            </span>
            <span>Live Sinkronisasi</span>
            <span class="text-xs font-medium text-emerald-600 lowercase opacity-80">({{ $lastUpdated ?? now()->format('H:i') }})</span>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="relative mt-5 sm:mt-6 grid gap-3.5 sm:grid-cols-3">
        <div class="group flex items-center gap-4 rounded-2xl border border-cyan-200/80 bg-gradient-to-br from-cyan-50/90 via-cyan-50/40 to-white p-4 sm:p-5 shadow-xs transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cyan-300 hover:shadow-md hover:shadow-cyan-600/10 motion-reduce:transform-none">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-cyan-800 text-white shadow-md shadow-cyan-600/25 transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none sm:size-13">
                <flux:icon.ticket class="size-6 sm:size-6.5" />
            </div>
            <div>
                <p class="text-2xl font-black text-cyan-950 sm:text-3xl lg:text-4xl tracking-tight">{{ $todayStats['total'] }}</p>
                <p class="text-xs font-bold tracking-[0.14em] text-cyan-800 uppercase">Total Tiket Terdaftar</p>
            </div>
        </div>

        <div class="group flex items-center gap-4 rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/90 via-amber-50/40 to-white p-4 sm:p-5 shadow-xs transition-all duration-300 ease-out hover:-translate-y-1 hover:border-amber-300 hover:shadow-md hover:shadow-amber-600/10 motion-reduce:transform-none">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-md shadow-amber-600/25 transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none sm:size-13">
                <flux:icon.clock class="size-6 sm:size-6.5" />
            </div>
            <div>
                <p class="text-2xl font-black text-amber-950 sm:text-3xl lg:text-4xl tracking-tight">{{ $todayStats['waiting'] }}</p>
                <p class="text-xs font-bold tracking-[0.14em] text-amber-800 uppercase">Sedang Menunggu</p>
            </div>
        </div>

        <div class="group flex items-center gap-4 rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-emerald-50/90 via-emerald-50/40 to-white p-4 sm:p-5 shadow-xs transition-all duration-300 ease-out hover:-translate-y-1 hover:border-emerald-300 hover:shadow-md hover:shadow-emerald-600/10 motion-reduce:transform-none">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white shadow-md shadow-emerald-600/25 transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none sm:size-13">
                <flux:icon.check-circle class="size-6 sm:size-6.5" />
            </div>
            <div>
                <p class="text-2xl font-black text-emerald-950 sm:text-3xl lg:text-4xl tracking-tight">{{ $todayStats['completed'] }}</p>
                <p class="text-xs font-bold tracking-[0.14em] text-emerald-800 uppercase">Selesai Dilayani</p>
            </div>
        </div>
    </div>

    {{-- Active Calling Display Cards --}}
    @if ($activeCallingTickets->isNotEmpty())
        <div class="relative mt-6 border-t border-cyan-100/90 pt-5 sm:pt-6">
            <div class="mb-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:icon.megaphone class="size-4 text-emerald-700" />
                    <p class="text-xs font-bold tracking-[0.16em] text-slate-700 uppercase">Sedang Dipanggil di Loket PTSP</p>
                </div>
                <span class="text-xs font-medium text-slate-500 hidden sm:inline">Panggilan Loket Aktif</span>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($activeCallingTickets as $callingTicket)
                    <div class="group relative flex items-center justify-between overflow-hidden rounded-2xl border-2 border-emerald-300/90 bg-gradient-to-br from-emerald-50/90 via-emerald-50/40 to-white p-3.5 sm:p-4 shadow-xs transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-emerald-400 hover:shadow-md hover:shadow-emerald-700/15 motion-reduce:transform-none">
                        <div class="min-w-0 pr-2">
                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-700 px-2.5 py-1 text-xs font-bold text-white uppercase shadow-xs">
                                <span class="inline-flex size-1.5 rounded-full bg-emerald-300 animate-pulse motion-reduce:hidden"></span>
                                {{ $callingTicket->counter?->name ?? 'Loket' }}
                            </div>
                            <p class="mt-1.5 truncate text-xs font-medium text-slate-700" title="{{ $callingTicket->service?->name }}">
                                {{ $callingTicket->service?->name }}
                            </p>
                        </div>
                        <div class="shrink-0 font-black text-2xl sm:text-3xl tracking-wider text-emerald-900 px-2 py-0.5">
                            {{ $callingTicket->ticket_number }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="relative mt-5 sm:mt-6 flex flex-col gap-2 rounded-2xl border border-cyan-100 bg-cyan-50/70 p-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5 text-xs text-cyan-900 shadow-xs">
            <div class="flex items-center gap-2.5">
                <flux:icon.sparkles class="size-4 text-cyan-700 shrink-0" />
                <span>Semua loket siap melayani antrian Anda dengan tertib dan transparan.</span>
            </div>
            <a href="{{ url('/antrian') }}" class="font-semibold text-cyan-800 hover:underline hover:text-cyan-950 transition print:hidden">
                Ambil nomor sekarang &rarr;
            </a>
        </div>
    @endif

    {{-- Quick Ticket Lookup Box (Delightful Mini-Widget) --}}
    <div class="relative mt-6 border-t border-cyan-100/90 pt-5 sm:pt-6 print:hidden">
        <div class="mb-2.5 flex items-center justify-between">
            <p class="text-xs font-bold tracking-[0.14em] text-slate-700 uppercase">Cek Posisi Antrian Cepat</p>
            <span class="text-xs text-slate-500">Ketik nomor tiket tanpa spasi</span>
        </div>

        <form wire:submit="searchTicket" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <flux:input
                    wire:model="quickTicketNumber"
                    placeholder="Contoh: A001 atau UMUM-0001..."
                    icon="magnifying-glass"
                    class="rounded-2xl border-cyan-200 focus:border-cyan-500 text-sm h-12 shadow-xs"
                />
            </div>
            <div class="flex items-center gap-2">
                <flux:button
                    type="submit"
                    variant="primary"
                    class="h-12 rounded-2xl bg-gradient-to-r from-cyan-700 to-teal-700 px-6 font-bold text-white shadow-xs hover:brightness-105 active:scale-[0.99] touch-manipulation w-full sm:w-auto transition-all duration-200"
                >
                    <span wire:loading.remove wire:target="searchTicket">Cek Posisi</span>
                    <span wire:loading wire:target="searchTicket" class="flex items-center gap-1.5">
                        <flux:icon.arrow-path class="size-4 animate-spin" />
                        <span>Mengecek...</span>
                    </span>
                </flux:button>

                @if ($quickResult || $lookupMessage)
                    <flux:button
                        type="button"
                        wire:click="clearLookup"
                        variant="subtle"
                        class="h-12 rounded-2xl border border-slate-200 bg-white px-3.5 font-semibold text-slate-600 hover:bg-slate-50 transition"
                    >
                        Tutup
                    </flux:button>
                @endif
            </div>
        </form>

        {{-- Offline Indicator --}}
        <div wire:offline class="mt-3 flex items-center gap-2 rounded-2xl border border-amber-300 bg-amber-100 px-4 py-2.5 text-xs font-semibold tracking-wide text-amber-900 shadow-xs">
            <flux:icon.signal-slash class="size-4 text-amber-700 shrink-0" />
            <span>Koneksi terputus. Periksa internet Anda — data akan dimuat ulang otomatis saat tersambung.</span>
        </div>

        {{-- Lookup Error / Notice Banner --}}
        @if ($lookupMessage)
            <div class="mt-3.5 flex items-start gap-2.5 rounded-2xl border border-amber-200 bg-amber-50/95 p-3.5 text-xs leading-relaxed font-medium text-amber-900 shadow-xs">
                <flux:icon.information-circle class="size-4 text-amber-700 shrink-0 mt-0.5" />
                <span class="flex-1 break-words">{{ $lookupMessage }}</span>
                @if (str_contains($lookupMessage, 'Gagal mencari') || str_contains($lookupMessage, 'Gagal memuat'))
                    <flux:button wire:click="searchTicket" variant="subtle" size="xs" class="shrink-0 rounded-xl border border-amber-300 bg-white font-bold tracking-wide text-amber-800 hover:bg-amber-50">
                        Coba Lagi
                    </flux:button>
                @endif
            </div>
        @endif

        {{-- Lookup Result Card with Humane Context --}}
        @if ($quickResult)
            <div class="mt-4 rounded-3xl border-2 border-cyan-200 bg-gradient-to-br from-white via-cyan-50/50 to-teal-50/30 p-5 shadow-md shadow-cyan-900/5 transition-all duration-300 ease-out animate-in fade-in slide-in-from-top-2">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="font-black text-2xl text-cyan-950 tracking-wider">{{ $quickResult['ticket_number'] }}</span>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase
                                @if($quickResult['status'] === 'waiting') bg-amber-100 text-amber-800 border border-amber-200
                                @elseif($quickResult['status'] === 'called') bg-emerald-100 text-emerald-800 border border-emerald-200
                                @elseif($quickResult['status'] === 'completed') bg-blue-100 text-blue-800 border border-blue-200
                                @else bg-slate-100 text-slate-700 border border-slate-200 @endif
                            ">
                                {{ $quickResult['status_label'] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600">
                            Pemohon: <span class="font-semibold text-slate-800">{{ $quickResult['visitor_name'] }}</span> &bull; <span class="text-cyan-900 font-medium">{{ $quickResult['service_name'] }}</span>
                        </p>
                        @if (!empty($quickResult['guidance_message']))
                            <p class="text-xs font-semibold text-cyan-800 bg-cyan-50/80 rounded-xl px-3 py-1.5 border border-cyan-100/80 inline-block">
                                {{ $quickResult['guidance_message'] }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 sm:flex-col sm:items-end">
                        @if ($quickResult['status'] === 'waiting')
                            <div class="text-left sm:text-right">
                                <p class="text-xs font-bold tracking-wider text-amber-800 uppercase">Sisa Antrian Di Depan</p>
                                <p class="text-2xl font-black text-amber-950">
                                    {{ max(0, $quickResult['queue_position'] - 1) }} <span class="text-sm font-semibold text-amber-800">Orang</span>
                                </p>
                            </div>
                        @elseif ($quickResult['status'] === 'called')
                            <div class="text-left sm:text-right">
                                <p class="text-xs font-bold tracking-wider text-emerald-800 uppercase">Sedang Dipanggil di</p>
                                <p class="text-xl font-black text-emerald-950">{{ $quickResult['counter_name'] ?? 'Loket PTSP' }}</p>
                            </div>
                        @endif

                        <a
                            href="{{ url('/antrian/cek?ticket_number=' . urlencode($quickResult['ticket_number']) . '&service_date=' . now()->toDateString()) }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-cyan-700 to-teal-700 px-4 py-2 text-xs font-bold text-white shadow-xs hover:brightness-105 active:scale-[0.98] transition touch-manipulation"
                        >
                            <span>Buka Detail Tiket</span>
                            <flux:icon.arrow-top-right-on-square class="size-3.5" />
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
