<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(bool $requiresConfirmation): void
    {
        $this->requiresConfirmation = $requiresConfirmation;
    }

    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication(auth()->user());

        $this->loadSetupData();
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user()?->fresh();

        try {
            if (! $user || ! $user->two_factor_secret) {
                throw new Exception('Two-factor setup secret is not available.');
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->setupComplete = true;

        $this->closeModal();

        $this->dispatch('two-factor-enabled');
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showVerificationStep',
            'setupComplete',
        );

        $this->resetErrorBag();
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->setupComplete) {
            return [
                'title' => __('Two-factor authentication enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify authentication code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable two-factor authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }
}; ?>

<flux:modal
    name="two-factor-setup-modal"
    class="max-w-md md:min-w-md"
    @close="closeModal"
>
    <div class="space-y-6">
        <div class="flex flex-col items-center space-y-3">
            <div class="flex size-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700 shadow-2xs dark:bg-cyan-950/70 dark:text-cyan-300">
                <flux:icon.qr-code class="size-6" />
            </div>

            <div class="space-y-1 text-center">
                <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">{{ $this->modalConfig['title'] }}</flux:heading>
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $this->modalConfig['description'] }}</flux:text>
            </div>
        </div>

        @if ($showVerificationStep)
            <div class="space-y-6">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <flux:otp
                        name="code"
                        wire:model="code"
                        length="6"
                        label="OTP Code"
                        label:sr-only
                        class="mx-auto font-mono text-lg"
                    />
                </div>

                <div class="flex items-center space-x-3">
                    <flux:button
                        variant="filled"
                        class="flex-1 font-semibold"
                        wire:click="resetVerification"
                    >
                        {{ __('Back') }}
                    </flux:button>

                    <flux:button
                        variant="primary"
                        class="flex-1 bg-cyan-700 font-bold text-white shadow-sm hover:bg-cyan-600"
                        wire:click="confirmTwoFactor"
                        x-bind:disabled="$wire.code.length < 6"
                    >
                        {{ __('Confirm') }}
                    </flux:button>
                </div>
            </div>
        @else
            @error('setupData')
                <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}"/>
            @enderror

            <div class="flex justify-center">
                <div class="relative w-64 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm aspect-square dark:border-zinc-700 dark:bg-zinc-800">
                    @empty($qrCodeSvg)
                        <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-zinc-800 animate-pulse">
                            <flux:icon.loading class="size-8 text-cyan-600"/>
                        </div>
                    @else
                        <div x-data class="flex items-center justify-center h-full">
                            <div
                                class="bg-white p-2 rounded-xl"
                                :style="($flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark)) ? 'filter: invert(1) brightness(1.5)' : ''"
                            >
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                    @endempty
                </div>
            </div>

            <div>
                <flux:button
                    :disabled="$errors->has('setupData')"
                    variant="primary"
                    class="w-full bg-cyan-700 font-bold text-white shadow-sm hover:bg-cyan-600"
                    wire:click="showVerificationIfNecessary"
                >
                    {{ $this->modalConfig['buttonText'] }}
                </flux:button>
            </div>

            <div class="space-y-3">
                <div class="relative flex items-center justify-center w-full">
                    <div class="absolute inset-0 w-full h-px top-1/2 bg-zinc-200 dark:bg-zinc-700"></div>
                    <span class="relative px-3 text-xs bg-white dark:bg-zinc-900 text-zinc-500 font-medium">
                        {{ __('or, enter the code manually') }}
                    </span>
                </div>

                <div>
                    <div class="flex items-stretch w-full">
                        @empty($manualSetupKey)
                            <div class="flex items-center justify-center w-full p-3 bg-zinc-100 dark:bg-zinc-800 rounded-xl">
                                <flux:icon.loading variant="mini"/>
                            </div>
                        @else
                            <flux:input
                                type="text"
                                readonly
                                value="{{ $manualSetupKey }}"
                                class="flex-1! font-mono text-xs"
                                copyable
                            />
                        @endempty
                    </div>
                </div>
            </div>
        @endif
    </div>
</flux:modal>
