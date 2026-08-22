@props([
    'sidebar' => false,
])

@php
    $institutionName = config('institution.name', config('app.name', 'Antrian PTSP'));
    $institutionLogoPath = config('institution.logo_path');
    $institutionLogoUrl = blank($institutionLogoPath)
        ? null
        : (\Illuminate\Support\Str::startsWith($institutionLogoPath, ['http://', 'https://', '/'])
            ? $institutionLogoPath
            : \Illuminate\Support\Facades\Storage::url($institutionLogoPath));
    $monogram = \Illuminate\Support\Str::of($institutionName)
        ->trim()
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $part) => \Illuminate\Support\Str::substr($part, 0, 1))
        ->implode('');
    if (blank($monogram)) {
        $monogram = 'PA';
    }
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$institutionName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-700 via-cyan-800 to-teal-800 text-white shadow-xs border border-cyan-600/30">
            @if ($institutionLogoUrl)
                <img src="{{ $institutionLogoUrl }}" alt="Logo {{ $institutionName }}" class="h-6 w-6 object-contain" />
            @else
                <span class="text-xs font-bold tracking-wider text-white">{{ $monogram }}</span>
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$institutionName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-700 via-cyan-800 to-teal-800 text-white shadow-xs border border-cyan-600/30">
            @if ($institutionLogoUrl)
                <img src="{{ $institutionLogoUrl }}" alt="Logo {{ $institutionName }}" class="h-6 w-6 object-contain" />
            @else
                <span class="text-xs font-bold tracking-wider text-white">{{ $monogram }}</span>
            @endif
        </x-slot>
    </flux:brand>
@endif

