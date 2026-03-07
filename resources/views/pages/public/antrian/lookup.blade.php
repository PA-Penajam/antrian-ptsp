<x-layouts::public :title="__('Cek Status Antrian')">
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
                    <flux:card class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
                        <flux:heading size="lg" class="text-blue-700 dark:text-blue-300 mb-4">Detail Tiket: {{ $ticket->ticket_number }}</flux:heading>
                        
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
                                <flux:badge color="{{ match($ticket->status) {
                                    'waiting' => 'zinc',
                                    'called' => 'amber',
                                    'completed' => 'green',
                                    'cancelled' => 'red',
                                    default => 'zinc',
                                } }}">{{ ucfirst($ticket->status) }}</flux:badge>
                            </div>
                            @if ($ticket->counter)
                            <div>
                                <flux:subheading>Loket</flux:subheading>
                                <flux:text class="font-medium">{{ $ticket->counter->name }}</flux:text>
                            </div>
                            @endif
                        </div>
                    </flux:card>
                @else
                    <flux:card class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-center py-8">
                        <flux:heading size="lg" class="text-red-700 dark:text-red-300">Tiket Tidak Ditemukan</flux:heading>
                        <flux:text class="mt-2 text-red-600 dark:text-red-400">Pastikan nomor antrian dan tanggal layanan sudah benar.</flux:text>
                    </flux:card>
                @endif
            @endif
        </div>
    </flux:main>
</x-layouts::public>
