<x-layouts::app :title="__('Manajemen Layanan')">
    <flux:main container>
        <div class="mx-auto max-w-6xl space-y-6">
            <div>
                <flux:heading size="xl" level="1">Manajemen Layanan</flux:heading>
                <flux:subheading>Kelola layanan aktif dan konfigurasi kanal layanan.</flux:subheading>
            </div>

            @if (session('status'))
                <flux:callout icon="check-circle" color="green">
                    {{ session('status') }}
                </flux:callout>
            @endif

            <flux:card class="space-y-4">
                <flux:heading size="lg">Tambah Layanan</flux:heading>
                <form method="POST" action="/admin/layanan" class="grid gap-4 md:grid-cols-2">
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
                                <option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->code }})</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Sort Order</flux:label>
                        <flux:input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" />
                    </flux:field>

                    <div class="grid gap-2 sm:grid-cols-3 md:col-span-2">
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Aktif</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="booking_enabled" value="0">
                            <input type="checkbox" name="booking_enabled" value="1" checked>
                            <span>Booking</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="walk_in_enabled" value="0">
                            <input type="checkbox" name="walk_in_enabled" value="1" checked>
                            <span>Walk-in</span>
                        </label>
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
                <flux:heading size="lg">Daftar Layanan</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nama</flux:table.column>
                        <flux:table.column>Kode</flux:table.column>
                        <flux:table.column>Pool</flux:table.column>
                        <flux:table.column>Aksi</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($services as $service)
                            <flux:table.row>
                                <flux:table.cell>{{ $service->name }}</flux:table.cell>
                                <flux:table.cell>{{ $service->code }}</flux:table.cell>
                                <flux:table.cell>{{ $service->queuePool?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    <form method="POST" action="/admin/layanan/{{ $service->id }}" class="flex flex-wrap gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" value="{{ $service->name }}">
                                        <input type="hidden" name="description" value="{{ $service->description }}">
                                        <input type="hidden" name="is_active" value="{{ $service->is_active ? 0 : 1 }}">
                                        <flux:button type="submit" variant="ghost" size="sm">Toggle Aktif</flux:button>
                                    </form>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>
