<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

        <title>{{ filled($title ?? null) ? $title . ' - ' . config('institution.name') : config('institution.name') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/tv-display.css', 'resources/js/tv-display.js'])
        @fluxAppearance
    </head>
    <body class="bg-zinc-950 text-white min-h-screen overflow-hidden">
        <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(34,197,94,0.16),_transparent_26%),radial-gradient(circle_at_right,_rgba(14,165,233,0.16),_transparent_30%),linear-gradient(135deg,#09090b_0%,#111827_46%,#020617_100%)]">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(120deg,rgba(255,255,255,0.04)_0%,transparent_26%,transparent_74%,rgba(255,255,255,0.04)_100%)]"></div>

            <main class="relative min-h-screen overflow-hidden">
                {{ $slot }}
            </main>
        </div>

        @include('partials.flux-scripts')
    </body>
</html>
