<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form method="POST" wire:submit="deleteUser" class="space-y-5">
        <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
            <div class="flex size-10 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400">
                <flux:icon.trash class="size-5" />
            </div>
            <div>
                <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">{{ __('Are you sure you want to delete your account?') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Tindakan ini permanen dan tidak dapat dibatalkan.') }}</flux:text>
            </div>
        </div>

        <flux:callout icon="exclamation-circle" color="red" class="rounded-2xl text-xs">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
        </flux:callout>

        <flux:field>
            <flux:label>{{ __('Password') }}</flux:label>
            <flux:input wire:model="password" type="password" placeholder="Masukkan sandi konfirmasi" required />
            <flux:error name="password" />
        </flux:field>

        <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
            <flux:modal.close>
                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" type="submit" icon="trash" data-test="confirm-delete-user-button">
                {{ __('Delete account') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
