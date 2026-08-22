<x-layouts::app :title="__('Manajemen Loket')">
    <div class="w-full space-y-6" x-data="{ tab: '{{ request('tab', 'list') }}', searchAssignment: '' }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Loket</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <flux:heading size="xl" level="1">Manajemen Loket</flux:heading>
                <flux:subheading>Kelola daftar loket pelayanan PTSP, pool antrian, dan status aktif.</flux:subheading>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <flux:modal.trigger name="pool-manager">
                    <flux:button variant="outline" icon="folder-open" class="w-full sm:w-auto">
                        Kelola Pool
                    </flux:button>
                </flux:modal.trigger>
                <flux:modal.trigger name="create-counter">
                    <flux:button variant="primary" icon="plus" class="w-full sm:w-auto">
                        Tambah Loket Baru
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

        {{-- Tabs Navigation --}}
        <div class="flex w-full overflow-x-auto p-1 gap-1 rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50 max-w-fit">
            <button
                type="button"
                x-on:click="tab = 'list'"
                :class="tab === 'list' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all whitespace-nowrap"
            >
                <flux:icon.building-office class="size-4" />
                Daftar Loket
            </button>
            <button
                type="button"
                x-on:click="tab = 'assignment'"
                :class="tab === 'assignment' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all whitespace-nowrap"
            >
                <flux:icon.users class="size-4" />
                Penugasan Petugas
            </button>
        </div>

        {{-- Tab 1: Daftar Loket --}}
        <div x-show="tab === 'list'" x-cloak>
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

                <div class="overflow-x-auto">
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
                                    <flux:table.cell class="font-medium whitespace-nowrap">{{ $counter->name }}</flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        <flux:badge size="sm" color="zinc">{{ $counter->code }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">{{ $counter->queuePool?->name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">{{ $counter->sort_order }}</flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($counter->is_active)
                                            <flux:badge size="sm" color="green" icon="check-circle">Aktif</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="zinc" icon="x-circle">Nonaktif</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                            <form method="POST" action="{{ route('admin.loket.update', $counter) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="name" value="{{ $counter->name }}">
                                                <input type="hidden" name="code" value="{{ $counter->code }}">
                                                <input type="hidden" name="queue_pool_id" value="{{ $counter->queue_pool_id }}">
                                                <input type="hidden" name="sort_order" value="{{ $counter->sort_order }}">
                                                <input type="hidden" name="is_active" value="{{ $counter->is_active ? '0' : '1' }}">
                                                <flux:button type="submit" size="sm" variant="ghost" icon="{{ $counter->is_active ? 'pause' : 'play' }}">
                                                    {{ $counter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </flux:button>
                                            </form>
                                            <flux:modal.trigger name="edit-counter-{{ $counter->id }}">
                                                <flux:button size="sm" variant="filled" icon="pencil">Edit</flux:button>
                                            </flux:modal.trigger>
                                            <flux:modal.trigger name="delete-counter-{{ $counter->id }}">
                                                <flux:button size="sm" variant="danger" icon="trash">
                                                    Hapus
                                                </flux:button>
                                            </flux:modal.trigger>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="6">
                                        <div class="flex flex-col items-center justify-center py-8 text-center">
                                            <flux:icon name="inbox" class="h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                            <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Belum ada loket</p>
                                            <p class="mt-1 text-xs text-zinc-500">Mulai dengan menambahkan loket baru menggunakan tombol di atas.</p>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>

                @if ($counters->hasPages())
                    <div class="mt-4">
                        {{ $counters->appends(['search' => request('search')])->links() }}
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Tab 2: Penugasan Petugas --}}
        <div x-show="tab === 'assignment'" x-cloak>
            <flux:card class="space-y-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                            <flux:icon.users class="size-5" />
                        </div>
                        <flux:heading size="lg">Penugasan Petugas ke Loket</flux:heading>
                    </div>
                    <flux:input
                        x-model="searchAssignment"
                        placeholder="Cari loket, pool, atau petugas..."
                        icon="magnifying-glass"
                        clearable
                        class="w-full sm:w-64"
                    />
                </div>

                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Loket</flux:table.column>
                            <flux:table.column>Pool</flux:table.column>
                            <flux:table.column>Petugas Aktif</flux:table.column>
                            <flux:table.column>Jenis Penugasan</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($counters->where('is_active', true) as $counter)
                                @php
                                    $session = $activeSessions->get($counter->id);
                                @endphp
                                <flux:table.row x-show="!searchAssignment || '{{ strtolower(addslashes($counter->name . ' ' . ($counter->queuePool?->name ?? '') . ' ' . ($session?->user?->name ?? ''))) }}'.includes(searchAssignment.toLowerCase())">
                                    <flux:table.cell class="font-medium whitespace-nowrap">{{ $counter->name }}</flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">{{ $counter->queuePool?->name ?? '-' }}</flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($session)
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-zinc-200 text-xs font-bold leading-6 text-center dark:bg-zinc-700">
                                                    {{ $session->user->initials() }}
                                                </div>
                                                <span>{{ $session->user->name }}</span>
                                            </div>
                                        @else
                                            <flux:text class="text-zinc-500">-</flux:text>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($session)
                                            @if ($session->assigned_by)
                                                <flux:badge size="sm" color="violet">Ditunjuk Admin</flux:badge>
                                            @else
                                                <flux:badge size="sm" color="emerald">Dipilih Sendiri</flux:badge>
                                            @endif
                                        @else
                                            <flux:text class="text-zinc-500">-</flux:text>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            @if ($session)
                                                <form method="POST" action="{{ route('admin.loket.release', $counter) }}" class="inline">
                                                    @csrf
                                                    <flux:button type="submit" size="sm" variant="ghost" icon="x-mark" class="text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400">
                                                        Lepas
                                                    </flux:button>
                                                </form>
                                            @else
                                                <flux:modal.trigger name="assign-counter-{{ $counter->id }}">
                                                    <flux:button size="sm" variant="filled" icon="user-plus" color="violet">
                                                        Tugaskan
                                                    </flux:button>
                                                </flux:modal.trigger>
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="5">
                                        <div class="flex flex-col items-center justify-center py-8 text-center">
                                            <flux:icon name="inbox" class="h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                            <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Belum ada loket aktif</p>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>
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
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Nama</th>
                                <th class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Kode</th>
                                <th class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Huruf</th>
                                <th class="px-4 py-2.5 text-center font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Layanan</th>
                                <th class="px-4 py-2.5 text-center font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach ($queuePools as $pool)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-2.5 font-medium text-zinc-900 dark:text-zinc-100 whitespace-nowrap">{{ $pool->name }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $pool->code }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ $pool->letter_code ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-zinc-500 whitespace-nowrap">{{ $pool->services()->count() }}</td>
                                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <flux:modal.trigger name="edit-pool-{{ $pool->id }}">
                                                <flux:button size="xs" variant="filled" icon="pencil">
                                                    Edit
                                                </flux:button>
                                            </flux:modal.trigger>
                                            <flux:modal.trigger name="delete-pool-{{ $pool->id }}">
                                                <flux:button size="xs" variant="danger" icon="trash">
                                                    Hapus
                                                </flux:button>
                                            </flux:modal.trigger>
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
                <form method="POST" action="{{ route('admin.loket.pool.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="is_active" value="1">

                    <div class="grid gap-3 grid-cols-1 sm:grid-cols-4">
                        <div>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama pool" required
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                            <flux:error name="name" />
                        </div>
                        <div>
                            <input type="text" name="code" value="{{ old('code') }}" placeholder="Kode" required maxlength="20"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                            <flux:error name="code" />
                        </div>
                        <div>
                            <input type="text" name="letter_code" value="{{ old('letter_code') }}" placeholder="Huruf" required maxlength="5"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                            <flux:error name="letter_code" />
                        </div>
                        <flux:button type="submit" variant="filled" class="sm:self-start">
                            <flux:icon.plus class="size-4" />
                            Tambah
                        </flux:button>
                    </div>
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
                <flux:select name="queue_pool_id" placeholder="Pilih Pool Loket" size="sm" required>
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
                    <flux:select name="queue_pool_id" size="sm" required>
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

    {{-- Delete Counter Confirmation Modals --}}
    @foreach ($counters as $counter)
        <flux:modal name="delete-counter-{{ $counter->id }}" class="w-full max-w-md">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                        <flux:icon.trash class="size-5" />
                    </div>
                    <flux:heading size="lg">Hapus Loket</flux:heading>
                </div>

                <flux:callout icon="exclamation-circle" color="red">
                    Apakah Anda yakin ingin menghapus loket <strong>{{ $counter->name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                </flux:callout>

                <form method="POST" action="{{ route('admin.loket.destroy', $counter) }}" class="flex justify-end gap-2 pt-2">
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

    {{-- Delete Pool Confirmation Modals --}}
    @foreach ($queuePools as $pool)
        <flux:modal name="delete-pool-{{ $pool->id }}" class="w-full max-w-md">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                        <flux:icon.trash class="size-5" />
                    </div>
                    <flux:heading size="lg">Hapus Pool</flux:heading>
                </div>

                <flux:callout icon="exclamation-circle" color="red">
                    Apakah Anda yakin ingin menghapus pool <strong>{{ $pool->name }}</strong>? Pool yang terhubung dengan layanan atau loket tidak dapat dihapus.
                </flux:callout>

                <form method="POST" action="{{ route('admin.loket.pool.destroy', $pool) }}" class="flex justify-end gap-2 pt-2">
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

    {{-- Assign Officer Modals --}}
    @foreach ($counters->where('is_active', true) as $counter)
        <flux:modal name="assign-counter-{{ $counter->id }}" class="w-full max-w-md">
            <form method="POST" action="{{ route('admin.loket.assign', $counter) }}" class="space-y-4">
                @csrf

                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                        <flux:icon.user-plus class="size-5" />
                    </div>
                    <flux:heading size="lg">Tugaskan Petugas ke {{ $counter->name }}</flux:heading>
                </div>

                <flux:field>
                    <flux:label>Pilih Petugas</flux:label>
                    <flux:select name="user_id" required>
                        <flux:select.option value="">Pilih petugas...</flux:select.option>
                        @foreach ($officers as $officer)
                            <flux:select.option value="{{ $officer->id }}">
                                {{ $officer->name }}
                                @if ($officer->services->isNotEmpty())
                                    ({{ $officer->services->pluck('name')->join(', ') }})
                                @endif
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="user_id" />
                </flux:field>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">
                            Batal
                        </flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" color="violet">Tugaskan</flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach

    {{-- Assign Officer Modals --}}
    @foreach ($counters->where('is_active', true) as $counter)
        <flux:modal name="assign-counter-{{ $counter->id }}" class="w-full max-w-md">
            <form method="POST" action="{{ route('admin.loket.assign', $counter) }}" class="space-y-4">
                @csrf

                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                        <flux:icon.user-plus class="size-5" />
                    </div>
                    <flux:heading size="lg">Tugaskan Petugas ke {{ $counter->name }}</flux:heading>
                </div>

                <flux:field>
                    <flux:label>Pilih Petugas</flux:label>
                    <flux:select name="user_id" required>
                        <flux:select.option value="">Pilih petugas...</flux:select.option>
                        @foreach ($officers as $officer)
                            <flux:select.option value="{{ $officer->id }}">
                                {{ $officer->name }}
                                @if ($officer->services->isNotEmpty())
                                    ({{ $officer->services->pluck('name')->join(', ') }})
                                @endif
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="user_id" />
                </flux:field>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">
                            Batal
                        </flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" color="violet">Tugaskan</flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach
</x-layouts::app>
