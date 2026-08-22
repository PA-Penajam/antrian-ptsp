@php
    $institutionName = config('institution.name', config('app.name', 'Antrian PTSP'));
    $institutionOperatingHours = config('institution.operating_hours', 'Senin - Jumat, 08:00 - 16:00 WIB');
    $institutionLogoPath = config('institution.logo_path');
    $institutionLogoUrl = blank($institutionLogoPath)
        ? null
        : (\Illuminate\Support\Str::startsWith($institutionLogoPath, ['http://', 'https://', '/'])
            ? $institutionLogoPath
            : \Illuminate\Support\Facades\Storage::url($institutionLogoPath));
    $initials = \Illuminate\Support\Str::of($institutionName)
        ->trim()
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $part) => \Illuminate\Support\Str::substr($part, 0, 1))
        ->implode('');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[linear-gradient(180deg,#eef8fb_0%,#f8fcfd_42%,#ffffff_100%)] dark:bg-[radial-gradient(ellipse_at_top,_#0c1a24_0%,_#09090b_100%)] text-slate-900 dark:text-zinc-100 antialiased">
        {{-- Ambient decorative lightings and grid mesh --}}
        <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden" aria-hidden="true">
            <div class="absolute -top-40 -right-40 size-[32rem] rounded-full bg-cyan-400/18 dark:bg-cyan-700/15 blur-[100px]"></div>
            <div class="absolute -bottom-40 -left-40 size-[32rem] rounded-full bg-teal-400/18 dark:bg-teal-700/15 blur-[100px]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#0e7490_1px,transparent_1px)] [background-size:28px_28px] opacity-[0.07] dark:opacity-[0.04]"></div>
        </div>

        <div class="flex min-h-screen flex-col justify-between p-4 sm:p-6 lg:p-8">
            {{-- Top Bar: Public navigation and operating status --}}
            <header class="mx-auto flex w-full max-w-5xl items-center justify-between gap-4 py-2">
                <a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5 rounded-2xl border border-cyan-200/80 dark:border-zinc-800 bg-white/90 dark:bg-zinc-900/90 px-3.5 py-2 text-xs font-semibold text-cyan-950 dark:text-cyan-100 shadow-2xs backdrop-blur-md transition hover:border-cyan-300 hover:bg-cyan-50/80 dark:hover:bg-zinc-800 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-cyan-600" wire:navigate>
                    <flux:icon.arrow-left class="size-3.5 text-cyan-700 dark:text-cyan-400 transition-transform group-hover:-translate-x-0.5" />
                    <span>{{ __('Beranda Publik') }}</span>
                    <span class="hidden text-cyan-700/70 dark:text-cyan-400/70 sm:inline">| {{ __('Public Portal') }}</span>
                </a>

                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 dark:border-emerald-900/60 bg-emerald-50/90 dark:bg-emerald-950/40 px-3.5 py-1.5 shadow-2xs backdrop-blur-md">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-emerald-950 dark:text-emerald-200 tracking-wide">
                        {{ $institutionOperatingHours }}
                    </span>
                </div>
            </header>

            {{-- Main Content Area: Elevated Glass Card --}}
            <main class="my-auto flex w-full flex-col items-center justify-center py-6 sm:py-10">
                <div class="w-full max-w-md sm:max-w-lg">
                    {{-- Institution Brand Header --}}
                    <div class="mb-6 flex flex-col items-center text-center">
                        <a href="{{ route('home') }}" class="group mb-3 flex flex-col items-center gap-3 transition-transform focus-visible:outline-hidden" wire:navigate>
                            @if ($institutionLogoUrl)
                                <img src="{{ $institutionLogoUrl }}" alt="Logo {{ $institutionName }}" class="size-16 rounded-3xl border border-cyan-200/90 dark:border-zinc-700 bg-white p-2 shadow-md shadow-cyan-900/5 transition-transform group-hover:scale-105 sm:size-20" />
                            @else
                                <div class="flex size-16 items-center justify-center rounded-3xl border border-cyan-200/80 dark:border-cyan-800 bg-gradient-to-br from-cyan-700 via-cyan-600 to-teal-800 text-xl font-black text-white shadow-lg shadow-cyan-700/20 transition-transform group-hover:scale-105 sm:size-20 sm:text-2xl">
                                    {{ filled($initials) ? $initials : 'PA' }}
                                </div>
                            @endif

                            <div class="space-y-0.5">
                                <p class="text-xs font-bold tracking-[0.2em] text-cyan-800 dark:text-cyan-400 uppercase">
                                    {{ $institutionName }}
                                </p>
                                <p class="text-xs font-medium text-slate-500 dark:text-zinc-400">
                                    Pelayanan Terpadu Satu Pintu (PTSP)
                                </p>
                            </div>
                        </a>
                    </div>

                    {{-- Glass Card Container --}}
                    <div class="relative overflow-hidden rounded-3xl border border-cyan-200/80 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/90 p-6 sm:p-9 shadow-[0_24px_60px_-24px_rgba(14,116,144,0.22)] backdrop-blur-xl">
                        {{-- Subtle card top accent light --}}
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-cyan-500/60 to-transparent"></div>
                        
                        {{ $slot }}
                    </div>

                    {{-- Module Shortcut Footnote (Kiosk & TV Display links) --}}
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-center text-xs text-slate-500 dark:text-zinc-400">
                        <span>{{ __('Akses Terminal Khusus:') }}</span>
                        <a href="{{ route('kiosk.login') }}" class="font-semibold text-cyan-700 dark:text-cyan-400 hover:text-cyan-800 hover:underline inline-flex items-center gap-1">
                            <flux:icon.printer class="size-3" />
                            {{ __('Kiosk Tiket') }}
                        </a>
                        <span class="text-slate-300 dark:text-zinc-700">•</span>
                        <a href="{{ route('tv-display.login') }}" class="font-semibold text-cyan-700 dark:text-cyan-400 hover:text-cyan-800 hover:underline inline-flex items-center gap-1">
                            <flux:icon.computer-desktop class="size-3" />
                            {{ __('TV Display Ruang Tunggu') }}
                        </a>
                    </div>
                </div>
            </main>

            {{-- Footer: Security Note & Copyright --}}
            <footer class="mx-auto flex w-full max-w-5xl flex-col items-center justify-between gap-2 py-4 text-center text-xs text-slate-500 dark:text-zinc-500 sm:flex-row">
                <div class="inline-flex items-center gap-1.5">
                    <flux:icon.shield-check class="size-4 text-emerald-600 dark:text-emerald-500" />
                    <span>{{ __('Sistem Keamanan Terautentikasi Pengadilan Agama') }}</span>
                </div>
                <p>&copy; {{ date('Y') }} {{ $institutionName }}. Hak Cipta Dilindungi.</p>
            </footer>
        </div>

        @include('partials.flux-scripts')
    </body>
</html>
