<x-layouts::app :title="__('Setting Wilayah')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-3">
                <flux:badge color="emerald" rounded>Admin Panel</flux:badge>
                <div>
                    <flux:heading size="xl" level="1">Setting Wilayah</flux:heading>
                    <flux:subheading class="mt-1">Pilih kabupaten aktif untuk membatasi pilihan kelurahan/desa pada kiosk.</flux:subheading>
                </div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Admin</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Setting Wilayah</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green">
                {{ session('status') }}
            </flux:callout>
        @endif

        <flux:card class="admin-stat-success admin-card-elevated p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                    <flux:icon.map-pin class="size-5" />
                </div>
                <flux:heading size="lg">Kabupaten Aktif</flux:heading>
            </div>

            @if ($selectedKabupaten)
                <div class="rounded-xl bg-emerald-100 px-4 py-3 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                    <span class="font-semibold">{{ $selectedKabupaten->nama }}</span>
                    <span class="ml-2 text-sm opacity-70">{{ $selectedKabupaten->kode }}</span>
                </div>
            @else
                <div class="rounded-xl bg-amber-100 px-4 py-3 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                    Belum ada kabupaten yang dipilih.
                </div>
            @endif
        </flux:card>

        <flux:card class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.map class="size-5" />
                    </div>
                    <flux:heading size="lg">Daftar Kabupaten</flux:heading>
                </div>

                <form method="GET" action="{{ route('admin.wilayah.index') }}" class="w-full sm:w-auto">
                    <flux:input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama atau kode..."
                        icon="magnifying-glass"
                        class="w-full sm:w-64"
                    />
                </form>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Kabupaten/Kota</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column class="text-right">Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($kabupatenList as $kabupaten)
                        <flux:table.row>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $kabupaten->kode }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="font-medium">{{ $kabupaten->nama }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($selectedKabupatenKode === $kabupaten->kode)
                                    <flux:badge size="sm" color="emerald" icon="check-circle">Aktif</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">Tidak Aktif</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="text-right">
                                @if ($selectedKabupatenKode !== $kabupaten->kode)
                                    <form method="POST" action="{{ route('admin.wilayah.update') }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="kabupaten_kode" value="{{ $kabupaten->kode }}">
                                        <flux:button type="submit" size="sm" variant="outline" icon="check">
                                            Pilih
                                        </flux:button>
                                    </form>
                                @else
                                    <flux:button size="sm" variant="filled" icon="check" disabled>
                                        Aktif
                                    </flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4">
                                <div class="flex flex-col items-center justify-center py-8 text-center">
                                    <flux:icon name="inbox" class="h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                    <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Data tidak ditemukan</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Silakan gunakan kata kunci pencarian yang lain.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div>
                {{ $kabupatenList->links() }}
            </div>
        </flux:card>
    </div>
</x-layouts::app>
