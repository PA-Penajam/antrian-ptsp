<x-layouts::app :title="__('Manajemen User')">
    @php
        $totalUsersCount = $users->count();
        $adminCount = $users->where('role', \App\Enums\UserRole::Admin)->count();
        $officerCount = $users->where('role', \App\Enums\UserRole::Officer)->count();
        $frontdeskCount = $users->where('role', \App\Enums\UserRole::Frontdesk)->count();
        $monitorCount = $users->where('role', \App\Enums\UserRole::Monitor)->count();
        $otherUsers = $users->filter(fn ($user) => $user->id !== auth()->id());
    @endphp

    <div
        class="w-full space-y-6 animate-fade-in-up motion-reduce:animate-none"
        x-data="{
            tab: @js(old('_method') !== 'PUT' && $errors->any() ? 'list' : request('tab', $tab ?? 'list')),
            searchUser: '',
            selectTab(nextTab, shouldFocus = false) {
                this.tab = nextTab;
                this.$nextTick(() => {
                    this.updateTabIndicator();
                    if (shouldFocus) {
                        const tabRef = nextTab === 'list' ? this.$refs.listTab : this.$refs.rolesTab;
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
        {{-- Header & Top Action Area --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" aria-label="Beranda" />
                    <flux:breadcrumbs.item>Users</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex flex-wrap items-center gap-3">
                    <flux:heading size="xl" level="1" class="font-extrabold tracking-tight text-zinc-900 dark:text-white">Manajemen User</flux:heading>
                    <flux:badge size="sm" color="cyan" class="font-bold shadow-2xs">
                        {{ $totalUsersCount }} User Terdaftar
                    </flux:badge>
                </div>
                <flux:subheading class="text-zinc-600 dark:text-zinc-400">
                    Kelola akun staf internal, pembagian peran akses, dan penugasan izin layanan petugas.
                </flux:subheading>
            </div>
            
            <div class="flex items-center gap-2">
                <flux:modal.trigger name="create-user">
                    <flux:button variant="primary" icon="user-plus" class="w-full bg-cyan-700 font-bold text-white shadow-md shadow-cyan-700/20 transition-all hover:bg-cyan-600 active:scale-[0.98] motion-reduce:transform-none dark:bg-cyan-700 dark:text-white dark:hover:bg-cyan-600 sm:w-auto">
                        Tambah User Baru
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        {{-- Quick Insights Stat Strip --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="admin-stat-total rounded-2xl p-4 border shadow-xs transition-all duration-200 hover:-translate-y-0.5 motion-reduce:hover:transform-none">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Total Pengguna</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-700 dark:text-sky-300">
                        <flux:icon.users class="size-4" />
                    </div>
                </div>
                <div class="mt-2 font-mono text-2xl font-black text-sky-950 dark:text-sky-100 tabular-nums">
                    {{ $totalUsersCount }}
                </div>
                <div class="mt-0.5 text-xs text-sky-700/80 dark:text-sky-400">akun aktif sistem</div>
            </div>

            <div class="admin-stat-danger rounded-2xl p-4 border shadow-xs transition-all duration-200 hover:-translate-y-0.5 motion-reduce:hover:transform-none">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300">Administrator</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-rose-500/15 text-rose-700 dark:text-rose-300">
                        <flux:icon.shield-check class="size-4" />
                    </div>
                </div>
                <div class="mt-2 font-mono text-2xl font-black text-rose-950 dark:text-rose-100 tabular-nums">
                    {{ $adminCount }}
                </div>
                <div class="mt-0.5 text-xs text-rose-700/80 dark:text-rose-400">hak akses penuh</div>
            </div>

            <div class="admin-stat-success rounded-2xl p-4 border shadow-xs transition-all duration-200 hover:-translate-y-0.5 motion-reduce:hover:transform-none">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Petugas Loket</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">
                        <flux:icon.identification class="size-4" />
                    </div>
                </div>
                <div class="mt-2 font-mono text-2xl font-black text-emerald-950 dark:text-emerald-100 tabular-nums">
                    {{ $officerCount }}
                </div>
                <div class="mt-0.5 text-xs text-emerald-700/80 dark:text-emerald-400">melayani di loket</div>
            </div>

            <div class="admin-stat-warning rounded-2xl p-4 border shadow-xs transition-all duration-200 hover:-translate-y-0.5 motion-reduce:hover:transform-none">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Frontdesk & Display</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-700 dark:text-amber-300">
                        <flux:icon.computer-desktop class="size-4" />
                    </div>
                </div>
                <div class="mt-2 font-mono text-2xl font-black text-amber-950 dark:text-amber-100 tabular-nums">
                    {{ $frontdeskCount + $monitorCount }}
                </div>
                <div class="mt-0.5 text-xs text-amber-700/80 dark:text-amber-400">{{ $frontdeskCount }} tamu · {{ $monitorCount }} display TV</div>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('status'))
            <div class="animate-fade-in-up motion-reduce:animate-none">
                <flux:callout icon="check-circle" color="green" class="shadow-xs rounded-2xl">
                    {{ session('status') }}
                </flux:callout>
            </div>
        @endif

        @if (session('error'))
            <div class="animate-fade-in-up motion-reduce:animate-none">
                <flux:callout icon="x-circle" color="red" class="shadow-xs rounded-2xl">
                    {{ session('error') }}
                </flux:callout>
            </div>
        @endif

        {{-- Accessible Animated Sliding Tab Bar --}}
        <div
            class="relative w-full overflow-x-auto rounded-2xl border border-cyan-200 bg-cyan-50/70 p-1 shadow-xs dark:border-cyan-900/70 dark:bg-cyan-950/30 sm:w-fit"
            role="tablist"
            aria-label="Navigasi manajemen user"
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
                    id="user-list-tab"
                    x-ref="listTab"
                    type="button"
                    role="tab"
                    aria-controls="user-list-panel"
                    x-bind:aria-selected="tab === 'list'"
                    x-bind:tabindex="tab === 'list' ? 0 : -1"
                    x-on:click="selectTab('list')"
                    x-on:keydown.right.prevent="selectTab('roles', true)"
                    x-on:keydown.left.prevent="selectTab('roles', true)"
                    x-on:keydown.home.prevent="selectTab('list', true)"
                    x-on:keydown.end.prevent="selectTab('roles', true)"
                    class="admin-tab-btn flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold whitespace-nowrap focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 sm:flex-none dark:focus-visible:outline-cyan-300"
                >
                    <flux:icon.users class="size-4" />
                    <span class="admin-tab-label" x-bind:class="tab === 'list' ? 'text-white' : 'text-zinc-700 hover:text-cyan-950 dark:text-zinc-300 dark:hover:text-white'">Semua Users</span>
                </button>
                <button
                    id="user-roles-tab"
                    x-ref="rolesTab"
                    type="button"
                    role="tab"
                    aria-controls="user-roles-panel"
                    x-bind:aria-selected="tab === 'roles'"
                    x-bind:tabindex="tab === 'roles' ? 0 : -1"
                    x-on:click="selectTab('roles')"
                    x-on:keydown.right.prevent="selectTab('list', true)"
                    x-on:keydown.left.prevent="selectTab('list', true)"
                    x-on:keydown.home.prevent="selectTab('roles', true)"
                    x-on:keydown.end.prevent="selectTab('roles', true)"
                    class="admin-tab-btn flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold whitespace-nowrap focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-700 sm:flex-none dark:focus-visible:outline-cyan-300"
                >
                    <flux:icon.shield-check class="size-4" />
                    <span class="admin-tab-label" x-bind:class="tab === 'roles' ? 'text-white' : 'text-zinc-700 hover:text-cyan-950 dark:text-zinc-300 dark:hover:text-white'">Role & Izin</span>
                </button>
            </div>
        </div>

        {{-- Tab 1: Semua Users Panel --}}
        <div
            id="user-list-panel"
            role="tabpanel"
            aria-labelledby="user-list-tab"
            tabindex="0"
            x-show="tab === 'list'"
            x-cloak
            x-transition:enter="motion-safe:transition-opacity motion-safe:duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        >
            <flux:card class="admin-card-elevated space-y-4 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400">
                            <flux:icon.users class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Daftar Akun Pengguna</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Kelola informasi akun staf dan kredensial akses.</flux:text>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <flux:input
                            x-model="searchUser"
                            placeholder="Cari nama, email, atau role..."
                            icon="magnifying-glass"
                            clearable
                            class="w-full sm:w-72"
                        />
                    </div>
                </div>

                @if ($otherUsers->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="flex size-16 items-center justify-center rounded-3xl bg-cyan-50 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-400 mb-4 shadow-inner">
                            <flux:icon.users class="size-8" />
                        </div>
                        <flux:heading size="md" class="font-bold text-zinc-900 dark:text-white">Belum ada user selain Anda</flux:heading>
                        <flux:text class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">
                            Tambahkan user baru melalui tombol di atas untuk mengelola tim loket dan staf operasional Anda.
                        </flux:text>
                        <div class="mt-5">
                            <flux:modal.trigger name="create-user">
                                <flux:button variant="primary" icon="plus" size="sm" class="shadow-sm">
                                    Tambah User Pertama
                                </flux:button>
                            </flux:modal.trigger>
                        </div>
                    </div>
                @else
                    <div class="admin-table-scroll overflow-x-auto rounded-xl">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Pengguna</flux:table.column>
                                <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Kontak / Email</flux:table.column>
                                <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Peran (Role)</flux:table.column>
                                <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Penugasan Layanan</flux:table.column>
                                <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider text-right">Aksi</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($users as $user)
                                    @php
                                        $isSelf = $user->id === auth()->id();
                                        $roleValue = $user->role?->value ?? 'monitor';
                                        $roleLabel = $user->role?->label() ?? ucfirst($roleValue);
                                        $roleColor = $user->role?->color() ?? 'zinc';
                                        
                                        $avatarBg = match ($roleValue) {
                                            'admin' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 ring-rose-200 dark:ring-rose-900',
                                            'officer' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-900',
                                            'frontdesk' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 ring-sky-200 dark:ring-sky-900',
                                            default => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 ring-purple-200 dark:ring-purple-900',
                                        };
                                        $initials = strtoupper(substr(trim($user->name), 0, 2));
                                    @endphp
                                    <flux:table.row
                                        x-show="!searchUser || '{{ strtolower(addslashes($user->name . ' ' . $user->email . ' ' . $roleLabel)) }}'.includes(searchUser.toLowerCase())"
                                        class="{{ $isSelf ? 'bg-cyan-50/40 dark:bg-cyan-950/20' : '' }} transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40"
                                    >
                                        {{-- Pengguna Column --}}
                                        <flux:table.cell class="whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl font-mono text-xs font-bold ring-1 {{ $avatarBg }} shadow-2xs">
                                                    {{ $initials }}
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                                        @if ($isSelf)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-bold text-cyan-800 dark:bg-cyan-950/80 dark:text-cyan-300">
                                                                <flux:icon.star class="size-2.5 fill-cyan-600 dark:fill-cyan-400" />
                                                                Anda
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">ID: #{{ $user->id }}</span>
                                                </div>
                                            </div>
                                        </flux:table.cell>

                                        {{-- Email / Kontak with Copy Delight --}}
                                        <flux:table.cell class="whitespace-nowrap">
                                            <div class="flex items-center gap-2" x-data="{ copied: false }">
                                                <span class="font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $user->email }}</span>
                                                <button
                                                    type="button"
                                                    x-on:click="
                                                        navigator.clipboard.writeText('{{ $user->email }}');
                                                        copied = true;
                                                        setTimeout(() => copied = false, 2000);
                                                    "
                                                    title="Salin Email"
                                                    aria-label="Salin email {{ $user->email }}"
                                                    class="inline-flex size-6 items-center justify-center rounded-md text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 focus-visible:outline-2 focus-visible:outline-cyan-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                                >
                                                    <template x-if="!copied">
                                                        <flux:icon.clipboard class="size-3.5" />
                                                    </template>
                                                    <template x-if="copied">
                                                        <flux:icon.check class="size-3.5 text-emerald-600 dark:text-emerald-400 animate-scale-in" />
                                                    </template>
                                                </button>
                                                <span x-show="copied" x-cloak class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Tersalin!</span>
                                            </div>
                                        </flux:table.cell>

                                        {{-- Role Column --}}
                                        <flux:table.cell class="whitespace-nowrap">
                                            <flux:badge size="sm" color="{{ $roleColor }}" class="font-semibold shadow-2xs">
                                                {{ $roleLabel }}
                                            </flux:badge>
                                        </flux:table.cell>

                                        {{-- Penugasan Layanan Column --}}
                                        <flux:table.cell class="whitespace-nowrap">
                                            @if ($user->services->isNotEmpty())
                                                <div class="flex items-center gap-1.5">
                                                    <flux:badge size="sm" color="zinc" icon="building-office" class="font-medium">
                                                        {{ $user->services->first()->name }}
                                                    </flux:badge>
                                                </div>
                                            @elseif ($user->role === \App\Enums\UserRole::Officer)
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 dark:text-amber-400">
                                                    <flux:icon.exclamation-triangle class="size-3.5" />
                                                    Belum Ditugaskan
                                                </span>
                                            @else
                                                <span class="text-xs text-zinc-400 dark:text-zinc-500">-</span>
                                            @endif
                                        </flux:table.cell>

                                        {{-- Aksi Column --}}
                                        <flux:table.cell class="whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-1.5 sm:gap-2">
                                                <flux:modal.trigger name="edit-user-{{ $user->id }}">
                                                    <flux:button size="sm" variant="filled" icon="pencil" class="font-semibold">
                                                        Edit
                                                    </flux:button>
                                                </flux:modal.trigger>

                                                @if (! $isSelf)
                                                    <flux:modal.trigger name="delete-user-{{ $user->id }}">
                                                        <flux:button size="sm" variant="danger" icon="trash" class="font-semibold">
                                                            Hapus
                                                        </flux:button>
                                                    </flux:modal.trigger>
                                                @endif
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>

                    {{-- Search Zero State --}}
                    <div
                        x-show="searchUser && !document.querySelectorAll('#user-list-panel tbody tr:not([style*=\'display: none\'])').length"
                        x-cloak
                        class="flex flex-col items-center justify-center py-10 text-center border-t border-zinc-100 dark:border-zinc-800"
                    >
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 mb-3">
                            <flux:icon.magnifying-glass class="size-6" />
                        </div>
                        <flux:heading size="sm" class="font-bold text-zinc-800 dark:text-zinc-200">Tidak ada hasil pencarian</flux:heading>
                        <flux:text class="mt-1 text-xs text-zinc-500">
                            Tidak ditemukan user yang cocok dengan kata kunci "<span x-text="searchUser" class="font-semibold text-zinc-700 dark:text-zinc-300"></span>".
                        </flux:text>
                        <button
                            type="button"
                            x-on:click="searchUser = ''"
                            class="mt-3 text-xs font-semibold text-cyan-600 hover:text-cyan-700 hover:underline dark:text-cyan-400"
                        >
                            Reset Pencarian
                        </button>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Tab 2: Role & Izin Layanan Panel --}}
        <div
            id="user-roles-panel"
            role="tabpanel"
            aria-labelledby="user-roles-tab"
            tabindex="0"
            x-show="tab === 'roles'"
            x-cloak
            x-transition:enter="motion-safe:transition-opacity motion-safe:duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        >
            <flux:card class="admin-card-elevated space-y-5 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                            <flux:icon.shield-check class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Role & Izin Layanan Petugas</flux:heading>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Atur hak akses operasional dan penugasan spesifik loket layanan.</flux:text>
                        </div>
                    </div>
                </div>

                {{-- Role Permission Informational Guide Strip --}}
                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex items-start gap-2.5 rounded-2xl border border-rose-200/80 bg-rose-50/50 p-3 dark:border-rose-900/50 dark:bg-rose-950/20">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-rose-500/15 text-rose-700 dark:text-rose-300">
                            <flux:icon.shield-check class="size-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-rose-900 dark:text-rose-200">Admin</span>
                            <p class="text-xs text-rose-800/80 dark:text-rose-300/80">Akses konfigurasi sistem, data master, user, dan laporan eksekutif.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">
                            <flux:icon.identification class="size-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-emerald-900 dark:text-emerald-200">Officer (Petugas)</span>
                            <p class="text-xs text-emerald-800/80 dark:text-emerald-300/80">Pemanggilan antrian tiket, operasional loket, dan pencatatan layanan.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 rounded-2xl border border-sky-200/80 bg-sky-50/50 p-3 dark:border-sky-900/50 dark:bg-sky-950/20">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/15 text-sky-700 dark:text-sky-300">
                            <flux:icon.user-group class="size-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-sky-900 dark:text-sky-200">Frontdesk</span>
                            <p class="text-xs text-sky-800/80 dark:text-sky-300/80">Penerimaan tamu walk-in, cetak tiket pendaftaran, dan verifikasi booking.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 rounded-2xl border border-purple-200/80 bg-purple-50/50 p-3 dark:border-purple-900/50 dark:bg-purple-950/20">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-purple-500/15 text-purple-700 dark:text-purple-300">
                            <flux:icon.computer-desktop class="size-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-purple-900 dark:text-purple-200">Monitor</span>
                            <p class="text-xs text-purple-800/80 dark:text-purple-300/80">Layar display TV antrean publik dan pemantauan status antrian.</p>
                        </div>
                    </div>
                </div>

                {{-- Interactive Fast Assignment Table --}}
                <div class="admin-table-scroll overflow-x-auto rounded-xl">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Pengguna</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Email Akun</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider min-w-[20rem]">Konfigurasi Peran & Layanan</flux:table.column>
                            <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Status Aktif</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($users as $user)
                                @php
                                    $isSelf = $user->id === auth()->id();
                                    $initials = strtoupper(substr(trim($user->name), 0, 2));
                                @endphp
                                <flux:table.row class="{{ $isSelf ? 'bg-cyan-50/40 dark:bg-cyan-950/20' : '' }} transition-colors hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                    <flux:table.cell class="whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 font-mono text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 ring-1 ring-zinc-200 dark:ring-zinc-700">
                                                {{ $initials }}
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                                @if ($isSelf)
                                                    <span class="inline-flex items-center gap-0.5 rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-bold text-cyan-800 dark:bg-cyan-950/80 dark:text-cyan-300">
                                                        Anda
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    
                                    <flux:table.cell class="whitespace-nowrap font-mono text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $user->email }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-2 py-1" x-data="{ role: '{{ $user->role?->value }}' }">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $user->name }}">
                                            <input type="hidden" name="email" value="{{ $user->email }}">

                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="w-40 shrink-0">
                                                    <flux:select name="role" size="sm" x-model="role">
                                                        <flux:select.option value="admin" :selected="$user->role?->value === 'admin'">Admin</flux:select.option>
                                                        <flux:select.option value="frontdesk" :selected="$user->role?->value === 'frontdesk'">Frontdesk</flux:select.option>
                                                        <flux:select.option value="officer" :selected="$user->role?->value === 'officer'">Officer</flux:select.option>
                                                        <flux:select.option value="monitor" :selected="$user->role?->value === 'monitor'">Monitor</flux:select.option>
                                                    </flux:select>
                                                </div>

                                                <flux:button type="submit" variant="filled" size="sm" class="font-semibold shadow-xs hover:scale-[1.02] active:scale-95 transition-all motion-reduce:transform-none">
                                                    Update
                                                </flux:button>
                                            </div>

                                            <div
                                                class="w-full max-w-sm pt-1"
                                                x-show="role === 'officer'"
                                                x-cloak
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                            >
                                                <flux:select
                                                    name="service_id"
                                                    size="sm"
                                                    placeholder="Pilih layanan bertugas..."
                                                >
                                                    <flux:select.option value="">Pilih layanan bertugas...</flux:select.option>
                                                    @foreach ($services as $service)
                                                        <flux:select.option
                                                            value="{{ $service->id }}"
                                                            :selected="$user->services->first()?->id == $service->id"
                                                        >
                                                            {{ $service->name }} (Pool: {{ $service->queuePool?->code ?? '-' }})
                                                        </flux:select.option>
                                                    @endforeach
                                                </flux:select>
                                            </div>
                                        </form>
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        @if ($user->services->isNotEmpty())
                                            <flux:badge size="sm" color="cyan" icon="check-circle" class="font-semibold">
                                                {{ $user->services->first()->name }}
                                            </flux:badge>
                                        @elseif ($user->role === \App\Enums\UserRole::Officer)
                                            <flux:badge size="sm" color="amber" icon="clock">
                                                Belum Ditugaskan
                                            </flux:badge>
                                        @else
                                            <flux:badge size="sm" color="zinc">
                                                Umum
                                            </flux:badge>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- Create User Modal Drawer --}}
        <flux:modal name="create-user" class="w-full max-w-lg">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4" x-data="{ role: '{{ old('role', 'admin') }}' }">
                @csrf
                <div class="flex items-center gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-400">
                        <flux:icon.user-plus class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Tambah User Baru</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Buat akun staf operasional atau admin PTSP baru.</flux:text>
                    </div>
                </div>

                <flux:field>
                    <flux:label>Nama Lengkap</flux:label>
                    <flux:input name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso, S.H." required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Alamat Email</flux:label>
                    <flux:input type="email" name="email" value="{{ old('email') }}" placeholder="budi@pa-penajam.go.id" required />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Peran (Role)</flux:label>
                    <flux:select name="role" x-model="role">
                        <flux:select.option value="admin">Admin - Konfigurasi & Hak Akses Penuh</flux:select.option>
                        <flux:select.option value="frontdesk">Frontdesk - Registrasi & Cetak Tiket</flux:select.option>
                        <flux:select.option value="officer">Officer - Petugas Loket Pelayanan</flux:select.option>
                        <flux:select.option value="monitor">Monitor - Display TV Antrean</flux:select.option>
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>

                {{-- Dynamic Role Info Box --}}
                <div
                    class="rounded-xl border p-3 text-xs transition-all"
                    x-bind:class="{
                        'border-rose-200 bg-rose-50/70 text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300': role === 'admin',
                        'border-emerald-200 bg-emerald-50/70 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300': role === 'officer',
                        'border-sky-200 bg-sky-50/70 text-sky-800 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-300': role === 'frontdesk',
                        'border-purple-200 bg-purple-50/70 text-purple-800 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-300': role === 'monitor',
                    }"
                >
                    <template x-if="role === 'admin'">
                        <div class="flex items-center gap-2">
                            <flux:icon.shield-check class="size-4 shrink-0" />
                            <span>Role <strong>Admin</strong> memiliki izin penuh untuk mengelola master data, konfigurasi loket, dan akun pengguna.</span>
                        </div>
                    </template>
                    <template x-if="role === 'officer'">
                        <div class="flex items-center gap-2">
                            <flux:icon.identification class="size-4 shrink-0" />
                            <span>Role <strong>Officer</strong> bertugas memanggil antrian dan melayani pemohon di loket pelayanan.</span>
                        </div>
                    </template>
                    <template x-if="role === 'frontdesk'">
                        <div class="flex items-center gap-2">
                            <flux:icon.user-group class="size-4 shrink-0" />
                            <span>Role <strong>Frontdesk</strong> melayani pendaftaran tamu, pencetakan tiket antrian, dan verifikasi berkas.</span>
                        </div>
                    </template>
                    <template x-if="role === 'monitor'">
                        <div class="flex items-center gap-2">
                            <flux:icon.computer-desktop class="size-4 shrink-0" />
                            <span>Role <strong>Monitor</strong> digunakan khusus untuk layar TV display antrean ruang tunggu.</span>
                        </div>
                    </template>
                </div>

                {{-- Smooth Service Selector for Officer --}}
                <div
                    x-show="role === 'officer'"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    <flux:field>
                        <flux:label>Layanan yang Ditugaskan</flux:label>
                        <flux:select
                            name="service_id"
                            placeholder="Pilih layanan tempat petugas bertugas"
                        >
                            <flux:select.option value="">Pilih layanan tempat petugas bertugas...</flux:select.option>
                            @foreach ($services as $service)
                                <flux:select.option value="{{ $service->id }}">
                                    {{ $service->name }} (Pool: {{ $service->queuePool?->code ?? '-' }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="service_id" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Kata Sandi (Password)</flux:label>
                    <flux:input type="password" name="password" placeholder="Minimal 8 karakter" required />
                    <flux:error name="password" />
                </flux:field>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" icon="check" class="bg-cyan-700 hover:bg-cyan-600">
                        Simpan User
                    </flux:button>
                </div>
            </form>
        </flux:modal>

        {{-- Per-User Edit Modals --}}
        @foreach ($users as $user)
            <flux:modal name="edit-user-{{ $user->id }}" class="w-full max-w-lg">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4" x-data="{ role: '{{ $user->role?->value }}' }">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                        <div class="admin-icon-box bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400">
                            <flux:icon.pencil-square class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Edit User</flux:heading>
                            <flux:text class="text-xs text-zinc-500">Perbarui profil dan peran: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $user->name }}</span></flux:text>
                        </div>
                    </div>

                    <flux:field>
                        <flux:label>Nama Lengkap</flux:label>
                        <flux:input name="name" value="{{ $user->name }}" required />
                    </flux:field>

                    <flux:field>
                        <flux:label>Alamat Email</flux:label>
                        <flux:input type="email" name="email" value="{{ $user->email }}" required />
                    </flux:field>

                    <flux:field>
                        <flux:label>Peran (Role)</flux:label>
                        <flux:select name="role" x-model="role">
                            <flux:select.option value="admin" :selected="$user->role?->value === 'admin'">Admin - Konfigurasi & Hak Akses Penuh</flux:select.option>
                            <flux:select.option value="frontdesk" :selected="$user->role?->value === 'frontdesk'">Frontdesk - Registrasi & Cetak Tiket</flux:select.option>
                            <flux:select.option value="officer" :selected="$user->role?->value === 'officer'">Officer - Petugas Loket Pelayanan</flux:select.option>
                            <flux:select.option value="monitor" :selected="$user->role?->value === 'monitor'">Monitor - Display TV Antrean</flux:select.option>
                        </flux:select>
                    </flux:field>

                    {{-- Dynamic Role Info Box --}}
                    <div
                        class="rounded-xl border p-3 text-xs transition-all"
                        x-bind:class="{
                            'border-rose-200 bg-rose-50/70 text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300': role === 'admin',
                            'border-emerald-200 bg-emerald-50/70 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300': role === 'officer',
                            'border-sky-200 bg-sky-50/70 text-sky-800 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-300': role === 'frontdesk',
                            'border-purple-200 bg-purple-50/70 text-purple-800 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-300': role === 'monitor',
                        }"
                    >
                        <template x-if="role === 'admin'">
                            <div class="flex items-center gap-2">
                                <flux:icon.shield-check class="size-4 shrink-0" />
                                <span>Akses penuh sistem, master data, konfigurasi loket, dan hak akses staf.</span>
                            </div>
                        </template>
                        <template x-if="role === 'officer'">
                            <div class="flex items-center gap-2">
                                <flux:icon.identification class="size-4 shrink-0" />
                                <span>Petugas pemanggil antrian dan pelayan permohonan di loket PTSP.</span>
                            </div>
                        </template>
                        <template x-if="role === 'frontdesk'">
                            <div class="flex items-center gap-2">
                                <flux:icon.user-group class="size-4 shrink-0" />
                                <span>Penerimaan permohonan walk-in, cetak nomor tiket, dan verifikasi antrean.</span>
                            </div>
                        </template>
                        <template x-if="role === 'monitor'">
                            <div class="flex items-center gap-2">
                                <flux:icon.computer-desktop class="size-4 shrink-0" />
                                <span>Tampilan display layar TV ruang tunggu publik.</span>
                            </div>
                        </template>
                    </div>

                    <div
                        x-show="role === 'officer'"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                    >
                        <flux:field>
                            <flux:label>Layanan yang Ditugaskan</flux:label>
                            <flux:select
                                name="service_id"
                                placeholder="Pilih layanan tempat petugas bertugas"
                            >
                                <flux:select.option value="">Pilih layanan...</flux:select.option>
                                @foreach ($services as $service)
                                    <flux:select.option
                                        value="{{ $service->id }}"
                                        :selected="$user->services->first()?->id == $service->id"
                                    >
                                        {{ $service->name }} (Pool: {{ $service->queuePool?->code ?? '-' }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:modal.close>
                            <flux:button type="button" variant="ghost">Batal</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="primary" icon="check" class="bg-cyan-700 hover:bg-cyan-600">
                            Simpan Perubahan
                        </flux:button>
                    </div>
                </form>
            </flux:modal>
        @endforeach

        {{-- Delete User Confirmation Modals --}}
        @foreach ($otherUsers as $user)
            <flux:modal name="delete-user-{{ $user->id }}" class="w-full max-w-md">
                <div class="space-y-4">
                    <div class="flex items-center gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                        <div class="admin-icon-box bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400">
                            <flux:icon.trash class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Hapus Akun User</flux:heading>
                            <flux:text class="text-xs text-zinc-500">Konfirmasi penghapusan akun pengguna.</flux:text>
                        </div>
                    </div>

                    {{-- User Preview Card --}}
                    <div class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-800/40">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-zinc-200 font-mono text-xs font-bold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                            {{ strtoupper(substr(trim($user->name), 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-zinc-900 dark:text-white">{{ $user->name }}</p>
                            <p class="truncate text-xs font-mono text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                        </div>
                        <flux:badge size="sm" color="{{ $user->role?->color() ?? 'zinc' }}">
                            {{ $user->role?->label() ?? 'User' }}
                        </flux:badge>
                    </div>

                    <flux:callout icon="exclamation-circle" color="red" class="rounded-2xl">
                        Apakah Anda yakin ingin menghapus user <strong>{{ $user->name }}</strong>? Tindakan ini permanen dan tidak dapat dibatalkan.
                    </flux:callout>

                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        @csrf
                        @method('DELETE')
                        <flux:modal.close>
                            <flux:button type="button" variant="ghost">Batal</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="danger" icon="trash">
                            Hapus
                        </flux:button>
                    </form>
                </div>
            </flux:modal>
        @endforeach
    </div>
</x-layouts::app>
