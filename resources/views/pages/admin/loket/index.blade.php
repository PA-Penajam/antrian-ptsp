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

            <div class="flex items-center gap-2">
                <flux:modal.trigger name="pool-manager">
                    <flux:button variant="filled" icon="folder-plus">
                        Pool
                    </flux:button>
                </flux:modal.trigger>
                <flux:modal.trigger name="create-counter">
                    <flux:button variant="primary" icon="plus">
                        Tambah Loket
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
            <flux:callout icon="exclamation-circle" color="red">
                {{ session('error') }}
            </flux:callout>
        @endif

        <flux:card class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.building-office class="size-5" />
                    </div>
                    <flux:heading size="lg">Daftar Loket</flux:heading>
                </div>
                <form method="GET" action="{{ route('admin.loket.index') }}" class="w-full sm:w-auto">
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
                    <flux:table.column>
                        <a href="{{ route('admin.loket.index', ['sort_by' => 'name', 'sort_direction' => $sortBy === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline">
                            Loket
                            @if ($sortBy === 'name')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3" />
                            @endif
                        </a>
                    </flux:table.column>
                    <flux:table.column>
                        <a href="{{ route('admin.loket.index', ['sort_by' => 'code', 'sort_direction' => $sortBy === 'code' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline">
                            Kode
                            @if ($sortBy === 'code')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3" />
                            @endif
                        </a>
                    </flux:table.column>
                    <flux:table.column>Pool</flux:table.column>
                    <flux:table.column>
                        <a href="{{ route('admin.loket.index', ['sort_by' => 'sort_order', 'sort_direction' => $sortBy === 'sort_order' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline">
                            Urutan
                            @if ($sortBy === 'sort_order')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3" />
                            @endif
                        </a>
                    </flux:table.column>
                    <flux:table.column>
                        <a href="{{ route('admin.loket.index', ['sort_by' => 'is_active', 'sort_direction' => $sortBy === 'is_active' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline">
                            Status
                            @if ($sortBy === 'is_active')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3" />
                            @endif
                        </a>
                    </flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($counters as $counter)
                        <flux:table.row>
                            <flux:table.cell class="font-medium">{{ $counter->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $counter->code }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $counter->queuePool?->name ?? '-' }}
                                <span class="ml-1 text-xs text-zinc-400">({{ $counter->queuePool?->letter_code ?? '-' }})</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $counter->sort_order }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($counter->is_active)
                                    <flux:badge size="sm" color="green" icon="check-circle">Aktif</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc" icon="x-circle">Nonaktif</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:modal.trigger name="edit-counter-{{ $counter->id }}">
                                        <flux:button size="sm" variant="filled" icon="pencil">
                                            Edit
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <form method="POST" action="{{ route('admin.loket.destroy', $counter) }}" class="inline"
                                        onsubmit="return confirm('Yakin ingin menghapus loket {{ $counter->name }}?')">
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
                            <flux:table.cell colspan="6">
                                <div class="flex flex-col items-center justify-center py-8 text-center">
                                    <flux:icon name="inbox" class="h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                    <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Belum ada loket</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Silakan tambah loket baru menggunakan tombol di atas.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if ($counters->hasPages())
                <div class="mt-4">
                    {{ $counters->appends(['search' => request('search')])->links() }}
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Pool Manager Modal (Winbox-style: list + create, no edit) --}}
    <flux:modal name="pool-manager" class="w-full max-w-2xl">
        <div class="space-y-5">
            <div class="flex items-center gap-3 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
                    <flux:icon.folder class="size-5" />
                </div>
                <div>
                    <flux:heading size="lg">Pool Antrian</flux:heading>
                    <p class="text-xs text-zinc-500 mt-0.5">Kelola pool untuk mengelompokkan layanan.</p>
                </div>
            </div>

            {{-- Table List --}}
            @if ($queuePools->count() > 0)
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400">Nama</th>
                                <th class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400">Kode</th>
                                <th class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400">Huruf</th>
                                <th class="px-4 py-2.5 text-center font-medium text-zinc-600 dark:text-zinc-400">Layanan</th>
                                <th class="px-4 py-2.5 text-center font-medium text-zinc-600 dark:text-zinc-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach ($queuePools as $pool)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-2.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $pool->name }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $pool->code }}</span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ $pool->letter_code ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-zinc-500">{{ $pool->services()->count() }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <flux:modal.trigger name="edit-pool-{{ $pool->id }}">
                                                <flux:button size="xs" variant="filled" icon="pencil">
                                                    Edit
                                                </flux:button>
                                            </flux:modal.trigger>
                                            <form method="POST" action="{{ route('admin.loket.pool.destroy', $pool) }}" class="inline"
                                                onsubmit="return confirm('Hapus pool {{ $pool->name }}? Pool yang terhubung dengan layanan/loket tidak bisa dihapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" size="xs" variant="danger" icon="trash">
                                                    Hapus
                                                </flux:button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-lg border border-dashed border-zinc-300 dark:border-zinc-600 py-8 text-center">
                    <flux:icon name="folder-open" class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-2 text-sm text-zinc-500">Belum ada pool. Buat pool pertama di bawah.</p>
                </div>
            @endif

            {{-- Create Form --}}
            <div class="rounded-lg border border-cyan-200 bg-cyan-50/50 dark:border-cyan-800 dark:bg-cyan-900/10 p-4 space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-400">Tambah Pool Baru</p>
                <form method="POST" action="{{ route('admin.loket.pool.store') }}" class="grid gap-3 sm:grid-cols-4">
                    @csrf
                    <div>
                        <input type="text" name="name" placeholder="Nama pool" required
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                    </div>
                    <div>
                        <input type="text" name="code" placeholder="Kode" required maxlength="20"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                    </div>
                    <div>
                        <input type="text" name="letter_code" placeholder="Huruf" required maxlength="5"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                    </div>
                    <flux:button type="submit" variant="filled" class="sm:self-end">
                        <flux:icon.plus class="size-4" />
                        Tambah
                    </flux:button>
                </form>
            </div>
        </div>
    </flux:modal>

    {{-- Edit Pool Modals --}}
    @foreach ($queuePools as $pool)
        <flux:modal name="edit-pool-{{ $pool->id }}" class="w-full max-w-md">
            <form method="POST" action="{{ route('admin.loket.pool.update', $pool) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <flux:heading size="lg">Edit Pool: {{ $pool->name }}</flux:heading>
                </div>

                <flux:field>
                    <flux:label>Nama Pool</flux:label>
                    <flux:input name="name" value="{{ old('name', $pool->name) }}" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Kode</flux:label>
                    <flux:input name="code" value="{{ old('code', $pool->code) }}" required maxlength="20" />
                    <flux:error name="code" />
                </flux:field>

                <flux:field>
                    <flux:label>Kode Huruf</flux:label>
                    <flux:input name="letter_code" value="{{ old('letter_code', $pool->letter_code) }}" required maxlength="5" />
                    <flux:error name="letter_code" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select name="is_active" required>
                        <flux:select.option value="1" :selected="old('is_active', $pool->is_active) == 1">Aktif</flux:select.option>
                        <flux:select.option value="0" :selected="old('is_active', $pool->is_active) == 0">Nonaktif</flux:select.option>
                    </flux:select>
                    <flux:error name="is_active" />
                </flux:field>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach

    {{-- Create Counter Modal --}}
    <flux:modal name="create-counter" class="w-full max-w-md">
        <form method="POST" action="{{ route('admin.loket.store') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                    <flux:icon.plus-circle class="size-5" />
                </div>
                <flux:heading size="lg">Tambah Loket Baru</flux:heading>
            </div>

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
                <flux:select name="queue_pool_id" placeholder="Pilih Pool Loket" required>
                    @foreach($queuePools as $pool)
                        <flux:select.option value="{{ $pool->id }}" :selected="old('queue_pool_id') == $pool->id">
                            {{ $pool->name }} ({{ $pool->letter_code ?? '-' }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="queue_pool_id" />
            </flux:field>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Urutan (Sort Order)</flux:label>
                    <flux:input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select name="is_active" required>
                        <flux:select.option value="1" :selected="old('is_active', '1') == '1'">Aktif</flux:select.option>
                        <flux:select.option value="0" :selected="old('is_active') == '0'">Nonaktif</flux:select.option>
                    </flux:select>
                    <flux:error name="is_active" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" x-bind:disabled="submitting">
                    <span x-show="!submitting">Tambah Loket</span>
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

    {{-- Edit Counter Modals --}}
    @foreach ($counters as $counter)
        <flux:modal name="edit-counter-{{ $counter->id }}" class="w-full max-w-md">
            <form method="POST" action="{{ route('admin.loket.update', $counter) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <flux:heading size="lg">Edit Loket: {{ $counter->name }}</flux:heading>
                </div>

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
                                {{ $pool->name }} ({{ $pool->letter_code ?? '-' }})
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
</x-layouts::app>
