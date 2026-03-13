@php
    $institutionName = config('institution.name', config('app.name', 'Laravel'));
@endphp

<x-layouts::public :title="'Konfirmasi Antrian - ' . $institutionName">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>

    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="space-y-2 text-center mb-8 no-print">
            <flux:badge color="emerald" rounded icon="check-circle">Berhasil</flux:badge>
            <flux:heading size="xl" level="1" class="text-slate-900">Konfirmasi Antrian</flux:heading>
            <flux:subheading>Terima kasih telah melakukan pendaftaran antrian</flux:subheading>
        </div>

        <!-- Ticket Card -->
        <div class="bg-white rounded-3xl shadow-lg border border-cyan-100 overflow-hidden mb-8">
            <!-- Ticket Header -->
            <div class="bg-gradient-to-r from-cyan-600 to-cyan-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <flux:icon name="ticket" class="text-white" size="xl" />
                        <flux:heading size="lg" class="text-white">Detail Tiket Antrian</flux:heading>
                    </div>
                    <flux:badge :color="$ticket->status->color()" class="text-sm">
                        {{ $ticket->status->label() }}
                    </flux:badge>
                </div>
            </div>

            <!-- Ticket Body -->
            <div class="p-6 space-y-6">
                <!-- Ticket Number -->
                <div class="text-center">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nomor Tiket</flux:text>
                    <p class="text-6xl font-bold text-slate-900">{{ $ticket->ticket_number }}</p>
                </div>

                <!-- Service Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Layanan</flux:text>
                        <flux:text class="font-semibold text-slate-900">{{ $ticket->service->name }}</flux:text>
                    </div>
                    <div class="space-y-2">
                        <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Tanggal</flux:text>
                        <flux:text class="font-semibold text-slate-900">{{ $ticket->service_date->format('d M Y') }}</flux:text>
                    </div>
                </div>

                <!-- Visitor Info -->
                <div class="space-y-2">
                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nama Pengunjung</flux:text>
                    <flux:text class="font-semibold text-slate-900">{{ $ticket->visitor_name }}</flux:text>
                </div>

                <!-- Queue Position -->
                @if($queuePosition > 0)
                    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon name="clock" class="text-orange-600" />
                            <flux:text class="text-orange-700 font-semibold text-sm">Posisi Antrian</flux:text>
                        </div>
                        <flux:text class="text-orange-900 text-lg font-bold">
                            Anda adalah antrian ke-{{ $queuePosition }} hari ini
                        </flux:text>
                    </div>
                @endif

                <!-- Instructions -->
                <div class="bg-cyan-50 border border-cyan-100 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon name="information-circle" class="text-cyan-600" />
                        <flux:text class="text-cyan-700 font-semibold text-sm">Panduan</flux:text>
                    </div>
                    <flux:text class="text-cyan-900 text-sm">
                        Silakan datang ke kantor pada jam operasional. Tunjukkan nomor tiket ini kepada petugas.
                    </flux:text>
                </div>
            </div>

            <!-- Ticket Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100">
                <div class="flex justify-between items-center">
                    <flux:text class="text-xs text-slate-500">
                        Dibuat pada {{ $ticket->created_at->format('d M Y H:i') }}
                    </flux:text>
                    <flux:text class="text-xs text-slate-500">
                        {{ $institutionName }}
                    </flux:text>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3 mb-8 no-print">
            <flux:button onclick="window.print()" icon="printer" class="w-full">
                Cetak Tiket
            </flux:button>
        </div>

        <!-- Links -->
        <div class="flex flex-col gap-2 text-center no-print">
            <flux:button href="{{ route('queue.cek') }}" variant="subtle" icon="magnifying-glass" class="justify-center">
                Cek Status Antrian
            </flux:button>
            <flux:button href="{{ route('home') }}" variant="ghost" icon="home" class="justify-center">
                Kembali ke Beranda
            </flux:button>
        </div>
    </div>
</x-layouts::public>
