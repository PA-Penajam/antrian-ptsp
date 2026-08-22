@php
    $institutionName = config('institution.name');
    $operatingHours = config('institution.operating_hours');
    $todayStats = $todayStats ?? ['total' => 0, 'waiting' => 0, 'completed' => 0];
    $activeCallingTickets = $activeCallingTickets ?? collect();
@endphp

<x-layouts::public :title="'Beranda - ' . $institutionName">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 sm:gap-12 lg:gap-14">
        
        {{-- 1. HERO BALAI SECTION --}}
        <div class="relative overflow-hidden rounded-3xl border border-cyan-200/80 bg-gradient-to-b from-white via-[#f7fcfd] to-[#edf8fa] p-5 sm:p-8 lg:p-12 shadow-[0_24px_60px_-36px_rgba(14,116,144,0.35)] print:bg-white print:border-slate-300 print:shadow-none">
            {{-- Ambient radial background highlights --}}
            <div aria-hidden="true" class="pointer-events-none absolute -right-20 -top-20 size-72 rounded-full bg-cyan-400/15 blur-3xl print:hidden"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -left-20 -bottom-20 size-72 rounded-full bg-teal-400/15 blur-3xl print:hidden"></div>

            <div class="relative grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(19rem,0.9fr)] lg:items-stretch">
                <div class="flex flex-col justify-between space-y-6 text-center lg:text-left">
                    <div class="space-y-3.5 sm:space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50/90 px-3.5 py-1.5 shadow-xs transition-all hover:bg-cyan-100/70 print:border-slate-300 print:bg-slate-100">
                            <flux:icon.building-office-2 class="size-4 text-cyan-700 print:text-slate-800" />
                            <span class="text-xs font-semibold tracking-wider text-cyan-900 uppercase print:text-slate-900">{{ $institutionName }}</span>
                        </div>

                        <div class="space-y-2.5 sm:space-y-3">
                            <flux:heading level="1" size="xl" class="text-balance font-black text-slate-900 text-3xl sm:text-4xl lg:text-5xl tracking-tight">
                                Sistem Antrian PTSP
                            </flux:heading>

                            <flux:subheading class="mx-auto max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base lg:mx-0 lg:text-lg">
                                Layanan terpadu satu pintu yang tertib, ramah, dan transparan. Ambil nomor antrian dari rumah, pantau giliran secara langsung, dan siapkan dokumen dengan tenang.
                            </flux:subheading>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col justify-center gap-3 sm:flex-row lg:justify-start print:hidden">
                        <flux:button
                            href="{{ url('/antrian') }}"
                            variant="primary"
                            icon="ticket"
                            class="h-14 w-full justify-center rounded-2xl bg-gradient-to-r from-cyan-700 via-cyan-600 to-teal-700 px-7 text-base font-bold text-white shadow-lg shadow-cyan-700/25 transition-all duration-200 hover:brightness-105 hover:shadow-cyan-700/35 active:scale-[0.99] touch-manipulation sm:w-auto"
                        >
                            Ambil Nomor Antrian
                        </flux:button>

                        <flux:button
                            href="{{ url('/antrian/cek') }}"
                            variant="subtle"
                            icon="magnifying-glass"
                            class="h-14 w-full justify-center rounded-2xl border-2 border-cyan-200/90 bg-white px-7 text-base font-bold text-cyan-950 shadow-xs transition-all duration-200 hover:border-cyan-300 hover:bg-cyan-50/70 active:scale-[0.99] touch-manipulation sm:w-auto"
                        >
                            Cek Status Antrian
                        </flux:button>
                    </div>
                </div>

                {{-- Hero Operational Card --}}
                <div class="flex flex-col justify-between space-y-4 rounded-3xl border border-cyan-200/90 bg-white/95 p-4.5 sm:p-6 shadow-sm backdrop-blur-sm transition-all duration-300 hover:shadow-md print:border-slate-300">
                    <div class="space-y-1">
                        <flux:heading size="lg" class="font-bold text-slate-900">Informasi Pelayanan</flux:heading>
                        <flux:text class="text-xs leading-relaxed text-slate-600 sm:text-sm">
                            Jam layanan tatap muka dan panduan persiapan dokumen di ruang PTSP.
                        </flux:text>
                    </div>

                    <div class="grid gap-3">
                        <div class="group flex items-start gap-3.5 rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50/80 to-white p-3.5 sm:p-4 shadow-xs transition-all duration-200 hover:border-cyan-200 hover:bg-cyan-50/90 print:border-slate-200">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-cyan-600 text-white shadow-xs transition-transform group-hover:scale-105 motion-reduce:transform-none sm:size-11">
                                <flux:icon.clock class="size-5" />
                            </div>
                            <div class="min-w-0 space-y-0.5">
                                <p class="text-xs font-bold tracking-[0.16em] text-cyan-800 uppercase print:text-slate-800">Jam Operasional</p>
                                <p class="text-sm font-semibold text-slate-800 truncate">
                                    {{ filled($operatingHours) ? $operatingHours : 'Senin - Jumat, 08:00 - 16:00 WIB' }}
                                </p>
                            </div>
                        </div>

                        <div class="group flex items-start gap-3.5 rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50/80 to-white p-3.5 sm:p-4 shadow-xs transition-all duration-200 hover:border-emerald-200 hover:bg-emerald-50/90 print:border-slate-200">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs transition-transform group-hover:scale-105 motion-reduce:transform-none sm:size-11">
                                <flux:icon.identification class="size-5" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold tracking-[0.16em] text-emerald-800 uppercase print:text-slate-800">Persiapan Dokumen</p>
                                <p class="text-xs leading-relaxed text-slate-700 sm:text-sm">
                                    Bawa e-KTP dan berkas persyaratan asli agar proses verifikasi di loket berjalan lancar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. LIVE ANTRIAN RINGKAS (REACTIVE LIVEWIRE COMPONENT) --}}
        <livewire:public-queue-monitor />

        {{-- 3. KATALOG LAYANAN INTERAKTIF --}}
        <section
            x-data="{ filter: 'all' }"
            class="space-y-5 sm:space-y-6"
        >
            <div class="flex flex-col gap-3.5 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-1">
                    <flux:heading level="2" size="xl" class="font-bold text-slate-900">Katalog Layanan</flux:heading>
                    <flux:text class="max-w-3xl text-xs leading-relaxed text-slate-600 sm:text-base">
                        Pilih jenis permohonan yang sesuai. Anda dapat memeriksa persyaratan dokumen serta mengambil nomor antrian secara langsung.
                    </flux:text>
                </div>

                {{-- Quick Filter Pills --}}
                <div class="flex items-center gap-1.5 overflow-x-auto rounded-2xl border border-cyan-100 bg-white p-1.5 shadow-2xs print:hidden touch-manipulation">
                    <button
                        type="button"
                        x-on:click="filter = 'all'"
                        :class="filter === 'all' ? 'bg-cyan-800 text-white shadow-xs' : 'text-cyan-900 hover:text-cyan-950 hover:bg-cyan-50'"
                        class="shrink-0 rounded-xl px-3.5 py-1.5 text-xs font-semibold transition-all duration-200 cursor-pointer"
                    >
                        Semua Layanan
                    </button>
                    <button
                        type="button"
                        x-on:click="filter = 'booking'"
                        :class="filter === 'booking' ? 'bg-emerald-800 text-white shadow-xs' : 'text-emerald-900 hover:text-emerald-950 hover:bg-emerald-50'"
                        class="shrink-0 rounded-xl px-3.5 py-1.5 text-xs font-semibold transition-all duration-200 cursor-pointer"
                    >
                        Booking Online
                    </button>
                    <button
                        type="button"
                        x-on:click="filter = 'walkin'"
                        :class="filter === 'walkin' ? 'bg-blue-800 text-white shadow-xs' : 'text-blue-900 hover:text-blue-950 hover:bg-blue-50'"
                        class="shrink-0 rounded-xl px-3.5 py-1.5 text-xs font-semibold transition-all duration-200 cursor-pointer"
                    >
                        Walk-in
                    </button>
                </div>
            </div>

            @if ($services->isNotEmpty())
                <div class="grid gap-4.5 sm:gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)
                        <div
                            x-show="filter === 'all' || (filter === 'booking' && {{ $service->booking_enabled ? 'true' : 'false' }}) || (filter === 'walkin' && {{ $service->walk_in_enabled ? 'true' : 'false' }})"
                            x-transition:enter="transition ease-out duration-250 motion-reduce:transition-none"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-1 scale-[0.98]"
                            x-data="{ showRequirements: false }"
                            class="group flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-[0_14px_34px_-24px_rgba(15,23,42,0.14)] transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cyan-300 hover:shadow-[0_24px_48px_-24px_rgba(14,116,144,0.22)] motion-reduce:transform-none print:shadow-none print:border-slate-300"
                        >
                            <div class="space-y-4">
                                {{-- Header Service --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-1">
                                        <flux:heading size="lg" class="font-bold text-slate-900">{{ $service->name }}</flux:heading>
                                        <p class="text-xs font-semibold tracking-[0.14em] text-cyan-800 uppercase">Pelayanan PTSP</p>
                                    </div>

                                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-100 to-teal-100 font-black text-base text-cyan-900 border border-cyan-200/60 shadow-2xs transition-transform duration-200 group-hover:scale-105 motion-reduce:transform-none">
                                        {{ filled($service->letter_code) ? $service->letter_code : 'U' }}
                                    </span>
                                </div>

                                @if ($service->description)
                                    <flux:text class="text-xs leading-relaxed text-slate-600 sm:text-sm">
                                        {{ $service->description }}
                                    </flux:text>
                                @endif

                                {{-- Badges --}}
                                <div class="flex flex-wrap gap-2">
                                    @if ($service->booking_enabled)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                            <flux:icon.check class="size-3 text-emerald-600" />
                                            Booking Online
                                        </span>
                                    @endif

                                    @if ($service->walk_in_enabled)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                                            <flux:icon.users class="size-3 text-blue-600" />
                                            Walk-in
                                        </span>
                                    @endif

                                    @if ($service->daily_quota)
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                            Kuota: {{ $service->daily_quota }}/hari
                                        </span>
                                    @endif
                                </div>

                                {{-- Requirements Accordion --}}
                                <div class="border-t border-slate-100 pt-3.5">
                                    <button
                                        type="button"
                                        x-on:click="showRequirements = !showRequirements"
                                        class="group/req flex w-full min-h-[44px] items-center justify-between rounded-xl py-1.5 text-xs font-bold tracking-[0.14em] text-cyan-800 uppercase transition hover:text-cyan-950 cursor-pointer touch-manipulation print:hidden"
                                    >
                                        <span class="flex items-center gap-1.5">
                                            <flux:icon.document-text class="size-3.5 text-cyan-600 group-hover/req:text-cyan-800 transition-colors" />
                                            Persyaratan Berkas
                                        </span>
                                        <svg
                                            class="size-4 text-cyan-600 transform transition-transform duration-200 motion-reduce:transition-none"
                                            :class="showRequirements ? 'rotate-180 text-cyan-800' : ''"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div
                                        x-show="showRequirements"
                                        x-collapse.duration.250ms
                                        class="mt-2.5 rounded-2xl border border-cyan-100 bg-gradient-to-br from-slate-50 to-cyan-50/40 p-4 text-xs leading-relaxed text-slate-700 print:!block print:bg-white print:border-slate-200"
                                    >
                                        @if (filled($service->requirements))
                                            <div class="space-y-2">
                                                @foreach (explode("\n", $service->requirements) as $reqLine)
                                                    @if (filled(trim($reqLine)))
                                                        <div class="flex items-start gap-2">
                                                            <flux:icon.check-circle class="size-4 shrink-0 text-emerald-600 mt-0.5" />
                                                            <span>{{ trim($reqLine) }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-slate-500 italic">Persyaratan umum akan dipandu langsung oleh petugas loket PTSP.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Action Button --}}
                            <div class="mt-6 pt-2 print:hidden">
                                <flux:button
                                    href="{{ url('/antrian?service_id=' . $service->id) }}"
                                    variant="primary"
                                    icon="ticket"
                                    class="h-12 w-full justify-center rounded-2xl bg-gradient-to-r from-cyan-700 to-teal-700 font-bold text-white shadow-xs transition-all duration-200 hover:brightness-105 active:scale-[0.99] touch-manipulation"
                                >
                                    Pilih Layanan
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white/90 p-10 text-center">
                    <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                        <div class="flex size-14 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                            <flux:icon.inbox class="size-7" />
                        </div>
                        <div class="space-y-1">
                            <flux:heading size="lg" class="text-slate-900">Layanan belum tersedia</flux:heading>
                            <flux:text class="text-sm text-slate-600">
                                Daftar layanan publik akan tampil setelah data layanan diaktifkan oleh administrator PTSP.
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- 4. PANDUAN PENGUNJUNG --}}
        <section class="rounded-3xl border border-cyan-200/80 bg-gradient-to-b from-white via-cyan-50/20 to-cyan-50/50 p-5 sm:p-8 lg:p-10 shadow-xs print:border-slate-300 print:bg-white">
            <div class="space-y-2 text-center lg:text-left">
                <flux:heading level="2" size="xl" class="font-bold text-slate-900">Panduan Pengunjung</flux:heading>
                <flux:text class="max-w-2xl text-xs leading-relaxed text-slate-600 sm:text-base">
                    Tiga langkah sederhana untuk memastikan kunjungan Anda ke PTSP {{ $institutionName }} tertib dan efisien.
                </flux:text>
            </div>

            <div class="mt-6 sm:mt-8 grid gap-4 sm:gap-5 sm:grid-cols-3 items-stretch">
                <div class="group flex flex-col justify-between space-y-4 rounded-3xl border border-cyan-200/90 bg-white p-5 sm:p-6 shadow-xs transition-all duration-300 hover:border-cyan-300 hover:shadow-md hover:-translate-y-0.5 motion-reduce:transform-none print:border-slate-200">
                    <div class="space-y-3.5">
                        <div class="flex size-11 sm:size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-cyan-800 font-black text-base sm:text-lg text-white shadow-md shadow-cyan-600/20 transition-transform duration-200 group-hover:scale-105 motion-reduce:transform-none">
                            1
                        </div>
                        <div class="space-y-1.5">
                            <flux:heading size="base" class="font-bold text-slate-900">Pilih Layanan</flux:heading>
                            <flux:text class="text-xs leading-relaxed text-slate-600 sm:text-sm">
                                Tinjau katalog layanan dan pahami jenis pelayanan serta persyaratan berkas yang wajib disiapkan.
                            </flux:text>
                        </div>
                    </div>
                </div>

                <div class="group flex flex-col justify-between space-y-4 rounded-3xl border border-emerald-200/90 bg-white p-5 sm:p-6 shadow-xs transition-all duration-300 hover:border-emerald-300 hover:shadow-md hover:-translate-y-0.5 motion-reduce:transform-none print:border-slate-200">
                    <div class="space-y-3.5">
                        <div class="flex size-11 sm:size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 font-black text-base sm:text-lg text-white shadow-md shadow-emerald-600/20 transition-transform duration-200 group-hover:scale-105 motion-reduce:transform-none">
                            2
                        </div>
                        <div class="space-y-1.5">
                            <flux:heading size="base" class="font-bold text-slate-900">Isi Data Diri</flux:heading>
                            <flux:text class="text-xs leading-relaxed text-slate-600 sm:text-sm">
                                Masukkan nama, nomor HP, dan NIK pada formulir booking untuk verifikasi identitas di meja frontdesk.
                            </flux:text>
                        </div>
                    </div>
                </div>

                <div class="group flex flex-col justify-between space-y-4 rounded-3xl border border-blue-200/90 bg-white p-5 sm:p-6 shadow-xs transition-all duration-300 hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5 motion-reduce:transform-none print:border-slate-200">
                    <div class="space-y-3.5">
                        <div class="flex size-11 sm:size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-800 font-black text-base sm:text-lg text-white shadow-md shadow-blue-600/20 transition-transform duration-200 group-hover:scale-105 motion-reduce:transform-none">
                            3
                        </div>
                        <div class="space-y-1.5">
                            <flux:heading size="base" class="font-bold text-slate-900">Tunjukkan Nomor Antrian</flux:heading>
                            <flux:text class="text-xs leading-relaxed text-slate-600 sm:text-sm">
                                Simpan bukti tiket dan hadir tepat waktu sesuai giliran panggilan loket di layar display ruang tunggu.
                            </flux:text>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assurance Banner & Internal Staff Login Hint --}}
            <div class="mt-6 sm:mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-cyan-100 pt-5 sm:pt-6 print:border-slate-200">
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-800 print:text-slate-800">
                    <flux:icon.shield-check class="size-4 text-emerald-600 print:text-slate-700" />
                    <span>Pelayanan Bebas Pungutan Liar &mdash; Cepat, Tertib, dan Akuntabel.</span>
                </div>
                <div class="print:hidden">
                    @auth
                        <flux:button href="{{ route('dashboard') }}" variant="subtle" icon="squares-2x2" size="sm" class="rounded-xl border border-cyan-200 bg-white text-cyan-950 font-semibold shadow-xs hover:bg-cyan-50">
                            Buka Dashboard
                        </flux:button>
                    @else
                        <flux:button href="{{ route('login') }}" variant="subtle" icon="arrow-right-start-on-rectangle" size="sm" class="rounded-xl border border-cyan-200 bg-white text-cyan-950 font-semibold shadow-xs hover:bg-cyan-50">
                            Masuk Petugas
                        </flux:button>
                    @endauth
                </div>
            </div>
        </section>

    </div>
</x-layouts::public>
