<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

        <title>{{ filled($title ?? null) ? $title . ' - ' . config('institution.name') : config('institution.name') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/kiosk.css', 'resources/js/kiosk.js'])
        @livewireStyles
        @fluxAppearance
    </head>
    <body class="min-h-screen overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-100 via-white to-cyan-100 text-slate-800 antialiased">
        {{-- Animated Background --}}
        <div class="fixed inset-0 overflow-hidden">
            {{-- Gradient Orbs --}}
            <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-cyan-400/20 blur-3xl animate-pulse" style="animation-duration: 8s;"></div>
            <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-blue-400/20 blur-3xl animate-pulse" style="animation-duration: 10s; animation-delay: 2s;"></div>
            <div class="absolute left-1/2 top-1/2 h-[600px] w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-emerald-400/10 blur-3xl animate-pulse" style="animation-duration: 12s; animation-delay: 4s;"></div>
            
            {{-- Subtle Grid Pattern --}}
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(to right, #0f172a 1px, transparent 1px), linear-gradient(to bottom, #0f172a 1px, transparent 1px); background-size: 60px 60px;"></div>
        </div>

        <div class="relative min-h-screen">
            <main class="relative min-h-screen">
                {{ $slot }}
            </main>
        </div>

        {{-- Epson ePOS SDK untuk thermal printing --}}
        @if (config('services.thermal_printer.enabled'))
            <script src="{{ asset('vendor/epson/epos-2.27.0.js') }}"></script>
            @vite(['resources/js/thermal-printer.js'])
        @endif

        @livewireScripts

        @php
            $fluxManifestPath = \Flux\Flux::pro()
                ? base_path('vendor/livewire/flux-pro/dist/manifest.json')
                : base_path('vendor/livewire/flux/dist/manifest.json');

            $fluxVersion = null;

            if (is_file($fluxManifestPath)) {
                $fluxManifest = json_decode((string) file_get_contents($fluxManifestPath), true);
                $fluxVersion = $fluxManifest['/flux.js'] ?? null;
            }

            $fluxScriptUrl = route('flux.script');

            if ($fluxVersion !== null && $fluxVersion !== '') {
                $fluxScriptUrl .= '?id='.$fluxVersion;
            }
        @endphp
        <script src="{{ $fluxScriptUrl }}" data-navigate-once></script>
    </body>
</html>
