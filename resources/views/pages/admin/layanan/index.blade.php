<x-layouts::app :title="__('Manajemen Layanan')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-3">
                <flux:badge color="cyan" rounded>Admin Panel</flux:badge>
                <div>
                    <flux:heading size="xl" level="1">Manajemen Layanan</flux:heading>
                    <flux:subheading class="mt-1">Kelola layanan aktif dan konfigurasi kanal layanan.</flux:subheading>
                </div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Layanan</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge size="sm" color="green" class="hidden sm:flex">{{ $services->total() ?? $services->count() }} layanan</flux:badge>
                <flux:modal.trigger name="create-service">
                    <flux:button variant="primary" icon="plus">
                        Tambah Layanan Baru
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green">
                {{ session('status') }}
            </flux:callout>
        @endif

        @if (session('error'))
            <flux:callout icon="x-circle" color="red">
                {{ session('error') }}
            </flux:callout>
        @endif

        <flux:card class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.clipboard-document-list class="size-5" />
                    </div>
                    <flux:heading size="lg">Daftar Layanan</flux:heading>
                </div>
                <form method="GET" action="{{ route('admin.layanan.index') }}" class="w-full sm:w-auto">
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
                    <flux:table.column>Nama</flux:table.column>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Pool</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($services as $service)
                        <flux:table.row>
                            <flux:table.cell class="font-medium">{{ $service->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $service->code }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $service->queuePool?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($service->is_active)
                                    <flux:badge size="sm" color="green" icon="check-circle">Aktif</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc" icon="x-circle">Nonaktif</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('admin.layanan.update', $service) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" value="{{ $service->name }}">
                                        <input type="hidden" name="description" value="{{ $service->description }}">
                                        <input type="hidden" name="is_active" value="{{ $service->is_active ? 0 : 1 }}">
                                        <flux:button type="submit" variant="ghost" size="sm">
                                            {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </flux:button>
                                    </form>
                                    <flux:modal.trigger name="edit-service-{{ $service->id }}">
                                        <flux:button size="sm" variant="filled" icon="pencil">
                                            Edit
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <form method="POST" action="{{ route('admin.layanan.destroy', $service) }}" class="inline" onsubmit="return confirm('Hapus layanan {{ $service->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" size="sm" variant="danger" icon="trash">
                                            Hapus
                                        </flux:button>
                                    </form>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5">
                                <div class="flex flex-col items-center justify-center py-8 text-center">
                                    <flux:icon name="inbox" class="h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                    <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Belum ada layanan</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Silakan tambah layanan baru menggunakan tombol di atas.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if ($services->hasPages())
                <div class="mt-4">
                    {{ $services->appends(['search' => request('search')])->links() }}
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Create Modal --}}
    <flux:modal name="create-service" class="w-full max-w-2xl">
        <form method="POST" action="{{ route('admin.layanan.store') }}" class="space-y-4">
            @csrf
            
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
                    <flux:icon.plus-circle class="size-5" />
                </div>
                <flux:heading size="lg">Tambah Layanan Baru</flux:heading>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>Nama Layanan</flux:label>
                    <flux:input name="name" value="{{ old('name') }}" required />
                </flux:field>

                <flux:field>
                    <flux:label>Kode</flux:label>
                    <flux:input name="code" value="{{ old('code') }}" required />
                </flux:field>

                <flux:field>
                    <flux:label>Slug (Opsional)</flux:label>
                    <flux:input name="slug" value="{{ old('slug') }}" />
                </flux:field>

                <flux:field>
                    <flux:label>Queue Pool</flux:label>
                    <flux:select name="queue_pool_id" required>
                        <flux:select.option value="">Pilih Pool</flux:select.option>
                        @foreach ($queuePools as $pool)
                            <flux:select.option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->code }})</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Sort Order</flux:label>
                    <flux:input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" />
                </flux:field>

                <flux:field>
                    <flux:label>Kuota Harian</flux:label>
                    <flux:input type="number" name="daily_quota" value="{{ old('daily_quota') }}" placeholder="Kosongkan jika tak terbatas" />
                </flux:field>

                <div class="grid gap-3 sm:grid-cols-3 md:col-span-2">
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <flux:checkbox name="is_active" value="1" :checked="(bool) old('is_active', 1)" label="Aktif" />
                    </div>
                    <div>
                        <input type="hidden" name="booking_enabled" value="0">
                        <flux:checkbox name="booking_enabled" value="1" :checked="(bool) old('booking_enabled', 1)" label="Terima Booking" />
                    </div>
                    <div>
                        <input type="hidden" name="walk_in_enabled" value="0">
                        <flux:checkbox name="walk_in_enabled" value="1" :checked="(bool) old('walk_in_enabled', 1)" label="Terima Walk-in" />
                    </div>
                </div>

                <flux:field class="md:col-span-2">
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea name="description" rows="2">{{ old('description') }}</flux:textarea>
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Persyaratan (Opsional)</flux:label>
                    <flux:textarea name="requirements" rows="2">{{ old('requirements') }}</flux:textarea>
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Tambah Layanan</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modals --}}
    @foreach ($services as $service)
        <flux:modal name="edit-service-{{ $service->id }}" class="w-full max-w-2xl">
            <form method="POST" action="{{ route('admin.layanan.update', $service) }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <flux:heading size="lg">Edit Layanan: {{ $service->name }}</flux:heading>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Nama Layanan</flux:label>
                        <flux:input name="name" value="{{ $service->name }}" required />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kode</flux:label>
                        <flux:input name="code" value="{{ $service->code }}" required />
                    </flux:field>

                    <flux:field>
                        <flux:label>Slug</flux:label>
                        <flux:input name="slug" value="{{ $service->slug }}" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Queue Pool</flux:label>
                        <flux:select name="queue_pool_id" required>
                            @foreach ($queuePools as $pool)
                                <flux:select.option value="{{ $pool->id }}" :selected="$pool->id === $service->queue_pool_id">
                                    {{ $pool->name }} ({{ $pool->code }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Kode Huruf Antrian</flux:label>
                        <flux:input name="letter_code" value="{{ $service->letter_code }}" maxlength="3" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Sort Order</flux:label>
                        <flux:input type="number" name="sort_order" value="{{ $service->sort_order }}" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kuota Harian</flux:label>
                        <flux:input type="number" name="daily_quota" value="{{ $service->daily_quota }}" />
                    </flux:field>

                    <div class="grid gap-3 sm:grid-cols-3 md:col-span-2">
                        <div>
                            <input type="hidden" name="is_active" value="0">
                            <flux:checkbox name="is_active" value="1" :checked="$service->is_active" label="Aktif" />
                        </div>
                        <div>
                            <input type="hidden" name="booking_enabled" value="0">
                            <flux:checkbox name="booking_enabled" value="1" :checked="$service->booking_enabled" label="Booking" />
                        </div>
                        <div>
                            <input type="hidden" name="walk_in_enabled" value="0">
                            <flux:checkbox name="walk_in_enabled" value="1" :checked="$service->walk_in_enabled" label="Walk-in" />
                        </div>
                    </div>

                    <flux:field class="md:col-span-2">
                        <flux:label>Deskripsi</flux:label>
                        <flux:textarea name="description" rows="2">{{ $service->description }}</flux:textarea>
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>Persyaratan</flux:label>
                        <flux:textarea name="requirements" rows="2">{{ $service->requirements }}</flux:textarea>
                    </flux:field>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach
</x-layouts::app>
