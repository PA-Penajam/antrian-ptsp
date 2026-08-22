<x-layouts::app :title="__('Frontdesk Antrian')">
    <div class="w-full space-y-6">
        {{-- Breadcrumb & Header --}}
        <div class="animate-fade-in-up flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" aria-label="Beranda" />
                    <flux:breadcrumbs.item>Frontdesk</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Antrian</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <flux:heading size="xl" level="1" class="font-extrabold tracking-tight">Frontdesk Antrian</flux:heading>
                <flux:subheading class="text-zinc-600 dark:text-zinc-400">Buat tiket antrian baru untuk pemohon langsung atau proses check-in tiket booking.</flux:subheading>
            </div>

            <div class="hidden sm:flex items-center gap-2 rounded-2xl bg-zinc-100/80 px-3.5 py-2 text-xs font-medium text-zinc-600 backdrop-blur-xs dark:bg-zinc-800/70 dark:text-zinc-300">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Workstation Frontdesk Aktif</span>
            </div>
        </div>

        {{-- Status Alerts --}}
        @if (session('status'))
            <flux:callout icon="check-circle" color="green" class="animate-fade-in-up rounded-2xl shadow-xs" style="animation-delay: 75ms;">
                {{ session('status') }}
            </flux:callout>
        @endif

        {{-- Created Ticket Celebration Card --}}
        @if ($ticket)
            <div class="animate-ticket-arrive overflow-hidden rounded-3xl border border-emerald-200/80 bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent p-5 sm:p-6 shadow-lg shadow-emerald-500/5 backdrop-blur-xs dark:border-emerald-500/30 dark:from-emerald-950/40 dark:via-zinc-900/60 dark:to-zinc-900 relative">
                <!-- Background decoration icon -->
                <div class="absolute -right-6 -top-6 text-emerald-500/10 dark:text-emerald-500/5 rotate-12 pointer-events-none">
                    <flux:icon.ticket class="size-48" />
                </div>

                <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="admin-icon-box size-14 rounded-2xl bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/30 dark:bg-emerald-500/20 dark:text-emerald-400">
                            <flux:icon.ticket class="size-7" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-400">Tiket Berhasil Dibuat</span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                    <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    {{ $ticket->status->label() }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-baseline gap-3">
                                <span class="font-mono text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ $ticket->ticket_number }}</span>
                                <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">({{ $ticket->service->name }})</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5 border-t border-emerald-200/60 pt-4 dark:border-emerald-800/40 sm:border-t-0 sm:pt-0">
                        <div class="rounded-xl bg-white/90 px-3.5 py-2 text-xs font-medium shadow-xs ring-1 ring-emerald-200/50 dark:bg-zinc-800/90 dark:ring-zinc-700">
                            <span class="text-zinc-500 dark:text-zinc-400">Pemohon:</span>
                            <span class="ml-1 font-bold text-zinc-900 dark:text-white">{{ $ticket->visitor_name }}</span>
                        </div>
                        <div class="rounded-xl bg-white/90 px-3.5 py-2 text-xs font-medium shadow-xs ring-1 ring-emerald-200/50 dark:bg-zinc-800/90 dark:ring-zinc-700">
                            <span class="text-zinc-500 dark:text-zinc-400">Waktu:</span>
                            <span class="ml-1 font-bold text-zinc-900 dark:text-white">{{ $ticket->created_at->format('H:i') }} WIB</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Checked-in Ticket Celebration Card --}}
        @if ($checkedInTicket)
            <div class="animate-ticket-arrive overflow-hidden rounded-3xl border border-violet-200/80 bg-gradient-to-br from-violet-500/10 via-violet-500/5 to-transparent p-5 sm:p-6 shadow-lg shadow-violet-500/5 backdrop-blur-xs dark:border-violet-500/30 dark:from-violet-950/40 dark:via-zinc-900/60 dark:to-zinc-900 relative">
                <!-- Background decoration icon -->
                <div class="absolute -right-6 -top-6 text-violet-500/10 dark:text-violet-500/5 rotate-12 pointer-events-none">
                    <flux:icon.check-circle class="size-48" />
                </div>

                <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="admin-icon-box size-14 rounded-2xl bg-violet-500/15 text-violet-600 ring-1 ring-violet-500/30 dark:bg-violet-500/20 dark:text-violet-400">
                            <flux:icon.check-circle class="size-7" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-widest text-violet-700 dark:text-violet-400">Check-in Berhasil</span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-bold text-violet-800 dark:bg-violet-900/50 dark:text-violet-300">
                                    <span class="size-1.5 rounded-full bg-violet-500 animate-pulse"></span>
                                    {{ $checkedInTicket->status->label() }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-baseline gap-3">
                                <span class="font-mono text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ $checkedInTicket->ticket_number }}</span>
                                <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">({{ $checkedInTicket->service->name }})</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5 border-t border-violet-200/60 pt-4 dark:border-violet-800/40 sm:border-t-0 sm:pt-0">
                        <div class="rounded-xl bg-white/90 px-3.5 py-2 text-xs font-medium shadow-xs ring-1 ring-violet-200/50 dark:bg-zinc-800/90 dark:ring-zinc-700">
                            <span class="text-zinc-500 dark:text-zinc-400">Pemohon:</span>
                            <span class="ml-1 font-bold text-zinc-900 dark:text-white">{{ $checkedInTicket->visitor_name }}</span>
                        </div>
                        <div class="rounded-xl bg-white/90 px-3.5 py-2 text-xs font-medium shadow-xs ring-1 ring-violet-200/50 dark:bg-zinc-800/90 dark:ring-zinc-700">
                            <span class="text-zinc-500 dark:text-zinc-400">Waktu Check-in:</span>
                            <span class="ml-1 font-bold text-zinc-900 dark:text-white">{{ $checkedInTicket->checked_in_at?->format('H:i') ?? now()->format('H:i') }} WIB</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Dual-Panel Workstation Grid --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
            {{-- Left Panel: Buat Tiket Antrian Baru --}}
            <div class="lg:col-span-7 animate-fade-in-up" style="animation-delay: 150ms;">
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-7 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/70 dark:text-cyan-300">
                            <flux:icon.plus-circle class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold">Buat Tiket Antrian Baru</flux:heading>
                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Registrasi tiket pemohon langsung (walk-in) atau bantuan frontdesk.</flux:text>
                        </div>
                    </div>

                    <form 
                        method="POST" 
                        action="{{ route('frontdesk.queue.store') }}" 
                        class="mt-6 space-y-5" 
                        x-data="{ 
                            serviceId: '{{ old('service_id') }}', 
                            umumServiceId: '{{ $umumServiceId }}',
                            submitting: false 
                        }" 
                        x-bind:aria-busy="submitting" 
                        @submit="submitting = true"
                    >
                        @csrf

                        <flux:field>
                            <flux:label class="font-semibold">Layanan Dituju</flux:label>
                            <flux:select name="service_id" required x-model="serviceId" placeholder="Pilih Layanan...">
                                <flux:select.option value="" :selected="old('service_id') === null">Pilih Layanan</flux:select.option>
                                @foreach ($services as $service)
                                    <flux:select.option value="{{ $service->id }}" :selected="(string) old('service_id') === (string) $service->id">
                                        {{ $service->name }} ({{ $service->code }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="service_id" />
                        </flux:field>

                        <div x-show="serviceId === umumServiceId" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            <flux:field>
                                <flux:label class="font-semibold text-cyan-800 dark:text-cyan-300">Tujuan Layanan PTSP Umum</flux:label>
                                <flux:select name="visit_purpose" x-bind:required="serviceId === umumServiceId">
                                    <flux:select.option value="" :selected="old('visit_purpose') === null">Pilih Tujuan Layanan</flux:select.option>
                                    <flux:select.option value="pendaftaran" :selected="old('visit_purpose') === 'pendaftaran'">Pendaftaran Perkara</flux:select.option>
                                    <flux:select.option value="informasi_pengaduan" :selected="old('visit_purpose') === 'informasi_pengaduan'">Informasi & Pengaduan</flux:select.option>
                                    <flux:select.option value="produk_hukum" :selected="old('visit_purpose') === 'produk_hukum'">Pengambilan Produk Hukum (Akta Cerai/Salinan Putusan)</flux:select.option>
                                    <flux:select.option value="ecourt" :selected="old('visit_purpose') === 'ecourt'">Layanan e-Court</flux:select.option>
                                </flux:select>
                                <flux:error name="visit_purpose" />
                            </flux:field>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:label class="font-semibold">Kanal Pendaftaran</flux:label>
                                <flux:select name="channel" required>
                                    <flux:select.option value="" :selected="old('channel') === null">Pilih Kanal</flux:select.option>
                                    <flux:select.option value="assisted_same_day" :selected="old('channel', 'assisted_same_day') === 'assisted_same_day'">Bantuan Frontdesk (Hari Ini)</flux:select.option>
                                    <flux:select.option value="walk_in_kiosk" :selected="old('channel') === 'walk_in_kiosk'">Walk-in / Kiosk</flux:select.option>
                                </flux:select>
                                <flux:error name="channel" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="font-semibold">Tanggal Layanan</flux:label>
                                <flux:input type="date" name="service_date" required value="{{ old('service_date', now()->toDateString()) }}" />
                                <flux:error name="service_date" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label class="font-semibold">Nama Lengkap Pemohon</flux:label>
                            <flux:input type="text" name="visitor_name" required placeholder="Contoh: Budi Santoso" value="{{ old('visitor_name') }}" icon="user" />
                            <flux:error name="visitor_name" />
                        </flux:field>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:label class="font-semibold">Nomor Identitas (NIK/Paspor)</flux:label>
                                <flux:input type="text" name="visitor_identifier" placeholder="Opsional (16 digit NIK)" value="{{ old('visitor_identifier') }}" icon="identification" />
                                <flux:error name="visitor_identifier" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="font-semibold">Nomor Telepon / WhatsApp</flux:label>
                                <flux:input type="text" name="visitor_phone" placeholder="Opsional (08xxx)" value="{{ old('visitor_phone') }}" icon="phone" />
                                <flux:error name="visitor_phone" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label class="font-semibold">Catatan Tambahan</flux:label>
                            <flux:textarea name="notes" placeholder="Opsional, tuliskan catatan khusus atau kebutuhan pemohon...">{{ old('notes') }}</flux:textarea>
                            <flux:error name="notes" />
                        </flux:field>

                        <div class="flex justify-end pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:button 
                                type="submit" 
                                variant="primary" 
                                class="bg-cyan-700 font-bold text-white shadow-md shadow-cyan-700/20 hover:bg-cyan-600 dark:bg-cyan-700 dark:text-white dark:hover:bg-cyan-600 px-6" 
                                x-bind:disabled="submitting"
                            >
                                <span x-show="!submitting" class="flex items-center gap-2">
                                    <flux:icon.plus class="size-4" />
                                    Buat Tiket Antrian
                                </span>
                                <span x-show="submitting" class="flex items-center gap-2" style="display: none;">
                                    <svg class="size-4 animate-spin motion-reduce:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Membuat Tiket...
                                </span>
                            </flux:button>
                        </div>
                    </form>
                </flux:card>
            </div>

            {{-- Right Panel: Check-in Tiket & Quick Scan --}}
            <div class="lg:col-span-5 space-y-6 animate-fade-in-up" style="animation-delay: 200ms;">
                {{-- Check-in Card --}}
                <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-7 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="admin-icon-box bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300">
                                <flux:icon.check-circle class="size-5" />
                            </div>
                            <div>
                                <flux:heading size="lg" class="font-bold">Check-in Tiket</flux:heading>
                                <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Verifikasi pemegang tiket booking.</flux:text>
                            </div>
                        </div>

                        <flux:button
                            type="button"
                            variant="filled"
                            icon="qr-code"
                            size="sm"
                            class="font-semibold text-xs self-start sm:self-auto shadow-2xs"
                            x-data
                            x-on:click="window.frontdeskScanner?.open()"
                            aria-label="Buka kamera scan barcode QR tiket"
                        >
                            Scan Kamera
                        </flux:button>
                    </div>

                    {{-- USB Scanner Status Badge --}}
                    <div class="mt-4 flex items-center gap-2.5 rounded-2xl bg-emerald-50/70 p-3 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/60 dark:bg-emerald-950/30 dark:text-emerald-300 dark:ring-emerald-800/40">
                        <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">
                            <flux:icon.viewfinder-circle class="size-4" />
                        </div>
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-bold">Scanner USB Siaga</span>
                            </div>
                            <p class="text-xs font-normal text-emerald-700/80 dark:text-emerald-400/80">Scan langsung dari barcode reader USB di mana saja pada layar ini.</p>
                        </div>
                    </div>

                    <form 
                        id="check-in-form" 
                        method="POST" 
                        action="{{ route('frontdesk.queue.check-in') }}" 
                        class="mt-5 space-y-4"
                        x-data="{ submitting: false }"
                        x-bind:aria-busy="submitting"
                        @submit="submitting = true"
                    >
                        @csrf

                        <flux:field>
                            <flux:label class="font-semibold">Nomor Antrian / Kode Booking</flux:label>
                            <flux:input 
                                id="ticket-number-input" 
                                type="text" 
                                name="ticket_number" 
                                required 
                                placeholder="Contoh: A-001 atau B-005" 
                                value="{{ old('ticket_number') }}" 
                                class="font-mono text-base tracking-wider uppercase"
                                icon="ticket"
                                autocomplete="off"
                            />
                            <flux:error name="ticket_number" />
                        </flux:field>

                        <div class="flex justify-end pt-2">
                            <flux:button 
                                type="submit" 
                                variant="primary" 
                                class="w-full bg-emerald-700 font-bold text-white shadow-md shadow-emerald-700/20 hover:bg-emerald-600 dark:bg-emerald-700 dark:text-white dark:hover:bg-emerald-600"
                                x-bind:disabled="submitting"
                            >
                                <span x-show="!submitting" class="flex items-center justify-center gap-2">
                                    <flux:icon.check class="size-4" />
                                    Proses Check-in
                                </span>
                                <span x-show="submitting" class="flex items-center justify-center gap-2" style="display: none;">
                                    <svg class="size-4 animate-spin motion-reduce:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memproses...
                                </span>
                            </flux:button>
                        </div>
                    </form>
                </flux:card>

                {{-- Operational Guide / Tips Card --}}
                <div class="rounded-3xl border border-zinc-200/80 bg-zinc-50/70 p-5 dark:border-zinc-800 dark:bg-zinc-900/50">
                    <div class="flex items-center gap-2.5 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                        <flux:icon.information-circle class="size-4 text-cyan-600 dark:text-cyan-400" />
                        <span>Panduan Operasional Frontdesk</span>
                    </div>
                    <ul class="mt-3 space-y-2 text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">
                        <li class="flex items-start gap-2">
                            <span class="mt-1 size-1.5 shrink-0 rounded-full bg-cyan-600 dark:bg-cyan-400"></span>
                            <span><strong>Pendaftaran Walk-in:</strong> Gunakan form sebelah kiri untuk pemohon yang datang langsung tanpa reservasi.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 size-1.5 shrink-0 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                            <span><strong>Check-in Online:</strong> Arahkan pemohon online booking ke barcode scanner atau masukkan nomor tiketnya.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                            <span><strong>Status Kuota:</strong> Jika kuota layanan hari ini penuh, sistem akan menolak pendaftaran tambahan.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Camera Scanner Modal --}}
        <flux:modal name="frontdesk-scan-ticket" class="w-full max-w-lg">
            <div class="space-y-4">
                <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/70 dark:text-cyan-300">
                        <flux:icon.qr-code class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold">Scan Barcode / QR Tiket</flux:heading>
                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Arahkan kamera perangkat ke barcode atau QR code tiket.</flux:text>
                    </div>
                </div>

                {{-- Status Bar --}}
                <div id="scan-status-text" class="rounded-xl bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    Menyiapkan kamera...
                </div>

                {{-- High-tech Viewfinder Container --}}
                <div class="relative overflow-hidden rounded-2xl border-2 border-zinc-800 bg-zinc-950 shadow-inner">
                    <video id="scan-video" autoplay playsinline muted class="h-64 sm:h-72 w-full object-cover"></video>
                    
                    {{-- HUD Reticle Corners --}}
                    <div class="pointer-events-none absolute inset-6 border border-cyan-500/30 rounded-xl flex items-center justify-center">
                        <div class="absolute -top-1 -left-1 size-4 border-t-2 border-l-2 border-cyan-400"></div>
                        <div class="absolute -top-1 -right-1 size-4 border-t-2 border-r-2 border-cyan-400"></div>
                        <div class="absolute -bottom-1 -left-1 size-4 border-b-2 border-l-2 border-cyan-400"></div>
                        <div class="absolute -bottom-1 -right-1 size-4 border-b-2 border-r-2 border-cyan-400"></div>
                        
                        {{-- Scan Laser Indicator --}}
                        <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent animate-pulse"></div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button
                        type="button"
                        variant="ghost"
                        x-data
                        x-on:click="window.frontdeskScanner?.stop(); Flux.modal('frontdesk-scan-ticket').close()"
                    >
                        Tutup Scanner
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
                if (eventName === 'open-modal') {
                    Flux.modal(modalName).show();
                    return;
                }
                if (eventName === 'close-modal') {
                    Flux.modal(modalName).close();
                    return;
                }
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

