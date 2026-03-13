<x-layouts::public :title="'Cek Status Antrian'">
    <flux:main container>
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-8 py-6 sm:gap-10 sm:py-8">
            {{-- Hero Section --}}
            <div class="space-y-3 text-center">
                <flux:badge color="cyan" rounded icon="magnifying-glass">Cek Status</flux:badge>
                <flux:heading size="xl" level="1" class="text-slate-900">Cek Status Antrian</flux:heading>
                <flux:subheading class="mx-auto max-w-2xl text-base leading-7 text-slate-600">
                    Masukkan nomor antrian dan tanggal layanan untuk melihat status tiket Anda.
                </flux:subheading>
            </div>

            {{-- Form Pencarian --}}
            <flux:card class="mx-auto w-full max-w-lg border-cyan-200 bg-white p-6 shadow-[0_24px_60px_-48px_rgba(8,145,178,0.45)] sm:p-8">
                <form method="GET" action="{{ url('/antrian/cek') }}" class="space-y-5">
                    <flux:field>
                        <flux:label>Nomor Antrian</flux:label>
                        <flux:input type="text" name="ticket_number" value="{{ request('ticket_number') }}" required placeholder="Contoh: A0001" icon="ticket" />
                        <flux:description>Nomor yang tertera pada tiket antrian Anda</flux:description>
                        <flux:error name="ticket_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Layanan</flux:label>
                        <flux:input type="date" name="service_date" value="{{ request('service_date') }}" required icon="calendar-days" />
                        <flux:error name="service_date" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" icon="magnifying-glass" class="w-full justify-center">
                        Cari Tiket
                    </flux:button>
                </form>
            </flux:card>

            {{-- Hasil Pencarian --}}
            @if (request()->filled('ticket_number') && request()->filled('service_date'))
                @if ($ticket)
                    <flux:card class="mx-auto w-full max-w-lg overflow-hidden border-cyan-100 bg-white p-0 shadow-[0_24px_60px_-48px_rgba(14,116,144,0.4)]">
                        {{-- Header Tiket --}}
                        <div class="bg-gradient-to-r from-cyan-600 to-cyan-700 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="ticket" class="text-white" />
                                    <flux:heading size="lg" class="text-white">{{ $ticket->ticket_number }}</flux:heading>
                                </div>
                                <flux:badge :color="$ticket->status->color()">
                                    {{ $ticket->status->label() }}
                                </flux:badge>
                            </div>
                        </div>

                        {{-- Detail Tiket --}}
                        <div class="space-y-5 p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nama</flux:text>
                                    <flux:text class="font-semibold text-slate-900">{{ $ticket->visitor_name }}</flux:text>
                                </div>
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Layanan</flux:text>
                                    <flux:text class="font-semibold text-slate-900">{{ $ticket->service->name ?? '-' }}</flux:text>
                                </div>
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Tanggal</flux:text>
                                    <flux:text class="font-semibold text-slate-900">{{ $ticket->service_date->format('d M Y') }}</flux:text>
                                </div>
                                @if ($ticket->counter)
                                    <div class="space-y-1">
                                        <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Loket</flux:text>
                                        <flux:text class="font-semibold text-slate-900">{{ $ticket->counter->name }}</flux:text>
                                    </div>
                                @endif
                            </div>

                            <flux:separator />

                            {{-- Panduan berdasarkan status --}}
                            @php
                                $statusConfig = match ($ticket->status) {
                                    \App\Enums\QueueStatus::Waiting => [
                                        'bg' => 'bg-amber-50 border-amber-200',
                                        'icon_color' => 'text-amber-600',
                                        'text_color' => 'text-amber-800',
                                        'icon' => 'clock',
                                        'message' => 'Posisi antrian Anda: ' . ($queuePosition ?? 0),
                                    ],
                                    \App\Enums\QueueStatus::Called => [
                                        'bg' => 'bg-purple-50 border-purple-200',
                                        'icon_color' => 'text-purple-600',
                                        'text_color' => 'text-purple-800',
                                        'icon' => 'speaker-wave',
                                        'message' => 'Silakan segera menuju ' . ($ticket->counter->name ?? 'loket yang ditunjuk'),
                                    ],
                                    \App\Enums\QueueStatus::Completed => [
                                        'bg' => 'bg-emerald-50 border-emerald-200',
                                        'icon_color' => 'text-emerald-600',
                                        'text_color' => 'text-emerald-800',
                                        'icon' => 'check-circle',
                                        'message' => 'Layanan telah selesai',
                                    ],
                                    \App\Enums\QueueStatus::Booked => [
                                        'bg' => 'bg-blue-50 border-blue-200',
                                        'icon_color' => 'text-blue-600',
                                        'text_color' => 'text-blue-800',
                                        'icon' => 'calendar-days',
                                        'message' => 'Tiket terdaftar. Silakan datang dan lakukan check-in di loket',
                                    ],
                                    \App\Enums\QueueStatus::Cancelled => [
                                        'bg' => 'bg-red-50 border-red-200',
                                        'icon_color' => 'text-red-600',
                                        'text_color' => 'text-red-800',
                                        'icon' => 'x-circle',
                                        'message' => 'Tiket ini telah dibatalkan',
                                    ],
                                    \App\Enums\QueueStatus::Skipped => [
                                        'bg' => 'bg-orange-50 border-orange-200',
                                        'icon_color' => 'text-orange-600',
                                        'text_color' => 'text-orange-800',
                                        'icon' => 'arrow-uturn-right',
                                        'message' => 'Tiket ini telah dilewati. Silakan hubungi petugas untuk dipanggil ulang',
                                    ],
                                };
                            @endphp

                            <div class="flex items-start gap-3 rounded-2xl border {{ $statusConfig['bg'] }} p-4">
                                <flux:icon :name="$statusConfig['icon']" class="{{ $statusConfig['icon_color'] }} mt-0.5 shrink-0" />
                                <flux:text class="{{ $statusConfig['text_color'] }} font-medium">
                                    {{ $statusConfig['message'] }}
                                </flux:text>
                            </div>
                        </div>
                    </flux:card>
                @else
                    <flux:card class="mx-auto w-full max-w-lg border-dashed border-slate-300 bg-white p-8 text-center shadow-none">
                        <div class="flex flex-col items-center gap-4">
                            <div class="flex size-14 items-center justify-center rounded-3xl bg-red-100 text-red-600">
                                <flux:icon.magnifying-glass class="size-7" />
                            </div>
                            <div class="space-y-2">
                                <flux:heading size="lg" class="text-slate-900">Tiket Tidak Ditemukan</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600">
                                    Pastikan nomor antrian dan tanggal layanan sudah benar.
                                </flux:text>
                            </div>
                            <flux:button href="{{ url('/antrian/cek') }}" variant="subtle" icon="arrow-path">
                                Periksa Ulang
                            </flux:button>
                        </div>
                    </flux:card>
                @endif
            @else
                {{-- State awal sebelum pencarian --}}
                <div class="mx-auto max-w-lg text-center">
                    <flux:card class="border-dashed border-slate-200 bg-slate-50/50 p-8 shadow-none">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-600">
                                <flux:icon.ticket class="size-6" />
                            </div>
                            <flux:text class="text-sm leading-6 text-slate-500">
                                Masukkan nomor tiket dan tanggal layanan pada form di atas untuk melihat status antrian Anda.
                            </flux:text>
                        </div>
                    </flux:card>
                </div>
            @endif
        </div>
    </flux:main>
</x-layouts::public>
