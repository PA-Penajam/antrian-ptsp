<x-layouts.tv-display :title="'TV Display'">
    <div class="flex min-h-screen flex-col justify-between p-6 sm:p-8 lg:p-10">
        <div class="rounded-[2rem] border border-white/10 bg-white/6 p-6 shadow-[0_32px_90px_-48px_rgba(15,23,42,0.95)] sm:p-8 lg:p-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-4">
                    <flux:badge color="zinc" size="sm" icon="presentation-chart-line" class="w-fit rounded-full border border-white/10 bg-white/10 text-zinc-100">
                        Placeholder Task 18
                    </flux:badge>
                    <div class="space-y-3">
                        <flux:heading level="1" size="xl" class="text-white">TV Display - Antrian</flux:heading>
                        <flux:text class="max-w-3xl text-base leading-7 text-zinc-300">
                            Halaman ini disiapkan sebagai placeholder untuk komponen tampilan antrian realtime pada task berikutnya.
                        </flux:text>
                    </div>
                </div>

                <div class="rounded-3xl border border-cyan-400/20 bg-cyan-400/10 px-5 py-4 text-sm leading-6 text-cyan-50">
                    Belum ada queue display aktif. Integrasi Livewire akan ditambahkan pada Task 18.
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-6">
            <form method="POST" action="{{ route('tv-display.logout') }}">
                @csrf

                <flux:button type="submit" variant="filled" icon="arrow-right-start-on-rectangle" class="rounded-2xl px-5 py-3 text-base font-semibold">
                    Logout TV Display
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts.tv-display>
