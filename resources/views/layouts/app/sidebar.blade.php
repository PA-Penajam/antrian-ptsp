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
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @guest
                <flux:sidebar.group :heading="__('Layanan Publik')" class="grid">
                    <flux:sidebar.item icon="ticket" href="/antrian" :current="request()->is('antrian')" wire:navigate>
                        {{ __('Ambil Antrian') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="magnifying-glass" href="/antrian/cek" :current="request()->is('antrian/cek')" wire:navigate>
                        {{ __('Cek Status Antrian') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endguest

                @auth
                @if (auth()->user()?->role === \App\Enums\UserRole::Admin)
                    <livewire:admin-role-switcher />
                @endif

                @php
                    $viewRole = auth()->user()?->activeRole();
                    $isAdmin = auth()->user()?->role === \App\Enums\UserRole::Admin;
                @endphp

                <flux:sidebar.group :heading="__('Manajemen Internal')" expandable class="grid mt-4">
                    <flux:sidebar.item icon="chart-pie" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    @if ($viewRole === \App\Enums\UserRole::Officer || $viewRole === \App\Enums\UserRole::Admin || (! $isAdmin && auth()->user()?->hasRole(\App\Enums\UserRole::Officer)))
                        <flux:sidebar.item icon="megaphone" :href="route('workstation')" :current="request()->routeIs('workstation')" wire:navigate>
                            {{ __('Workstation') }}
                        </flux:sidebar.item>
                    @endif
                    @if ($viewRole === \App\Enums\UserRole::Frontdesk || $viewRole === \App\Enums\UserRole::Admin || (! $isAdmin && auth()->user()?->hasRole(\App\Enums\UserRole::Frontdesk)))
                        <flux:sidebar.item icon="users" href="/frontdesk/antrian" :current="request()->is('frontdesk/antrian')" wire:navigate>
                            {{ __('Frontdesk') }}
                        </flux:sidebar.item>
                    @endif
                    @if ($viewRole === \App\Enums\UserRole::Monitor || $viewRole === \App\Enums\UserRole::Admin || (! $isAdmin && auth()->user()?->hasRole(\App\Enums\UserRole::Monitor)))
                        <flux:sidebar.item icon="chart-bar" href="/laporan/antrian" :current="request()->is('laporan/antrian')" wire:navigate>
                            {{ __('Laporan') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                    <flux:sidebar.group
                        :heading="__('Master Data')"
                        expandable
                        :expanded="request()->is('admin/layanan') || request()->is('admin/loket') || request()->is('admin/wilayah')"
                        class="grid mt-4"
                    >
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

                    <flux:sidebar.group
                        :heading="__('Manajemen Pengguna')"
                        expandable
                        :expanded="request()->is('admin/users') || request()->is('admin/roles') || request()->is('admin/izin-layanan')"
                        class="grid mt-4"
                    >
                        <flux:sidebar.item icon="users" href="/admin/users" :current="request()->is('admin/users') && request()->query('tab') !== 'roles'" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shield-check" href="/admin/users?tab=roles" :current="request()->query('tab') === 'roles' || request()->is('admin/roles') || request()->is('admin/izin-layanan')" wire:navigate>
                            {{ __('Role & Izin') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group
                        :heading="__('Perangkat')"
                        expandable
                        :expanded="request()->is('kiosk*') || request()->is('tv-display*')"
                        class="grid mt-4"
                    >
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

            <div class="px-3 pb-3">
                <flux:button
                    x-on:click="theme = theme === 'dark' ? 'light' : 'dark'"
                    ::icon="theme === 'dark' ? 'sun' : 'moon'"
                    variant="subtle"
                    size="sm"
                    class="w-full justify-start"
                    aria-label="{{ __('Toggle tema') }}"
                    title="{{ __('Toggle tema') }}"
                >
                    <span x-text="theme === 'dark' ? @js(__('Tema terang')) : @js(__('Tema gelap'))"></span>
                </flux:button>
            </div>

            @auth
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @else
                <flux:sidebar.nav>
                    <flux:sidebar.item icon="arrow-right-end-on-rectangle" href="/login" wire:navigate>
                        {{ __('Log in') }}
                    </flux:sidebar.item>
                </flux:sidebar.nav>
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:button
                x-on:click="theme = theme === 'dark' ? 'light' : 'dark'"
                ::icon="theme === 'dark' ? 'sun' : 'moon'"
                variant="subtle"
                size="sm"
                class="me-2"
                aria-label="{{ __('Toggle tema') }}"
                title="{{ __('Toggle tema') }}"
            />

            @auth
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    @if (auth()->user()?->hasRole(\App\Enums\UserRole::Admin))
                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('dashboard')" icon="chart-pie" wire:navigate>
                                {{ __('Dashboard') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('admin.layanan.index')" icon="clipboard-document-list" wire:navigate>
                                {{ __('Layanan') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('admin.loket.index')" icon="building-office" wire:navigate>
                                {{ __('Loket') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('admin.users.index')" icon="users" wire:navigate>
                                {{ __('Users') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('admin.wilayah.index')" icon="map" wire:navigate>
                                {{ __('Setting Wilayah') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('kiosk.index')" icon="device-tablet" wire:navigate>
                                {{ __('Kiosk') }}
                            </flux:menu.item>
                            <flux:menu.item :href="route('tv-display.index')" icon="tv" wire:navigate>
                                {{ __('TV Display') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>
                    @endif

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            @else
                <flux:button variant="ghost" icon="arrow-right-end-on-rectangle" href="/login" class="px-2!" wire:navigate />
            @endauth
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
