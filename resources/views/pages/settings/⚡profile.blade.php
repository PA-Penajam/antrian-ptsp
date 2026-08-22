<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        {{-- Profile Avatar & Role Badge Header --}}
        @php
            $currentUser = auth()->user();
            $userInitials = $currentUser ? strtoupper(substr(trim($currentUser->name), 0, 2)) : 'U';
            $isVerified = $currentUser && $currentUser->email_verified_at !== null;
        @endphp
        
        <div class="mb-6 flex items-center gap-4 rounded-2xl border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
            <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-cyan-700 font-mono text-lg font-black text-white shadow-sm ring-2 ring-cyan-200 dark:ring-cyan-800">
                {{ $userInitials }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-white">{{ $currentUser?->name }}</h3>
                    <flux:badge size="sm" color="{{ $currentUser?->role?->color() ?? 'zinc' }}" class="text-xs font-semibold">
                        {{ $currentUser?->role?->label() ?? 'User' }}
                    </flux:badge>
                </div>
                <p class="truncate font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $currentUser?->email }}</p>
                <div class="mt-1 flex items-center gap-1.5">
                    @if ($isVerified)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <flux:icon.check-circle class="size-3.5" />
                            Email Terverifikasi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 dark:text-amber-400">
                            <flux:icon.exclamation-circle class="size-3.5" />
                            Email Belum Diverifikasi
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <form wire:submit="updateProfileInformation" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="name" icon="user" type="text" required autofocus autocomplete="name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input wire:model="email" icon="envelope" type="email" required autocomplete="email" />
                <flux:error name="email" />

                @if ($this->hasUnverifiedEmail)
                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-xs dark:border-amber-900/50 dark:bg-amber-950/30">
                        <div class="flex items-start gap-2">
                            <flux:icon.exclamation-triangle class="size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                            <div>
                                <span class="font-medium text-amber-900 dark:text-amber-200">{{ __('Your email address is unverified.') }}</span>
                                <div class="mt-1">
                                    <flux:link class="cursor-pointer font-bold text-amber-800 underline hover:text-amber-950 dark:text-amber-300 dark:hover:text-white" wire:click.prevent="resendVerificationNotification">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </flux:link>
                                </div>
                            </div>
                        </div>

                        @if (session('status') === 'verification-link-sent')
                            <div class="mt-2 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </div>
                        @endif
                    </div>
                @endif
            </flux:field>

            <div class="flex items-center gap-4 pt-2">
                <flux:button variant="primary" type="submit" icon="check" class="bg-cyan-700 font-bold text-white shadow-sm hover:bg-cyan-600" data-test="update-profile-button">
                    {{ __('Save') }}
                </flux:button>

                <x-action-message class="text-xs font-semibold text-emerald-600 dark:text-emerald-400" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <div class="mt-10 border-t border-zinc-100 pt-8 dark:border-zinc-800">
                <livewire:pages::settings.delete-user-form />
            </div>
        @endif
    </x-pages::settings.layout>
</section>
