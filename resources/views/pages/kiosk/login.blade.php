<x-layouts.kiosk :title="'Login Kiosk'">
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="grid w-full max-w-6xl gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(24rem,30rem)] xl:gap-8">
            <section class="hidden rounded-[2rem] border border-slate-200 bg-white/80 p-8 shadow-xl xl:flex xl:flex-col xl:justify-between">
                <div class="space-y-8">
                    <div class="flex items-center gap-5">
                        <div class="flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-teal-600 shadow-lg">
                            <flux:icon.building-office class="size-8 text-white" />
                        </div>
                        <div>
                            <flux:heading level="1" size="xl" class="text-balance text-slate-900">
                                Pengadilan Agama Penajam
                            </flux:heading>
                            <flux:text class="mt-1 text-base text-slate-600">
                                PTSP — Layanan Antrian Mandiri
                            </flux:text>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:badge color="cyan" size="sm" icon="device-phone-mobile" class="rounded-full">Layar Sentuh</flux:badge>
                    <flux:badge color="emerald" size="sm" icon="shield-check" class="rounded-full">Akses Terlindungi</flux:badge>
                    <flux:badge color="amber" size="sm" icon="bolt" class="rounded-full">Antrian Cepat</flux:badge>
                </div>
            </section>

            <flux:card class="border-slate-200 bg-white/90 p-6 shadow-xl backdrop-blur sm:p-8 xl:p-10">
                <div class="space-y-8">
                    <div class="space-y-4 text-center">
                        <div class="flex justify-center">
                            <div class="flex size-18 items-center justify-center rounded-[1.75rem] bg-gradient-to-br from-cyan-600 to-teal-600 shadow-lg">
                                <flux:icon.lock-closed class="size-8 text-white" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <flux:heading level="1" size="xl" class="text-slate-900">Selamat Datang</flux:heading>
                            <flux:text class="text-base leading-7 text-slate-600">
                                Antrian PTSP Pengadilan Agama Penajam
                            </flux:text>
                        </div>
                    </div>

                    @if ($errors->has('password'))
                        <flux:callout variant="danger" icon="x-circle" heading="{{ $errors->first('password') }}" />
                    @endif

                    <form method="POST" action="{{ route('kiosk.authenticate') }}" class="space-y-6">
                        @csrf

                        <flux:field x-data="{ showPassword: false }">
                            <flux:label class="text-sm font-semibold tracking-[0.18em] text-slate-600 uppercase">Password Kiosk</flux:label>
                            <div class="relative mt-3">
                                <input
                                    name="password"
                                    x-bind:type="showPassword ? 'text' : 'password'"
                                    inputmode="text"
                                    autofocus
                                    autocomplete="current-password"
                                    placeholder="Masukkan password"
                                    class="h-16 w-full rounded-2xl border border-slate-300 bg-white px-5 pr-16 text-xl text-slate-900 placeholder:text-slate-400 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                                />
                                <button
                                    type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-900 active:bg-slate-300"
                                    aria-label="Toggle password visibility"
                                >
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L6.228 6.228" />
                                    </svg>
                                </button>
                            </div>
                            <flux:error name="password" class="mt-3 text-sm text-red-500" />
                        </flux:field>

                        <flux:button type="submit" variant="primary" class="w-full justify-center rounded-2xl py-4 text-xl font-semibold">
                            Masuk ke Kiosk
                        </flux:button>
                    </form>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts.kiosk>
