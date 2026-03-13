<x-layouts::app :title="__('Manajemen Loket')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-3">
                <flux:badge color="amber" rounded>Admin Panel</flux:badge>
                <div>
                    <flux:heading size="xl" level="1">Manajemen Loket</flux:heading>
                    <flux:subheading class="mt-1">Kelola loket antrian, mapping pool, dan status aktif.</flux:subheading>
                </div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Loket</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green">
                {{ session('status') }}
            </flux:callout>
        @endif

        @if (session('error'))
            <flux:callout icon="exclamation-circle" color="red">
                {{ session('error') }}
            </flux:callout>
        @endif

        {{-- Form Tambah Loket --}}
        <flux:card class="admin-card-elevated space-y-4 border-amber-200 dark:border-amber-800">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                    <flux:icon.plus-circle class="size-5" />
                </div>
                <flux:heading size="lg">Tambah Loket Baru</flux:heading>
            </div>
            <form method="POST" action="{{ route('admin.loket.store') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @csrf
                <flux:field>
                    <flux:label>Nama Loket</flux:label>
                    <flux:input name="name" value="{{ old('name') }}" placeholder="Contoh: Loket 1" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Kode</flux:label>
                    <flux:input name="code" value="{{ old('code') }}" placeholder="Contoh: L1" required />
                    <flux:error name="code" />
                </flux:field>

                <flux:field>
                    <flux:label>Queue Pool</flux:label>
                    <flux:select name="queue_pool_id" required>
                        <flux:select.option value="">Pilih Pool</flux:select.option>
                        @foreach($queuePools as $pool)
                            <flux:select.option value="{{ $pool->id }}" :selected="old('queue_pool_id') == $pool->id">
                                {{ $pool->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="queue_pool_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Urutan</flux:label>
                    <flux:input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>

                <div class="flex items-end">
                    <flux:button type="submit" variant="primary" class="w-full">
                        <flux:icon name="plus" class="mr-2 h-4 w-4" />
                        Tambah Loket
                    </flux:button>
                </div>
            </form>
        </flux:card>

        {{-- Daftar Loket --}}
        <flux:card class="space-y-4">
            <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.building-office class="size-5" />
                    </div>
                    <flux:heading size="lg">Daftar Loket</flux:heading>
                </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Loket</flux:table.column>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Pool</flux:table.column>
                    <flux:table.column>Urutan</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($counters as $counter)
                        <flux:table.row>
                            <flux:table.cell>{{ $counter->name }}</flux:table.cell>
                            <flux:table.cell>{{ $counter->code }}</flux:table.cell>
                            <flux:table.cell>{{ $counter->queuePool?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $counter->sort_order }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($counter->is_active)
                                    <flux:badge color="green">Aktif</flux:badge>
                                @else
                                    <flux:badge color="red">Nonaktif</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="ghost" icon="pencil"
                                        x-data x-on:click="$dispatch('open-modal', 'edit-counter-{{ $counter->id }}')">
                                        Edit
                                    </flux:button>
                                    <form method="POST" action="{{ route('admin.loket.destroy', $counter) }}" class="inline"
                                        onsubmit="return confirm('Yakin ingin menghapus loket {{ $counter->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" size="sm" variant="ghost" icon="trash" color="red">
                                            Hapus
                                        </flux:button>
                                    </form>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-8">
                                <flux:icon name="inbox" class="mx-auto h-12 w-12 text-gray-400" />
                                <p class="mt-2 text-sm text-gray-500">Belum ada loket</p>
                                <p class="text-xs text-gray-400">Silakan tambah loket baru menggunakan form di atas.</p>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    {{-- Edit Modals --}}
    @foreach ($counters as $counter)
        <flux:modal name="edit-counter-{{ $counter->id }}" class="w-full max-w-md">
            <form method="POST" action="{{ route('admin.loket.update', $counter) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <flux:heading>Edit Loket: {{ $counter->name }}</flux:heading>

                <flux:field>
                    <flux:label>Nama Loket</flux:label>
                    <flux:input name="name" value="{{ old('name', $counter->name) }}" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Kode</flux:label>
                    <flux:input name="code" value="{{ old('code', $counter->code) }}" required />
                    <flux:error name="code" />
                </flux:field>

                <flux:field>
                    <flux:label>Queue Pool</flux:label>
                    <flux:select name="queue_pool_id" required>
                        @foreach($queuePools as $pool)
                            <flux:select.option value="{{ $pool->id }}" :selected="old('queue_pool_id', $counter->queue_pool_id) == $pool->id">
                                {{ $pool->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="queue_pool_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Urutan</flux:label>
                    <flux:input type="number" name="sort_order" value="{{ old('sort_order', $counter->sort_order) }}" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select name="is_active" required>
                        <flux:select.option value="1" :selected="old('is_active', $counter->is_active) == 1">Aktif</flux:select.option>
                        <flux:select.option value="0" :selected="old('is_active', $counter->is_active) == 0">Nonaktif</flux:select.option>
                    </flux:select>
                    <flux:error name="is_active" />
                </flux:field>

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" x-on:click="$dispatch('close-modal', 'edit-counter-{{ $counter->id }}')">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach
</x-layouts::app>
