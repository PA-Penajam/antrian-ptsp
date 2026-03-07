<x-layouts::public :title="__('Ambil Antrian PTSP')">
    @php
        $bookableServices = $services->filter(fn ($service) => (bool) $service->booking_enabled)->values();
        $initialServiceId = old('service_id');
        $initialService = filled($initialServiceId) ? $bookableServices->firstWhere('id', (int) $initialServiceId) : null;
        $serviceCatalog = $bookableServices->mapWithKeys(function ($service) {
            return [
                (string) $service->id => [
                    'id' => (string) $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'requirements' => $service->requirements,
                    'daily_quota' => $service->daily_quota,
                    'walk_in_enabled' => (bool) $service->walk_in_enabled,
                    'is_full' => $service->daily_quota === 0,
                ],
            ];
        });
    @endphp

    <flux:main container>
        <div
            class="mx-auto flex w-full max-w-5xl flex-col gap-8"
            x-data="{
                step: {{ $initialService ? 2 : 1 }},
                selectedService: @js($initialService ? (string) $initialService->id : null),
                selectedServiceName: @js($initialService?->name ?? ''),
                formData: {
                    service_date: @js(old('service_date', now()->toDateString())),
                    visitor_name: @js(old('visitor_name', '')),
                    visitor_identifier: @js(old('visitor_identifier', '')),
                    visitor_phone: @js(old('visitor_phone', '')),
                    notes: @js(old('notes', '')),
                },
                services: @js($serviceCatalog),
                init() {
                    if (this.selectedService && this.services[this.selectedService]) {
                        this.selectedServiceName = this.services[this.selectedService].name;
                    }

                    if ({{ $errors->any() ? 'true' : 'false' }}) {
                        this.step = this.selectedService ? 2 : 1;
                    }
                },
                currentService() {
                    if (! this.selectedService) {
                        return null;
                    }

                    return this.services[this.selectedService] ?? null;
                },
                selectService(serviceId) {
                    const service = this.services[serviceId];

                    if (! service || service.is_full) {
                        return;
                    }

                    this.selectedService = serviceId;
                    this.selectedServiceName = service.name;
                    this.step = 2;
                },
                canReview() {
                    return Boolean(this.selectedService && this.formData.service_date && this.formData.visitor_name.trim());
                },
                goToReview() {
                    if (this.canReview()) {
                        this.step = 3;
                    }
                },
                formattedServiceDate() {
                    if (! this.formData.service_date) {
                        return '-';
                    }

                    const serviceDate = new Date(`${this.formData.service_date}T00:00:00`);

                    if (Number.isNaN(serviceDate.getTime())) {
                        return this.formData.service_date;
                    }

                    return new Intl.DateTimeFormat('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                    }).format(serviceDate);
                },
            }"
        >
            <div class="relative overflow-hidden rounded-[2rem] border border-cyan-100/80 bg-[linear-gradient(135deg,rgba(255,255,255,0.96),rgba(236,254,255,0.92))] px-6 py-8 shadow-[0_28px_80px_-48px_rgba(14,116,144,0.55)] sm:px-8 lg:px-10">
                <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.18),transparent_48%),radial-gradient(circle_at_bottom_right,rgba(8,145,178,0.14),transparent_44%)]"></div>

                <div class="relative flex flex-col gap-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-2xl space-y-3">
                            <flux:badge color="blue">Reservasi Online PTSP</flux:badge>
                            <div class="space-y-2">
                                <flux:heading size="xl" level="1">Ambil Antrian PTSP</flux:heading>
                                <flux:subheading>
                                    Pilih layanan yang tersedia, isi data singkat Anda, lalu lakukan konfirmasi sebelum nomor antrian diproses.
                                </flux:subheading>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[26rem] lg:gap-2">
                            <div class="rounded-3xl border px-4 py-4 transition" x-bind:class="step >= 1 ? 'border-cyan-200 bg-white text-cyan-950 shadow-sm' : 'border-slate-200 bg-white/70 text-slate-400'">
                                <p class="text-xs font-semibold tracking-[0.18em] uppercase">Langkah 1</p>
                                <p class="mt-2 text-sm font-semibold">Pilih Layanan</p>
                            </div>
                            <div class="rounded-3xl border px-4 py-4 transition" x-bind:class="step >= 2 ? 'border-cyan-200 bg-white text-cyan-950 shadow-sm' : 'border-slate-200 bg-white/70 text-slate-400'">
                                <p class="text-xs font-semibold tracking-[0.18em] uppercase">Langkah 2</p>
                                <p class="mt-2 text-sm font-semibold">Isi Data</p>
                            </div>
                            <div class="rounded-3xl border px-4 py-4 transition" x-bind:class="step >= 3 ? 'border-cyan-200 bg-white text-cyan-950 shadow-sm' : 'border-slate-200 bg-white/70 text-slate-400'">
                                <p class="text-xs font-semibold tracking-[0.18em] uppercase">Langkah 3</p>
                                <p class="mt-2 text-sm font-semibold">Konfirmasi</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/antrian') }}" class="space-y-6">
                        @csrf

                        <input type="hidden" name="service_id" :value="selectedService">

                        <div x-show="step === 1">
                            <flux:card class="space-y-6 border border-white/80 bg-white/95 p-6 shadow-[0_24px_70px_-50px_rgba(15,23,42,0.45)] sm:p-8">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                    <div class="space-y-2">
                                        <flux:heading size="lg">Pilih layanan yang ingin Anda ajukan</flux:heading>
                                        <flux:text class="max-w-2xl text-sm leading-6 text-slate-600">
                                            Setiap layanan menampilkan ringkasan persyaratan, kanal layanan, dan kuota harian bila tersedia.
                                        </flux:text>
                                    </div>

                                    <flux:badge color="green">Langkah awal reservasi</flux:badge>
                                </div>

                                @if ($bookableServices->isEmpty())
                                    <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                                        <flux:heading size="lg">Belum ada layanan online yang tersedia</flux:heading>
                                        <flux:text class="mt-2 text-sm text-slate-600">
                                            Silakan hubungi petugas PTSP untuk mendapatkan informasi jadwal layanan terbaru.
                                        </flux:text>
                                    </div>
                                @else
                                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        @foreach ($bookableServices as $service)
                                            <button
                                                type="button"
                                                class="group h-full text-left disabled:cursor-not-allowed disabled:opacity-65"
                                                x-on:click="selectService('{{ $service->id }}')"
                                                @disabled($service->daily_quota === 0)
                                            >
                                                <flux:card
                                                    class="flex h-full flex-col gap-5 border border-slate-200/90 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(248,250,252,0.98))] p-5 transition duration-200 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-[0_24px_60px_-48px_rgba(8,145,178,0.75)]"
                                                    x-bind:class="selectedService === '{{ $service->id }}' ? 'border-cyan-300 ring-2 ring-cyan-200 shadow-[0_24px_60px_-48px_rgba(8,145,178,0.75)]' : ''"
                                                >
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="space-y-2">
                                                            <flux:heading size="lg">{{ $service->name }}</flux:heading>
                                                            <flux:text class="text-sm leading-6 text-slate-600">
                                                                {{ filled($service->description) ? $service->description : 'Layanan tersedia untuk kebutuhan administrasi dan konsultasi PTSP.' }}
                                                            </flux:text>
                                                        </div>

                                                        <div class="rounded-2xl bg-cyan-50 p-3 text-cyan-700">
                                                            <flux:icon.ticket class="size-5" />
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-wrap gap-2">
                                                        <flux:badge color="green">Online</flux:badge>

                                                        @if ($service->walk_in_enabled)
                                                            <flux:badge color="blue">Walk-in</flux:badge>
                                                        @endif

                                                        @if ($service->daily_quota === 0)
                                                            <flux:badge color="red">Kuota Habis</flux:badge>
                                                        @elseif (! is_null($service->daily_quota))
                                                            <flux:badge color="amber">Kuota {{ $service->daily_quota }}/hari</flux:badge>
                                                        @endif
                                                    </div>

                                                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4">
                                                        <p class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Persyaratan ringkas</p>
                                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                                            {{ \Illuminate\Support\Str::limit($service->requirements ?: 'Persyaratan akan ditampilkan lengkap pada langkah berikutnya.', 120) }}
                                                        </p>
                                                    </div>

                                                    <div class="mt-auto flex items-center justify-between gap-3 text-sm font-medium">
                                                        <span class="{{ $service->daily_quota === 0 ? 'text-slate-400' : 'text-cyan-700' }}">
                                                            {{ $service->daily_quota === 0 ? 'Reservasi ditutup' : 'Pilih layanan ini' }}
                                                        </span>
                                                        <flux:icon.arrow-right class="size-4 {{ $service->daily_quota === 0 ? 'text-slate-300' : 'text-cyan-600' }}" />
                                                    </div>
                                                </flux:card>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                <flux:error name="service_id" />
                            </flux:card>
                        </div>

                        <div x-cloak style="display: none;" x-show="step === 2">
                            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)]">
                                <flux:card class="space-y-6 border border-white/80 bg-white/95 p-6 shadow-[0_24px_70px_-50px_rgba(15,23,42,0.45)] sm:p-8">
                                    <div class="space-y-4 rounded-[1.75rem] border border-cyan-100 bg-[linear-gradient(135deg,#ecfeff_0%,#f8fafc_100%)] p-5">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="space-y-2">
                                                <p class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase">Layanan terpilih</p>
                                                <flux:heading size="lg" x-text="selectedServiceName || 'Pilih layanan terlebih dahulu'"></flux:heading>
                                            </div>

                                            <flux:button type="button" variant="subtle" x-on:click="step = 1">Ganti layanan</flux:button>
                                        </div>

                                        <p class="text-sm leading-6 text-slate-600" x-text="currentService() && currentService().description ? currentService().description : 'Informasi layanan akan muncul setelah Anda memilih salah satu kartu layanan.'"></p>

                                        <div class="rounded-2xl border border-cyan-100 bg-white/80 p-4">
                                            <p class="text-xs font-semibold tracking-[0.16em] text-cyan-700 uppercase">Persyaratan layanan</p>
                                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700" x-text="currentService() && currentService().requirements ? currentService().requirements : 'Tidak ada persyaratan tambahan yang ditampilkan untuk layanan ini.'"></p>
                                        </div>
                                    </div>

                                    <div class="grid gap-5 md:grid-cols-2">
                                        <flux:field>
                                            <flux:label>Tanggal Layanan</flux:label>
                                            <flux:input type="date" name="service_date" min="{{ now()->toDateString() }}" required x-model="formData.service_date" />
                                            <flux:error name="service_date" />
                                        </flux:field>

                                        <flux:field>
                                            <flux:label>Nama Lengkap</flux:label>
                                            <flux:input type="text" name="visitor_name" placeholder="Masukkan nama lengkap Anda" required x-model="formData.visitor_name" />
                                            <flux:error name="visitor_name" />
                                        </flux:field>

                                        <flux:field>
                                            <flux:label>Nomor Identitas</flux:label>
                                            <flux:input type="text" name="visitor_identifier" placeholder="Opsional" x-model="formData.visitor_identifier" />
                                            <flux:error name="visitor_identifier" />
                                        </flux:field>

                                        <flux:field>
                                            <flux:label>Nomor Telepon / WhatsApp</flux:label>
                                            <flux:input type="text" name="visitor_phone" placeholder="Opsional" x-model="formData.visitor_phone" />
                                            <flux:error name="visitor_phone" />
                                        </flux:field>
                                    </div>

                                    <flux:field>
                                        <flux:label>Catatan Tambahan</flux:label>
                                        <flux:textarea name="notes" rows="4" placeholder="Opsional, tuliskan kebutuhan singkat Anda" x-model="formData.notes"></flux:textarea>
                                        <flux:error name="notes" />
                                    </flux:field>

                                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-between">
                                        <flux:button type="button" variant="subtle" x-on:click="step = 1">Kembali ke layanan</flux:button>
                                        <flux:button type="button" variant="primary" x-on:click="goToReview()" x-bind:disabled="!canReview()">Lanjut ke konfirmasi</flux:button>
                                    </div>
                                </flux:card>

                                <flux:card class="space-y-5 border border-emerald-100/80 bg-[linear-gradient(180deg,rgba(240,253,250,0.96),rgba(255,255,255,0.98))] p-6 shadow-[0_24px_70px_-50px_rgba(5,150,105,0.35)]">
                                    <div class="space-y-2">
                                        <p class="text-xs font-semibold tracking-[0.18em] text-emerald-700 uppercase">Ringkasan sementara</p>
                                        <flux:heading size="lg">Cek kembali data Anda sebelum lanjut</flux:heading>
                                    </div>

                                    <div class="grid gap-4">
                                        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4">
                                            <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">Layanan</p>
                                            <p class="mt-2 text-sm font-medium text-slate-800" x-text="selectedServiceName || '-'"></p>
                                        </div>

                                        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4">
                                            <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">Tanggal</p>
                                            <p class="mt-2 text-sm font-medium text-slate-800" x-text="formattedServiceDate()"></p>
                                        </div>

                                        <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4">
                                            <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">Nama Pemohon</p>
                                            <p class="mt-2 text-sm font-medium text-slate-800" x-text="formData.visitor_name || '-'"></p>
                                        </div>
                                    </div>

                                    <flux:text class="text-sm leading-6 text-slate-600">
                                        Setelah konfirmasi, permohonan Anda akan dikirim ke sistem antrian dan dilanjutkan ke halaman konfirmasi tiket.
                                    </flux:text>
                                </flux:card>
                            </div>
                        </div>

                        <div x-cloak style="display: none;" x-show="step === 3">
                            <flux:card class="space-y-6 border border-white/80 bg-white/95 p-6 shadow-[0_24px_70px_-50px_rgba(15,23,42,0.45)] sm:p-8">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="space-y-2">
                                        <flux:badge color="amber">Konfirmasi sebelum kirim</flux:badge>
                                        <flux:heading size="lg">Pastikan detail booking sudah sesuai</flux:heading>
                                        <flux:text class="max-w-2xl text-sm leading-6 text-slate-600">
                                            Tinjau layanan, tanggal kunjungan, dan data utama Anda. Gunakan tombol ubah jika ada informasi yang perlu diperbaiki.
                                        </flux:text>
                                    </div>

                                    <flux:button type="button" variant="subtle" x-on:click="step = 1">Ubah layanan</flux:button>
                                </div>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/90 p-5">
                                        <p class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Layanan</p>
                                        <p class="mt-3 text-base font-semibold text-slate-900" x-text="selectedServiceName || '-'"></p>
                                    </div>

                                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/90 p-5">
                                        <p class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Tanggal layanan</p>
                                        <p class="mt-3 text-base font-semibold text-slate-900" x-text="formattedServiceDate()"></p>
                                    </div>

                                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50/90 p-5">
                                        <p class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nama pemohon</p>
                                        <p class="mt-3 text-base font-semibold text-slate-900" x-text="formData.visitor_name || '-'"></p>
                                    </div>
                                </div>

                                <div class="rounded-[1.75rem] border border-cyan-100 bg-[linear-gradient(135deg,#ecfeff_0%,#ffffff_100%)] p-5">
                                    <p class="text-xs font-semibold tracking-[0.16em] text-cyan-700 uppercase">Yang akan terjadi berikutnya</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-700">
                                        Sistem akan membuat tiket antrian online untuk layanan yang Anda pilih. Simpan halaman konfirmasi setelah berhasil untuk referensi kedatangan Anda.
                                    </p>
                                </div>

                                <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-between">
                                    <div class="flex flex-col gap-3 sm:flex-row">
                                        <flux:button type="button" variant="subtle" x-on:click="step = 2">Kembali ke data</flux:button>
                                        <flux:button type="button" variant="ghost" x-on:click="step = 1">Ubah</flux:button>
                                    </div>

                                    <flux:button type="submit" variant="primary">Konfirmasi &amp; Ambil Antrian</flux:button>
                                </div>
                            </flux:card>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts::public>
