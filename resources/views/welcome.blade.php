@php
    $institutionName = config('institution.name');
    $operatingHours = config('institution.operating_hours');
@endphp

<x-layouts::public :title="'Sistem Antrian PTSP'">
    <section class="space-y-10 pb-6 sm:space-y-12 lg:space-y-16">
        <flux:card class="overflow-hidden border-cyan-200 bg-[linear-gradient(135deg,#0f172a_0%,#0f4c81_46%,#1d4ed8_100%)] text-white shadow-[0_32px_90px_-42px_rgba(15,76,129,0.95)]">
            <div class="grid gap-8 px-6 py-7 sm:px-8 sm:py-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(21rem,0.9fr)] lg:items-end lg:gap-10 lg:px-10 lg:py-10">
                <div class="space-y-6">
                    <flux:badge variant="solid" color="sky" class="w-fit rounded-full border border-white/15 bg-white/12 text-white">
                        {{ $institutionName }}
                    </flux:badge>

                    <div class="space-y-4">
                        <flux:heading level="1" size="xl" class="max-w-3xl text-balance text-white">
                            Sistem Antrian PTSP
                        </flux:heading>

                        <flux:text class="max-w-2xl text-base leading-7 text-cyan-50 sm:text-lg">
                            Layanan publik {{ $institutionName }} untuk membantu pengunjung memilih layanan, memahami persyaratan,
                            dan datang dengan nomor antrian yang sudah siap diperiksa petugas.
                        </flux:text>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <flux:button href="{{ url('/antrian') }}" variant="primary" icon="ticket" class="justify-center border-white/20 bg-white text-cyan-900 hover:bg-cyan-50">
                            Ambil Nomor Antrian
                        </flux:button>
                        <flux:button href="{{ url('/antrian/cek') }}" variant="subtle" icon="magnifying-glass" class="justify-center border border-white/20 bg-white/10 text-white hover:bg-white/16">
                            Cek Status Antrian
                        </flux:button>
                        <flux:button href="{{ url('/display') }}" variant="subtle" icon="tv" class="justify-center border border-white/20 bg-white/10 text-white hover:bg-white/16">
                            Lihat Papan Antrian
                        </flux:button>
                    </div>
                </div>

                <div class="space-y-4 rounded-[2rem] border border-white/15 bg-white/10 p-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.14)] backdrop-blur sm:p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-white/14 text-white shadow-inner shadow-white/10">
                            <flux:icon.building-office-2 class="size-6" />
                        </div>

                        <div class="space-y-2">
                            <flux:heading size="lg" class="text-white">Layanan Publik Lebih Terarah</flux:heading>
                            <flux:text class="text-sm leading-6 text-cyan-50">
                                Cek layanan, siapkan berkas, lalu datang sesuai kebutuhan tanpa harus menebak alur pelayanan.
                            </flux:text>
                        </div>
                    </div>

                    <flux:separator class="border-white/15" />

                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:card class="border-white/10 bg-white/10 p-4 text-white shadow-none">
                            <div class="flex items-start gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/20 text-cyan-100">
                                    <flux:icon.clock class="size-5" />
                                </div>
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.18em] text-cyan-100 uppercase">Jam Operasional</flux:text>
                                    <flux:text class="text-sm leading-6 text-white">
                                        {{ filled($operatingHours) ? $operatingHours : 'Informasi jam layanan belum tersedia.' }}
                                    </flux:text>
                                </div>
                            </div>
                        </flux:card>

                        <flux:card class="border-white/10 bg-white/10 p-4 text-white shadow-none">
                            <div class="flex items-start gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-400/20 text-emerald-100">
                                    <flux:icon.identification class="size-5" />
                                </div>
                                <div class="space-y-1">
                                    <flux:text class="text-xs font-semibold tracking-[0.18em] text-cyan-100 uppercase">Persiapan Pengunjung</flux:text>
                                    <flux:text class="text-sm leading-6 text-white">
                                        Pastikan identitas dan dokumen pendukung sudah dibawa sebelum menuju loket layanan.
                                    </flux:text>
                                </div>
                            </div>
                        </flux:card>
                    </div>
                </div>
            </div>
        </flux:card>

        <section class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-2">
                    <flux:badge color="blue" class="w-fit rounded-full">Katalog Layanan</flux:badge>
                    <flux:heading level="2" size="xl" class="text-slate-900">Pilih layanan yang sesuai dengan keperluan Anda</flux:heading>
                    <flux:text class="max-w-3xl text-base leading-7 text-slate-600">
                        Informasi layanan berikut membantu pengunjung melihat jenis layanan, syarat dasar, dan kuota harian
                        yang tersedia sebelum mengambil nomor antrian.
                    </flux:text>
                </div>

                <flux:badge color="zinc" class="w-fit rounded-full border border-slate-200 bg-white text-slate-700">
                    {{ $institutionName }}
                </flux:badge>
            </div>

            @isset($services)
                @if ($services->isNotEmpty())
                    <div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
                        @foreach ($services as $service)
                            <flux:card class="h-full border-slate-200 bg-white/95 p-6 shadow-[0_24px_60px_-48px_rgba(15,23,42,0.9)]">
                                <div class="flex h-full flex-col gap-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="space-y-2">
                                            <flux:heading size="lg" class="text-slate-900">{{ $service->name }}</flux:heading>

                                            <flux:text class="text-sm leading-6 text-slate-600">
                                                {{ filled($service->description) ? $service->description : 'Deskripsi layanan akan diperbarui oleh petugas PTSP.' }}
                                            </flux:text>
                                        </div>

                                        <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#ecfeff_0%,#eff6ff_100%)] text-cyan-700 shadow-inner shadow-cyan-100/80">
                                            <flux:icon.clipboard-document-list class="size-6" />
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        @if ($service->booking_enabled)
                                            <flux:badge variant="solid" color="blue" rounded>Online</flux:badge>
                                        @endif

                                        @if ($service->walk_in_enabled)
                                            <flux:badge variant="solid" color="emerald" rounded>Walk-in</flux:badge>
                                        @endif

                                        @if (! is_null($service->daily_quota))
                                            <flux:badge color="amber" rounded>Kuota/hari: {{ $service->daily_quota }}</flux:badge>
                                        @endif
                                    </div>

                                    <flux:separator />

                                    <div class="space-y-2">
                                        <flux:text class="text-xs font-semibold tracking-[0.18em] text-slate-500 uppercase">
                                            Persyaratan
                                        </flux:text>

                                        <flux:text class="whitespace-pre-line text-sm leading-6 text-slate-700">
                                            {{ filled($service->requirements) ? $service->requirements : 'Belum ada persyaratan tambahan untuk ditampilkan.' }}
                                        </flux:text>
                                    </div>

                                    <div class="mt-auto rounded-3xl bg-[linear-gradient(135deg,#f8fafc_0%,#eff6ff_100%)] px-4 py-3">
                                        <div class="flex items-start gap-3">
                                            <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-white text-cyan-700 shadow-sm">
                                                <flux:icon.information-circle class="size-5" />
                                            </div>

                                            <flux:text class="text-sm leading-6 text-slate-700">
                                                @if (! is_null($service->daily_quota))
                                                    Kuota harian ditampilkan sebagai informasi awal bagi pengunjung sebelum mengambil nomor.
                                                @else
                                                    Layanan ini dapat diakses sesuai jam operasional dan kesiapan dokumen yang dibutuhkan.
                                                @endif
                                            </flux:text>
                                        </div>
                                    </div>
                                </div>
                            </flux:card>
                        @endforeach
                    </div>
                @else
                    <flux:card class="border-dashed border-slate-300 bg-white/90 p-8 text-center">
                        <div class="mx-auto flex max-w-2xl flex-col items-center gap-4">
                            <div class="flex size-14 items-center justify-center rounded-3xl bg-[linear-gradient(135deg,#ecfeff_0%,#eff6ff_100%)] text-cyan-700">
                                <flux:icon.inbox class="size-7" />
                            </div>
                            <flux:heading size="lg" class="text-slate-900">Layanan belum tersedia</flux:heading>
                            <flux:text class="text-base leading-7 text-slate-600">
                                Daftar layanan publik akan muncul di halaman ini setelah data layanan diaktifkan oleh petugas PTSP.
                            </flux:text>
                        </div>
                    </flux:card>
                @endif
            @else
                <flux:card class="border-dashed border-slate-300 bg-white/90 p-8 text-center">
                    <div class="mx-auto flex max-w-2xl flex-col items-center gap-4">
                        <div class="flex size-14 items-center justify-center rounded-3xl bg-[linear-gradient(135deg,#ecfeff_0%,#eff6ff_100%)] text-cyan-700">
                            <flux:icon.queue-list class="size-7" />
                        </div>
                        <flux:heading size="lg" class="text-slate-900">Katalog layanan segera tersedia</flux:heading>
                        <flux:text class="text-base leading-7 text-slate-600">
                            Halaman ini sudah siap menampilkan daftar layanan begitu variabel <code>$services</code> dikirim oleh controller.
                        </flux:text>
                    </div>
                </flux:card>
            @endisset
        </section>

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
            <flux:card class="border-cyan-200 bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] p-6 sm:p-7">
                <div class="space-y-5">
                    <div class="space-y-2">
                        <flux:badge color="cyan" class="w-fit rounded-full">Panduan Pengunjung</flux:badge>
                        <flux:heading level="2" size="xl" class="text-slate-900">Datang dengan alur yang jelas</flux:heading>
                        <flux:text class="max-w-2xl text-base leading-7 text-slate-600">
                            Tiga langkah sederhana ini membantu proses pelayanan berjalan lebih cepat saat Anda tiba di PTSP.
                        </flux:text>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-3">
                        <flux:card class="border-slate-200 bg-white p-5 shadow-none">
                            <div class="space-y-3">
                                <flux:badge variant="solid" color="blue" rounded>Step 1</flux:badge>
                                <flux:heading size="lg" class="text-slate-900">Pilih Layanan</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600">
                                    Tinjau katalog layanan dan pastikan Anda memilih kebutuhan pelayanan yang tepat.
                                </flux:text>
                            </div>
                        </flux:card>

                        <flux:card class="border-slate-200 bg-white p-5 shadow-none">
                            <div class="space-y-3">
                                <flux:badge variant="solid" color="emerald" rounded>Step 2</flux:badge>
                                <flux:heading size="lg" class="text-slate-900">Isi Data Diri</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600">
                                    Masukkan data yang diperlukan dengan benar agar petugas mudah mencocokkan kebutuhan layanan.
                                </flux:text>
                            </div>
                        </flux:card>

                        <flux:card class="border-slate-200 bg-white p-5 shadow-none">
                            <div class="space-y-3">
                                <flux:badge variant="solid" color="amber" rounded>Step 3</flux:badge>
                                <flux:heading size="lg" class="text-slate-900">Tunjukkan Nomor Antrian</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600">
                                    Tunjukkan nomor antrian kepada petugas atau pantau panggilan melalui papan antrian.
                                </flux:text>
                            </div>
                        </flux:card>
                    </div>
                </div>
            </flux:card>

            <div class="grid gap-6">
                <flux:card class="border-emerald-200 bg-[linear-gradient(180deg,#f0fdf4_0%,#ffffff_100%)] p-6 sm:p-7">
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <flux:icon.clock class="size-6" />
                            </div>
                            <div class="space-y-2">
                                <flux:badge color="green" class="w-fit rounded-full">Jam Operasional</flux:badge>
                                <flux:heading size="lg" class="text-slate-900">Waktu layanan {{ $institutionName }}</flux:heading>
                            </div>
                        </div>

                        <flux:text class="text-base leading-7 text-slate-700">
                            {{ filled($operatingHours) ? $operatingHours : 'Jam operasional belum diatur.' }}
                        </flux:text>

                        <flux:text class="text-sm leading-6 text-slate-600">
                            Datang lebih awal untuk verifikasi dokumen dan memudahkan petugas memproses nomor antrian Anda.
                        </flux:text>
                    </div>
                </flux:card>

                <flux:card class="border-slate-200 bg-white p-6 sm:p-7">
                    <div class="space-y-5">
                        <div class="space-y-2">
                            <flux:badge color="zinc" class="w-fit rounded-full">Akses Cepat</flux:badge>
                            <flux:heading size="lg" class="text-slate-900">Lanjutkan ke layanan yang Anda butuhkan</flux:heading>
                        </div>

                        <div class="grid gap-3">
                            <flux:button href="{{ url('/antrian') }}" variant="primary" icon="ticket" class="justify-center">
                                Ambil Nomor Antrian
                            </flux:button>
                            <flux:button href="{{ url('/antrian/cek') }}" variant="filled" icon="magnifying-glass" class="justify-center">
                                Cek Status Antrian
                            </flux:button>
                            <flux:button href="{{ url('/display') }}" variant="subtle" icon="tv" class="justify-center">
                                Lihat Papan Antrian
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            </div>
        </section>
    </section>
</x-layouts::public>
