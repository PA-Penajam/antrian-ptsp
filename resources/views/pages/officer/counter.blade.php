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
<body 
    x-data="{
        currentTime: '',
        currentDate: '',
        isFullscreen: false,
        clockTimer: null,
        
        init() {
            this.updateClock();
            this.clockTimer = setInterval(() => this.updateClock(), 1000);
            
            document.addEventListener('fullscreenchange', () => {
                this.isFullscreen = !!document.fullscreenElement;
            });
        },
        
        updateClock() {
            try {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
                this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
            } catch (e) {}
        },
        
        toggleFullscreen() {
            try {
                if (!document.fullscreenElement) {
                    const el = document.documentElement;
                    if (el.requestFullscreen) {
                        el.requestFullscreen().catch(() => {});
                    }
                    this.isFullscreen = true;
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(() => {});
                    }
                    this.isFullscreen = false;
                }
            } catch (e) {}
        }
    }"
    class="min-h-screen bg-zinc-950 text-zinc-100 antialiased"
>
    <div class="flex min-h-screen flex-col bg-[radial-gradient(circle_at_top,_rgba(6,182,212,0.12),_transparent_32%),radial-gradient(circle_at_right,_rgba(16,185,129,0.08),_transparent_28%),linear-gradient(135deg,#09090b_0%,#111827_50%,#09090b_100%)]">
        <header class="border-b border-zinc-800/80 bg-zinc-950/80 px-4 py-3 backdrop-blur-md md:px-6">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400">
                        <flux:icon.building-office class="size-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base font-extrabold text-white md:text-xl tracking-tight">{{ $counter->name }}</h1>
                            <flux:badge size="sm" color="cyan" class="font-semibold">
                                {{ $counter->queuePool?->name ?? 'Pool' }}
                            </flux:badge>
                        </div>
                        <p class="text-xs text-zinc-400 hidden sm:block">{{ config('institution.name') }}</p>
                    </div>
                </div>

                <!-- Clock & Action Tools -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Live Digital Clock -->
                    <div class="hidden sm:flex items-center gap-2 rounded-xl border border-zinc-800 bg-zinc-900/90 px-3 py-1.5 text-xs font-mono shadow-xs">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-zinc-400" x-text="currentDate"></span>
                        <span class="font-bold text-white tabular-nums tracking-wider" x-text="currentTime">00:00:00</span>
                    </div>

                    <!-- Fullscreen Toggle Button -->
                    <button 
                        x-on:click="toggleFullscreen()"
                        type="button" 
                        class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-800 bg-zinc-900/90 px-2.5 py-1.5 text-xs font-semibold text-zinc-300 hover:bg-zinc-800 hover:text-white transition-colors"
                        title="Toggle Fullscreen Layar"
                        aria-label="Toggle Fullscreen Layar"
                    >
                        <flux:icon.arrows-pointing-out class="size-4" />
                        <span class="hidden md:inline" x-text="isFullscreen ? 'Kecilkan' : 'Fullscreen'"></span>
                    </button>

                    <flux:button :href="route('dashboard')" variant="ghost" size="sm" icon="arrow-left" wire:navigate class="text-zinc-400 hover:text-white">
                        <span class="hidden sm:inline">Dashboard</span>
                    </flux:button>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-4 md:px-6 md:py-6 max-w-7xl w-full mx-auto">
            <livewire:dashboard.petugas-dashboard :counter-id="$counter->id" :full-screen="true" />
        </main>
    </div>

    @include('partials.flux-scripts')
</body>
</html>
