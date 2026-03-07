<x-layouts::app :title="__('Manajemen Layanan')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div>
            <flux:heading size="xl" level="1">Manajemen Layanan</flux:heading>
            <flux:subheading>Kelola layanan aktif dan konfigurasi kanal layanan.</flux:subheading>
        </div>

        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
            <flux:breadcrumbs.item>Layanan</flux:breadcrumbs.item>
        </flux:breadcrumbs>

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
            <flux:heading size="lg">Tambah Layanan</flux:heading>
            <form method="POST" action="{{ route('admin.layanan.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <flux:field>
                    <flux:label>Nama Layanan</flux:label>
                    <flux:input name="name" value="{{ old('name') }}" />
                </flux:field>

                <flux:field>
                    <flux:label>Kode</flux:label>
                    <flux:input name="code" value="{{ old('code') }}" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input name="slug" value="{{ old('slug') }}" />
                </flux:field>

                <flux:field>
                    <flux:label>Queue Pool</flux:label>
                    <flux:select name="queue_pool_id">
                        @foreach ($queuePools as $pool)
                            <flux:select.option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->code }})</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Sort Order</flux:label>
                    <flux:input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" />
                </flux:field>

                <div class="grid gap-3 sm:grid-cols-3 md:col-span-2">
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <flux:checkbox name="is_active" value="1" :checked="(bool) old('is_active', 1)" label="Aktif" />
                    </div>
                    <div>
                        <input type="hidden" name="booking_enabled" value="0">
                        <flux:checkbox name="booking_enabled" value="1" :checked="(bool) old('booking_enabled', 1)" label="Booking" />
                    </div>
                    <div>
                        <input type="hidden" name="walk_in_enabled" value="0">
                        <flux:checkbox name="walk_in_enabled" value="1" :checked="(bool) old('walk_in_enabled', 1)" label="Walk-in" />
                    </div>
                </div>

                <flux:field class="md:col-span-2">
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea name="description">{{ old('description') }}</flux:textarea>
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Persyaratan</flux:label>
                    <flux:textarea name="requirements">{{ old('requirements') }}</flux:textarea>
                </flux:field>

                <flux:button type="submit" variant="primary" class="md:col-span-2">Simpan Layanan</flux:button>
            </form>
        </flux:card>

        <flux:card class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <flux:heading size="lg">Daftar Layanan</flux:heading>
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
                            <flux:table.cell>{{ $service->name }}</flux:table.cell>
                            <flux:table.cell>{{ $service->code }}</flux:table.cell>
                            <flux:table.cell>{{ $service->queuePool?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$service->is_active ? 'green' : 'zinc'">
                                    {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                </flux:badge>
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
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'edit-service-{{ $service->id }}')"
                                    >
                                        Edit
                                    </flux:button>
                                    <form method="POST" action="{{ route('admin.layanan.destroy', $service) }}" class="inline" onsubmit="return confirm('Hapus layanan {{ $service->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" size="sm" variant="danger">
                                            Hapus
                                        </flux:button>
                                    </form>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500">
                                Belum ada layanan
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

    @foreach ($services as $service)
        <flux:modal name="edit-service-{{ $service->id }}" class="w-full max-w-xl">
            <form method="POST" action="{{ route('admin.layanan.update', $service) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <flux:heading>Edit Layanan: {{ $service->name }}</flux:heading>

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
                    <flux:select name="queue_pool_id">
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

                <div class="grid gap-3 sm:grid-cols-3">
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

                <flux:field>
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea name="description">{{ $service->description }}</flux:textarea>
                </flux:field>

                <flux:field>
                    <flux:label>Persyaratan</flux:label>
                    <flux:textarea name="requirements">{{ $service->requirements }}</flux:textarea>
                </flux:field>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button type="button" variant="ghost" x-on:click="$dispatch('close-modal', 'edit-service-{{ $service->id }}')">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach
</x-layouts::app>
