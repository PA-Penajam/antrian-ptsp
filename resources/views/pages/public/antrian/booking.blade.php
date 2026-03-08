@php
    $today = now()->toDateString();

    $bookableServices->loadCount([
        'queueTickets as today_tickets_count' => fn ($query) => $query->whereDate('service_date', today()),
    ]);

    $wizardSteps = [
        1 => 'Pilih Layanan',
        2 => 'Isi Data',
        3 => 'Konfirmasi',
    ];

    $serviceCards = $bookableServices
        ->map(function ($service) {
            $remainingQuota = $service->daily_quota > 0
                ? max($service->daily_quota - $service->today_tickets_count, 0)
                : null;

            return [
                'id' => (string) $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'requirements' => $service->requirements,
                'letter_code' => $service->letter_code,
                'daily_quota' => $service->daily_quota,
                'remaining_quota' => $remainingQuota,
                'quota_label' => $service->daily_quota > 0
                    ? ($remainingQuota > 0
                        ? 'Sisa '.$remainingQuota.' dari '.$service->daily_quota.' kuota hari ini'
                        : 'Kuota hari ini sudah habis')
                    : 'Kuota fleksibel sesuai ketersediaan petugas',
                'is_unavailable' => $service->daily_quota > 0 && $remainingQuota <= 0,
            ];
        })
        ->values();

    $initialServiceId = (string) old('service_id', '');
    $initialStep = filled($initialServiceId) ? 2 : 1;
@endphp

