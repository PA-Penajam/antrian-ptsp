@php
    $institutionName = config('institution.name', config('app.name', 'Laravel'));
    $institutionAddress = config('institution.address');
    $institutionPhone = config('institution.phone');
    $institutionOperatingHours = config('institution.operating_hours');
    $institutionLogoPath = config('institution.logo_path');
    $institutionLogoUrl = blank($institutionLogoPath)
        ? null
        : (\Illuminate\Support\Str::startsWith($institutionLogoPath, ['http://', 'https://', '/'])
            ? $institutionLogoPath
            : \Illuminate\Support\Facades\Storage::url($institutionLogoPath));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>
            {{ filled($title ?? null) ? $title . ' - ' . $institutionName : $institutionName }}
        </title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-[linear-gradient(180deg,#eef8fb_0%,#f8fcfd_42%,#ffffff_100%)] text-slate-900 antialiased">
        <div class="relative flex min-h-screen flex-col overflow-hidden">
            <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(circle_at_top_left,_rgba(8,145,178,0.18),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(14,116,144,0.14),_transparent_38%)]"></div>

            <flux:header class="border-b border-cyan-100/80 bg-white/92 shadow-[0_18px_50px_-32px_rgba(14,116,144,0.4)] backdrop-blur">
                <div class="mx-auto flex w-full max-w-7xl items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ url('/') }}" wire:navigate class="flex min-w-0 items-center gap-3 rounded-2xl px-1 py-1 transition hover:bg-cyan-50/80">
                        @if ($institutionLogoUrl)
                            <img src="{{ $institutionLogoUrl }}" alt="Logo {{ $institutionName }}" class="h-12 w-12 rounded-2xl border border-cyan-100 bg-white object-contain p-2 shadow-sm sm:h-14 sm:w-14" />
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0f766e_0%,#0f4c81_100%)] text-sm font-semibold text-white shadow-sm sm:h-14 sm:w-14 sm:text-base">
                                {{ \Illuminate\Support\Str::of($institutionName)->trim()->explode(' ')->filter()->take(2)->map(fn (string $part) => \Illuminate\Support\Str::substr($part, 0, 1))->implode('') }}
                            </div>
                        @endif

                        <div class="min-w-0 space-y-1">
                            <p class="truncate text-base font-semibold tracking-[0.18em] text-cyan-800 uppercase">{{ $institutionName }}</p>
                            <p class="text-sm leading-5 text-slate-600 sm:text-[15px]">Sistem Pelayanan Terpadu Satu Pintu</p>
                        </div>
                    </a>

                    <flux:spacer />

                    <flux:navbar class="hidden items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50/70 p-1.5 lg:flex">
                        <flux:navbar.item href="{{ url('/') }}" :current="request()->routeIs('home') || request()->path() === '/'" wire:navigate>
                            Beranda
                        </flux:navbar.item>
                        <flux:navbar.item href="{{ url('/antrian') }}" :current="request()->is('antrian')" wire:navigate>
                            Ambil Antrian
                        </flux:navbar.item>
                        <flux:navbar.item href="{{ url('/antrian/cek') }}" :current="request()->is('antrian/cek')" wire:navigate>
                            Cek Status
                        </flux:navbar.item>
                    </flux:navbar>

                    <div class="hidden items-center gap-3 xl:flex">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-2 text-right shadow-sm">
                            <p class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase">Jam Layanan</p>
                            <p class="text-sm font-medium text-slate-700">{{ filled($institutionOperatingHours) ? $institutionOperatingHours : 'Informasi belum tersedia' }}</p>
                        </div>
                    </div>

                    <flux:dropdown position="bottom" align="end" class="lg:hidden">
                        <flux:button variant="subtle" icon="bars-2" inset="top bottom" aria-label="Buka navigasi" class="rounded-2xl border border-cyan-100 bg-cyan-50 text-cyan-900" />

                        <flux:menu class="w-80 max-w-[calc(100vw-2rem)] rounded-3xl border border-cyan-100 bg-white p-2 shadow-2xl shadow-cyan-900/10">
                            <div class="px-3 pb-3 pt-2">
                                <p class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase">Navigasi Publik</p>
                                <p class="mt-1 text-sm text-slate-600">Akses cepat untuk layanan antrian PTSP.</p>
                            </div>

                            <flux:navlist>
                                <flux:navlist.item href="{{ url('/') }}" :current="request()->routeIs('home') || request()->path() === '/'" icon="home" wire:navigate>
                                    Beranda
                                </flux:navlist.item>
                                <flux:navlist.item href="{{ url('/antrian') }}" :current="request()->is('antrian')" icon="ticket" wire:navigate>
                                    Ambil Antrian
                                </flux:navlist.item>
                                <flux:navlist.item href="{{ url('/antrian/cek') }}" :current="request()->is('antrian/cek')" icon="magnifying-glass" wire:navigate>
                                    Cek Status
                                </flux:navlist.item>
                            </flux:navlist>

                            <div class="mt-3 rounded-2xl bg-[linear-gradient(135deg,#ecfeff_0%,#f0fdfa_100%)] px-4 py-3 text-sm text-slate-700">
                                <p class="font-semibold text-cyan-900">{{ $institutionName }}</p>
                                <p class="mt-1">{{ filled($institutionOperatingHours) ? $institutionOperatingHours : 'Jam layanan belum tersedia' }}</p>
                            </div>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </flux:header>

            <div class="relative flex-1">
                <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-40 bg-[linear-gradient(180deg,rgba(6,95,70,0.08),transparent)]"></div>

                <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
                    {{ $slot }}
                    @yield('content')
                </main>
            </div>

            <footer class="border-t border-cyan-100 bg-[linear-gradient(180deg,#f8fdff_0%,#ecfeff_100%)]">
                <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.8fr)_minmax(0,0.9fr)] lg:px-8 lg:py-10">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-white px-3 py-1 text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase shadow-sm">
                            Layanan Publik PTSP
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-slate-900">{{ $institutionName }}</p>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Sistem layanan yang dirancang sederhana, jelas, dan mudah diakses untuk membantu masyarakat mengambil serta memantau antrian tanpa kebingungan.</p>
                        </div>
                    </div>

                    <div class="space-y-3 rounded-3xl border border-cyan-100 bg-white/80 p-5 shadow-sm">
                        <p class="text-xs font-semibold tracking-[0.18em] text-cyan-700 uppercase">Alamat</p>
                        <p class="text-sm leading-6 text-slate-700">{{ filled($institutionAddress) ? $institutionAddress : 'Alamat institusi belum diatur.' }}</p>
                    </div>

                    <div class="grid gap-3 rounded-3xl border border-emerald-100 bg-white/90 p-5 shadow-sm">
                        <div>
                            <p class="text-xs font-semibold tracking-[0.18em] text-emerald-700 uppercase">Telepon</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">{{ filled($institutionPhone) ? $institutionPhone : 'Nomor telepon belum diatur.' }}</p>
                        </div>

                        <div class="h-px bg-gradient-to-r from-emerald-100 via-cyan-100 to-transparent"></div>

                        <div>
                            <p class="text-xs font-semibold tracking-[0.18em] text-emerald-700 uppercase">Jam Operasional</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">{{ filled($institutionOperatingHours) ? $institutionOperatingHours : 'Jam operasional belum diatur.' }}</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
