@php
    $user = auth()->user();
    $userInitials = $user ? strtoupper(substr(trim($user->name), 0, 2)) : 'U';
    $roleLabel = $user?->role?->label() ?? 'Staff';
    $roleColor = $user?->role?->color() ?? 'zinc';
@endphp

<div class="flex flex-col gap-6 lg:flex-row lg:items-start">
    {{-- Left Sidebar Navigation --}}
    <aside class="w-full space-y-4 lg:w-64 lg:shrink-0">
        {{-- Mini Profile Identity Box --}}
        @if ($user)
            <div class="flex items-center gap-3 rounded-2xl border border-cyan-100 bg-cyan-50/60 p-3.5 shadow-2xs dark:border-cyan-900/50 dark:bg-cyan-950/20">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-cyan-700 font-mono text-sm font-bold text-white shadow-xs">
                    {{ $userInitials }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-zinc-900 dark:text-white">{{ $user->name }}</p>
                    <div class="flex items-center gap-1.5 pt-0.5">
                        <flux:badge size="sm" color="{{ $roleColor }}" class="text-xs font-semibold">
                            {{ $roleLabel }}
                        </flux:badge>
                    </div>
                </div>
            </div>
        @endif

        {{-- Navlist Navigation --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-2 shadow-2xs dark:border-zinc-800 dark:bg-zinc-900">
            <flux:navlist aria-label="{{ __('Settings') }}" class="space-y-1">
                <flux:navlist.item :href="route('profile.edit')" icon="user" wire:navigate class="rounded-xl font-semibold">
                    {{ __('Profile') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('user-password.edit')" icon="key" wire:navigate class="rounded-xl font-semibold">
                    {{ __('Password') }}
                </flux:navlist.item>
                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <flux:navlist.item :href="route('two-factor.show')" icon="shield-check" wire:navigate class="rounded-xl font-semibold">
                        {{ __('Two-factor auth') }}
                    </flux:navlist.item>
                @endif
                <flux:navlist.item :href="route('appearance.edit')" icon="paint-brush" wire:navigate class="rounded-xl font-semibold">
                    {{ __('Appearance') }}
                </flux:navlist.item>
            </flux:navlist>
        </div>
    </aside>

    {{-- Main Settings Content Area --}}
    <main class="min-w-0 flex-1">
        <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
            <div class="border-b border-zinc-100 pb-5 dark:border-zinc-800">
                <flux:heading size="lg" level="2" class="font-bold text-zinc-900 dark:text-white">
                    {{ $heading ?? '' }}
                </flux:heading>
                @if (! empty($subheading))
                    <flux:subheading class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $subheading }}
                    </flux:subheading>
                @endif
            </div>

            <div class="mt-6 w-full max-w-2xl">
                {{ $slot }}
            </div>
        </flux:card>
    </main>
</div>

