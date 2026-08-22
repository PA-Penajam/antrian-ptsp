<x-layouts::auth :title="__('Masuk ke Sistem PTSP')">
    <div class="flex flex-col gap-5 sm:gap-6">
        {{-- Card Header --}}
        <div class="flex flex-col items-center text-center space-y-1.5">
            <flux:heading level="1" size="xl" class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                {{ __('Masuk ke Sistem PTSP') }}
            </flux:heading>
            <flux:subheading class="text-xs sm:text-sm text-slate-600 dark:text-zinc-400 max-w-xs sm:max-w-sm">
                {{ __('Silakan masukkan kredensial akun dinas Anda untuk memulai sesi layanan operasional.') }}
            </flux:subheading>
        </div>

        {{-- Role Navigation Overview --}}
        <div x-data="{ activeTab: 'loket' }" class="rounded-2xl border border-cyan-100 dark:border-zinc-800 bg-cyan-50/40 dark:bg-zinc-800/30 p-2.5">
            <div class="flex items-center justify-between gap-1">
                <button 
                    type="button" 
                    @click="activeTab = 'loket'" 
                    :class="activeTab === 'loket' ? 'bg-white dark:bg-zinc-800 text-cyan-900 dark:text-cyan-300 shadow-2xs font-bold border-cyan-200/80 dark:border-zinc-700' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white border-transparent'"
                    class="flex-1 rounded-xl border py-1.5 text-center text-xs transition-all cursor-pointer"
                >
                    {{ __('Loket') }}
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'frontdesk'" 
                    :class="activeTab === 'frontdesk' ? 'bg-white dark:bg-zinc-800 text-cyan-900 dark:text-cyan-300 shadow-2xs font-bold border-cyan-200/80 dark:border-zinc-700' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white border-transparent'"
                    class="flex-1 rounded-xl border py-1.5 text-center text-xs transition-all cursor-pointer"
                >
                    {{ __('Frontdesk') }}
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'admin'" 
                    :class="activeTab === 'admin' ? 'bg-white dark:bg-zinc-800 text-cyan-900 dark:text-cyan-300 shadow-2xs font-bold border-cyan-200/80 dark:border-zinc-700' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white border-transparent'"
                    class="flex-1 rounded-xl border py-1.5 text-center text-xs transition-all cursor-pointer"
                >
                    {{ __('Admin') }}
                </button>
                <button 
                    type="button" 
                    @click="activeTab = 'monitor'" 
                    :class="activeTab === 'monitor' ? 'bg-white dark:bg-zinc-800 text-cyan-900 dark:text-cyan-300 shadow-2xs font-bold border-cyan-200/80 dark:border-zinc-700' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white border-transparent'"
                    class="flex-1 rounded-xl border py-1.5 text-center text-xs transition-all cursor-pointer"
                >
                    {{ __('Pimpinan') }}
                </button>
            </div>

            <div class="mt-2 text-center text-xs text-slate-600 dark:text-zinc-400 px-1">
                <span x-show="activeTab === 'loket'">🏛️ {{ __('Petugas Loket: Panggil, panggil ulang, dan layani tiket antrian aktif.') }}</span>
                <span x-show="activeTab === 'frontdesk'" x-cloak>🎫 {{ __('Petugas Frontdesk: Penerbitan tiket langsung & verifikasi check-in booking.') }}</span>
                <span x-show="activeTab === 'admin'" x-cloak>⚙️ {{ __('Administrator: Pengaturan layanan, loket, kuota antrian, & manajemen akun.') }}</span>
                <span x-show="activeTab === 'monitor'" x-cloak>📊 {{ __('Pimpinan / Monitor: Rekap laporan statistik & audit trail operasional.') }}</span>
            </div>
        </div>

        {{-- Session Status Flash --}}
        <x-auth-session-status class="text-center" :status="session('status')" />

        {{-- Authentication Form --}}
        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4.5 sm:gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Alamat Pos-el / Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="nama@pengadilan.go.id"
                icon="envelope"
                class="w-full"
            />

            <!-- Password -->
            <div class="relative space-y-1">
                <div class="flex items-center justify-between">
                    <flux:label for="password">{{ __('Kata Sandi / Password') }}</flux:label>

                    @if (Route::has('password.request'))
                        <flux:link class="text-xs font-semibold text-cyan-700 hover:text-cyan-800 dark:text-cyan-400 hover:underline" :href="route('password.request')" wire:navigate>
                            {{ __('Lupa kata sandi?') }}
                        </flux:link>
                    @endif
                </div>

                <flux:input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Masukkan kata sandi')"
                    viewable
                    icon="key"
                    class="w-full"
                />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <flux:checkbox name="remember" :label="__('Ingat sesi saya di perangkat ini')" :checked="old('remember')" />
            </div>

            <!-- Submit Button -->
            <flux:button 
                variant="primary" 
                type="submit" 
                icon="arrow-right-start-on-rectangle"
                class="w-full h-12 rounded-2xl bg-gradient-to-r from-cyan-700 via-cyan-600 to-teal-700 font-bold text-white shadow-lg shadow-cyan-700/25 transition-all duration-200 hover:brightness-105 hover:shadow-cyan-700/35 active:scale-[0.99] cursor-pointer" 
                data-test="login-button"
            >
                {{ __('Masuk ke Layanan PTSP') }}
            </flux:button>
        </form>

        {{-- Helpdesk / Assistance Note --}}
        <div class="rounded-2xl border border-cyan-100 dark:border-zinc-800/80 bg-cyan-50/50 dark:bg-zinc-800/40 p-3 text-center">
            <p class="text-xs text-slate-600 dark:text-zinc-400">
                <flux:icon.information-circle class="inline size-3.5 mr-1 text-cyan-700 dark:text-cyan-400 align-text-bottom" />
                {{ __('Kendala login akun dinas? Hubungi Administrator TI Pengadilan Agama.') }}
            </p>
        </div>

        @if (Route::has('register'))
            <div class="space-x-1 text-xs text-center rtl:space-x-reverse text-zinc-500 dark:text-zinc-400">
                <span>{{ __('Belum memiliki akun?') }}</span>
                <flux:link :href="route('register')" wire:navigate class="font-semibold text-cyan-700 dark:text-cyan-400">{{ __('Daftar Akun Baru') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>

