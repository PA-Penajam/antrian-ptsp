<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Antrian Mandiri — {{ config('institution.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen overflow-x-hidden overflow-y-auto touch-pan-y bg-gradient-to-br from-slate-50 via-white to-cyan-50 text-slate-800 antialiased">
    <livewire:kiosk-booking />
    @fluxScripts
</body>
</html>
