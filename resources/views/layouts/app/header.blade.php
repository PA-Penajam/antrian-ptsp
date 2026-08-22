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
        <flux:header container class="border-b border-slate-200/90 bg-white/95 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/95 shadow-2xs">
            <flux:sidebar.toggle class="lg:hidden mr-2 rounded-2xl border border-slate-200/80 bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('home') }}" wire:navigate />

            <flux:navbar class="-mb-px ms-4 max-lg:hidden">
                <flux:navbar.item icon="ticket" href="/antrian" :current="request()->is('antrian')" wire:navigate class="rounded-xl font-semibold">
                    {{ __('Ambil Antrian') }}
                </flux:navbar.item>
                <flux:navbar.item icon="magnifying-glass" href="/antrian/cek" :current="request()->is('antrian/cek')" wire:navigate class="rounded-xl font-semibold">
                    {{ __('Cek Status') }}
                </flux:navbar.item>

                @auth
                    <flux:navbar.item icon="chart-pie" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate class="rounded-xl font-semibold">
                        {{ __('Dashboard') }}
                    </flux:navbar.item>
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Officer))
                        <flux:navbar.item icon="megaphone" :href="route('workstation')" :current="request()->routeIs('workstation')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Workstation Loket') }}
                        </flux:navbar.item>
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Frontdesk) || auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:navbar.item icon="users" href="/frontdesk/antrian" :current="request()->is('frontdesk/antrian')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Frontdesk') }}
                        </flux:navbar.item>
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Monitor) || auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:navbar.item icon="chart-bar" href="/laporan/antrian" :current="request()->is('laporan/antrian')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Laporan') }}
                        </flux:navbar.item>
                        <flux:navbar.item icon="calendar-days" :href="route('laporan.bulanan')" :current="request()->routeIs('laporan.bulanan')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Laporan Bulanan') }}
                        </flux:navbar.item>
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:navbar.item icon="cog-6-tooth" href="/admin/layanan" :current="request()->is('admin/*')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Admin Master') }}
                        </flux:navbar.item>
                    @endif
                @endauth
            </flux:navbar>

            <flux:spacer />

            {{-- Header Theme Switcher --}}
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
                <x-desktop-user-menu />
            @else
                <flux:navbar class="ms-2 max-lg:hidden">
                    <flux:navbar.item icon="arrow-right-end-on-rectangle" href="/login" wire:navigate class="rounded-xl font-semibold">
                        {{ __('Masuk Petugas') }}
                    </flux:navbar.item>
                </flux:navbar>
            @endauth
        </flux:header>

        <!-- Mobile Menu Sidebar -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-slate-200/90 bg-white/95 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/95">
            <flux:sidebar.header class="pb-2">
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="gap-1.5">
                <flux:sidebar.group :heading="__('Layanan Publik')">
                    <flux:sidebar.item icon="ticket" href="/antrian" :current="request()->is('antrian')" wire:navigate class="rounded-xl font-semibold">
                        {{ __('Ambil Antrian') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="magnifying-glass" href="/antrian/cek" :current="request()->is('antrian/cek')" wire:navigate class="rounded-xl font-semibold">
                        {{ __('Cek Status') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @auth
                <flux:sidebar.group :heading="__('Manajemen Internal')" class="mt-3">
                    <flux:sidebar.item icon="chart-pie" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate class="rounded-xl font-semibold">
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Officer))
                        <flux:sidebar.item icon="megaphone" :href="route('workstation')" :current="request()->routeIs('workstation')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Workstation Loket') }}
                        </flux:sidebar.item>
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Frontdesk))
                        <flux:sidebar.item icon="users" href="/frontdesk/antrian" :current="request()->is('frontdesk/antrian')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Frontdesk') }}
                        </flux:sidebar.item>
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Monitor) || auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:sidebar.item icon="chart-bar" href="/laporan/antrian" :current="request()->is('laporan/antrian')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Laporan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" :href="route('laporan.audit')" :current="request()->routeIs('laporan.audit')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Audit Trail') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar-days" :href="route('laporan.bulanan')" :current="request()->routeIs('laporan.bulanan')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Laporan Bulanan') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                    <flux:sidebar.group heading="Master Data" expandable :expanded="request()->is('admin/layanan') || request()->is('admin/loket') || request()->is('admin/wilayah')" class="mt-3">
                        <flux:sidebar.item icon="clipboard-document-list" href="/admin/layanan" :current="request()->is('admin/layanan')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Layanan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-office" href="/admin/loket" :current="request()->is('admin/loket')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Loket & Pool') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="map" href="/admin/wilayah" :current="request()->is('admin/wilayah')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Setting Wilayah') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Manajemen Pengguna" expandable :expanded="request()->is('admin/users') || request()->is('admin/roles') || request()->is('admin/izin-layanan')" class="mt-3">
                        <flux:sidebar.item icon="users" href="/admin/users" :current="request()->is('admin/users') && request()->query('tab') !== 'roles'" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Daftar Pengguna') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shield-check" href="/admin/users?tab=roles" :current="request()->query('tab') === 'roles' || request()->is('admin/roles') || request()->is('admin/izin-layanan')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Role & Hak Akses') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Perangkat" expandable :expanded="request()->is('kiosk*') || request()->is('tv-display*')" class="mt-3">
                        <flux:sidebar.item icon="device-tablet" href="/kiosk/login" :current="request()->is('kiosk*')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('Kiosk Tiket') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="tv" href="/tv-display/login" :current="request()->is('tv-display*')" wire:navigate class="rounded-xl font-semibold">
                            {{ __('TV Display Antrian') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
                @endauth
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        @include('partials.flux-scripts')
    </body>
</html>

