<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div
    class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-2xs dark:border-zinc-800 dark:bg-zinc-900/80"
    wire:cloak
    x-data="{ showRecoveryCodes: false, copiedAll: false }"
>
    <div class="space-y-1.5">
        <div class="flex items-center gap-2">
            <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                <flux:icon.lock-closed class="size-4" />
            </div>
            <flux:heading size="md" level="3" class="font-bold text-zinc-900 dark:text-white">{{ __('2FA recovery codes') }}</flux:heading>
        </div>
        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
        </flux:text>
    </div>

    <div>
        <div class="flex flex-wrap items-center gap-2.5">
            <flux:button
                x-show="!showRecoveryCodes"
                icon="eye"
                variant="primary"
                @click="showRecoveryCodes = true;"
                aria-expanded="false"
                aria-controls="recovery-codes-section"
                class="bg-cyan-700 font-semibold text-white hover:bg-cyan-600"
            >
                {{ __('View recovery codes') }}
            </flux:button>

            <flux:button
                x-show="showRecoveryCodes"
                icon="eye-slash"
                variant="filled"
                @click="showRecoveryCodes = false"
                aria-expanded="true"
                aria-controls="recovery-codes-section"
                class="font-semibold"
            >
                {{ __('Hide recovery codes') }}
            </flux:button>

            @if (filled($recoveryCodes))
                <flux:button
                    x-show="showRecoveryCodes"
                    icon="arrow-path"
                    variant="ghost"
                    wire:click="regenerateRecoveryCodes"
                    class="font-semibold"
                >
                    {{ __('Regenerate codes') }}
                </flux:button>

                <button
                    x-show="showRecoveryCodes"
                    type="button"
                    x-on:click="
                        navigator.clipboard.writeText({{ json_encode(implode("\n", $recoveryCodes)) }});
                        copiedAll = true;
                        setTimeout(() => copiedAll = false, 2000);
                    "
                    class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
                >
                    <flux:icon.clipboard x-show="!copiedAll" class="size-3.5 text-zinc-500" />
                    <flux:icon.check x-show="copiedAll" x-cloak class="size-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span x-text="copiedAll ? 'Tersalin Semua!' : 'Salin Semua Kode'"></span>
                </button>
            @endif
        </div>

        <div
            x-show="showRecoveryCodes"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            id="recovery-codes-section"
            class="relative mt-4 overflow-hidden"
            x-bind:aria-hidden="!showRecoveryCodes"
        >
            <div class="space-y-3">
                @error('recoveryCodes')
                    <flux:callout variant="danger" icon="x-circle" heading="{{$message}}"/>
                @enderror

                @if (filled($recoveryCodes))
                    <div
                        class="grid grid-cols-2 gap-2 rounded-xl border border-zinc-200/80 bg-zinc-50 p-4 font-mono text-xs dark:border-zinc-800 dark:bg-zinc-950/60"
                        role="list"
                        aria-label="{{ __('Recovery codes') }}"
                    >
                        @foreach($recoveryCodes as $code)
                            <div
                                role="listitem"
                                class="select-all rounded-lg bg-white px-3 py-1.5 text-center font-bold text-zinc-900 shadow-2xs ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-100 dark:ring-zinc-800"
                                wire:loading.class="opacity-50 animate-pulse"
                            >
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate codes above.') }}
                    </flux:text>
                @endif
            </div>
        </div>
    </div>
</div>
