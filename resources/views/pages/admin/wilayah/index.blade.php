<x-layouts::app :title="__('Setting Wilayah')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div>
            <flux:heading size="xl" level="1">Setting Wilayah</flux:heading>
            <flux:subheading>Pilih kabupaten aktif untuk membatasi pilihan kelurahan/desa pada kiosk.</flux:subheading>
        </div>

        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
            <flux:breadcrumbs.item>Admin</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Setting Wilayah</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green">
                {{ session('status') }}
            </flux:callout>
        @endif

        <flux:card class="space-y-4">
            <flux:heading size="lg">Kabupaten Aktif</flux:heading>

            @if ($selectedKabupaten)
                <div class="rounded-xl bg-emerald-50 px-4 py-3 text-emerald-800">
                    <span class="font-semibold">{{ $selectedKabupaten->nama }}</span>
                    <span class="ml-2 text-sm">{{ $selectedKabupaten->kode }}</span>
                </div>
            @else
                <div class="rounded-xl bg-amber-50 px-4 py-3 text-amber-800">
                    Belum ada kabupaten yang dipilih.
                </div>
            @endif
        </flux:card>

        <flux:card class="space-y-4">
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
