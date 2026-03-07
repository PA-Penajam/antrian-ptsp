<x-layouts::public :title="__('Ambil Antrian PTSP')">
    <flux:main container>
        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <flux:heading size="xl" level="1">Ambil Antrian PTSP</flux:heading>
                <flux:subheading>Silakan isi form di bawah ini untuk mengambil nomor antrian Anda.</flux:subheading>
            </div>

            @if ($ticket)
                <flux:card class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                    <flux:heading size="lg" class="text-green-700 dark:text-green-300">Booking Berhasil</flux:heading>
                    <div class="mt-4 space-y-2">
                        <flux:text><strong>Nomor Antrian:</strong> {{ $ticket->ticket_number }}</flux:text>
                        <flux:text><strong>Status:</strong> {{ $ticket->status }}</flux:text>
                        <flux:text><strong>Tanggal:</strong> {{ $ticket->service_date->format('d M Y') }}</flux:text>
                    </div>
                    <div class="mt-4">
                        <flux:button href="{{ url('/antrian/cek?ticket_number=' . $ticket->ticket_number . '&service_date=' . $ticket->service_date->toDateString()) }}" variant="primary">Cek Status Antrian</flux:button>
                    </div>
                </flux:card>
            @endif

            <flux:card>
                <form method="POST" action="{{ url('/antrian') }}" class="space-y-6">
                    @csrf
                    
                    <flux:field>
                        <flux:label>Layanan</flux:label>
                        <flux:select name="service_id" required>
                            <flux:select.option value="" disabled selected>Pilih Layanan</flux:select.option>
                            @foreach ($services as $service)
                                <flux:select.option value="{{ $service->id }}">{{ $service->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="service_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Layanan</flux:label>
                        <flux:input type="date" name="service_date" required min="{{ now()->toDateString() }}" />
                        <flux:error name="service_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nama Lengkap</flux:label>
                        <flux:input type="text" name="visitor_name" required placeholder="Masukkan nama lengkap Anda" />
                        <flux:error name="visitor_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nomor Identitas (NIK/No. Paspor)</flux:label>
                        <flux:input type="text" name="visitor_identifier" placeholder="Opsional" />
                        <flux:error name="visitor_identifier" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nomor Telepon / WhatsApp</flux:label>
                        <flux:input type="text" name="visitor_phone" placeholder="Opsional" />
                        <flux:error name="visitor_phone" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Catatan Tambahan</flux:label>
                        <flux:textarea name="notes" placeholder="Opsional, tuliskan keperluan Anda secara singkat" />
                        <flux:error name="notes" />
                    </flux:field>

                    <div class="flex justify-end mt-4">
                        <flux:button type="submit" variant="primary">Ambil Antrian</flux:button>
                    </div>
                </form>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::public>
