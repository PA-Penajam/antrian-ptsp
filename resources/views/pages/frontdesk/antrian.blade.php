<x-layouts::app :title="__('Frontdesk Antrian')">
    <flux:main container>
        <div class="max-w-3xl mx-auto space-y-6">
            <div>
                <flux:heading size="xl" level="1">Frontdesk Antrian</flux:heading>
                <flux:subheading>Buat tiket antrian baru atau lakukan check-in tiket yang sudah ada.</flux:subheading>
            </div>

            @if ($ticket)
                <flux:card class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                    <flux:heading size="lg" class="text-green-700 dark:text-green-300">Tiket Berhasil Dibuat</flux:heading>
                    <div class="mt-4 space-y-2">
                        <flux:text><strong>Nomor Antrian:</strong> {{ $ticket->ticket_number }}</flux:text>
                        <flux:text><strong>Status:</strong>
                            <flux:badge size="sm" color="lime">{{ $ticket->status }}</flux:badge>
                        </flux:text>
                    </div>
                </flux:card>
            @endif

            @if ($checkedInTicket)
                <flux:card class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
                    <flux:heading size="lg" class="text-blue-700 dark:text-blue-300">Check-in Berhasil</flux:heading>
                    <div class="mt-4 space-y-2">
                        <flux:text><strong>Nomor Antrian:</strong> {{ $checkedInTicket->ticket_number }}</flux:text>
                        <flux:text><strong>Status:</strong>
                            <flux:badge size="sm" color="sky">{{ $checkedInTicket->status }}</flux:badge>
                        </flux:text>
                    </div>
                </flux:card>
            @endif

            {{-- Form Buat Tiket Baru --}}
            <flux:card>
                <flux:heading size="lg">Buat Tiket Antrian Baru</flux:heading>
                <form method="POST" action="{{ url('/frontdesk/antrian') }}" class="mt-4 space-y-6">
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
                        <flux:label>Kanal</flux:label>
                        <flux:select name="channel" required>
                            <flux:select.option value="" disabled selected>Pilih Kanal</flux:select.option>
                            <flux:select.option value="assisted_same_day">Bantuan Hari Ini</flux:select.option>
                            <flux:select.option value="walk_in_kiosk">Walk-in / Kiosk</flux:select.option>
                        </flux:select>
                        <flux:error name="channel" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Layanan</flux:label>
                        <flux:input type="date" name="service_date" required value="{{ now()->toDateString() }}" />
                        <flux:error name="service_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nama Pengunjung</flux:label>
                        <flux:input type="text" name="visitor_name" required placeholder="Masukkan nama lengkap" />
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
                        <flux:label>Catatan</flux:label>
                        <flux:textarea name="notes" placeholder="Opsional, tuliskan catatan singkat" />
                        <flux:error name="notes" />
                    </flux:field>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" icon="plus">Buat Tiket</flux:button>
                    </div>
                </form>
            </flux:card>

            {{-- Form Check-in --}}
            <flux:card>
                <flux:heading size="lg">Check-in Tiket</flux:heading>
                <form method="POST" action="{{ url('/frontdesk/antrian/check-in') }}" class="mt-4 space-y-6">
                    @csrf

                    <flux:field>
                        <flux:label>ID Tiket</flux:label>
                        <flux:input type="text" name="ticket_id" required placeholder="Masukkan ID tiket" />
                        <flux:error name="ticket_id" />
                    </flux:field>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="filled" icon="check">Check-in</flux:button>
                    </div>
                </form>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>
