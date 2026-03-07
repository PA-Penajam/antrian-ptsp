<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
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
                    <flux:sidebar.item icon="tv" href="/display" :current="request()->is('display')" wire:navigate>
                        {{ __('Layar Display') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endguest

                @auth
                <flux:sidebar.group :heading="__('Manajemen Internal')" class="grid mt-4">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
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
