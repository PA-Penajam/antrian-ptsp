<?php

use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

new #[Title('Two-factor authentication')] class extends Component {
    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }
} ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Two-factor authentication settings') }}</flux:heading>

    <x-pages::settings.layout
        :heading="__('Two-factor authentication')"
        :subheading="__('Manage your two-factor authentication settings')"
    >
        <div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
            @if ($twoFactorEnabled)
                {{-- Enabled State Card --}}
                <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-5 dark:border-emerald-900/50 dark:bg-emerald-950/20 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-600/15 text-emerald-700 dark:text-emerald-400">
                                <flux:icon.shield-check class="size-5" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-emerald-950 dark:text-emerald-200">2FA Aktif</h4>
                                <p class="text-xs text-emerald-800/80 dark:text-emerald-300/80">Akun Anda terlindungi otentikasi dua faktor.</p>
                            </div>
                        </div>
                        <flux:badge color="green" size="sm" class="font-bold">{{ __('Enabled') }}</flux:badge>
                    </div>

                    <flux:text class="text-xs text-emerald-900/90 dark:text-emerald-200/90">
                        {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                    </flux:text>
                </div>

                <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />

                <div class="flex justify-start pt-2">
                    <flux:button
                        variant="danger"
                        icon="shield-exclamation"
                        wire:click="disable"
                        class="font-semibold"
                    >
                        {{ __('Disable 2FA') }}
                    </flux:button>
                </div>
            @else
                {{-- Disabled State Card --}}
                <div class="rounded-2xl border border-amber-200/80 bg-amber-50/50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-amber-600/15 text-amber-700 dark:text-amber-400">
                                <flux:icon.shield-exclamation class="size-5" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-amber-950 dark:text-amber-200">2FA Belum Aktif</h4>
                                <p class="text-xs text-amber-800/80 dark:text-amber-300/80">Tingkatkan keamanan akun staf dengan PIN TOTP.</p>
                            </div>
                        </div>
                        <flux:badge color="red" size="sm" class="font-bold">{{ __('Disabled') }}</flux:badge>
                    </div>

                    <flux:text variant="subtle" class="text-xs">
                        {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                    </flux:text>

                    <div class="pt-2">
                        <flux:modal.trigger name="two-factor-setup-modal">
                            <flux:button
                                variant="primary"
                                icon="shield-check"
                                wire:click="$dispatch('start-two-factor-setup')"
                                class="bg-cyan-700 font-bold text-white shadow-sm hover:bg-cyan-600"
                            >
                                {{ __('Enable 2FA') }}
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>

                <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
            @endif
        </div>
    </x-pages::settings.layout>
</section>
