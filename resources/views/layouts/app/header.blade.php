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
                <flux:navbar.item icon="tv" href="/display" :current="request()->is('display')" wire:navigate>
                    {{ __('Display') }}
                </flux:navbar.item>

                @auth
                    <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:navbar.item>
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Frontdesk))
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
                        <flux:navbar.item icon="cog-6-tooth" href="/admin/layanan" :current="request()->is('admin/layanan')" wire:navigate>
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
                    <flux:sidebar.item icon="tv" href="/display" :current="request()->is('display')" wire:navigate>
                        {{ __('Display') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @auth
                <flux:sidebar.group :heading="__('Manajemen Internal')" class="mt-4">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard')  }}
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
                    @endif
                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:sidebar.item icon="cog-6-tooth" href="/admin/layanan" :current="request()->is('admin/layanan')" wire:navigate>
                            {{ __('Admin') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
                @endauth
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>