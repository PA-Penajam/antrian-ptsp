@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

        <title>{{ filled($title) ? $title . ' - ' . config('institution.name') : config('institution.name') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen overflow-hidden bg-zinc-900 text-white antialiased">
        <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.14),_transparent_30%),radial-gradient(circle_at_bottom_left,_rgba(16,185,129,0.14),_transparent_34%),linear-gradient(180deg,#09090b_0%,#111827_52%,#09090b_100%)]">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[linear-gradient(135deg,rgba(255,255,255,0.03)_0%,transparent_28%,transparent_72%,rgba(255,255,255,0.03)_100%)]"></div>

            <main class="relative min-h-screen overflow-hidden">
                {{ $slot }}
            </main>
        </div>

        @fluxScripts
    </body>
</html>
