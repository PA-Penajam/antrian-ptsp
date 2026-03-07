<x-layouts.kiosk :title="'Login Kiosk'">
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="grid w-full max-w-6xl gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(24rem,30rem)] lg:gap-8">
            <section class="hidden rounded-[2rem] border border-white/10 bg-white/6 p-8 shadow-[0_32px_90px_-48px_rgba(15,23,42,0.95)] lg:flex lg:flex-col lg:justify-between">
                <div class="space-y-6">
                    <flux:badge color="zinc" size="sm" icon="device-phone-mobile" class="w-fit rounded-full border border-white/10 bg-white/10 text-zinc-100">
                        Mode Kiosk PTSP
                    </flux:badge>

                    <div class="space-y-4">
                        <flux:heading level="1" size="xl" class="text-balance text-white">
                            Layar mandiri untuk pengambilan antrian yang cepat dan terarah.
                        </flux:heading>

                        <flux:text class="max-w-2xl text-base leading-7 text-zinc-300">
                            Gunakan password kiosk untuk membuka mode layar sentuh. Halaman ini disiapkan untuk perangkat potret tanpa sidebar agar alur pengunjung tetap fokus.
                        </flux:text>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-3xl border border-cyan-400/20 bg-cyan-400/10 p-4">
                        <p class="text-xs font-semibold tracking-[0.18em] text-cyan-200 uppercase">Fokus</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-100">Satu layar khusus untuk pelayanan mandiri kiosk.</p>
                    </div>
                    <div class="rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                        <p class="text-xs font-semibold tracking-[0.18em] text-emerald-200 uppercase">Sentuh</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-100">Komponen besar agar nyaman dipakai di perangkat touchscreen.</p>
                    </div>
                    <div class="rounded-3xl border border-amber-400/20 bg-amber-400/10 p-4">
                        <p class="text-xs font-semibold tracking-[0.18em] text-amber-200 uppercase">Aman</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-100">Akses dilindungi password modul dan session timeout.</p>
                    </div>
                </div>
            </section>

            <flux:card class="border-white/10 bg-zinc-950/80 p-6 shadow-[0_32px_90px_-48px_rgba(8,47,73,0.9)] backdrop-blur sm:p-8 lg:p-10">
                <div class="space-y-8">
                    <div class="space-y-4 text-center">
                        <div class="flex justify-center">
                            <div class="flex size-18 items-center justify-center rounded-[1.75rem] bg-[linear-gradient(135deg,#0f172a_0%,#155e75_48%,#0f766e_100%)] shadow-[0_18px_45px_-24px_rgba(6,182,212,0.55)]">
                                <flux:icon.lock-closed class="size-8 text-white" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <flux:heading level="1" size="xl" class="text-white">Selamat Datang</flux:heading>
                            <flux:text class="text-base leading-7 text-zinc-300">
                                Antrian PTSP Pengadilan Agama Penajam
                            </flux:text>
                        </div>
                    </div>

                    @if ($errors->has('password'))
                        <flux:callout variant="danger" icon="x-circle" heading="{{ $errors->first('password') }}" />
                    @endif

                    <form method="POST" action="{{ route('kiosk.authenticate') }}" class="space-y-6">
                        @csrf

                        <flux:field>
                            <flux:label class="text-sm font-semibold tracking-[0.18em] text-zinc-300 uppercase">Password Kiosk</flux:label>
                            <flux:input
                                name="password"
                                type="password"
                                inputmode="text"
                                autofocus
                                autocomplete="current-password"
                                placeholder="Masukkan password"
                                class="mt-3 h-16 rounded-2xl border border-white/10 bg-zinc-900/80 px-5 text-xl text-white placeholder:text-zinc-500"
                            />
                            <flux:error name="password" class="mt-3 text-sm text-rose-300" />
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
