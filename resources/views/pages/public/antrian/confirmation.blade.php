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
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Konfirmasi Antrian</h1>
            <p class="text-slate-600">Terima kasih telah melakukan pendaftaran antrian</p>
        </div>

        <!-- Ticket Card -->
        <div class="bg-white rounded-3xl shadow-lg border border-cyan-100 overflow-hidden mb-8">
            <!-- Ticket Header -->
            <div class="bg-gradient-to-r from-cyan-600 to-cyan-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <flux:icon name="ticket" class="text-white" size="xl" />
                        <h2 class="text-white font-semibold text-lg">Detail Tiket Antrian</h2>
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
                    <p class="text-slate-500 text-sm font-medium mb-1">Nomor Tiket</p>
                    <p class="text-6xl font-bold text-slate-900">{{ $ticket->ticket_number }}</p>
                </div>

                <!-- Service Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <p class="text-slate-500 text-sm font-medium">Layanan</p>
                        <p class="text-slate-900 font-semibold">{{ $ticket->service->name }}</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-slate-500 text-sm font-medium">Tanggal</p>
                        <p class="text-slate-900 font-semibold">{{ $ticket->service_date->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Visitor Info -->
                <div class="space-y-2">
                    <p class="text-slate-500 text-sm font-medium">Nama Pengunjung</p>
                    <p class="text-slate-900 font-semibold">{{ $ticket->visitor_name }}</p>
                </div>

                <!-- Queue Position -->
                @if($queuePosition > 0)
                    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon name="clock" class="text-orange-600" />
                            <p class="text-orange-700 font-semibold text-sm">Posisi Antrian</p>
                        </div>
                        <p class="text-orange-900 text-lg font-bold">
                            Anda adalah antrian ke-{{ $queuePosition }} hari ini
                        </p>
                    </div>
                @endif

                <!-- Instructions -->
                <div class="bg-cyan-50 border border-cyan-100 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon name="information-circle" class="text-cyan-600" />
                        <p class="text-cyan-700 font-semibold text-sm">Panduan</p>
                    </div>
                    <p class="text-cyan-900 text-sm">
                        Silakan datang ke kantor pada jam operasional. Tunjukkan nomor tiket ini kepada petugas.
                    </p>
                </div>
            </div>

            <!-- Ticket Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100">
                <div class="flex justify-between items-center">
                    <div class="text-xs text-slate-500">
                        Dibuat pada {{ $ticket->created_at->format('d M Y H:i') }}
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ $institutionName }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3 mb-8">
            <flux:button onclick="window.print()" icon="printer" class="w-full">
                Cetak Tiket
            </flux:button>
        </div>

        <!-- Links -->
        <div class="flex flex-col gap-2 text-center">
            <a href="{{ route('queue.cek') }}" class="text-cyan-600 hover:text-cyan-700 font-medium text-sm no-print">
                Cek Status Antrian
            </a>
            <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-700 font-medium text-sm no-print">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</x-layouts::public>