@php
    $institutionName = config('institution.name', config('app.name', 'Laravel'));
@endphp

<x-layouts::public :title="'Konfirmasi Antrian - ' . $institutionName">
    <style>
        @media print {
            /* Hilangkan header/footer bawaan browser */
            @page {
                margin: 0;
            }
            /* Sembunyikan semua elemen layout kecuali tiket */
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            flux-header, header, footer, .no-print {
                display: none !important;
            }
            /* Sembunyikan elemen dekoratif background */
            div[aria-hidden="true"] {
                display: none !important;
            }
            /* Reset container agar tiket full-width */
            main, main > div {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            /* Tiket card: hilangkan shadow, border minimal */
            #printable-ticket {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 0 !important;
                margin: 10mm !important;
            }
        }
    </style>

    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="space-y-2 text-center mb-8 no-print">
            <flux:heading size="xl" level="1" class="text-slate-900 font-bold">Konfirmasi Antrian</flux:heading>
            <flux:subheading class="text-slate-600">Pendaftaran antrian Anda telah berhasil dikonfirmasi</flux:subheading>
        </div>

        <!-- Ticket Card -->
        <div id="printable-ticket" class="bg-white rounded-3xl shadow-[0_20px_60px_-25px_rgba(14,116,144,0.35)] border border-cyan-200 overflow-hidden mb-8 print:shadow-none print:border-slate-300">
            <!-- Ticket Header -->
            <div class="bg-gradient-to-r from-cyan-700 to-teal-800 px-6 py-4.5 text-white">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2.5">
                        <flux:icon name="ticket" class="text-cyan-200 size-6" />
                        <span class="font-bold text-lg text-white">Detail Tiket Antrian</span>
                    </div>
                    <flux:badge :color="$ticket->status->color()" class="text-xs font-semibold uppercase tracking-wider">
                        {{ $ticket->status->label() }}
                    </flux:badge>
                </div>
            </div>

            <!-- Ticket Body -->
            <div class="p-6 sm:p-8 space-y-6">
                <!-- Ticket Number -->
                <div class="text-center py-2">
                    <flux:text class="text-xs font-bold tracking-[0.18em] text-slate-500 uppercase">Nomor Tiket Antrian</flux:text>
                    <p class="text-6xl font-black text-slate-900 tracking-tight mt-1">{{ $ticket->ticket_number }}</p>
                </div>

                <!-- Perforation / Divider line -->
                <div class="relative flex items-center justify-center">
                    <div class="w-full border-t border-dashed border-slate-200"></div>
                </div>

                <!-- Service Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <flux:text class="text-xs font-bold tracking-[0.14em] text-slate-500 uppercase">Layanan</flux:text>
                        <p class="font-bold text-slate-900 text-sm sm:text-base">{{ $ticket->service->name }}</p>
                    </div>
                    <div class="space-y-1">
                        <flux:text class="text-xs font-bold tracking-[0.14em] text-slate-500 uppercase">Tanggal Layanan</flux:text>
                        <p class="font-bold text-slate-900 text-sm sm:text-base">{{ $ticket->service_date->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Visitor Info -->
                <div class="space-y-1">
                    <flux:text class="text-xs font-bold tracking-[0.14em] text-slate-500 uppercase">Nama Pemohon</flux:text>
                    <p class="font-bold text-slate-900 text-sm sm:text-base">{{ $ticket->visitor_name }}</p>
                </div>

                <!-- Queue Position -->
                @if($queuePosition > 0)
                    <div class="bg-amber-50/90 border border-amber-200 rounded-2xl p-4.5">
                        <div class="flex items-center gap-2 mb-1.5">
                            <flux:icon name="clock" class="size-4 text-amber-700" />
                            <span class="text-amber-800 font-bold text-xs uppercase tracking-wider">Posisi Antrian</span>
                        </div>
                        <p class="text-amber-950 text-base sm:text-lg font-bold">
                            Anda adalah antrian ke-{{ $queuePosition }} hari ini
                        </p>
                    </div>
                @endif

                <!-- Instructions -->
                <div class="bg-cyan-50/70 border border-cyan-100 rounded-2xl p-4.5">
                    <div class="flex items-center gap-2 mb-1.5">
                        <flux:icon name="information-circle" class="size-4 text-cyan-700" />
                        <span class="text-cyan-800 font-bold text-xs uppercase tracking-wider">Panduan Layanan</span>
                    </div>
                    <p class="text-slate-700 text-xs sm:text-sm leading-relaxed">
                        Silakan datang ke ruang tunggu PTSP pada jam operasional. Tunjukkan tiket atau nomor antrian ini kepada petugas saat nomor Anda dipanggil.
                    </p>
                </div>
            </div>

            <!-- Ticket Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100">
                <div class="flex justify-between items-center text-xs text-slate-500 font-medium">
                    <span>Dibuat: {{ $ticket->created_at->format('d M Y H:i') }}</span>
                    <span>{{ $institutionName }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3 mb-6 no-print">
            <flux:button
                onclick="window.print()"
                variant="primary"
                icon="printer"
                class="h-12 w-full justify-center rounded-2xl bg-gradient-to-r from-cyan-700 to-teal-700 font-bold text-white shadow-xs hover:brightness-105"
            >
                Cetak Tiket Antrian
            </flux:button>
        </div>

        <!-- Links -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center text-center no-print">
            <flux:button href="{{ route('queue.cek') }}" variant="subtle" icon="magnifying-glass" class="justify-center rounded-2xl">
                Cek Status Antrian
            </flux:button>
            <flux:button href="{{ route('home') }}" variant="ghost" icon="home" class="justify-center rounded-2xl">
                Kembali ke Beranda
            </flux:button>
        </div>
    </div>
</x-layouts::public>
