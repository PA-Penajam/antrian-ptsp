@php
    $institutionName = config('institution.name');
    $operatingHours = config('institution.operating_hours');
@endphp

<x-layouts::public :title="'Beranda - ' . config('institution.name')">
    <flux:main container>
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 py-6 sm:gap-10 sm:py-8 lg:gap-12">
            <flux:card class="overflow-hidden border-cyan-200 bg-[linear-gradient(180deg,#f6fcff_0%,#edf8fd_55%,#ffffff_100%)] p-0 shadow-[0_30px_80px_-48px_rgba(14,116,144,0.45)]">
                <div class="grid gap-8 px-6 py-8 sm:px-8 sm:py-10 lg:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.85fr)] lg:px-10">
                    <div class="space-y-5 text-center lg:text-left">
                        <div class="flex justify-center lg:justify-start">
                            <flux:badge color="sky" rounded icon="building-office-2">
                                {{ $institutionName }}
                            </flux:badge>
                        </div>

                        <div class="space-y-3">
                            <flux:heading level="1" size="xl" class="text-balance text-slate-900">
                                Sistem Antrian PTSP
                            </flux:heading>

                            <flux:subheading class="mx-auto max-w-3xl text-base leading-7 text-slate-600 lg:mx-0 sm:text-lg">
                                {{ $institutionName }} menghadirkan layanan antrian publik yang lebih cepat, jelas, dan tertib untuk membantu masyarakat memilih layanan, memahami persyaratan, dan datang dengan persiapan yang tepat.
                            </flux:subheading>
                        </div>

                        <div class="flex flex-col justify-center gap-3 sm:flex-row lg:justify-start">
                            <flux:button href="{{ url('/antrian') }}" variant="primary" icon="ticket" class="justify-center">
                                Ambil Nomor Antrian
                            </flux:button>

                            <flux:button href="{{ url('/antrian/cek') }}" variant="filled" icon="magnifying-glass" class="justify-center">
                                Cek Status Antrian
                            </flux:button>
                        </div>
                    </div>

                    <flux:card class="space-y-5 border-cyan-100 bg-white/90 p-6 shadow-none">
                        <div class="space-y-2">
                            <flux:heading size="lg" class="text-slate-900">Informasi Pelayanan</flux:heading>
                            <flux:text class="text-sm leading-6 text-slate-600">
                                Gunakan katalog layanan untuk menyiapkan berkas dan cek tiket tanpa perlu antre ulang di kantor.
                            </flux:text>
                        </div>

                        <div class="grid gap-3">
                            <flux:card class="border-slate-200 bg-slate-50 p-4 shadow-none">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                                        <flux:icon.clock class="size-5" />
                                    </div>

                                    <div class="space-y-1">
                                        <flux:text class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase">Jam Operasional</flux:text>
                                        <flux:text class="text-sm leading-6 text-slate-700">
                                            {{ filled($operatingHours) ? $operatingHours : 'Informasi jam layanan belum tersedia.' }}
                                        </flux:text>
                                    </div>
                                </div>
                            </flux:card>

                            <flux:card class="border-slate-200 bg-slate-50 p-4 shadow-none">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <flux:icon.identification class="size-5" />
                                    </div>

                                    <div class="space-y-1">
                                        <flux:text class="text-xs font-semibold tracking-[0.18em] text-emerald-700 uppercase">Persiapan Pengunjung</flux:text>
                                        <flux:text class="text-sm leading-6 text-slate-700">
                                            Bawa identitas dan dokumen pendukung agar verifikasi layanan berjalan lebih singkat di loket PTSP.
                                        </flux:text>
                                    </div>
                                </div>
                            </flux:card>
                        </div>
                    </flux:card>
                </div>
            </flux:card>

            <section class="space-y-4">
                <div class="space-y-2">
                    <flux:badge color="blue" rounded>Katalog Layanan</flux:badge>
                    <flux:heading level="2" size="lg" class="text-slate-900">Layanan Tersedia</flux:heading>
                    <flux:text class="max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
                        Berikut daftar layanan aktif yang dapat diakses masyarakat melalui PTSP {{ $institutionName }}. Perhatikan jenis layanan, persyaratan, dan kuota hariannya.
                    </flux:text>
                </div>

                @if ($services->isNotEmpty())
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($services as $service)
                            <flux:card class="space-y-4 border-slate-200 bg-white p-5 shadow-[0_24px_60px_-52px_rgba(15,23,42,0.4)]">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-1">
                                        <flux:heading size="base" class="text-slate-900">{{ $service->name }}</flux:heading>
                                        <flux:text class="text-xs font-medium tracking-[0.16em] text-slate-500 uppercase">Informasi Layanan</flux:text>
                                    </div>

                                    <flux:badge color="zinc" rounded>
                                        {{ filled($service->letter_code) ? $service->letter_code : 'UMUM' }}
                                    </flux:badge>
                                </div>

                                @if ($service->description)
                                    <flux:text class="text-sm leading-6 text-slate-600">{{ $service->description }}</flux:text>
                                @endif

                                <div class="flex flex-wrap gap-2">
                                    @if ($service->booking_enabled)
                                        <flux:badge color="green" size="sm">Booking Online</flux:badge>
                                    @endif

                                    @if ($service->walk_in_enabled)
                                        <flux:badge color="blue" size="sm">Walk-in</flux:badge>
                                    @endif

                                    @if ($service->daily_quota)
                                        <flux:badge color="zinc" size="sm">Kuota: {{ $service->daily_quota }}/hari</flux:badge>
                                    @endif
                                </div>

                                <flux:separator />

                                <div class="space-y-2">
                                    <flux:text class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase">Persyaratan</flux:text>
                                    <flux:text class="whitespace-pre-line text-sm leading-6 text-slate-700">
                                        {{ filled($service->requirements) ? $service->requirements : 'Persyaratan layanan akan diinformasikan oleh petugas PTSP.' }}
                                    </flux:text>
                                </div>
                            </flux:card>
                        @endforeach
                    </div>
                @else
                    <flux:card class="border-dashed border-slate-300 bg-white/90 p-8 text-center">
                        <div class="mx-auto flex max-w-2xl flex-col items-center gap-4">
                            <div class="flex size-14 items-center justify-center rounded-3xl bg-cyan-100 text-cyan-700">
                                <flux:icon.inbox class="size-7" />
                            </div>

                            <div class="space-y-2">
                                <flux:heading size="lg" class="text-slate-900">Layanan belum tersedia</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600 sm:text-base">
                                    Daftar layanan publik akan tampil di halaman ini setelah data layanan diaktifkan oleh petugas PTSP.
                                </flux:text>
                            </div>
                        </div>
                    </flux:card>
                @endif
            </section>

            <section>
                <flux:card class="space-y-5 border-cyan-200 bg-[linear-gradient(180deg,#ffffff_0%,#f4fbff_100%)] p-6 sm:p-7">
                    <div class="space-y-2">
                        <flux:badge color="cyan" rounded>Panduan Pengunjung</flux:badge>
                        <flux:heading level="2" size="lg" class="text-slate-900">Datang dengan alur yang jelas</flux:heading>
                        <flux:text class="max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                            Tiga langkah sederhana ini membantu masyarakat mendapatkan layanan yang lebih tertib sejak sebelum datang ke kantor pengadilan.
                        </flux:text>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <flux:card class="border-slate-200 bg-white p-5 shadow-none">
                            <div class="space-y-3">
                                <flux:badge color="blue" rounded>Step 1</flux:badge>
                                <flux:heading size="base" class="text-slate-900">Pilih Layanan</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600">
                                    Tinjau katalog layanan dan pilih jenis pelayanan yang paling sesuai dengan kebutuhan Anda.
                                </flux:text>
                            </div>
                        </flux:card>

                        <flux:card class="border-slate-200 bg-white p-5 shadow-none">
                            <div class="space-y-3">
                                <flux:badge color="green" rounded>Step 2</flux:badge>
                                <flux:heading size="base" class="text-slate-900">Isi Data Diri</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600">
                                    Lengkapi data yang diperlukan secara benar agar petugas mudah memverifikasi kebutuhan layanan.
                                </flux:text>
                            </div>
                        </flux:card>

                        <flux:card class="border-slate-200 bg-white p-5 shadow-none">
                            <div class="space-y-3">
                                <flux:badge color="amber" rounded>Step 3</flux:badge>
                                <flux:heading size="base" class="text-slate-900">Tunjukkan Nomor Antrian</flux:heading>
                                <flux:text class="text-sm leading-6 text-slate-600">
                                    Pantau display dan tunjukkan nomor antrian kepada petugas saat layanan Anda dipanggil.
                                </flux:text>
                            </div>
                        </flux:card>
                    </div>

                    <flux:separator />

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <flux:text class="text-sm text-slate-600">Petugas PTSP? Masuk ke panel administrasi.</flux:text>
                        <div class="flex gap-2">
                            @auth
                                <flux:button href="{{ route('dashboard') }}" variant="subtle" icon="squares-2x2" size="sm">
                                    Dashboard
                                </flux:button>
                            @else
                                <flux:button href="{{ route('login') }}" variant="subtle" icon="arrow-right-start-on-rectangle" size="sm">
                                    Masuk
                                </flux:button>
                            @endauth
                        </div>
                    </div>
                </flux:card>
            </section>
        </div>
    </flux:main>
</x-layouts::public>
