<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('home') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="ticket" href="/antrian" :current="request()->is('antrian')" wire:navigate>
                    {{ __('Ambil Antrian') }}
                </flux:navbar.item>
                <flux:navbar.item icon="magnifying-glass" href="/antrian/cek" :current="request()->is('antrian/cek')" wire:navigate>
                    {{ __('Cek Status') }}
                </flux:navbar.item>

                @auth
                    <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        @if (auth()->user()?->hasRole(\App\Enums\UserRole::Officer))
                            {{ __('Workstation') }}
                        @else
                            {{ __('Dashboard') }}
                        @endif
                    </flux:navbar.item>
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Frontdesk) || auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:navbar.item icon="users" href="/frontdesk/antrian" :current="request()->is('frontdesk/antrian')" wire:navigate>
                            {{ __('Frontdesk') }}
                        </flux:navbar.item>
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Monitor))
                        <flux:navbar.item icon="chart-bar" href="/laporan/antrian" :current="request()->is('laporan/antrian')" wire:navigate>
                            {{ __('Laporan') }}
                        </flux:navbar.item>
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:navbar.item icon="cog-6-tooth" href="/admin/layanan" :current="request()->is('admin/*')" wire:navigate>
                            {{ __('Admin') }}
                        </flux:navbar.item>
                    @endif
                @endauth
            </flux:navbar>

            <flux:spacer />

            @auth
                <x-desktop-user-menu />
            @else
                <flux:navbar class="ms-2 max-lg:hidden">
                    <flux:navbar.item icon="arrow-right-end-on-rectangle" href="/login" wire:navigate>
                        {{ __('Log in') }}
                    </flux:navbar.item>
                </flux:navbar>
            @endauth
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Layanan Publik')">
                    <flux:sidebar.item icon="ticket" href="/antrian" :current="request()->is('antrian')" wire:navigate>
                        {{ __('Ambil Antrian') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="magnifying-glass" href="/antrian/cek" :current="request()->is('antrian/cek')" wire:navigate>
                        {{ __('Cek Status') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @auth
                <flux:sidebar.group :heading="__('Manajemen Internal')" class="mt-4">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        @if (auth()->user()?->hasRole(\App\Enums\UserRole::Officer))
                            {{ __('Workstation') }}
                        @else
                            {{ __('Dashboard') }}
                        @endif
                    </flux:sidebar.item>
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Frontdesk))
                        <flux:sidebar.item icon="users" href="/frontdesk/antrian" :current="request()->is('frontdesk/antrian')" wire:navigate>
                            {{ __('Frontdesk') }}
                        </flux:sidebar.item>
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Monitor))
                        <flux:sidebar.item icon="chart-bar" href="/laporan/antrian" :current="request()->is('laporan/antrian')" wire:navigate>
                            {{ __('Laporan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" :href="route('laporan.audit')" :current="request()->routeIs('laporan.audit')" wire:navigate>
                            {{ __('Audit Trail') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                    <flux:sidebar.group heading="Master Data" expandable :expanded="request()->is('admin/layanan') || request()->is('admin/loket') || request()->is('admin/wilayah')">
                        <flux:sidebar.item icon="clipboard-document-list" href="/admin/layanan" :current="request()->is('admin/layanan')" wire:navigate>
                            {{ __('Layanan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-office" href="/admin/loket" :current="request()->is('admin/loket')" wire:navigate>
                            {{ __('Loket') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="map" href="/admin/wilayah" :current="request()->is('admin/wilayah')" wire:navigate>
                            {{ __('Setting Wilayah') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Manajemen Pengguna" expandable :expanded="request()->is('admin/users') || request()->is('admin/roles') || request()->is('admin/izin-layanan')">
                        <flux:sidebar.item icon="users" href="/admin/users" :current="request()->is('admin/users') && request()->query('tab') !== 'roles'" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shield-check" href="/admin/users?tab=roles" :current="request()->query('tab') === 'roles' || request()->is('admin/roles') || request()->is('admin/izin-layanan')" wire:navigate>
                            {{ __('Role & Izin') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group heading="Perangkat" expandable :expanded="request()->is('kiosk*') || request()->is('tv-display*')">
                        <flux:sidebar.item icon="device-tablet" href="/kiosk/login" :current="request()->is('kiosk*')" wire:navigate>
                            {{ __('Kiosk') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="tv" href="/tv-display/login" :current="request()->is('tv-display*')" wire:navigate>
                            {{ __('TV Display') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
                @endauth
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
