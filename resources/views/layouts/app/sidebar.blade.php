<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ theme: localStorage.getItem('theme') || 'light' }"
    x-init="$watch('theme', value => localStorage.setItem('theme', value))"
    :class="theme"
>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-100/70 text-slate-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-slate-200/90 bg-white/95 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/95">
            <flux:sidebar.header class="pb-2">
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="gap-1.5">
                @guest
                <flux:sidebar.group :heading="__('Layanan Publik')" class="grid">
                    <flux:sidebar.item icon="ticket" href="/antrian" :current="request()->is('antrian')" wire:navigate.hover class="rounded-xl">
                        {{ __('Ambil Antrian') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="magnifying-glass" href="/antrian/cek" :current="request()->is('antrian/cek')" wire:navigate.hover class="rounded-xl">
                        {{ __('Cek Status Antrian') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endguest

                @auth
                @if (auth()->user()?->role === \App\Enums\UserRole::Admin)
                    <div class="mb-2 rounded-2xl border border-cyan-100/80 bg-gradient-to-br from-cyan-50/80 to-teal-50/40 p-1 dark:border-cyan-900/40 dark:bg-zinc-900/80">
                        <livewire:admin-role-switcher />
                    </div>
                @endif

                @php
                    $viewRole = auth()->user()?->activeRole();
                    $isAdmin = auth()->user()?->role === \App\Enums\UserRole::Admin;
                @endphp

                <flux:sidebar.group :heading="__('Manajemen Internal')" expandable class="grid mt-2">
                    <flux:sidebar.item icon="chart-pie" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate.hover class="rounded-xl">
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    @if ($viewRole === \App\Enums\UserRole::Officer || $viewRole === \App\Enums\UserRole::Admin || (! $isAdmin && auth()->user()?->hasRole(\App\Enums\UserRole::Officer)))
                        <flux:sidebar.item icon="megaphone" :href="route('workstation')" :current="request()->routeIs('workstation')" wire:navigate.hover class="rounded-xl">
                            {{ __('Workstation Loket') }}
                        </flux:sidebar.item>
                    @endif
                    @if ($viewRole === \App\Enums\UserRole::Frontdesk || $viewRole === \App\Enums\UserRole::Admin || (! $isAdmin && auth()->user()?->hasRole(\App\Enums\UserRole::Frontdesk)))
                        <flux:sidebar.item icon="users" href="/frontdesk/antrian" :current="request()->is('frontdesk/antrian')" wire:navigate.hover class="rounded-xl">
                            {{ __('Frontdesk Antrian') }}
                        </flux:sidebar.item>
                    @endif
                    @if ($viewRole === \App\Enums\UserRole::Monitor || $viewRole === \App\Enums\UserRole::Admin || (! $isAdmin && auth()->user()?->hasRole(\App\Enums\UserRole::Monitor)))
                        <flux:sidebar.item icon="chart-bar" href="/laporan/antrian" :current="request()->is('laporan/antrian')" wire:navigate.hover class="rounded-xl">
                            {{ __('Laporan Antrian') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" :href="route('laporan.audit')" :current="request()->routeIs('laporan.audit')" wire:navigate.hover class="rounded-xl">
                            {{ __('Audit Trail') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar-days" :href="route('laporan.bulanan')" :current="request()->routeIs('laporan.bulanan')" wire:navigate class="rounded-xl">
                            {{ __('Laporan Bulanan') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                    <flux:sidebar.group
                        :heading="__('Master Data')"
                        expandable
                        :expanded="request()->is('admin/layanan') || request()->is('admin/loket') || request()->is('admin/wilayah')"
                        class="grid mt-3"
                    >
                        <flux:sidebar.item icon="clipboard-document-list" href="/admin/layanan" :current="request()->is('admin/layanan')" wire:navigate.hover class="rounded-xl">
                            {{ __('Layanan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-office" href="/admin/loket" :current="request()->is('admin/loket')" wire:navigate.hover class="rounded-xl">
                            {{ __('Loket & Pool') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="map" href="/admin/wilayah" :current="request()->is('admin/wilayah')" wire:navigate.hover class="rounded-xl">
                            {{ __('Setting Wilayah') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group
                        :heading="__('Manajemen Pengguna')"
                        expandable
                        :expanded="request()->is('admin/users') || request()->is('admin/roles') || request()->is('admin/izin-layanan')"
                        class="grid mt-3"
                    >
                        <flux:sidebar.item icon="users" href="/admin/users" :current="request()->is('admin/users') && request()->query('tab') !== 'roles'" wire:navigate.hover class="rounded-xl">
                            {{ __('Daftar Pengguna') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shield-check" href="/admin/users?tab=roles" :current="request()->query('tab') === 'roles' || request()->is('admin/roles') || request()->is('admin/izin-layanan')" wire:navigate.hover class="rounded-xl">
                            {{ __('Role & Hak Akses') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group
                        :heading="__('Perangkat')"
                        expandable
                        :expanded="request()->is('kiosk*') || request()->is('tv-display*')"
                        class="grid mt-3"
                    >
                        <flux:sidebar.item icon="device-tablet" href="/kiosk/login" :current="request()->is('kiosk*')" wire:navigate.hover class="rounded-xl">
                            {{ __('Kiosk Tiket') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="tv" href="/tv-display/login" :current="request()->is('tv-display*')" wire:navigate.hover class="rounded-xl">
                            {{ __('TV Display Antrian') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
                @endauth
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- Sidebar Theme Switcher --}}
            <div class="px-2 pb-2">
                <flux:button
                    x-on:click="theme = theme === 'dark' ? 'light' : 'dark'"
                    ::icon="theme === 'dark' ? 'sun' : 'moon'"
                    variant="subtle"
                    size="sm"
                    class="w-full justify-start rounded-2xl border border-slate-200/70 bg-slate-50/80 font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-zinc-800 dark:bg-zinc-800/80 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    aria-label="{{ __('Toggle tema') }}"
                    title="{{ __('Toggle tema') }}"
                >
                    <span x-text="theme === 'dark' ? @js(__('Tema Terang')) : @js(__('Tema Gelap'))"></span>
                </flux:button>
            </div>

            @auth
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @else
                <flux:sidebar.nav>
                    <flux:sidebar.item icon="arrow-right-end-on-rectangle" href="/login" wire:navigate class="rounded-xl font-semibold">
                        {{ __('Masuk Petugas') }}
                    </flux:sidebar.item>
                </flux:sidebar.nav>
            @endauth
        </flux:sidebar>

        <!-- Mobile Header Bar -->
        <flux:header class="sticky top-0 z-40 lg:hidden border-b border-slate-200/90 bg-white/95 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/95 px-3 py-2 shadow-2xs">
            <flux:sidebar.toggle class="lg:hidden rounded-2xl border border-slate-200/90 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 p-2" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:button
                x-on:click="theme = theme === 'dark' ? 'light' : 'dark'"
                ::icon="theme === 'dark' ? 'sun' : 'moon'"
                variant="subtle"
                size="sm"
                class="me-2 rounded-2xl border border-slate-200/80 bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 min-h-[40px] min-w-[40px]"
                aria-label="{{ __('Toggle tema') }}"
                title="{{ __('Toggle tema') }}"
            />

            @auth
            <flux:dropdown position="bottom" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="rounded-2xl"
                />

                <flux:menu class="w-72 rounded-3xl border border-slate-200/90 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/95 p-2 shadow-xl shadow-cyan-950/10 backdrop-blur-md">
                    <div class="flex items-center gap-3 p-2.5">
                        <div class="flex size-10 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-teal-700 text-xs font-bold text-white shadow-xs">
                            {{ auth()->user()->initials() }}
                        </div>
                        <div class="grid flex-1 text-start text-xs leading-tight min-w-0">
                            <flux:heading class="truncate font-bold text-slate-900 dark:text-zinc-100">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate text-slate-500 dark:text-zinc-400 text-xs">{{ auth()->user()->email }}</flux:text>
                            @if (auth()->user()?->activeRole())
                                <div class="mt-1">
                                    <span class="inline-flex items-center rounded-full bg-cyan-50 dark:bg-cyan-950/60 px-2 py-0.5 text-xs font-bold tracking-wider text-cyan-800 dark:text-cyan-300 uppercase border border-cyan-200/60 dark:border-cyan-800/60">
                                        {{ auth()->user()->activeRole()->label() }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Monitor) || auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:menu.separator class="my-1.5 border-slate-100 dark:border-zinc-800" />

                        <flux:menu.radio.group>
                            <flux:menu.item icon="chart-bar" href="/laporan/antrian" :current="request()->is('laporan/antrian')" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Laporan Antrian') }}
                            </flux:menu.item>
                            <flux:menu.item icon="clock" :href="route('laporan.audit')" :current="request()->routeIs('laporan.audit')" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Audit Trail') }}
                            </flux:menu.item>
                            <flux:menu.item icon="calendar-days" :href="route('laporan.bulanan')" :current="request()->routeIs('laporan.bulanan')" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Laporan Bulanan') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>
                    @endif

                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:menu.separator class="my-1.5 border-slate-100 dark:border-zinc-800" />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('dashboard')" icon="chart-pie" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Dashboard') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('admin.layanan.index')" icon="clipboard-document-list" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Layanan') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('admin.loket.index')" icon="building-office" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Loket & Pool') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('admin.users.index')" icon="users" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Daftar Pengguna') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('admin.wilayah.index')" icon="map" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Setting Wilayah') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('kiosk.index')" icon="device-tablet" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('Kiosk Tiket') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('tv-display.index')" icon="tv" wire:navigate class="rounded-xl text-xs font-semibold">
                                {{ __('TV Display Antrian') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>
                    @endif

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
            @else
                <flux:button variant="ghost" icon="arrow-right-end-on-rectangle" href="/login" class="px-2! rounded-2xl" wire:navigate />
            @endauth
        </flux:header>

        {{-- Global offline banner (admin) --}}
        <div wire:offline class="sticky top-0 z-50 flex items-center justify-center gap-2 bg-amber-500 px-4 py-2.5 text-xs font-bold tracking-wide text-white shadow-sm">
            <flux:icon.signal-slash class="size-4 shrink-0" />
            <span>Koneksi terputus — beberapa aksi mungkin gagal. Periksa internet Anda.</span>
        </div>

        {{-- Global flash: rate-limit / error passthrough --}}
        @if (session('error'))
            <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                <flux:callout icon="x-circle" color="red" heading="Terjadi Kendala" class="rounded-2xl shadow-xs">
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </flux:callout>
            </div>
        @endif

        {{ $slot }}

        @include('partials.flux-scripts')
    </body>
</html>

