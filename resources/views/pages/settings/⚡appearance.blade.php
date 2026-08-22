<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Appearance settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div class="space-y-6" x-data>
            {{-- Visual Theme Selector Cards --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3" role="radiogroup" aria-label="Pilihan mode tampilan tema">
                {{-- Light Theme Card --}}
                <button
                    type="button"
                    role="radio"
                    x-bind:aria-checked="$flux.appearance === 'light'"
                    x-on:click="$flux.appearance = 'light'"
                    class="group relative flex flex-col justify-between rounded-2xl border p-4 text-left transition-all duration-200 hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 dark:focus-visible:outline-cyan-400 motion-reduce:hover:transform-none"
                    x-bind:class="$flux.appearance === 'light' ? 'border-cyan-600 bg-cyan-50/50 ring-2 ring-cyan-600/30 dark:border-cyan-500 dark:bg-cyan-950/30' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700'"
                >
                    <div class="flex items-center justify-between w-full mb-3">
                        <div class="flex size-8 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                            <flux:icon.sun class="size-4" />
                        </div>
                        <div
                            class="flex size-5 items-center justify-center rounded-full border transition-all"
                            x-bind:class="$flux.appearance === 'light' ? 'border-cyan-600 bg-cyan-600 text-white' : 'border-zinc-300 dark:border-zinc-700'"
                        >
                            <flux:icon.check x-show="$flux.appearance === 'light'" class="size-3 stroke-[3]" />
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-white">{{ __('Light') }}</h4>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Tampilan cerah dengan palet Court Cyan & Soft Mist.</p>
                    </div>
                </button>

                {{-- Dark Theme Card --}}
                <button
                    type="button"
                    role="radio"
                    x-bind:aria-checked="$flux.appearance === 'dark'"
                    x-on:click="$flux.appearance = 'dark'"
                    class="group relative flex flex-col justify-between rounded-2xl border p-4 text-left transition-all duration-200 hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 dark:focus-visible:outline-cyan-400 motion-reduce:hover:transform-none"
                    x-bind:class="$flux.appearance === 'dark' ? 'border-cyan-600 bg-cyan-50/50 ring-2 ring-cyan-600/30 dark:border-cyan-500 dark:bg-cyan-950/30' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700'"
                >
                    <div class="flex items-center justify-between w-full mb-3">
                        <div class="flex size-8 items-center justify-center rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400">
                            <flux:icon.moon class="size-4" />
                        </div>
                        <div
                            class="flex size-5 items-center justify-center rounded-full border transition-all"
                            x-bind:class="$flux.appearance === 'dark' ? 'border-cyan-600 bg-cyan-600 text-white' : 'border-zinc-300 dark:border-zinc-700'"
                        >
                            <flux:icon.check x-show="$flux.appearance === 'dark'" class="size-3 stroke-[3]" />
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-white">{{ __('Dark') }}</h4>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Latar obsidian yang tenang untuk kenyamanan mata.</p>
                    </div>
                </button>

                {{-- System Theme Card --}}
                <button
                    type="button"
                    role="radio"
                    x-bind:aria-checked="$flux.appearance === 'system'"
                    x-on:click="$flux.appearance = 'system'"
                    class="group relative flex flex-col justify-between rounded-2xl border p-4 text-left transition-all duration-200 hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 dark:focus-visible:outline-cyan-400 motion-reduce:hover:transform-none"
                    x-bind:class="$flux.appearance === 'system' ? 'border-cyan-600 bg-cyan-50/50 ring-2 ring-cyan-600/30 dark:border-cyan-500 dark:bg-cyan-950/30' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700'"
                >
                    <div class="flex items-center justify-between w-full mb-3">
                        <div class="flex size-8 items-center justify-center rounded-xl bg-zinc-500/15 text-zinc-600 dark:text-zinc-400">
                            <flux:icon.computer-desktop class="size-4" />
                        </div>
                        <div
                            class="flex size-5 items-center justify-center rounded-full border transition-all"
                            x-bind:class="$flux.appearance === 'system' ? 'border-cyan-600 bg-cyan-600 text-white' : 'border-zinc-300 dark:border-zinc-700'"
                        >
                            <flux:icon.check x-show="$flux.appearance === 'system'" class="size-3 stroke-[3]" />
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-white">{{ __('System') }}</h4>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Sinkronisasi otomatis dengan setelan sistem perangkat.</p>
                    </div>
                </button>
            </div>

            {{-- Native Segmented Control fallback & a11y --}}
            <div class="pt-2">
                <flux:radio.group variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>
            </div>
        </div>
    </x-pages::settings.layout>
</section>

