<x-layouts.tv-display :title="'Login TV Display'">
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="grid w-full max-w-7xl gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(28rem,34rem)] xl:gap-10">
            <section class="hidden rounded-[2rem] border border-white/10 bg-white/6 p-10 shadow-[0_32px_90px_-48px_rgba(15,23,42,0.95)] xl:flex xl:flex-col xl:justify-between">
                <div class="space-y-6">
                    <flux:badge color="zinc" size="sm" icon="computer-desktop" class="w-fit rounded-full border border-white/10 bg-white/10 text-zinc-100">
                        Mode TV Display PTSP
                    </flux:badge>

                    <div class="space-y-4">
                        <flux:heading level="1" size="xl" class="text-balance text-white">
                            Monitor antrian besar untuk ruang tunggu yang rapi dan mudah dipantau.
                        </flux:heading>

                        <flux:text class="max-w-3xl text-lg leading-8 text-zinc-300">
                            Gunakan password modul untuk membuka layar TV Display. Layout ini dibuat tanpa sidebar, fokus ke mode landscape, dan siap menjadi dasar tampilan monitor antrian.
                        </flux:text>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-3xl border border-sky-400/20 bg-sky-400/10 p-5">
                        <p class="text-xs font-semibold tracking-[0.18em] text-sky-200 uppercase">Landscape</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-100">Komposisi lebar untuk monitor ruang tunggu dan area pelayanan.</p>
                    </div>
                    <div class="rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                        <p class="text-xs font-semibold tracking-[0.18em] text-emerald-200 uppercase">Jelas</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-100">Elemen besar agar operator cepat membuka mode tampilan publik.</p>
                    </div>
                    <div class="rounded-3xl border border-amber-400/20 bg-amber-400/10 p-5">
                        <p class="text-xs font-semibold tracking-[0.18em] text-amber-200 uppercase">Terlindungi</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-100">Akses modul tetap dijaga dengan password dan session khusus.</p>
                    </div>
                </div>
            </section>

            <flux:card class="border-white/10 bg-zinc-950/80 p-6 shadow-[0_32px_90px_-48px_rgba(8,47,73,0.9)] backdrop-blur sm:p-8 lg:p-10 xl:p-12">
                <div class="space-y-8">
                    <div class="space-y-4 text-center">
                        <div class="flex justify-center">
                            <div class="flex size-20 items-center justify-center rounded-[1.75rem] bg-[linear-gradient(135deg,#020617_0%,#0f766e_45%,#0369a1_100%)] shadow-[0_18px_45px_-24px_rgba(34,211,238,0.5)]">
                                <flux:icon.computer-desktop class="size-9 text-white" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <flux:heading level="1" size="xl" class="text-white">Monitor Antrian PTSP</flux:heading>
                            <flux:text class="text-base leading-7 text-zinc-300">
                                Masuk untuk membuka tampilan TV Display antrian Pengadilan Agama Penajam.
                            </flux:text>
                        </div>
                    </div>

                    @if ($errors->has('password'))
                        <flux:callout variant="danger" icon="x-circle" heading="{{ $errors->first('password') }}" />
                    @endif

                    <form method="POST" action="{{ route('tv-display.authenticate') }}" class="space-y-6">
                        @csrf

                        <flux:field>
                            <flux:label class="text-sm font-semibold tracking-[0.18em] text-zinc-300 uppercase">Password TV Display</flux:label>
                            <flux:input
                                name="password"
                                type="password"
                                inputmode="text"
                                autofocus
                                autocomplete="current-password"
                                placeholder="Masukkan password"
                                class="mt-3 h-18 rounded-2xl border border-white/10 bg-zinc-900/80 px-6 text-2xl text-white placeholder:text-zinc-500"
                            />
                            <flux:error name="password" class="mt-3 text-sm text-rose-300" />
                        </flux:field>

                        <flux:button type="submit" variant="primary" class="h-16 w-full justify-center rounded-2xl text-xl font-semibold">
                            Masuk ke TV Display
                        </flux:button>
                    </form>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts.tv-display>
