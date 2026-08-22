<?php

use Livewire\Component;

new class extends Component {}; ?>

<section class="space-y-4">
    <div class="rounded-2xl border border-rose-200/80 bg-rose-50/40 p-5 dark:border-rose-900/50 dark:bg-rose-950/20">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="flex size-7 items-center justify-center rounded-lg bg-rose-500/15 text-rose-700 dark:text-rose-300">
                        <flux:icon.trash class="size-4" />
                    </div>
                    <flux:heading size="md" class="font-bold text-rose-950 dark:text-rose-200">{{ __('Delete account') }}</flux:heading>
                </div>
                <flux:text class="text-xs text-rose-800/80 dark:text-rose-300/80">{{ __('Delete your account and all of its resources') }}</flux:text>
            </div>

            <flux:modal.trigger name="confirm-user-deletion">
                <flux:button variant="danger" icon="trash" data-test="delete-user-button" class="shrink-0 font-semibold shadow-xs">
                    {{ __('Delete account') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <livewire:pages::settings.delete-user-modal />
</section>

