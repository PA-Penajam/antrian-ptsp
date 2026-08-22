<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Password settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Password settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        {{-- Security Tip Banner --}}
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-cyan-100 bg-cyan-50/60 p-4 text-xs dark:border-cyan-900/50 dark:bg-cyan-950/20">
            <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-cyan-600/15 text-cyan-700 dark:text-cyan-300">
                <flux:icon.key class="size-4" />
            </div>
            <div class="space-y-0.5">
                <p class="font-bold text-cyan-950 dark:text-cyan-200">Keamanan Sandi Akun</p>
                <p class="text-cyan-800/80 dark:text-cyan-300/80">Gunakan kombinasi minimal 8 karakter dengan paduan huruf, angka, dan simbol untuk perlindungan akun maksimal.</p>
            </div>
        </div>

        <form method="POST" wire:submit="updatePassword" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Current password') }}</flux:label>
                <flux:input
                    wire:model="current_password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan sandi saat ini"
                />
                <flux:error name="current_password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('New password') }}</flux:label>
                <flux:input
                    wire:model="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Confirm password') }}</flux:label>
                <flux:input
                    wire:model="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Ketik ulang sandi baru"
                />
                <flux:error name="password_confirmation" />
            </flux:field>

            <div class="flex items-center gap-4 pt-2">
                <flux:button variant="primary" type="submit" icon="check" class="bg-cyan-700 font-bold text-white shadow-sm hover:bg-cyan-600" data-test="update-password-button">
                    {{ __('Save') }}
                </flux:button>

                <x-action-message class="text-xs font-semibold text-emerald-600 dark:text-emerald-400" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