<x-layouts::public :title="__('Ambil Antrian PTSP')">
    <flux:main container>
        <script>
            window.bookingWizard = function (config) {
                return {
                    step: config.initial.step,
                    services: config.services,
                    serviceId: config.initial.serviceId,
                    serviceDate: config.initial.serviceDate,
                    visitorName: config.initial.visitorName,
                    visitorIdentifier: config.initial.visitorIdentifier,
                    visitorPhone: config.initial.visitorPhone,
                    notes: config.initial.notes,

                    get selectedService() {
                        return this.services.find((service) => String(service.id) === String(this.serviceId)) ?? null;
                    },

                    isStepActive(stepNumber) {
                        return this.step === stepNumber;
                    },

                    isStepComplete(stepNumber) {
                        return this.step > stepNumber;
                    },

                    canAdvanceToReview() {
                        return Boolean(this.serviceId && this.serviceDate && this.visitorName.trim());
                    },

                    goToStep(stepNumber) {
                        if (stepNumber === 1) {
                            this.step = 1;

                            return;
                        }

                        if (stepNumber === 2 && this.serviceId) {
                            this.step = 2;

                            return;
                        }

                        if (stepNumber === 3 && this.canAdvanceToReview()) {
                            this.step = 3;
                        }
                    },

                    selectService(serviceId) {
                        const service = this.services.find((item) => String(item.id) === String(serviceId));

                        if (!service || service.is_unavailable) {
                            return;
                        }

                        this.serviceId = String(serviceId);
                        this.step = 2;
                    },

                    advanceToReview() {
                        if (this.canAdvanceToReview()) {
                            this.step = 3;
                        }
                    },

                    formatServiceDate() {
                        if (!this.serviceDate) {
                            return '-';
                        }

                        const [year, month, day] = this.serviceDate.split('-');

                        if (!year || !month || !day) {
                            return this.serviceDate;
                        }

                        return `${day}/${month}/${year}`;
                    },

                    stepButtonClasses(stepNumber) {
                        if (this.isStepActive(stepNumber)) {
                            return 'border-cyan-300 bg-cyan-50 shadow-[0_20px_48px_-38px_rgba(8,145,178,0.7)]';
                        }

                        if (this.isStepComplete(stepNumber)) {
                            return 'border-emerald-200 bg-emerald-50';
                        }

                        return 'border-slate-200 bg-slate-50/80';
                    },

                    stepCircleClasses(stepNumber) {
                        if (this.isStepActive(stepNumber)) {
                            return 'bg-cyan-600 text-white';
                        }

                        if (this.isStepComplete(stepNumber)) {
                            return 'bg-emerald-600 text-white';
                        }

                        return 'bg-white text-slate-500 ring-1 ring-slate-200';
                    },

                    stepCaptionClasses(stepNumber) {
                        if (this.isStepActive(stepNumber)) {
                            return 'text-cyan-700';
                        }

                        if (this.isStepComplete(stepNumber)) {
                            return 'text-emerald-700';
                        }

                        return 'text-slate-400';
                    },

                    stepLabelClasses(stepNumber) {
                        if (this.isStepActive(stepNumber)) {
                            return 'text-slate-900';
                        }

                        if (this.isStepComplete(stepNumber)) {
                            return 'text-emerald-900';
                        }

                        return 'text-slate-500';
                    },

                    serviceCardClasses(serviceId) {
                        const service = this.services.find((item) => String(item.id) === String(serviceId));

                        if (!service) {
                            return 'border-slate-200 bg-white';
                        }

                        if (service.is_unavailable) {
                            return 'cursor-not-allowed border-red-200 bg-red-50/80 opacity-70';
                        }

                        if (String(this.serviceId) === String(serviceId)) {
                            return 'border-cyan-400 bg-cyan-50/70 ring-2 ring-cyan-200';
                        }

                        return 'cursor-pointer border-slate-200 bg-white hover:-translate-y-1 hover:border-cyan-300 hover:shadow-[0_30px_80px_-58px_rgba(8,145,178,0.78)]';
                    },
                };
            };
        </script>

        <div
            class="mx-auto flex w-full max-w-6xl flex-col gap-8 py-6 sm:gap-10 sm:py-8"
            x-data="bookingWizard({
                services: @js($serviceCards),
                initial: {
                    step: {{ $initialStep }},
                    serviceId: @js($initialServiceId),
                    serviceDate: @js(old('service_date', '')),
                    visitorName: @js(old('visitor_name', '')),
                    visitorIdentifier: @js(old('visitor_identifier', '')),
                    visitorPhone: @js(old('visitor_phone', '')),
                    notes: @js(old('notes', '')),
                },
            })"
        >
            <flux:card class="overflow-hidden border-cyan-200 bg-[linear-gradient(145deg,#effbff_0%,#f8fdff_45%,#ffffff_100%)] p-0 shadow-[0_34px_90px_-54px_rgba(14,116,144,0.48)]">
                <div class="grid gap-8 px-6 py-8 sm:px-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.7fr)] lg:px-10">
                    <div class="space-y-5">
                        <div class="flex flex-wrap items-center gap-3">
                            <flux:badge color="cyan" rounded icon="sparkles">Booking Online PTSP</flux:badge>
                            <flux:badge color="sky" rounded icon="rectangle-group">3 Langkah Cepat</flux:badge>
                        </div>

                        <div class="space-y-3">
                            <flux:heading size="xl" level="1" class="text-balance text-slate-900">
                                Ambil Antrian PTSP dengan alur yang lebih jelas.
                            </flux:heading>

                            <flux:subheading class="max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">
                                Pilih layanan, lengkapi data kunjungan, lalu periksa ulang ringkasan sebelum mengirim booking Anda.
                            </flux:subheading>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <flux:card class="border-slate-200 bg-white/90 p-4 shadow-none">
                                <div class="space-y-2">
                                    <div class="flex size-10 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                                        <flux:icon.ticket class="size-5" />
                                    </div>
                                    <flux:text class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase">Pilih</flux:text>
                                    <flux:text class="text-sm leading-6 text-slate-700">Layanan ditampilkan sebagai kartu agar lebih mudah dibandingkan cepat.</flux:text>
                                </div>
                            </flux:card>

                            <flux:card class="border-slate-200 bg-white/90 p-4 shadow-none">
                                <div class="space-y-2">
                                    <div class="flex size-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <flux:icon.identification class="size-5" />
                                    </div>
                                    <flux:text class="text-xs font-semibold tracking-[0.18em] text-emerald-700 uppercase">Lengkapi</flux:text>
                                    <flux:text class="text-sm leading-6 text-slate-700">Isi data pengunjung dan cek persyaratan layanan sebelum datang ke loket.</flux:text>
                                </div>
                            </flux:card>

                            <flux:card class="border-slate-200 bg-white/90 p-4 shadow-none">
                                <div class="space-y-2">
                                    <div class="flex size-10 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                        <flux:icon.clipboard-document-check class="size-5" />
                                    </div>
                                    <flux:text class="text-xs font-semibold tracking-[0.18em] text-amber-700 uppercase">Konfirmasi</flux:text>
                                    <flux:text class="text-sm leading-6 text-slate-700">Tinjau ringkasan terakhir agar booking yang dikirim tetap rapi dan akurat.</flux:text>
                                </div>
                            </flux:card>
                        </div>
                    </div>

                    <flux:card class="space-y-5 border-slate-200 bg-white/92 p-6 shadow-none">
                        <div class="space-y-2">
                            <flux:heading size="lg" class="text-slate-900">Yang perlu disiapkan</flux:heading>
                            <flux:text class="text-sm leading-6 text-slate-600">
                                Gunakan wizard ini untuk memastikan Anda memilih layanan yang tepat dan datang dengan dokumen yang sesuai.
                            </flux:text>
                        </div>

                        <div class="grid gap-3">
                            <flux:card class="border-slate-200 bg-slate-50 p-4 shadow-none">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                                        <flux:icon.calendar-days class="size-5" />
                                    </div>
                                    <div class="space-y-1">
                                        <flux:text class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase">Tanggal Layanan</flux:text>
                                        <flux:text class="text-sm leading-6 text-slate-700">Pilih tanggal yang diinginkan, minimal hari ini.</flux:text>
                                    </div>
                                </div>
                            </flux:card>

                            <flux:card class="border-slate-200 bg-slate-50 p-4 shadow-none">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <flux:icon.document-text class="size-5" />
                                    </div>
                                    <div class="space-y-1">
                                        <flux:text class="text-xs font-semibold tracking-[0.18em] text-emerald-700 uppercase">Persyaratan</flux:text>
                                        <flux:text class="text-sm leading-6 text-slate-700">Setiap layanan menampilkan kebutuhan dokumen agar pengajuan tidak tertunda.</flux:text>
                                    </div>
                                </div>
                            </flux:card>

                            <flux:card class="border-slate-200 bg-slate-50 p-4 shadow-none">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                        <flux:icon.phone class="size-5" />
                                    </div>
                                    <div class="space-y-1">
                                        <flux:text class="text-xs font-semibold tracking-[0.18em] text-amber-700 uppercase">Kontak Opsional</flux:text>
                                        <flux:text class="text-sm leading-6 text-slate-700">Tambahkan nomor telepon atau WhatsApp agar petugas lebih mudah menghubungi bila diperlukan.</flux:text>
                                    </div>
                                </div>
                            </flux:card>
                        </div>
                    </flux:card>
                </div>
            </flux:card>

            <form method="POST" action="{{ url('/antrian') }}" class="grid gap-6 lg:grid-cols-[minmax(0,1.18fr)_minmax(18rem,0.82fr)]">
                @csrf

                <input type="hidden" name="service_id" x-model="serviceId" />

                <flux:card class="space-y-6 border-cyan-200 bg-white/96 p-6 shadow-[0_24px_70px_-52px_rgba(14,116,144,0.45)] sm:p-7">
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="space-y-1">
                                <flux:text class="text-xs font-semibold tracking-[0.2em] text-cyan-700 uppercase">Wizard Booking</flux:text>
                                <flux:heading size="lg" class="text-slate-900">Selesaikan booking dalam tiga langkah</flux:heading>
                            </div>

                            <flux:badge color="zinc" rounded x-text="`Langkah ${step} dari 3`">Langkah 1 dari 3</flux:badge>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            @foreach ($wizardSteps as $stepNumber => $label)
                                <button
                                    type="button"
                                    class="flex items-center gap-3 rounded-3xl border px-4 py-3 text-left transition"
                                    x-on:click="goToStep({{ $stepNumber }})"
                                    x-bind:class="stepButtonClasses({{ $stepNumber }})"
                                >
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold transition" x-bind:class="stepCircleClasses({{ $stepNumber }})">
                                        <span x-show="!isStepComplete({{ $stepNumber }})">{{ $stepNumber }}</span>
                                        <flux:icon.check x-show="isStepComplete({{ $stepNumber }})" class="size-4" />
                                    </span>

                                    <span class="min-w-0">
                                        <span class="block text-xs font-semibold tracking-[0.16em] uppercase" x-bind:class="stepCaptionClasses({{ $stepNumber }})">
                                            Step {{ $stepNumber }}
                                        </span>
                                        <span class="block text-sm font-medium" x-bind:class="stepLabelClasses({{ $stepNumber }})">
                                            {{ $label }}
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <flux:separator />

                    <section class="space-y-5" x-show="step === 1" x-transition.opacity.duration.250ms>
                        <div class="space-y-2">
                            <flux:text class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase">Layanan</flux:text>
                            <flux:heading size="lg" class="text-slate-900">Pilih layanan yang ingin Anda booking</flux:heading>
                            <flux:text class="max-w-3xl text-sm leading-6 text-slate-600">
                                Setiap kartu menampilkan gambaran layanan, persyaratan, dan informasi kuota hari ini. Pilih satu layanan untuk melanjutkan.
                            </flux:text>
                        </div>

                        @if ($bookableServices->isNotEmpty())
                            <div class="grid gap-4 xl:grid-cols-2">
                                @foreach ($bookableServices as $service)
                                    @php
                                        $remainingQuota = $service->daily_quota > 0
                                            ? max($service->daily_quota - $service->today_tickets_count, 0)
                                            : null;
                                        $isUnavailable = $service->daily_quota > 0 && $remainingQuota <= 0;
                                    @endphp

                                    <flux:card
                                        tabindex="{{ $isUnavailable ? '-1' : '0' }}"
                                        role="button"
                                        x-on:click="selectService('{{ $service->id }}')"
                                        x-on:keydown.enter.prevent="selectService('{{ $service->id }}')"
                                        x-on:keydown.space.prevent="selectService('{{ $service->id }}')"
                                        x-bind:class="serviceCardClasses('{{ $service->id }}')"
                                        class="group flex h-full flex-col gap-4 border p-5 shadow-[0_26px_64px_-54px_rgba(15,23,42,0.45)] transition"
                                    >
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="space-y-1">
                                                <flux:heading size="base" class="text-slate-900">{{ $service->name }}</flux:heading>
                                                <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Pilihan layanan</flux:text>
                                            </div>

                                            <div class="flex flex-wrap justify-end gap-2">
                                                @if (filled($service->letter_code))
                                                    <flux:badge color="zinc" rounded>{{ $service->letter_code }}</flux:badge>
                                                @endif

                                                @if ($service->daily_quota > 0)
                                                    <flux:badge :color="$isUnavailable ? 'red' : 'cyan'" rounded>
                                                        {{ $isUnavailable ? 'Kuota Habis' : 'Sisa '.$remainingQuota }}
                                                    </flux:badge>
                                                @else
                                                    <flux:badge color="emerald" rounded>Kuota Fleksibel</flux:badge>
                                                @endif
                                            </div>
                                        </div>

                                        <flux:text class="text-sm leading-6 text-slate-600">
                                            {{ filled($service->description) ? $service->description : 'Layanan ini dapat dibooking secara online melalui PTSP.' }}
                                        </flux:text>

                                        <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-4">
                                            <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Persyaratan</flux:text>
                                            <flux:text class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                                {{ filled($service->requirements) ? $service->requirements : 'Persyaratan akan diinformasikan oleh petugas PTSP saat proses verifikasi.' }}
                                            </flux:text>
                                        </div>

                                        <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                                            <flux:text class="text-xs leading-5 text-slate-500">
                                                {{ $service->daily_quota > 0 ? 'Kuota harian: '.$service->daily_quota.' tiket' : 'Booking mengikuti kapasitas layanan yang tersedia.' }}
                                            </flux:text>

                                            <flux:text class="text-xs font-semibold tracking-[0.16em] uppercase {{ $isUnavailable ? 'text-red-600' : 'text-cyan-700' }}">
                                                {{ $isUnavailable ? 'Tidak dapat dipilih' : 'Klik untuk lanjut' }}
                                            </flux:text>
                                        </div>
                                    </flux:card>
                                @endforeach
                            </div>
                        @else
                            <flux:card class="border-dashed border-slate-300 bg-slate-50/70 p-8 text-center shadow-none">
                                <div class="mx-auto flex max-w-2xl flex-col items-center gap-4">
                                    <div class="flex size-14 items-center justify-center rounded-3xl bg-cyan-100 text-cyan-700">
                                        <flux:icon.inbox class="size-7" />
                                    </div>

                                    <div class="space-y-2">
                                        <flux:heading size="lg" class="text-slate-900">Belum ada layanan booking</flux:heading>
                                        <flux:text class="text-sm leading-6 text-slate-600 sm:text-base">
                                            Daftar layanan publik akan muncul di sini setelah layanan booking diaktifkan oleh petugas PTSP.
                                        </flux:text>
                                    </div>
                                </div>
                            </flux:card>
                        @endif

                        <flux:error name="service_id" />
                    </section>

                    <section class="space-y-5" x-show="step === 2" x-transition.opacity.duration.250ms>
                        <div class="space-y-2">
                            <flux:text class="text-xs font-semibold tracking-[0.18em] text-emerald-700 uppercase">Isi Data</flux:text>
                            <flux:heading size="lg" class="text-slate-900">Lengkapi detail kunjungan Anda</flux:heading>
                            <flux:text class="max-w-3xl text-sm leading-6 text-slate-600">
                                Pastikan nama dan tanggal layanan sudah benar. Informasi tambahan membantu petugas memahami kebutuhan Anda sebelum kedatangan.
                            </flux:text>
                        </div>

                        <flux:card class="border-cyan-200 bg-[linear-gradient(180deg,#effbff_0%,#f8fdff_100%)] p-5 shadow-none">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="space-y-2">
                                    <flux:text class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase">Layanan terpilih</flux:text>
                                    <flux:heading size="base" class="text-slate-900" x-text="selectedService ? selectedService.name : 'Pilih layanan terlebih dahulu'">
                                        Pilih layanan terlebih dahulu
                                    </flux:heading>
                                    <flux:text class="text-sm leading-6 text-slate-600" x-text="selectedService && selectedService.description ? selectedService.description : 'Kembali ke langkah pertama bila ingin memilih layanan yang berbeda.'">
                                        Kembali ke langkah pertama bila ingin memilih layanan yang berbeda.
                                    </flux:text>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <template x-if="selectedService && selectedService.daily_quota > 0 && selectedService.remaining_quota > 0">
                                        <flux:badge color="cyan" rounded x-text="`Sisa ${selectedService.remaining_quota} kuota`">Sisa kuota</flux:badge>
                                    </template>

                                    <template x-if="selectedService && selectedService.daily_quota > 0 && selectedService.remaining_quota <= 0">
                                        <flux:badge color="red" rounded>Kuota Habis</flux:badge>
                                    </template>

                                    <template x-if="selectedService && (!selectedService.daily_quota || selectedService.daily_quota <= 0)">
                                        <flux:badge color="emerald" rounded>Kuota Fleksibel</flux:badge>
                                    </template>
                                </div>
                            </div>

                            <div class="mt-4 rounded-3xl border border-white/80 bg-white/90 p-4">
                                <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Persyaratan layanan</flux:text>
                                <flux:text class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700" x-text="selectedService && selectedService.requirements ? selectedService.requirements : 'Persyaratan akan diinformasikan oleh petugas PTSP.'">
                                    Persyaratan akan diinformasikan oleh petugas PTSP.
                                </flux:text>
                            </div>
                        </flux:card>

                        <div class="grid gap-5 md:grid-cols-2">
                            <flux:field>
                                <flux:label>Tanggal Layanan</flux:label>
                                <flux:input type="date" name="service_date" x-model="serviceDate" required min="{{ $today }}" />
                                <flux:error name="service_date" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Nama Lengkap</flux:label>
                                <flux:input type="text" name="visitor_name" x-model="visitorName" required placeholder="Masukkan nama lengkap Anda" />
                                <flux:error name="visitor_name" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Nomor Identitas</flux:label>
                                <flux:input type="text" name="visitor_identifier" x-model="visitorIdentifier" placeholder="Opsional" />
                                <flux:error name="visitor_identifier" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Nomor Telepon / WhatsApp</flux:label>
                                <flux:input type="text" name="visitor_phone" x-model="visitorPhone" placeholder="Opsional" />
                                <flux:error name="visitor_phone" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Catatan Tambahan</flux:label>
                            <flux:textarea name="notes" x-model="notes" placeholder="Opsional, tuliskan keperluan Anda secara singkat" />
                            <flux:error name="notes" />
                        </flux:field>

                        <div class="flex flex-col-reverse justify-between gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center">
                            <flux:button type="button" variant="subtle" icon="arrow-left" x-on:click="goToStep(1)">
                                Kembali ke layanan
                            </flux:button>

                            <flux:button type="button" variant="primary" icon:trailing="arrow-right" x-bind:disabled="!canAdvanceToReview()" x-on:click="advanceToReview()">
                                Lanjutkan
                            </flux:button>
                        </div>
                    </section>

                    <section class="space-y-5" x-show="step === 3" x-transition.opacity.duration.250ms>
                        <div class="space-y-2">
                            <flux:text class="text-xs font-semibold tracking-[0.18em] text-amber-700 uppercase">Konfirmasi</flux:text>
                            <flux:heading size="lg" class="text-slate-900">Periksa ringkasan booking sebelum dikirim</flux:heading>
                            <flux:text class="max-w-3xl text-sm leading-6 text-slate-600">
                                Pastikan layanan, tanggal, dan data pengunjung sudah sesuai. Setelah dikirim, booking akan diproses ke halaman konfirmasi tiket.
                            </flux:text>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:card class="space-y-4 border-slate-200 bg-slate-50/70 p-5 shadow-none">
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Layanan</flux:text>
                                    <flux:heading size="base" class="text-slate-900" x-text="selectedService ? selectedService.name : '-'">-</flux:heading>
                                </div>

                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Tanggal Layanan</flux:text>
                                    <flux:text class="text-sm leading-6 text-slate-700" x-text="formatServiceDate()">-</flux:text>
                                </div>

                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nama Lengkap</flux:text>
                                    <flux:text class="text-sm leading-6 text-slate-700" x-text="visitorName || '-'">-</flux:text>
                                </div>
                            </flux:card>

                            <flux:card class="space-y-4 border-slate-200 bg-white p-5 shadow-none">
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nomor Identitas</flux:text>
                                    <flux:text class="text-sm leading-6 text-slate-700" x-text="visitorIdentifier || '-'">-</flux:text>
                                </div>

                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Nomor Telepon / WhatsApp</flux:text>
                                    <flux:text class="text-sm leading-6 text-slate-700" x-text="visitorPhone || '-'">-</flux:text>
                                </div>

                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Catatan Tambahan</flux:text>
                                    <flux:text class="whitespace-pre-line text-sm leading-6 text-slate-700" x-text="notes || '-'">-</flux:text>
                                </div>
                            </flux:card>
                        </div>

                        <flux:card class="border-cyan-200 bg-[linear-gradient(180deg,#ffffff_0%,#f3fbff_100%)] p-5 shadow-none">
                            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                                <div class="space-y-2">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-cyan-700 uppercase">Final check</flux:text>
                                    <flux:text class="text-sm leading-6 text-slate-700">
                                        Dengan menekan tombol kirim, Anda mengonfirmasi bahwa data booking sudah sesuai dengan identitas dan kebutuhan layanan yang dipilih.
                                    </flux:text>
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <flux:button type="button" variant="subtle" icon="arrow-left" x-on:click="goToStep(2)">
                                        Kembali
                                    </flux:button>
                                    <flux:button type="submit" variant="primary" icon="paper-airplane">
                                        Kirim
                                    </flux:button>
                                </div>
                            </div>
                        </flux:card>
                    </section>
                </flux:card>

                <div class="space-y-4">
                    <flux:card class="space-y-4 border-slate-200 bg-white/92 p-5 shadow-[0_24px_60px_-52px_rgba(15,23,42,0.35)]">
                        <div class="space-y-2">
                            <flux:text class="text-xs font-semibold tracking-[0.18em] text-slate-500 uppercase">Ringkasan aktif</flux:text>
                            <flux:heading size="base" class="text-slate-900">Pilihan Anda saat ini</flux:heading>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-1">
                                <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Layanan</flux:text>
                                <flux:text class="text-sm leading-6 text-slate-700" x-text="selectedService ? selectedService.name : 'Belum dipilih'">Belum dipilih</flux:text>
                            </div>

                            <div class="space-y-1">
                                <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Tanggal</flux:text>
                                <flux:text class="text-sm leading-6 text-slate-700" x-text="serviceDate ? formatServiceDate() : 'Belum diisi'">Belum diisi</flux:text>
                            </div>

                            <div class="space-y-1">
                                <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Pemohon</flux:text>
                                <flux:text class="text-sm leading-6 text-slate-700" x-text="visitorName || 'Belum diisi'">Belum diisi</flux:text>
                            </div>
                        </div>
                    </flux:card>

                    <flux:card class="space-y-4 border-slate-200 bg-white/92 p-5 shadow-[0_24px_60px_-52px_rgba(15,23,42,0.35)]">
                        <div class="space-y-2">
                            <flux:text class="text-xs font-semibold tracking-[0.18em] text-slate-500 uppercase">Petunjuk</flux:text>
                            <flux:heading size="base" class="text-slate-900">Alur cepat pengunjung</flux:heading>
                        </div>

                        <div class="space-y-3">
                            <div class="rounded-3xl border border-cyan-100 bg-cyan-50/70 p-4">
                                <flux:text class="text-xs font-semibold tracking-[0.16em] text-cyan-700 uppercase">1. Pilih layanan</flux:text>
                                <flux:text class="mt-1 text-sm leading-6 text-slate-700">Bandingkan kartu layanan dan hindari pilihan yang sudah berstatus kuota habis.</flux:text>
                            </div>

                            <div class="rounded-3xl border border-emerald-100 bg-emerald-50/70 p-4">
                                <flux:text class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">2. Isi data</flux:text>
                                <flux:text class="mt-1 text-sm leading-6 text-slate-700">Masukkan nama lengkap dan tanggal layanan, lalu tambahkan identitas atau catatan bila perlu.</flux:text>
                            </div>

                            <div class="rounded-3xl border border-amber-100 bg-amber-50/70 p-4">
                                <flux:text class="text-xs font-semibold tracking-[0.16em] text-amber-700 uppercase">3. Konfirmasi</flux:text>
                                <flux:text class="mt-1 text-sm leading-6 text-slate-700">Periksa ringkasan terakhir sebelum mengirim booking ke sistem antrian.</flux:text>
                            </div>
                        </div>
                    </flux:card>
                </div>
            </form>
        </div>

    </flux:main>
</x-layouts::public>
