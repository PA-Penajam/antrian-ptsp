<x-layouts::app :title="__('Manajemen Loket')">
    <div
        class="w-full space-y-6"
        x-data="{
            tab: @js(request('tab', 'list')),
            searchAssignment: '',
            selectTab(nextTab, shouldFocus = false) {
                this.tab = nextTab;
                this.$nextTick(() => {
                    this.updateTabIndicator();
                    if (shouldFocus) {
                        const tabRef = nextTab === 'list' ? this.$refs.listTab : this.$refs.assignmentTab;
                        tabRef?.focus();
                    }
                });
            },
            updateTabIndicator() {
                const activeTab = this.$refs.tabs?.querySelector('[role=tab][aria-selected=true]');
                if (! activeTab || ! this.$refs.indicator) return;
                this.$refs.indicator.style.width = activeTab.offsetWidth + 'px';
                this.$refs.indicator.style.transform = 'translateX(' + activeTab.offsetLeft + 'px)';
            },
        }"
        x-init="$nextTick(() => updateTabIndicator())"
    >
        <div class="animate-fade-in-up flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" aria-label="Beranda" />
                    <flux:breadcrumbs.item>Loket</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex flex-wrap items-center gap-3">
                    <flux:heading size="xl" level="1" class="font-extrabold tracking-tight">Manajemen Loket</flux:heading>
                    <flux:badge size="sm" color="cyan" class="font-bold shadow-2xs">{{ $counters->total() }} Loket</flux:badge>
                </div>
                <flux:subheading class="text-zinc-600 dark:text-zinc-400">Kelola daftar loket pelayanan PTSP, pool antrian, dan status aktif.</flux:subheading>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <flux:modal.trigger name="pool-manager">
                    <flux:button variant="outline" icon="folder-open" class="w-full font-semibold sm:w-auto">
                        Kelola Pool
                    </flux:button>
                </flux:modal.trigger>
                <flux:modal.trigger name="create-counter">
                    <flux:button variant="primary" icon="plus" class="w-full bg-cyan-700 font-bold text-white shadow-md shadow-cyan-700/20 transition-transform hover:bg-cyan-600 active:scale-[0.98] motion-reduce:transform-none dark:bg-cyan-700 dark:text-white dark:hover:bg-cyan-600 sm:w-auto">
                        Tambah Loket Baru
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green" class="rounded-2xl shadow-xs">
                {{ session('status') }}
            </flux:callout>
        @endif

        @if (session('error'))
            <flux:callout icon="x-circle" color="red" class="rounded-2xl shadow-xs">
                {{ session('error') }}
            </flux:callout>
        @endif

        {{-- Navigasi tab dengan state dan fokus keyboard yang sinkron --}}
        <div
            class="relative w-full overflow-x-auto rounded-2xl border border-cyan-200 bg-cyan-50/70 p-1 shadow-xs dark:border-cyan-900/70 dark:bg-cyan-950/30 sm:w-fit"
            role="tablist"
            aria-label="Navigasi manajemen loket"
            x-effect="tab; $nextTick(() => updateTabIndicator())"
            x-on:resize.window.debounce.150ms="updateTabIndicator()"
        >
            <div
                x-ref="indicator"
                class="admin-tab-indicator absolute inset-y-1 left-1 z-0 rounded-xl bg-cyan-800 shadow-sm shadow-cyan-950/25 dark:bg-cyan-800"
                style="width: 0"
                aria-hidden="true"
            ></div>
            <div x-ref="tabs" class="relative z-10 flex min-w-max gap-1 sm:min-w-0">
                <button
                    id="counter-list-tab"
                    x-ref="listTab"
                    type="button"
                    role="tab"
                    aria-controls="counter-list-panel"
                    x-bind:aria-selected="tab === 'list'"
                    x-bind:tabindex="tab === 'list' ? 0 : -1"
                    x-on:click="selectTab('list')"
                    x-on:keydown.right.prevent="selectTab('assignment', true)"
                    x-on:keydown.left.prevent="selectTab('assignment', true)"
                    x-on:keydown.home.prevent="selectTab('list', true)"
                    x-on:keydown.end.prevent="selectTab('assignment', true)"
                    class="admin-tab-btn flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold whitespace-nowrap focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 sm:flex-none dark:focus-visible:outline-cyan-300"
                >
                    <flux:icon.building-office class="size-4" />
                    <span class="admin-tab-label" x-bind:class="tab === 'list' ? 'text-white' : 'text-zinc-700 hover:text-cyan-950 dark:text-zinc-300 dark:hover:text-white'">Daftar Loket</span>
                </button>
                <button
                    id="counter-assignment-tab"
                    x-ref="assignmentTab"
                    type="button"
                    role="tab"
                    aria-controls="counter-assignment-panel"
                    x-bind:aria-selected="tab === 'assignment'"
                    x-bind:tabindex="tab === 'assignment' ? 0 : -1"
                    x-on:click="selectTab('assignment')"
                    x-on:keydown.right.prevent="selectTab('list', true)"
                    x-on:keydown.left.prevent="selectTab('list', true)"
                    x-on:keydown.home.prevent="selectTab('list', true)"
                    x-on:keydown.end.prevent="selectTab('assignment', true)"
                    class="admin-tab-btn flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold whitespace-nowrap focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 sm:flex-none dark:focus-visible:outline-cyan-300"
                >
                    <flux:icon.users class="size-4" />
                    <span class="admin-tab-label" x-bind:class="tab === 'assignment' ? 'text-white' : 'text-zinc-700 hover:text-cyan-950 dark:text-zinc-300 dark:hover:text-white'">Penugasan Petugas</span>
                </button>
            </div>
        </div>

        {{-- Tab 1: Daftar Loket --}}
        <div
            id="counter-list-panel"
            role="tabpanel"
            aria-labelledby="counter-list-tab"
            tabindex="0"
            x-show="tab === 'list'"
            x-cloak
            x-transition:enter="motion-safe:transition-opacity motion-safe:duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        >
            <flux:card class="admin-card-elevated overflow-hidden rounded-3xl border border-zinc-200 bg-white p-0 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 border-b border-zinc-100 p-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/70 dark:text-cyan-300">
                            <flux:icon.building-office class="size-5" />
                        </div>
                        <div>
                            <flux:heading id="counter-list-heading" size="lg" class="font-bold">Daftar Loket</flux:heading>
                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Loket fisik, urutan layanan, dan pool antrian yang terhubung.</flux:text>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('admin.loket.index') }}" class="w-full sm:w-auto">
                        <flux:input
                            name="search"
                            aria-label="Cari loket"
                            value="{{ request('search') }}"
                            placeholder="Cari nama atau kode..."
                            icon="magnifying-glass"
                            class="w-full sm:w-64"
                        />
                    </form>
                </div>

                <p id="counter-list-scroll-hint" class="sr-only">Geser tabel secara horizontal untuk melihat seluruh kolom.</p>
                <div
                    class="admin-table-scroll overflow-x-auto px-5 pb-5 sm:px-6 sm:pb-6"
                    tabindex="0"
                    aria-labelledby="counter-list-heading"
                    aria-describedby="counter-list-scroll-hint"
                >
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>
                                <a href="{{ route('admin.loket.index', ['sort_by' => 'name', 'sort_direction' => $sortBy === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex min-h-6 items-center gap-1.5 whitespace-nowrap text-xs font-bold uppercase tracking-wider text-zinc-600 underline-offset-4 hover:text-cyan-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 dark:text-zinc-400 dark:hover:text-cyan-300">
                                    Loket
                                    @if ($sortBy === 'name')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3 text-cyan-700 dark:text-cyan-300" />
                                    @endif
                                </a>
                            </flux:table.column>
                            <flux:table.column>
                                <a href="{{ route('admin.loket.index', ['sort_by' => 'code', 'sort_direction' => $sortBy === 'code' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex min-h-6 items-center gap-1.5 whitespace-nowrap text-xs font-bold uppercase tracking-wider text-zinc-600 underline-offset-4 hover:text-cyan-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 dark:text-zinc-400 dark:hover:text-cyan-300">
                                    Kode
                                    @if ($sortBy === 'code')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3 text-cyan-700 dark:text-cyan-300" />
                                    @endif
                                </a>
                            </flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Pool</flux:table.column>
                            <flux:table.column>
                                <a href="{{ route('admin.loket.index', ['sort_by' => 'sort_order', 'sort_direction' => $sortBy === 'sort_order' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex min-h-6 items-center gap-1.5 whitespace-nowrap text-xs font-bold uppercase tracking-wider text-zinc-600 underline-offset-4 hover:text-cyan-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 dark:text-zinc-400 dark:hover:text-cyan-300">
                                    Urutan
                                    @if ($sortBy === 'sort_order')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3 text-cyan-700 dark:text-cyan-300" />
                                    @endif
                                </a>
                            </flux:table.column>
                            <flux:table.column>
                                <a href="{{ route('admin.loket.index', ['sort_by' => 'is_active', 'sort_direction' => $sortBy === 'is_active' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex min-h-6 items-center gap-1.5 whitespace-nowrap text-xs font-bold uppercase tracking-wider text-zinc-600 underline-offset-4 hover:text-cyan-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 dark:text-zinc-400 dark:hover:text-cyan-300">
                                    Status
                                    @if ($sortBy === 'is_active')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3 text-cyan-700 dark:text-cyan-300" />
                                    @endif
                                </a>
                            </flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-right text-xs font-bold uppercase tracking-wider">Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($counters as $counter)
                                <flux:table.row class="transition-colors hover:bg-cyan-50/50 dark:hover:bg-zinc-800/60">
                                    <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">{{ $counter->name }}</flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        <flux:badge size="sm" color="zinc" class="font-mono font-bold">{{ $counter->code }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($counter->queuePool)
                                            <flux:badge size="sm" color="zinc">{{ $counter->queuePool->name }}</flux:badge>
                                        @else
                                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Belum diatur</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        <span class="inline-flex size-7 items-center justify-center rounded-lg bg-zinc-100 font-mono text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $counter->sort_order }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($counter->is_active)
                                            <flux:badge size="sm" color="green" icon="check-circle" class="font-bold">Aktif</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="zinc" icon="x-circle" class="font-semibold">Nonaktif</flux:badge>
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
                                                <flux:button
                                                    type="submit"
                                                    size="sm"
                                                    variant="ghost"
                                                    icon="{{ $counter->is_active ? 'pause' : 'play' }}"
                                                    aria-label="{{ $counter->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $counter->name }}"
                                                    class="font-semibold"
                                                >
                                                    {{ $counter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </flux:button>
                                            </form>
                                            <flux:modal.trigger name="edit-counter-{{ $counter->id }}">
                                                <flux:button size="sm" variant="filled" icon="pencil" aria-label="Edit {{ $counter->name }}" class="font-semibold shadow-2xs">Edit</flux:button>
                                            </flux:modal.trigger>
                                            <flux:modal.trigger name="delete-counter-{{ $counter->id }}">
                                                <flux:button
                                                    size="sm"
                                                    variant="ghost"
                                                    icon="trash"
                                                    aria-label="Hapus {{ $counter->name }}"
                                                    class="font-semibold text-zinc-600 hover:bg-red-50 hover:text-red-700 dark:text-zinc-400 dark:hover:bg-red-950/40 dark:hover:text-red-300"
                                                >
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
                                            <div class="flex size-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300">
                                                <flux:icon name="inbox" class="size-7" />
                                            </div>
                                            <p class="mt-4 whitespace-normal text-sm font-bold text-zinc-900 dark:text-zinc-100">Belum ada loket terdaftar</p>
                                            <p class="mx-auto mt-1 max-w-72 whitespace-normal text-xs leading-5 text-zinc-600 dark:text-zinc-400 sm:max-w-sm">Tambahkan loket pertama, lalu atur pool dan statusnya sebelum pelayanan dimulai.</p>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>

                @if ($counters->hasPages())
                    <div class="border-t border-zinc-100 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        {{ $counters->appends(['search' => request('search')])->links() }}
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Tab 2: Penugasan Petugas --}}
        <div
            id="counter-assignment-panel"
            role="tabpanel"
            aria-labelledby="counter-assignment-tab"
            tabindex="0"
            x-show="tab === 'assignment'"
            x-cloak
            x-transition:enter="motion-safe:transition-opacity motion-safe:duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        >
            <flux:card class="admin-card-elevated overflow-hidden rounded-3xl border border-zinc-200 bg-white p-0 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 border-b border-zinc-100 p-5 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300">
                            <flux:icon.users class="size-5" />
                        </div>
                        <div>
                            <flux:heading id="counter-assignment-heading" size="lg" class="font-bold">Penugasan Petugas ke Loket</flux:heading>
                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Pantau loket terisi dan tempatkan petugas pada meja pelayanan.</flux:text>
                        </div>
                    </div>
                    <flux:input
                        x-model="searchAssignment"
                        aria-label="Cari penugasan petugas"
                        placeholder="Cari loket, pool, atau petugas..."
                        icon="magnifying-glass"
                        clearable
                        class="w-full sm:w-64"
                    />
                </div>

                <p id="counter-assignment-scroll-hint" class="sr-only">Geser tabel secara horizontal untuk melihat seluruh kolom.</p>
                <div
                    class="admin-table-scroll overflow-x-auto px-5 pb-5 sm:px-6 sm:pb-6"
                    tabindex="0"
                    aria-labelledby="counter-assignment-heading"
                    aria-describedby="counter-assignment-scroll-hint"
                >
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Loket</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Pool</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Petugas Aktif</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs font-bold uppercase tracking-wider">Jenis Penugasan</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-right text-xs font-bold uppercase tracking-wider">Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($counters->where('is_active', true) as $counter)
                                @php
                                    $session = $activeSessions->get($counter->id);
                                @endphp
                                <flux:table.row
                                    x-show="!searchAssignment || @js(strtolower($counter->name . ' ' . ($counter->queuePool?->name ?? '') . ' ' . ($session?->user?->name ?? ''))).includes(searchAssignment.toLowerCase())"
                                    class="transition-colors hover:bg-blue-50/50 dark:hover:bg-zinc-800/60"
                                >
                                    <flux:table.cell class="font-bold whitespace-nowrap text-zinc-900 dark:text-zinc-100">{{ $counter->name }}</flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($counter->queuePool)
                                            <flux:badge size="sm" color="zinc">{{ $counter->queuePool->name }}</flux:badge>
                                        @else
                                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Belum diatur</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($session)
                                            <div class="flex items-center gap-2">
                                                <div class="flex size-7 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-800 dark:bg-blue-950/70 dark:text-blue-200">
                                                    {{ $session->user->initials() }}
                                                </div>
                                                <span>{{ $session->user->name }}</span>
                                            </div>
                                        @else
                                            <flux:badge size="sm" color="amber" icon="clock" class="font-semibold">Menunggu petugas</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($session)
                                            @if ($session->assigned_by)
                                                <flux:badge size="sm" color="blue" icon="user-plus" class="font-semibold">Ditunjuk Admin</flux:badge>
                                            @else
                                                <flux:badge size="sm" color="zinc" icon="user" class="font-semibold">Dipilih Sendiri</flux:badge>
                                            @endif
                                        @else
                                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">Belum ditugaskan</flux:text>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if ($session)
                                                <form method="POST" action="{{ route('admin.loket.release', $counter) }}" class="inline">
                                                    @csrf
                                                    <flux:button
                                                        type="submit"
                                                        size="sm"
                                                        variant="ghost"
                                                        icon="x-mark"
                                                        aria-label="Lepas {{ $session->user->name }} dari {{ $counter->name }}"
                                                        class="font-semibold text-zinc-600 hover:bg-red-50 hover:text-red-700 dark:text-zinc-400 dark:hover:bg-red-950/40 dark:hover:text-red-300"
                                                    >
                                                        Lepas
                                                    </flux:button>
                                                </form>
                                            @else
                                                <flux:modal.trigger name="assign-counter-{{ $counter->id }}">
                                                    <flux:button size="sm" variant="primary" icon="user-plus" aria-label="Tugaskan petugas ke {{ $counter->name }}" class="font-semibold shadow-2xs">
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
                                            <div class="flex size-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300">
                                                <flux:icon name="inbox" class="size-7" />
                                            </div>
                                            <p class="mt-4 whitespace-normal text-sm font-bold text-zinc-900 dark:text-zinc-100">Belum ada loket aktif</p>
                                            <p class="mx-auto mt-1 max-w-72 whitespace-normal text-xs leading-5 text-zinc-600 dark:text-zinc-400 sm:max-w-sm">Aktifkan minimal satu loket di tab "Daftar Loket" agar penugasan petugas bisa dimulai.</p>
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
                <div class="admin-table-scroll overflow-x-auto rounded-2xl border border-zinc-200 dark:border-zinc-700" tabindex="0" aria-label="Daftar pool antrian">
                    <table class="w-full text-sm">
                        <caption class="sr-only">Daftar pool antrian beserta kode, huruf, jumlah layanan, dan aksi.</caption>
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th scope="col" class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Nama</th>
                                <th scope="col" class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Kode</th>
                                <th scope="col" class="px-4 py-2.5 text-left font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Huruf</th>
                                <th scope="col" class="px-4 py-2.5 text-center font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Layanan</th>
                                <th scope="col" class="px-4 py-2.5 text-center font-medium text-zinc-600 dark:text-zinc-400 whitespace-nowrap">Aksi</th>
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
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 font-mono text-xs font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">{{ $pool->letter_code ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-zinc-500 whitespace-nowrap">{{ $pool->services()->count() }}</td>
                                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <flux:modal.trigger name="edit-pool-{{ $pool->id }}">
                                                <flux:button size="xs" variant="filled" icon="pencil" aria-label="Edit pool {{ $pool->name }}">
                                                    Edit
                                                </flux:button>
                                            </flux:modal.trigger>
                                            <flux:modal.trigger name="delete-pool-{{ $pool->id }}">
                                                <flux:button size="xs" variant="ghost" icon="trash" aria-label="Hapus pool {{ $pool->name }}" class="text-zinc-600 hover:bg-red-50 hover:text-red-700 dark:text-zinc-400 dark:hover:bg-red-950/40 dark:hover:text-red-300">
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
                <div class="rounded-lg border border-dashed border-zinc-300 py-8 text-center dark:border-zinc-600">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300">
                        <flux:icon name="folder-open" class="size-6" />
                    </div>
                    <p class="mt-2 text-sm text-zinc-500">Belum ada pool. Buat pool pertama lewat formulir di bawah.</p>
                </div>
            @endif

            {{-- Create Form --}}
            <div class="space-y-3 rounded-2xl border border-cyan-200 bg-cyan-50/50 p-4 dark:border-cyan-800 dark:bg-cyan-900/10">
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-400">Tambah Pool Baru</p>
                <form method="POST" action="{{ route('admin.loket.pool.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="is_active" value="1">

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <flux:field>
                            <flux:label>Nama pool</flux:label>
                            <flux:input name="name" value="{{ old('name') }}" placeholder="Contoh: Pool Umum" required />
                            <flux:error name="name" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Kode pool</flux:label>
                            <flux:input name="code" value="{{ old('code') }}" placeholder="Contoh: UMUM" maxlength="20" required />
                            <flux:error name="code" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Kode huruf</flux:label>
                            <flux:input name="letter_code" value="{{ old('letter_code') }}" placeholder="Contoh: A" maxlength="5" required />
                            <flux:error name="letter_code" />
                        </flux:field>
                        <flux:button type="submit" variant="filled" icon="plus" class="self-end">
                            Tambah Pool
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
        <form method="POST" action="{{ route('admin.loket.store') }}" class="space-y-4" x-data="{ submitting: false }" x-bind:aria-busy="submitting" @submit="submitting = true">
            @csrf

            <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/70 dark:text-cyan-300">
                    <flux:icon.plus-circle class="size-5" />
                </div>
                <div>
                    <flux:heading size="lg" class="font-bold">Tambah Loket Baru</flux:heading>
                    <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Hubungkan meja pelayanan ke pool antrian yang tepat.</flux:text>
                </div>
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
                <flux:label>Pool Antrian</flux:label>
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
                    <flux:label>Urutan Tampilan</flux:label>
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
                        <svg class="size-4 animate-spin motion-reduce:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            <form method="POST" action="{{ route('admin.loket.update', $counter) }}" class="space-y-4" x-data="{ submitting: false }" x-bind:aria-busy="submitting" @submit="submitting = true">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="admin-icon-box bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold">Edit Loket: {{ $counter->name }}</flux:heading>
                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Perbarui identitas, pool, urutan, atau status loket.</flux:text>
                    </div>
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
                    <flux:label>Pool Antrian</flux:label>
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
                    <flux:button type="submit" variant="primary" class="bg-cyan-700 font-bold text-white shadow-md shadow-cyan-700/20 hover:bg-cyan-600 dark:bg-cyan-700 dark:text-white dark:hover:bg-cyan-600" x-bind:disabled="submitting">
                        <span x-show="!submitting">Simpan</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="size-4 animate-spin motion-reduce:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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

                <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="admin-icon-box bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300">
                        <flux:icon.user-plus class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold">Tugaskan Petugas ke {{ $counter->name }}</flux:heading>
                        <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Pilih petugas yang akan aktif di loket ini.</flux:text>
                    </div>
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
                    <flux:button type="submit" variant="primary" class="bg-blue-700 font-bold text-white shadow-md shadow-blue-700/20 hover:bg-blue-600 dark:bg-blue-700 dark:text-white dark:hover:bg-blue-600">Tugaskan</flux:button>
                </div>
            </form>
        </flux:modal>
    @endforeach
</x-layouts::app>
