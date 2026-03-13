<x-layouts::app :title="__('Frontdesk Antrian')">
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="space-y-3">
                <flux:badge color="cyan" rounded>Frontdesk</flux:badge>
                <div>
                    <flux:heading size="xl" level="1">Frontdesk Antrian</flux:heading>
                    <flux:subheading class="mt-1">Buat tiket antrian baru atau lakukan check-in tiket yang sudah ada.</flux:subheading>
                </div>
            </div>

            @if (session('status'))
                <flux:callout icon="check-circle" color="green">
                    {{ session('status') }}
                </flux:callout>
            @endif

            @if ($ticket)
                <flux:card class="admin-stat-success admin-card-elevated p-5">
                    <div class="flex items-start gap-4">
                        <div class="admin-icon-box bg-emerald-200 text-emerald-700 dark:bg-emerald-800 dark:text-emerald-300">
                            <flux:icon.check-circle class="size-6" />
                        </div>
                        <div class="space-y-1">
                            <flux:heading size="lg" class="text-emerald-800 dark:text-emerald-200">Tiket Berhasil Dibuat</flux:heading>
                            <flux:text class="text-emerald-700 dark:text-emerald-300"><strong>Nomor Antrian:</strong> {{ $ticket->ticket_number }}</flux:text>
                            <flux:text class="text-emerald-700 dark:text-emerald-300"><strong>Status:</strong>
                                <flux:badge size="sm" color="{{ $ticket->status->color() }}">{{ $ticket->status->label() }}</flux:badge>
                            </flux:text>
                        </div>
                    </div>
                </flux:card>
            @endif

            @if ($checkedInTicket)
                <flux:card class="admin-stat-info admin-card-elevated p-5">
                    <div class="flex items-start gap-4">
                        <div class="admin-icon-box bg-violet-200 text-violet-700 dark:bg-violet-800 dark:text-violet-300">
                            <flux:icon.check-circle class="size-6" />
                        </div>
                        <div class="space-y-1">
                            <flux:heading size="lg" class="text-violet-800 dark:text-violet-200">Check-in Berhasil</flux:heading>
                            <flux:text class="text-violet-700 dark:text-violet-300"><strong>Nomor Antrian:</strong> {{ $checkedInTicket->ticket_number }}</flux:text>
                            <flux:text class="text-violet-700 dark:text-violet-300"><strong>Status:</strong>
                                <flux:badge size="sm" color="{{ $checkedInTicket->status->color() }}">{{ $checkedInTicket->status->label() }}</flux:badge>
                            </flux:text>
                        </div>
                    </div>
                </flux:card>
            @endif

            {{-- Form Buat Tiket Baru --}}
            <flux:card class="admin-card-elevated">
                <div class="flex items-center gap-3 mb-4">
                    <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
                        <flux:icon.plus-circle class="size-5" />
                    </div>
                    <flux:heading size="lg">Buat Tiket Antrian Baru</flux:heading>
                </div>
                <form method="POST" action="{{ route('frontdesk.queue.store') }}" class="mt-4 space-y-6">
                    @csrf

                    <flux:field>
                        <flux:label>Layanan</flux:label>
                        <flux:select name="service_id" required>
                            <flux:select.option value="" :selected="old('service_id') === null">Pilih Layanan</flux:select.option>
                            @foreach ($services as $service)
                                <flux:select.option value="{{ $service->id }}" :selected="(string) old('service_id') === (string) $service->id">{{ $service->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="service_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kanal</flux:label>
                        <flux:select name="channel" required>
                            <flux:select.option value="" :selected="old('channel') === null">Pilih Kanal</flux:select.option>
                            <flux:select.option value="assisted_same_day" :selected="old('channel') === 'assisted_same_day'">Bantuan Hari Ini</flux:select.option>
                            <flux:select.option value="walk_in_kiosk" :selected="old('channel') === 'walk_in_kiosk'">Walk-in / Kiosk</flux:select.option>
                        </flux:select>
                        <flux:error name="channel" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Layanan</flux:label>
                        <flux:input type="date" name="service_date" required value="{{ old('service_date', now()->toDateString()) }}" />
                        <flux:error name="service_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nama Pengunjung</flux:label>
                        <flux:input type="text" name="visitor_name" required placeholder="Masukkan nama lengkap" value="{{ old('visitor_name') }}" />
                        <flux:error name="visitor_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nomor Identitas (NIK/No. Paspor)</flux:label>
                        <flux:input type="text" name="visitor_identifier" placeholder="Opsional" value="{{ old('visitor_identifier') }}" />
                        <flux:error name="visitor_identifier" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nomor Telepon / WhatsApp</flux:label>
                        <flux:input type="text" name="visitor_phone" placeholder="Opsional" value="{{ old('visitor_phone') }}" />
                        <flux:error name="visitor_phone" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Catatan</flux:label>
                        <flux:textarea name="notes" placeholder="Opsional, tuliskan catatan singkat">{{ old('notes') }}</flux:textarea>
                        <flux:error name="notes" />
                    </flux:field>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" icon="plus">Buat Tiket</flux:button>
                    </div>
                </form>
            </flux:card>

            {{-- Form Check-in --}}
            <flux:card class="admin-card-elevated">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                            <flux:icon.check-circle class="size-5" />
                        </div>
                        <flux:heading size="lg">Check-in Tiket</flux:heading>
                    </div>
                    <flux:button
                        type="button"
                        variant="ghost"
                        icon="qr-code"
                        x-data
                        x-on:click="window.frontdeskScanner?.open()"
                    >
                        Scan Barcode / QR
                    </flux:button>
                </div>
                <form id="check-in-form" method="POST" action="{{ route('frontdesk.queue.check-in') }}" class="mt-4 space-y-6">
                    @csrf

                    <flux:field>
                        <flux:label>Nomor Antrian</flux:label>
                        <flux:input id="ticket-number-input" type="text" name="ticket_number" required placeholder="Contoh: A-001" value="{{ old('ticket_number') }}" />
                        <flux:error name="ticket_number" />
                    </flux:field>

                    <flux:text class="text-zinc-500">
                        Tips: scanner barcode USB bisa langsung dipakai. Saat kode terbaca, form akan submit otomatis.
                    </flux:text>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="filled" icon="check">Check-in</flux:button>
                    </div>
                </form>
            </flux:card>

            <flux:modal name="frontdesk-scan-ticket" class="w-full max-w-xl">
                <div class="space-y-4">
                    <flux:heading size="lg">Scan Barcode / QR Tiket</flux:heading>
                    <flux:text id="scan-status-text" class="text-zinc-500">
                        Arahkan kamera ke barcode/QR tiket. Nomor antrian akan terisi otomatis.
                    </flux:text>

                    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-950 dark:border-zinc-700">
                        <video id="scan-video" autoplay playsinline muted class="h-72 w-full object-cover"></video>
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:button
                            type="button"
                            variant="ghost"
                            x-data
                            x-on:click="window.frontdeskScanner?.stop(); $dispatch('close-modal', 'frontdesk-scan-ticket')"
                        >
                            Tutup
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        </div>

    <script>
        (() => {
            const state = window.frontdeskScannerState ??= {
                stream: null,
                detector: null,
                rafId: null,
                keyboardBuffer: '',
                lastKeyAt: 0,
            };

            const modalName = 'frontdesk-scan-ticket';

            const dispatchModalEvent = (eventName) => {
                const modalEvent = new CustomEvent(eventName, { detail: modalName });

                document.dispatchEvent(modalEvent);
                window.dispatchEvent(modalEvent);
            };

            const checkInForm = document.getElementById('check-in-form');
            const ticketNumberInput = document.getElementById('ticket-number-input');
            const videoElement = document.getElementById('scan-video');
            const scanStatusText = document.getElementById('scan-status-text');

            if (!checkInForm || !ticketNumberInput) {
                return;
            }

            const updateStatus = (message) => {
                if (!scanStatusText) {
                    return;
                }

                scanStatusText.textContent = message;
            };

            const isEditableElement = (target) => {
                if (!(target instanceof HTMLElement)) {
                    return false;
                }

                if (target.isContentEditable) {
                    return true;
                }

                return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName);
            };

            const extractTicketNumber = (rawValue) => {
                if (!rawValue) {
                    return null;
                }

                const normalized = String(rawValue).trim();
                if (normalized === '') {
                    return null;
                }

                try {
                    const parsed = JSON.parse(normalized);
                    const jsonTicket = parsed?.ticket_number;

                    if (typeof jsonTicket === 'string' && jsonTicket.trim() !== '') {
                        return jsonTicket.trim().toUpperCase();
                    }
                } catch {
                }

                try {
                    const asUrl = new URL(normalized);
                    const fromQuery = asUrl.searchParams.get('ticket_number');

                    if (fromQuery && fromQuery.trim() !== '') {
                        return fromQuery.trim().toUpperCase();
                    }
                } catch {
                }

                const match = normalized.toUpperCase().match(/[A-Z]{1,4}-?\d{1,6}/);

                return match ? match[0] : normalized.toUpperCase();
            };

            const fillAndSubmit = (ticketNumber) => {
                if (!ticketNumber) {
                    return;
                }

                ticketNumberInput.value = ticketNumber;
                ticketNumberInput.dispatchEvent(new Event('input', { bubbles: true }));
                checkInForm.requestSubmit();
            };

            const stopCamera = () => {
                if (state.rafId) {
                    cancelAnimationFrame(state.rafId);
                    state.rafId = null;
                }

                if (state.stream) {
                    state.stream.getTracks().forEach((track) => track.stop());
                    state.stream = null;
                }

                if (videoElement) {
                    videoElement.srcObject = null;
                }
            };

            const detectFromVideo = async () => {
                if (!state.detector || !videoElement) {
                    return;
                }

                try {
                    const barcodes = await state.detector.detect(videoElement);
                    const firstValue = barcodes?.[0]?.rawValue;
                    const ticketNumber = extractTicketNumber(firstValue);

                    if (ticketNumber) {
                        updateStatus(`Terdeteksi: ${ticketNumber}. Memproses check-in...`);
                        stopCamera();
                        dispatchModalEvent('close-modal');
                        fillAndSubmit(ticketNumber);

                        return;
                    }
                } catch {
                }

                state.rafId = requestAnimationFrame(detectFromVideo);
            };

            const startCamera = async () => {
                if (!videoElement) {
                    return;
                }

                if (!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia) {
                    updateStatus('Browser ini belum mendukung scan kamera. Gunakan scanner USB atau input manual.');

                    return;
                }

                try {
                    const preferredFormats = ['qr_code', 'code_128', 'code_39', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'itf', 'codabar', 'pdf417', 'data_matrix'];
                    const supportedFormats = typeof window.BarcodeDetector.getSupportedFormats === 'function'
                        ? await window.BarcodeDetector.getSupportedFormats()
                        : preferredFormats;
                    const selectedFormats = preferredFormats.filter((format) => supportedFormats.includes(format));

                    state.detector = new window.BarcodeDetector({
                        formats: selectedFormats.length > 0 ? selectedFormats : preferredFormats,
                    });

                    state.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'environment',
                        },
                        audio: false,
                    });

                    videoElement.srcObject = state.stream;
                    await videoElement.play();

                    updateStatus('Kamera aktif. Arahkan barcode/QR ke tengah layar.');

                    state.rafId = requestAnimationFrame(detectFromVideo);
                } catch {
                    stopCamera();
                    updateStatus('Izin kamera ditolak atau kamera tidak tersedia. Gunakan scanner USB / input manual.');
                }
            };

            const handleKeyboardScanner = (event) => {
                if (isEditableElement(event.target)) {
                    return;
                }

                const now = Date.now();
                const elapsed = now - state.lastKeyAt;
                state.lastKeyAt = now;

                if (event.key === 'Enter') {
                    if (state.keyboardBuffer.length < 4) {
                        state.keyboardBuffer = '';

                        return;
                    }

                    event.preventDefault();

                    const ticketNumber = extractTicketNumber(state.keyboardBuffer);
                    state.keyboardBuffer = '';

                    if (ticketNumber) {
                        fillAndSubmit(ticketNumber);
                    }

                    return;
                }

                if (event.key.length !== 1 || event.ctrlKey || event.metaKey || event.altKey) {
                    return;
                }

                if (elapsed > 120) {
                    state.keyboardBuffer = '';
                }

                state.keyboardBuffer += event.key;
            };

            window.frontdeskScanner = {
                open: () => {
                    dispatchModalEvent('open-modal');
                    updateStatus('Menyiapkan kamera...');
                    setTimeout(() => {
                        startCamera();
                    }, 180);
                },
                stop: () => {
                    stopCamera();
                    updateStatus('Scan dihentikan.');
                },
            };

            document.addEventListener('keydown', handleKeyboardScanner, true);
            window.addEventListener('beforeunload', stopCamera);
        })();
    </script>
</x-layouts::app>
