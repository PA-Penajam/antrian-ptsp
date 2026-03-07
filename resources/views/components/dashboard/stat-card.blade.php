@props([
    'value',
    'label',
    'icon',
    'color',
    'trend' => null,
])

@php
    $backgroundClass = match ($color) {
        'blue' => 'bg-blue-50 dark:bg-blue-900/20',
        'green' => 'bg-green-50 dark:bg-green-900/20',
        'red' => 'bg-red-50 dark:bg-red-900/20',
        'amber' => 'bg-amber-50 dark:bg-amber-900/20',
        default => 'bg-zinc-50 dark:bg-zinc-900/20',
    };

    $borderClass = match ($color) {
        'blue' => 'border-blue-200 dark:border-blue-800',
        'green' => 'border-green-200 dark:border-green-800',
        'red' => 'border-red-200 dark:border-red-800',
        'amber' => 'border-amber-200 dark:border-amber-800',
        default => 'border-zinc-200 dark:border-zinc-800',
    };

    $iconClass = match ($color) {
        'blue' => 'text-blue-600 dark:text-blue-400',
        'green' => 'text-green-600 dark:text-green-400',
        'red' => 'text-red-600 dark:text-red-400',
        'amber' => 'text-amber-600 dark:text-amber-400',
        default => 'text-zinc-600 dark:text-zinc-400',
    };

    $valueClass = match ($color) {
        'blue' => 'text-blue-700 dark:text-blue-300',
        'green' => 'text-green-700 dark:text-green-300',
        'red' => 'text-red-700 dark:text-red-300',
        'amber' => 'text-amber-700 dark:text-amber-300',
        default => 'text-zinc-700 dark:text-zinc-300',
    };

    $labelClass = match ($color) {
        'blue' => 'text-blue-600/80 dark:text-blue-400/80',
        'green' => 'text-green-600/80 dark:text-green-400/80',
        'red' => 'text-red-600/80 dark:text-red-400/80',
        'amber' => 'text-amber-600/80 dark:text-amber-400/80',
        default => 'text-zinc-600/80 dark:text-zinc-400/80',
    };
@endphp

<div {{ $attributes->class(['rounded-xl border p-4', $backgroundClass, $borderClass]) }}>
    <div class="mb-2 flex items-center justify-between">
        <flux:icon :name="$icon" class="size-5 {{ $iconClass }}" />
    </div>

    <div class="text-2xl font-bold {{ $valueClass }}">{{ $value }}</div>
    <div class="mt-1 text-sm {{ $labelClass }}">{{ $label }}</div>
</div>
