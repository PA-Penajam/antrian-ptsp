<x-layouts.kiosk :title="'Kiosk - Step 1'">
    <div class="flex min-h-screen flex-col justify-between gap-6 p-4 sm:p-6 lg:p-8">
        <div class="mx-auto flex w-full max-w-5xl flex-1 items-center justify-center">
            <flux:card class="w-full border-white/10 bg-zinc-950/82 p-8 text-center shadow-[0_32px_90px_-48px_rgba(8,47,73,0.9)] backdrop-blur sm:p-10">
                <div class="space-y-5">
                    <div class="flex justify-center">
                        <flux:badge color="cyan" rounded size="sm">Placeholder Task 15</flux:badge>
                    </div>

                    <div class="space-y-3">
                        <flux:heading level="1" size="xl" class="text-white">Kiosk - Step 1</flux:heading>
                        <flux:text class="mx-auto max-w-2xl text-base leading-7 text-zinc-300">
                            Halaman ini masih placeholder. Wizard pengambilan antrian mandiri akan dibangun pada Task 15.
                        </flux:text>
                    </div>
                </div>
            </flux:card>
        </div>

        <div class="mx-auto w-full max-w-5xl">
            <form method="POST" action="{{ route('kiosk.logout') }}" class="flex justify-end">
                @csrf

                <flux:button type="submit" variant="ghost" icon="arrow-right-start-on-rectangle" class="rounded-2xl border border-white/10 bg-zinc-900/70 px-6 py-4 text-lg text-zinc-100">
                    Keluar
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts.kiosk>
