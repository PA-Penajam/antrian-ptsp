<x-layouts::app :title="__('Setting Wilayah')">
    <div class="w-full space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
        <div class="animate-fade-in-up flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" aria-label="Beranda" />
                    <flux:breadcrumbs.item>Wilayah</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <flux:heading size="xl" level="1" class="font-extrabold tracking-tight">Setting Wilayah</flux:heading>
                <flux:subheading class="text-zinc-600 dark:text-zinc-400">Pilih kabupaten/kota tempat instansi beroperasi untuk membatasi wilayah di Kiosk.</flux:subheading>
            </div>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green" class="animate-fade-in-up rounded-2xl shadow-xs" style="animation-delay: 100ms;">
                {{ session('status') }}
            </flux:callout>
        @endif

        @if (session('error'))
            <flux:callout icon="x-circle" color="red" class="animate-fade-in-up rounded-2xl shadow-xs" style="animation-delay: 100ms;">
                {{ session('error') }}
            </flux:callout>
        @endif

        <div class="animate-fade-in-up" style="animation-delay: 150ms;">
            <flux:card class="admin-stat-success admin-card-elevated p-5 sm:p-6 overflow-hidden relative">
                <!-- Background decoration -->
                <div class="absolute -right-6 -top-6 text-emerald-500/10 dark:text-emerald-500/5 rotate-12 pointer-events-none">
                    <flux:icon.map-pin class="size-48" />
                </div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="admin-icon-box bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300">
                            <flux:icon.map-pin class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold text-emerald-900 dark:text-emerald-100">Kabupaten Aktif</flux:heading>
                            <flux:text class="text-xs text-emerald-700/80 dark:text-emerald-300/80">Wilayah yang sedang digunakan oleh instansi</flux:text>
                        </div>
                    </div>

                    @if ($selectedKabupaten)
                        <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-white/60 p-4 shadow-sm ring-1 ring-emerald-200/50 backdrop-blur-md dark:bg-zinc-900/50 dark:ring-emerald-800/30">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                                <flux:icon.building-library class="size-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-bold text-emerald-950 dark:text-emerald-50">{{ $selectedKabupaten->nama }}</span>
                                    <div class="flex items-center gap-1.5 rounded-full bg-emerald-100 py-0.5 pl-1.5 pr-2 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <span class="admin-live-dot size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                        Aktif
                                    </div>
                                </div>
                                <div class="mt-0.5 text-xs font-medium text-emerald-700/80 dark:text-emerald-400/80">Kode Wilayah: <span class="font-mono text-emerald-800 dark:text-emerald-300">{{ $selectedKabupaten->kode }}</span></div>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-amber-50 p-4 shadow-sm ring-1 ring-amber-200/50 dark:bg-amber-950/30 dark:ring-amber-800/30">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                                <flux:icon.exclamation-triangle class="size-5" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-amber-900 dark:text-amber-100">Belum ada wilayah yang dipilih</div>
                                <div class="mt-0.5 text-xs font-medium text-amber-700/80 dark:text-amber-400/80">Silakan pilih satu kabupaten dari daftar di bawah.</div>
                            </div>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        <div class="animate-fade-in-up" style="animation-delay: 200ms;">
            <flux:card class="admin-card-elevated overflow-hidden rounded-3xl border border-zinc-200 bg-white p-0 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 border-b border-zinc-100 p-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/70 dark:text-cyan-300">
                            <flux:icon.map class="size-5" />
                        </div>
                        <div>
                            <flux:heading id="wilayah-list-heading" size="lg" class="font-bold">Daftar Kabupaten</flux:heading>
                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Cari dan tentukan wilayah operasi instansi.</flux:text>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.wilayah.index') }}" class="w-full sm:w-auto" x-data="{ searching: false }" @submit="searching = true">
                        <flux:input
                            name="search"
                            aria-label="Cari kabupaten"
                            value="{{ request('search') }}"
                            placeholder="Cari nama atau kode..."
                            icon="magnifying-glass"
                            clearable
                            class="w-full sm:w-64"
                            x-bind:disabled="searching"
                        />
                    </form>
                </div>

                <p id="wilayah-list-scroll-hint" class="sr-only">Geser tabel secara horizontal untuk melihat seluruh kolom.</p>
                <div 
                    class="admin-table-scroll overflow-x-auto px-5 pb-5 sm:px-6 sm:pb-6"
                    tabindex="0"
                    aria-labelledby="wilayah-list-heading"
                    aria-describedby="wilayah-list-scroll-hint"
                >
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Kode</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Kabupaten/Kota</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Status</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-right text-xs font-bold uppercase tracking-wider">Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows class="admin-row-stagger">
                            @forelse ($kabupatenList as $kabupaten)
                                <flux:table.row class="admin-row-enter transition-colors hover:bg-cyan-50/50 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                    <flux:table.cell class="whitespace-nowrap">
                                        <flux:badge size="sm" color="zinc" class="font-mono font-bold">{{ $kabupaten->kode }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">{{ $kabupaten->nama }}</flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($selectedKabupatenKode === $kabupaten->kode)
                                            <flux:badge size="sm" color="emerald" icon="check-circle" class="font-bold">Aktif</flux:badge>
                                        @else
                                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Tidak Aktif</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap text-right">
                                        @if ($selectedKabupatenKode !== $kabupaten->kode)
                                            <form method="POST" action="{{ route('admin.wilayah.update') }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="kabupaten_kode" value="{{ $kabupaten->kode }}">
                                                <flux:button 
                                                    type="submit" 
                                                    size="sm" 
                                                    variant="primary" 
                                                    icon="check" 
                                                    aria-label="Pilih {{ $kabupaten->nama }} sebagai wilayah aktif"
                                                    class="font-semibold shadow-2xs"
                                                    x-bind:disabled="submitting"
                                                >
                                                    Pilih
                                                </flux:button>
                                            </form>
                                        @else
                                            <flux:button size="sm" variant="filled" color="emerald" icon="check" class="font-semibold" disabled aria-label="{{ $kabupaten->nama }} sudah aktif">
                                                Aktif
                                            </flux:button>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4">
                                        <div class="flex flex-col items-center justify-center py-8 text-center">
                                            <div class="admin-empty-icon flex size-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300">
                                                <flux:icon name="magnifying-glass" class="size-7" />
                                            </div>
                                            <p class="mt-4 whitespace-normal text-sm font-bold text-zinc-900 dark:text-zinc-100">Wilayah tidak ditemukan</p>
                                            <p class="mx-auto mt-1 max-w-72 whitespace-normal text-xs leading-5 text-zinc-600 dark:text-zinc-400 sm:max-w-sm">Coba gunakan nama kabupaten atau kode lain.</p>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>

                @if ($kabupatenList->hasPages())
                    <div class="border-t border-zinc-100 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        {{ $kabupatenList->links() }}
                    </div>
                @endif
            </flux:card>
        </div>
    </div>
</x-layouts::app>
