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

        <flux:card class="admin-card-elevated space-y-4">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon.magnifying-glass class="size-5" />
                </div>
                <flux:heading size="lg">Pilih Kabupaten</flux:heading>
            </div>

            <form method="GET" action="{{ route('admin.wilayah.index') }}" class="grid gap-3 sm:grid-cols-[1fr_auto]">
                <flux:input
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama atau kode kabupaten"
                />
                <flux:button type="submit" icon="magnifying-glass">Cari</flux:button>
            </form>

            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Kode</flux:table.column>
                        <flux:table.column>Kabupaten/Kota</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($kabupatenList as $kabupaten)
                            <flux:table.row>
                                <flux:table.cell>{{ $kabupaten->kode }}</flux:table.cell>
                                <flux:table.cell>{{ $kabupaten->nama }}</flux:table.cell>
                                <flux:table.cell class="text-right">
                                    <form method="POST" action="{{ route('admin.wilayah.update') }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="kabupaten_kode" value="{{ $kabupaten->kode }}">
                                        <flux:button
                                            type="submit"
                                            size="sm"
                                            variant="{{ $selectedKabupatenKode === $kabupaten->kode ? 'filled' : 'outline' }}"
                                            icon="check"
                                        >
                                            {{ $selectedKabupatenKode === $kabupaten->kode ? 'Aktif' : 'Pilih' }}
                                        </flux:button>
                                    </form>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="3" class="text-center text-zinc-500">
                                    Data kabupaten tidak ditemukan.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>

            <div>
                {{ $kabupatenList->links() }}
            </div>
        </flux:card>
    </div>
</x-layouts::app>
