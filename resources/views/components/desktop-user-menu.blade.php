@php
    $user = auth()->user();
    $activeRole = $user?->activeRole();
@endphp

<flux:dropdown position="bottom" align="start">
    <flux:sidebar.profile
        :name="$user->name"
        :initials="$user->initials()"
        icon:trailing="chevrons-up-down"
        data-test="sidebar-menu-button"
        class="rounded-2xl transition hover:bg-cyan-50/60 dark:hover:bg-zinc-800/60"
    />

    <flux:menu class="w-72 rounded-3xl border border-slate-200/90 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/95 p-2 shadow-xl shadow-cyan-950/10 backdrop-blur-md">
        <div class="flex items-center gap-3 p-2.5">
            <div class="flex size-10 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-teal-700 text-xs font-bold text-white shadow-xs">
                {{ $user->initials() }}
            </div>
            <div class="grid flex-1 text-start text-xs leading-tight min-w-0">
                <flux:heading class="truncate font-bold text-slate-900 dark:text-zinc-100">{{ $user->name }}</flux:heading>
                <flux:text class="truncate text-slate-500 dark:text-zinc-400 text-xs">{{ $user->email }}</flux:text>
                @if ($activeRole)
                    <div class="mt-1">
                        <span class="inline-flex items-center rounded-full bg-cyan-50 dark:bg-cyan-950/60 px-2 py-0.5 text-xs font-bold tracking-wider text-cyan-800 dark:text-cyan-300 uppercase border border-cyan-200/60 dark:border-cyan-800/60">
                            {{ $activeRole->label() }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <flux:menu.separator class="my-1.5 border-slate-100 dark:border-zinc-800" />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate class="rounded-xl text-xs font-semibold">
                {{ __('Pengaturan Akun') }}
            </flux:menu.item>
        </flux:menu.radio.group>

        <flux:menu.separator class="my-1.5 border-slate-100 dark:border-zinc-800" />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item
                as="button"
                type="submit"
                icon="arrow-right-start-on-rectangle"
                variant="danger"
                class="w-full cursor-pointer rounded-xl text-xs font-semibold"
                data-test="logout-button"
            >
                {{ __('Keluar (Log Out)') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>

