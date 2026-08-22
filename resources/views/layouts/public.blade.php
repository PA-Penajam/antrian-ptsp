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
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

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
            <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-[radial-gradient(circle_at_top_left,_rgba(8,145,178,0.18),_transparent_48%),radial-gradient(circle_at_top_right,_rgba(14,116,144,0.14),_transparent_42%)]"></div>

            {{-- Sticky Header --}}
            <flux:header class="sticky top-0 z-40 border-b border-cyan-100/80 bg-white/95 shadow-[0_18px_50px_-32px_rgba(14,116,144,0.35)] backdrop-blur-md">
                <div class="mx-auto flex w-full max-w-7xl items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <a href="{{ url('/') }}" wire:navigate class="group flex min-w-0 items-center gap-3 rounded-2xl p-1 transition hover:bg-cyan-50/80 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-cyan-600">
                        @if ($institutionLogoUrl)
                            <img src="{{ $institutionLogoUrl }}" alt="Logo {{ $institutionName }}" class="h-11 w-11 rounded-2xl border border-cyan-100/90 bg-white object-contain p-1.5 shadow-xs transition-transform group-hover:scale-105 sm:h-12 sm:w-12" />
                        @else
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-700 via-cyan-800 to-teal-800 text-sm font-bold text-white shadow-xs transition-transform group-hover:scale-105 sm:h-12 sm:w-12 sm:text-base border border-cyan-600/30">
                                {{ \Illuminate\Support\Str::of($institutionName)->trim()->explode(' ')->filter()->take(2)->map(fn (string $part) => \Illuminate\Support\Str::substr($part, 0, 1))->implode('') }}
                            </div>
                        @endif

                        <div class="min-w-0 space-y-0.5">
                            <p class="truncate text-sm sm:text-base font-extrabold tracking-[0.14em] text-cyan-950 uppercase">{{ $institutionName }}</p>
                            <p class="text-xs text-slate-500 font-medium">Sistem Pelayanan Terpadu Satu Pintu</p>
                        </div>
                    </a>

                    <flux:spacer />

                    {{-- Desktop Nav --}}
                    <flux:navbar class="hidden items-center gap-1.5 rounded-full border border-cyan-200/70 bg-cyan-50/75 p-1.5 lg:flex shadow-2xs backdrop-blur-xs">
                        <flux:navbar.item href="{{ url('/') }}" :current="request()->routeIs('home') || request()->path() === '/'" wire:navigate class="rounded-full px-4 py-2 text-sm font-semibold text-cyan-900 transition hover:bg-white hover:text-cyan-950">
                            Beranda
                        </flux:navbar.item>
                        <flux:navbar.item href="{{ url('/antrian') }}" :current="request()->is('antrian')" wire:navigate class="rounded-full px-4 py-2 text-sm font-semibold text-cyan-900 transition hover:bg-white hover:text-cyan-950">
                            Ambil Antrian
                        </flux:navbar.item>
                        <flux:navbar.item href="{{ url('/antrian/cek') }}" :current="request()->is('antrian/cek')" wire:navigate class="rounded-full px-4 py-2 text-sm font-semibold text-cyan-900 transition hover:bg-white hover:text-cyan-950">
                            Cek Status
                        </flux:navbar.item>
                    </flux:navbar>

                    {{-- Operating Hours Live Beacon Chip --}}
                    <div class="hidden items-center gap-3 xl:flex">
                        <div class="flex items-center gap-2.5 rounded-2xl border border-emerald-200/90 bg-gradient-to-r from-emerald-50/90 via-teal-50/80 to-cyan-50/60 px-4 py-2 text-right shadow-2xs">
                            <div class="relative flex size-2.5 items-center justify-center shrink-0">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex size-2 rounded-full bg-emerald-600"></span>
                            </div>
                            <div>
                                <p class="text-xs font-bold tracking-wider text-emerald-800 uppercase">Jam Pelayanan</p>
                                <p class="text-xs font-semibold text-slate-800">{{ filled($institutionOperatingHours) ? $institutionOperatingHours : 'Senin - Jumat, 08:00 - 16:00' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Mobile Navigation Dropdown --}}
                    <flux:dropdown position="bottom" align="end" class="lg:hidden">
                        <flux:button variant="subtle" icon="bars-2" inset="top bottom" aria-label="Buka navigasi" class="min-h-[44px] min-w-[44px] rounded-2xl border border-cyan-200 bg-cyan-50 text-cyan-950 hover:bg-cyan-100 shadow-2xs touch-manipulation" />

                        <flux:menu class="w-80 max-w-[calc(100vw-2rem)] rounded-3xl border border-cyan-100/90 bg-white/95 backdrop-blur-xl p-3 shadow-2xl shadow-cyan-950/15">
                            <div class="px-3 pb-3 pt-2">
                                <p class="text-xs font-bold tracking-[0.18em] text-cyan-800 uppercase">Navigasi Publik</p>
                                <p class="mt-1 text-xs text-slate-600">Akses cepat layanan antrian PTSP.</p>
                            </div>

                            <flux:navlist>
                                <flux:navlist.item href="{{ url('/') }}" :current="request()->routeIs('home') || request()->path() === '/'" icon="home" wire:navigate class="rounded-xl">
                                    Beranda
                                </flux:navlist.item>
                                <flux:navlist.item href="{{ url('/antrian') }}" :current="request()->is('antrian')" icon="ticket" wire:navigate class="rounded-xl">
                                    Ambil Antrian
                                </flux:navlist.item>
                                <flux:navlist.item href="{{ url('/antrian/cek') }}" :current="request()->is('antrian/cek')" icon="magnifying-glass" wire:navigate class="rounded-xl">
                                    Cek Status
                                </flux:navlist.item>
                            </flux:navlist>

                            <div class="mt-3 rounded-2xl bg-[linear-gradient(135deg,#ecfeff_0%,#f0fdfa_100%)] p-3.5 text-xs text-slate-700 border border-cyan-100">
                                <p class="font-bold text-cyan-900">{{ $institutionName }}</p>
                                <p class="mt-1 font-medium text-slate-600">{{ filled($institutionOperatingHours) ? $institutionOperatingHours : 'Senin - Jumat, 08:00 - 16:00' }}</p>
                            </div>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </flux:header>

            {{-- Main Content Canvas --}}
            <div class="relative flex-1">
                <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
                    {{ $slot }}
                    @yield('content')
                </main>
            </div>

            {{-- Footer --}}
            <footer class="border-t border-cyan-100 bg-[linear-gradient(180deg,#f8fcfd_0%,#eef8fb_45%,#e0f2fe_100%)] pb-[max(2rem,env(safe-area-inset-bottom))]">
                <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.85fr)_minmax(0,0.85fr)] lg:px-8 lg:py-10">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white/95 px-3.5 py-1 text-xs font-bold tracking-[0.16em] text-cyan-800 uppercase shadow-2xs">
                            <flux:icon.building-office class="size-3.5 text-cyan-700" />
                            <span>Layanan Publik PTSP</span>
                        </div>
                        <div class="space-y-2">
                            <p class="text-base sm:text-lg font-bold text-slate-900">{{ $institutionName }}</p>
                            <p class="max-w-2xl text-xs sm:text-sm leading-relaxed text-slate-600">
                                Pelayanan Terpadu Satu Pintu yang dirancang sederhana, transparan, dan mudah diakses untuk membantu masyarakat mendapatkan pelayanan hukum dan peradilan secara tertib dan nyaman.
                            </p>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200/80 bg-emerald-50/80 px-3 py-1.5 text-xs font-semibold text-emerald-800">
                            <flux:icon.check-circle class="size-4 text-emerald-600 shrink-0" />
                            <span>Pelayanan Ramah, Pasti, & Bebas Pungli</span>
                        </div>
                    </div>

                    <div class="space-y-2.5 rounded-3xl border border-cyan-100/90 bg-white/95 p-6 shadow-xs backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-cyan-800">
                            <flux:icon.map-pin class="size-4 shrink-0 text-cyan-600" />
                            <p class="text-xs font-bold tracking-[0.18em] uppercase">Alamat Kantor</p>
                        </div>
                        <p class="text-xs sm:text-sm leading-relaxed text-slate-700">{{ filled($institutionAddress) ? $institutionAddress : 'Alamat institusi belum diatur dalam konfigurasi.' }}</p>
                    </div>

                    <div class="grid gap-4 rounded-3xl border border-emerald-100/90 bg-white/95 p-6 shadow-xs backdrop-blur-sm">
                        <div>
                            <div class="flex items-center gap-2 text-emerald-800">
                                <flux:icon.phone class="size-4 shrink-0 text-emerald-600" />
                                <p class="text-xs font-bold tracking-[0.18em] uppercase">Telepon & Kontak</p>
                            </div>
                            <p class="mt-1.5 text-xs sm:text-sm font-semibold text-slate-800">
                                @if (filled($institutionPhone))
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $institutionPhone) }}" class="transition hover:text-cyan-700 underline decoration-cyan-300 underline-offset-4">
                                        {{ $institutionPhone }}
                                    </a>
                                @else
                                    Nomor telepon belum diatur.
                                @endif
                            </p>
                        </div>

                        <div class="h-px bg-gradient-to-r from-emerald-100 via-cyan-100 to-transparent"></div>

                        <div>
                            <div class="flex items-center gap-2 text-emerald-800">
                                <flux:icon.clock class="size-4 shrink-0 text-emerald-600" />
                                <p class="text-xs font-bold tracking-[0.18em] uppercase">Jam Operasional</p>
                            </div>
                            <p class="mt-1.5 text-xs sm:text-sm font-semibold text-slate-800">{{ filled($institutionOperatingHours) ? $institutionOperatingHours : 'Senin - Jumat, 08:00 - 16:00' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Bottom Subfooter Bar --}}
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="h-px bg-gradient-to-r from-transparent via-cyan-200/80 to-transparent"></div>
                    <div class="flex flex-col items-center justify-between gap-3 py-4 text-center sm:flex-row sm:text-left">
                        <p class="text-xs text-slate-500">
                            &copy; {{ date('Y') }} {{ $institutionName }}. Hak Cipta Dilindungi.
                        </p>
                        <div class="flex items-center gap-4 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                Sistem Antrian PTSP Online
                            </span>
                            <span>&bull;</span>
                            <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-1 text-slate-500 transition hover:text-cyan-800 font-medium">
                                <flux:icon.lock-closed class="size-3 text-slate-400" />
                                <span>Akses Petugas</span>
                            </a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        @include('partials.flux-scripts')
    </body>
</html>

