<x-layouts::app :title="__('Manajemen Layanan')">
    <div class="w-full space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Layanan</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <flux:heading size="xl" level="1">Manajemen Layanan</flux:heading>
                <flux:subheading>Kelola layanan aktif dan konfigurasi kanal layanan.</flux:subheading>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge size="sm" color="cyan" class="hidden sm:inline-flex">{{ $services->total() ?? $services->count() }} layanan</flux:badge>
                <flux:modal.trigger name="create-service">
                    <flux:button variant="primary" icon="plus" class="w-full sm:w-auto">
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

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>
                            <a href="{{ route('admin.layanan.index', ['sort_by' => 'name', 'sort_direction' => $sortBy === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline whitespace-nowrap">
                                Nama
                                @if ($sortBy === 'name')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3" />
                                @endif
                            </a>
                        </flux:table.column>
                        <flux:table.column>
                            <a href="{{ route('admin.layanan.index', ['sort_by' => 'code', 'sort_direction' => $sortBy === 'code' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline whitespace-nowrap">
                                Kode
                                @if ($sortBy === 'code')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3" />
                                @endif
                            </a>
                        </flux:table.column>
                        <flux:table.column class="whitespace-nowrap">Pool</flux:table.column>
                        <flux:table.column>
                            <a href="{{ route('admin.layanan.index', ['sort_by' => 'is_active', 'sort_direction' => $sortBy === 'is_active' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline whitespace-nowrap">
                                Status
                                @if ($sortBy === 'is_active')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3" />
                                @endif
                            </a>
                        </flux:table.column>
                        <flux:table.column class="whitespace-nowrap">Aksi</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($services as $service)
                            <flux:table.row>
                                <flux:table.cell class="font-medium whitespace-nowrap">{{ $service->name }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    <flux:badge size="sm" color="zinc">{{ $service->code }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $service->queuePool?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    @if ($service->is_active)
                                        <flux:badge size="sm" color="green" icon="check-circle">Aktif</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc" icon="x-circle">Nonaktif</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
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
                                        <flux:modal.trigger name="delete-service-{{ $service->id }}">
                                            <flux:button size="sm" variant="danger" icon="trash">
                                                Hapus
                                            </flux:button>
                                        </flux:modal.trigger>
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
            </div>

            @if ($services->hasPages())
                <div class="mt-4">
                    {{ $services->appends(['search' => request('search')])->links() }}
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Create Modal --}}
    <flux:modal name="create-service" class="w-full max-w-2xl">
        <form method="POST" action="{{ route('admin.layanan.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
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
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Kode</flux:label>
                    <flux:input name="code" value="{{ old('code') }}" required />
                    <flux:error name="code" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input name="slug" value="{{ old('slug') }}" placeholder="Akan dibuat otomatis dari nama" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Queue Pool</flux:label>
                    <flux:select name="queue_pool_id" required>
                        <flux:select.option value="">Pilih Pool</flux:select.option>
                        @foreach ($queuePools as $pool)
                            <flux:select.option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->letter_code ?? '-' }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="queue_pool_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Sort Order</flux:label>
                    <flux:input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" required />
                    <flux:error name="sort_order" />
                </flux:field>

                <flux:field>
                    <flux:label>Kuota Harian</flux:label>
                    <flux:input type="number" name="daily_quota" value="{{ old('daily_quota') }}" placeholder="Kosongkan jika tak terbatas" />
                    <flux:error name="daily_quota" />
                </flux:field>

                <div class="grid gap-3 sm:grid-cols-3 md:col-span-2">
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <flux:checkbox name="is_active" value="1" :checked="(bool) old('is_active', 1)" label="Aktif" />
                        <flux:error name="is_active" />
                    </div>
                    <div>
                        <input type="hidden" name="booking_enabled" value="0">
                        <flux:checkbox name="booking_enabled" value="1" :checked="(bool) old('booking_enabled', 1)" label="Terima Booking" />
                        <flux:error name="booking_enabled" />
                    </div>
                    <div>
                        <input type="hidden" name="walk_in_enabled" value="0">
                        <flux:checkbox name="walk_in_enabled" value="1" :checked="(bool) old('walk_in_enabled', 1)" label="Terima Walk-in" />
                        <flux:error name="walk_in_enabled" />
                    </div>
                </div>

                <flux:field class="md:col-span-2">
                    <flux:label>Deskripsi</flux:label>
                    <flux:textarea name="description" rows="2">{{ old('description') }}</flux:textarea>
                    <flux:error name="description" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Persyaratan</flux:label>
                    <flux:textarea name="requirements" rows="2">{{ old('requirements') }}</flux:textarea>
                    <flux:error name="requirements" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" x-bind:disabled="submitting">
                    <span x-show="!submitting">Tambah Layanan</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modals --}}
    @foreach ($services as $service)
        <flux:modal name="edit-service-{{ $service->id }}" class="w-full max-w-2xl">
            <form method="POST" action="{{ route('admin.layanan.update', $service) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
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
                        <flux:input name="name" value="{{ old('name', $service->name) }}" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kode</flux:label>
                        <flux:input name="code" value="{{ old('code', $service->code) }}" required />
                        <flux:error name="code" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Slug</flux:label>
                        <flux:input name="slug" value="{{ old('slug', $service->slug) }}" />
                        <flux:error name="slug" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Queue Pool</flux:label>
                        <flux:select name="queue_pool_id" required>
                            @foreach ($queuePools as $pool)
                                <flux:select.option value="{{ $pool->id }}" :selected="$pool->id === old('queue_pool_id', $service->queue_pool_id)">
                                    {{ $pool->name }} ({{ $pool->letter_code ?? '-' }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="queue_pool_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Sort Order</flux:label>
                        <flux:input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}" required />
                        <flux:error name="sort_order" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kuota Harian</flux:label>
                        <flux:input type="number" name="daily_quota" value="{{ old('daily_quota', $service->daily_quota) }}" />
                        <flux:error name="daily_quota" />
                    </flux:field>

                    <div class="grid gap-3 sm:grid-cols-3 md:col-span-2">
                        <div>
                            <input type="hidden" name="is_active" value="0">
                            <flux:checkbox name="is_active" value="1" :checked="(bool) old('is_active', $service->is_active)" label="Aktif" />
                            <flux:error name="is_active" />
                        </div>
                        <div>
                            <input type="hidden" name="booking_enabled" value="0">
                            <flux:checkbox name="booking_enabled" value="1" :checked="(bool) old('booking_enabled', $service->booking_enabled)" label="Booking" />
                            <flux:error name="booking_enabled" />
                        </div>
                        <div>
                            <input type="hidden" name="walk_in_enabled" value="0">
                            <flux:checkbox name="walk_in_enabled" value="1" :checked="(bool) old('walk_in_enabled', $service->walk_in_enabled)" label="Walk-in" />
                            <flux:error name="walk_in_enabled" />
                        </div>
                    </div>

                    <flux:field class="md:col-span-2">
                        <flux:label>Deskripsi</flux:label>
                        <flux:textarea name="description" rows="2">{{ old('description', $service->description) }}</flux:textarea>
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>Persyaratan</flux:label>
                        <flux:textarea name="requirements" rows="2">{{ old('requirements', $service->requirements) }}</flux:textarea>
                        <flux:error name="requirements" />
                    </flux:field>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" x-bind:disabled="submitting">
                        <span x-show="!submitting">Simpan</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach

    {{-- Delete Confirmation Modals --}}
    @foreach ($services as $service)
        <flux:modal name="delete-service-{{ $service->id }}" class="w-full max-w-md">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                        <flux:icon.trash class="size-5" />
                    </div>
                    <flux:heading size="lg">Hapus Layanan</flux:heading>
                </div>

                <flux:callout icon="exclamation-circle" color="red">
                    Apakah Anda yakin ingin menghapus layanan <strong>{{ $service->name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                </flux:callout>

                <form method="POST" action="{{ route('admin.layanan.destroy', $service) }}" class="flex justify-end gap-2 pt-2">
                    @csrf
                    @method('DELETE')
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger" icon="trash">Hapus</flux:button>
                </form>
            </div>
        </flux:modal>
    @endforeach
</x-layouts::app>
