<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Workstation {{ $counter->name }} - {{ config('institution.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
    <div class="flex min-h-screen flex-col bg-[radial-gradient(circle_at_top,_rgba(34,197,94,0.16),_transparent_28%),radial-gradient(circle_at_right,_rgba(14,165,233,0.12),_transparent_28%),linear-gradient(135deg,#09090b_0%,#111827_48%,#020617_100%)]">
        <header class="border-b border-zinc-800/80 bg-zinc-950/70 px-4 py-3 backdrop-blur md:px-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-cyan-300/85">Mode Layar Loket</p>
                    <h1 class="text-lg font-semibold text-white md:text-2xl">{{ $counter->name }} - {{ $counter->queuePool?->name ?? 'Pool' }}</h1>
                </div>
                <flux:button :href="route('dashboard')" variant="ghost" icon="arrow-left" wire:navigate>
                    Kembali Dashboard
                </flux:button>
            </div>
        </header>

        <main class="flex-1 px-4 py-4 md:px-6 md:py-6">
            <livewire:dashboard.petugas-dashboard :counter-id="$counter->id" :full-screen="true" />
        </main>
    </div>

    @include('partials.flux-scripts')
</body>
</html>
