<x-layouts::public :title="'Cek Status Antrian'">
    <flux:main container>
        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <flux:heading size="xl" level="1">Cek Status Antrian</flux:heading>
                <flux:subheading>Masukkan nomor antrian dan tanggal layanan untuk melihat status tiket Anda.</flux:subheading>
            </div>

            <flux:card>
                <form method="GET" action="{{ url('/antrian/cek') }}" class="space-y-6">
                    <flux:field>
                        <flux:label>Nomor Antrian</flux:label>
                        <flux:input type="text" name="ticket_number" value="{{ request('ticket_number') }}" required placeholder="Contoh: A-001" />
                        <flux:error name="ticket_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Layanan</flux:label>
                        <flux:input type="date" name="service_date" value="{{ request('service_date') }}" required />
                        <flux:error name="service_date" />
                    </flux:field>

                    <div class="flex justify-end mt-4">
                        <flux:button type="submit" variant="primary">Cari Tiket</flux:button>
                    </div>
                </form>
            </flux:card>

            @if (request()->filled('ticket_number') && request()->filled('service_date'))
                @if ($ticket)
                    <flux:card>
                        <flux:heading size="lg" class="mb-4">Detail Tiket: {{ $ticket->ticket_number }}</flux:heading>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:subheading>Nama</flux:subheading>
                                <flux:text class="font-medium">{{ $ticket->visitor_name }}</flux:text>
                            </div>
                            <div>
                                <flux:subheading>Layanan</flux:subheading>
                                <flux:text class="font-medium">{{ $ticket->service->name ?? '-' }}</flux:text>
                            </div>
                            <div>
                                <flux:subheading>Tanggal</flux:subheading>
                                <flux:text class="font-medium">{{ $ticket->service_date->format('d M Y') }}</flux:text>
                            </div>
                            <div>
                                <flux:subheading>Status</flux:subheading>
                                <flux:badge :color="$ticket->status->color()">{{ $ticket->status->label() }}</flux:badge>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            @if ($ticket->status === \App\Enums\QueueStatus::Waiting)
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                    <flux:text class="font-medium text-amber-800">Posisi antrian Anda: {{ $queuePosition ?? 0 }}</flux:text>
                                </div>
                            @elseif ($ticket->status === \App\Enums\QueueStatus::Called)
                                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <flux:text class="font-medium text-green-800">Silakan segera menuju {{ $ticket->counter->name ?? 'loket yang ditunjuk' }}</flux:text>
                                </div>
                            @elseif ($ticket->status === \App\Enums\QueueStatus::Completed)
                                <div class="p-4 bg-zinc-50 border border-zinc-200 rounded-lg">
                                    <flux:text class="font-medium text-zinc-800">Layanan telah selesai</flux:text>
                                </div>
                            @elseif ($ticket->status === \App\Enums\QueueStatus::Booked)
                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <flux:text class="font-medium text-blue-800">Tiket terdaftar. Silakan datang dan lakukan check-in di loket</flux:text>
                                </div>
                            @elseif ($ticket->status === \App\Enums\QueueStatus::Cancelled)
                                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <flux:text class="font-medium text-red-800">Tiket ini telah dibatalkan</flux:text>
                                </div>
                            @elseif ($ticket->status === \App\Enums\QueueStatus::Skipped)
                                <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg">
                                    <flux:text class="font-medium text-orange-800">Tiket ini telah dilewati</flux:text>
                                </div>
                            @endif
                        </div>
                    </flux:card>
                @else
                    <flux:card class="bg-red-50 border-red-200 text-center py-8">
                        <flux:heading size="lg" class="text-red-700">Tiket Tidak Ditemukan</flux:heading>
                        <flux:text class="mt-2 text-red-600">Pastikan nomor antrian dan tanggal layanan sudah benar.</flux:text>
                        <div class="mt-6">
                            <flux:button type="button" variant="secondary" onclick="window.location.href='{{ url('/antrian/cek') }}'">Periksa Ulang</flux:button>
                        </div>
                    </flux:card>
                @endif
            @endif
        </div>
    </flux:main>
</x-layouts::public>