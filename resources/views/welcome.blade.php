<x-layouts::public :title="__('Selamat Datang') . ' - ' . config('app.name')">
    <flux:main container>
        <div class="max-w-4xl mx-auto space-y-10 py-8">
            {{-- Hero Section --}}
            <div class="text-center space-y-4">
                <flux:heading size="xl" level="1">Sistem Antrian PTSP</flux:heading>
                <flux:subheading class="max-w-2xl mx-auto">
                    Pelayanan Terpadu Satu Pintu — Ambil nomor antrian, pantau status, dan dapatkan layanan lebih cepat.
                </flux:subheading>
            </div>

            {{-- Quick Actions --}}
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <flux:card class="text-center space-y-4">
                    <div class="flex justify-center">
                        <flux:icon.ticket class="size-10 text-zinc-500 dark:text-zinc-400" />
                    </div>
                    <flux:heading size="lg">Ambil Antrian</flux:heading>
                    <flux:text>Booking nomor antrian secara online untuk layanan PTSP.</flux:text>
                    <flux:button href="{{ url('/antrian') }}" variant="primary" class="w-full">
                        Ambil Antrian
                    </flux:button>
                </flux:card>

                <flux:card class="text-center space-y-4">
                    <div class="flex justify-center">
                        <flux:icon.magnifying-glass class="size-10 text-zinc-500 dark:text-zinc-400" />
                    </div>
                    <flux:heading size="lg">Cek Status Tiket</flux:heading>
                    <flux:text>Periksa status dan posisi antrian Anda saat ini.</flux:text>
                    <flux:button href="{{ url('/antrian/cek') }}" variant="filled" class="w-full">
                        Cek Tiket
                    </flux:button>
                </flux:card>

                <flux:card class="text-center space-y-4">
                    <div class="flex justify-center">
                        <flux:icon.tv class="size-10 text-zinc-500 dark:text-zinc-400" />
                    </div>
                    <flux:heading size="lg">Display Antrian</flux:heading>
                    <flux:text>Lihat tampilan panggilan antrian secara real-time.</flux:text>
                    <flux:button href="{{ url('/display') }}" variant="filled" class="w-full">
                        Lihat Display
                    </flux:button>
                </flux:card>
            </div>

            {{-- Auth Section --}}
            <div class="flex justify-center gap-3">
                @auth
                    <flux:button href="{{ route('dashboard') }}" variant="primary" icon="squares-2x2">
                        Dashboard
                    </flux:button>
                @else
                    <flux:button href="{{ route('login') }}" variant="filled" icon="arrow-right-start-on-rectangle">
                        Masuk
                    </flux:button>

                    @if (Route::has('register'))
                        <flux:button href="{{ route('register') }}" variant="subtle" icon="user-plus">
                            Daftar
                        </flux:button>
                    @endif
                @endauth
            </div>
        </div>
    </flux:main>
</x-layouts::public>
