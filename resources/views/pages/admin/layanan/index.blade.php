<x-layouts::app :title="__('Manajemen Layanan')">
    <div class="w-full space-y-6 animate-fade-in-up">
        <!-- Header & Top Action Area -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Layanan</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" level="1" class="font-extrabold tracking-tight">Manajemen Layanan</flux:heading>
                    <flux:badge size="sm" color="cyan" class="font-bold shadow-2xs">
                        {{ $stats['total'] ?? $services->total() }} Layanan
                    </flux:badge>
                </div>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">
                    Konfigurasi katalog layanan PTSP, pemetaan pool antrean, kuota harian, dan kanal pendaftaran.
                </flux:subheading>
            </div>
            
            <div class="flex items-center gap-2">
                <flux:modal.trigger name="create-service">
                    <flux:button variant="primary" icon="plus" class="w-full sm:w-auto font-bold shadow-md shadow-cyan-600/20 active:scale-95 transition-all">
                        Tambah Layanan Baru
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <!-- Quick Insights Stat Strip (Delight & Clarity) -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="admin-stat-total rounded-2xl p-4 border shadow-xs transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Total Katalog</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-700 dark:text-sky-300">
                        <flux:icon.clipboard-document-list class="size-4" />
                    </div>
                </div>
                <div class="mt-2 font-mono text-2xl font-black text-sky-950 dark:text-sky-100 tabular-nums">
                    {{ $stats['total'] ?? $services->total() }}
                </div>
                <div class="mt-0.5 text-xs text-sky-700/80 dark:text-sky-400">layanan terdaftar</div>
            </div>

            <div class="admin-stat-success rounded-2xl p-4 border shadow-xs transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Layanan Aktif</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">
                        <flux:icon.check-circle class="size-4" />
                    </div>
                </div>
                <div class="mt-2 font-mono text-2xl font-black text-emerald-950 dark:text-emerald-100 tabular-nums">
                    {{ $stats['active'] ?? 0 }}
                </div>
                <div class="mt-0.5 text-xs text-emerald-700/80 dark:text-emerald-400">siap melayani publik</div>
            </div>

            <div class="admin-stat-total rounded-2xl p-4 border shadow-xs transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-800 dark:text-cyan-300">Booking Daring</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-700 dark:text-cyan-300">
                        <flux:icon.globe-alt class="size-4" />
                    </div>
                </div>
                <div class="mt-2 font-mono text-2xl font-black text-cyan-950 dark:text-cyan-100 tabular-nums">
                    {{ $stats['booking_enabled'] ?? 0 }}
                </div>
                <div class="mt-0.5 text-xs text-cyan-700/80 dark:text-cyan-400">dapat dibooking online</div>
            </div>

            <div class="admin-stat-warning rounded-2xl p-4 border shadow-xs transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Walk-in Langsung</span>
                    <div class="flex size-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-700 dark:text-amber-300">
                        <flux:icon.user-group class="size-4" />
                    </div>
                </div>
                <div class="mt-2 font-mono text-2xl font-black text-amber-950 dark:text-amber-100 tabular-nums">
                    {{ $stats['walk_in_enabled'] ?? 0 }}
                </div>
                <div class="mt-0.5 text-xs text-amber-700/80 dark:text-amber-400">tiket mandiri di kiosk</div>
            </div>
        </div>

        @if (session('status'))
            <div class="animate-fade-in-up">
                <flux:callout icon="check-circle" color="green" class="shadow-xs rounded-2xl">
                    {{ session('status') }}
                </flux:callout>
            </div>
        @endif

        @if (session('error'))
            <div class="animate-fade-in-up">
                <flux:callout icon="x-circle" color="red" class="shadow-xs rounded-2xl">
                    {{ session('error') }}
                </flux:callout>
            </div>
        @endif

        <!-- Main Data Table Card -->
        <flux:card class="admin-card-elevated space-y-4 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-400">
                        <flux:icon.clipboard-document-list class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Daftar Layanan Pengadilan</flux:heading>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Katalog layanan resmi yang tersedia di PTSP.</flux:text>
                    </div>
                </div>
                
                <form method="GET" action="{{ route('admin.layanan.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    <flux:input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama atau kode..."
                        icon="magnifying-glass"
                        class="w-full sm:w-64"
                        clearable
                    />
                    @if (request('search'))
                        <flux:button :href="route('admin.layanan.index')" variant="ghost" size="sm" icon="x-mark" title="Reset Pencarian" />
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-16">
                            <a href="{{ route('admin.layanan.index', ['sort_by' => 'sort_order', 'sort_direction' => $sortBy === 'sort_order' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline whitespace-nowrap text-xs uppercase font-bold tracking-wider">
                                Urutan
                                @if ($sortBy === 'sort_order')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3 text-cyan-600" />
                                @endif
                            </a>
                        </flux:table.column>
                        <flux:table.column>
                            <a href="{{ route('admin.layanan.index', ['sort_by' => 'name', 'sort_direction' => $sortBy === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline whitespace-nowrap text-xs uppercase font-bold tracking-wider">
                                Layanan & Kode
                                @if ($sortBy === 'name')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3 text-cyan-600" />
                                @endif
                            </a>
                        </flux:table.column>
                        <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Queue Pool</flux:table.column>
                        <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider">Kanal Pelayanan</flux:table.column>
                        <flux:table.column>
                            <a href="{{ route('admin.layanan.index', ['sort_by' => 'is_active', 'sort_direction' => $sortBy === 'is_active' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="flex items-center gap-1 hover:underline whitespace-nowrap text-xs uppercase font-bold tracking-wider">
                                Status
                                @if ($sortBy === 'is_active')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="size-3 text-cyan-600" />
                                @endif
                            </a>
                        </flux:table.column>
                        <flux:table.column class="whitespace-nowrap text-xs uppercase font-bold tracking-wider text-right">Aksi</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($services as $service)
                            <flux:table.row class="transition-all duration-200 hover:bg-cyan-50/30 dark:hover:bg-zinc-800/60">
                                <flux:table.cell>
                                    <span class="inline-flex size-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        {{ $service->sort_order }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-cyan-100 font-mono text-sm font-black text-cyan-700 dark:bg-cyan-950/80 dark:text-cyan-300 border border-cyan-200/80 dark:border-cyan-800/60 shadow-2xs">
                                            {{ $service->code }}
                                        </span>
                                        <div class="min-w-0">
                                            <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $service->name }}</div>
                                            <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                <span class="font-mono text-zinc-400">/{{ $service->slug }}</span>
                                                @if ($service->daily_quota)
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-1.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40">
                                                        Kuota: {{ $service->daily_quota }}/hari
                                                    </span>
                                                @else
                                                    <span class="text-zinc-400 dark:text-zinc-500">Kuota: Bebas</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    @if ($service->queuePool)
                                        <flux:badge size="sm" color="cyan" class="font-semibold">
                                            {{ $service->queuePool->name }}
                                            @if ($service->queuePool->letter_code)
                                                ({{ $service->queuePool->letter_code }})
                                            @endif
                                        </flux:badge>
                                    @else
                                        <span class="text-zinc-400 text-xs italic">Belum diatur</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        @if ($service->booking_enabled)
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-sky-50 px-2 py-0.5 text-xs font-semibold text-sky-700 border border-sky-200/80 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800/40" title="Mendukung Booking Online">
                                                <flux:icon.globe-alt class="size-3" />
                                                Online
                                            </span>
                                        @endif
                                        @if ($service->walk_in_enabled)
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/40" title="Mendukung Pengambilan Tiket Walk-in di Kiosk">
                                                <flux:icon.user-group class="size-3" />
                                                Walk-in
                                            </span>
                                        @endif
                                        @if (! $service->booking_enabled && ! $service->walk_in_enabled)
                                            <span class="text-xs text-zinc-400 italic">Kanal ditutup</span>
                                        @endif
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    @if ($service->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/40 shadow-2xs">
                                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700">
                                            Nonaktif
                                        </span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('admin.layanan.update', $service) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $service->name }}">
                                            <input type="hidden" name="code" value="{{ $service->code }}">
                                            <input type="hidden" name="slug" value="{{ $service->slug }}">
                                            <input type="hidden" name="queue_pool_id" value="{{ $service->queue_pool_id }}">
                                            <input type="hidden" name="sort_order" value="{{ $service->sort_order }}">
                                            <input type="hidden" name="daily_quota" value="{{ $service->daily_quota }}">
                                            <input type="hidden" name="booking_enabled" value="{{ $service->booking_enabled ? 1 : 0 }}">
                                            <input type="hidden" name="walk_in_enabled" value="{{ $service->walk_in_enabled ? 1 : 0 }}">
                                            <input type="hidden" name="description" value="{{ $service->description }}">
                                            <input type="hidden" name="requirements" value="{{ $service->requirements }}">
                                            <input type="hidden" name="is_active" value="{{ $service->is_active ? 0 : 1 }}">
                                            <flux:button type="submit" variant="ghost" size="sm" class="text-xs font-semibold text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                                                {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </flux:button>
                                        </form>

                                        <flux:modal.trigger name="edit-service-{{ $service->id }}">
                                            <flux:button size="sm" variant="filled" icon="pencil" class="shadow-2xs">
                                                Edit
                                            </flux:button>
                                        </flux:modal.trigger>

                                        <flux:modal.trigger name="delete-service-{{ $service->id }}">
                                            <flux:button size="sm" variant="danger" icon="trash" class="shadow-2xs">
                                                Hapus
                                            </flux:button>
                                        </flux:modal.trigger>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6">
                                    <div class="flex flex-col items-center justify-center py-12 text-center">
                                        <div class="flex size-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/60 dark:text-cyan-400">
                                            <flux:icon.clipboard-document-list class="size-7" />
                                        </div>
                                        <p class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                            @if (request('search'))
                                                Tidak ada layanan yang cocok dengan "{{ request('search') }}"
                                            @else
                                                Belum Ada Layanan Terdaftar
                                            @endif
                                        </p>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 max-w-sm">
                                            @if (request('search'))
                                                Coba gunakan kata kunci lain atau bersihkan pencarian untuk melihat semua layanan.
                                            @else
                                                Mulai tambahkan katalog layanan PTSP seperti Pendaftaran Perkara, Pengambilan Produk, atau Konsultasi Posbakum.
                                            @endif
                                        </p>
                                        <div class="mt-4">
                                            @if (request('search'))
                                                <flux:button :href="route('admin.layanan.index')" variant="subtle" size="sm" icon="arrow-path">
                                                    Reset Pencarian
                                                </flux:button>
                                            @else
                                                <flux:modal.trigger name="create-service">
                                                    <flux:button variant="primary" size="sm" icon="plus">
                                                        Tambah Layanan Pertama
                                                    </flux:button>
                                                </flux:modal.trigger>
                                            @endif
                                        </div>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>

            @if ($services->hasPages())
                <div class="border-t border-zinc-100 dark:border-zinc-800/80 pt-4">
                    {{ $services->appends(['search' => request('search'), 'sort_by' => $sortBy, 'sort_direction' => $sortDirection])->links() }}
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Create Modal with Reactive Slug Generator Delight --}}
    <flux:modal name="create-service" class="w-full max-w-2xl">
        <form 
            method="POST" 
            action="{{ route('admin.layanan.store') }}" 
            class="space-y-5" 
            x-data="{ 
                name: @js(old('name', '')), 
                slug: @js(old('slug', '')), 
                submitting: false, 
                autoSlug: true,
                updateSlug() {
                    if (this.autoSlug) {
                        this.slug = this.name.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
                    }
                }
            }" 
            @submit="submitting = true"
        >
            @csrf
            
            <div class="flex items-center gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
                    <flux:icon.plus-circle class="size-5" />
                </div>
                <div>
                    <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Tambah Layanan Baru</flux:heading>
                    <flux:text class="text-xs text-zinc-500">Konfigurasikan data dasar layanan dan rute antrean PTSP.</flux:text>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label class="font-semibold">Nama Layanan</flux:label>
                    <flux:input 
                        name="name" 
                        x-model="name" 
                        x-on:input="updateSlug()" 
                        placeholder="Contoh: Pendaftaran Perkara Gugatan" 
                        required 
                    />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label class="font-semibold">Kode Layanan</flux:label>
                    <flux:input name="code" value="{{ old('code') }}" placeholder="Contoh: A, B, atau C1" required />
                    <flux:error name="code" />
                </flux:field>

                <flux:field>
                    <div class="flex items-center justify-between">
                        <flux:label class="font-semibold">Slug URL</flux:label>
                        <button 
                            type="button" 
                            x-on:click="autoSlug = !autoSlug; if(autoSlug) updateSlug();" 
                            class="text-xs text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 font-semibold"
                            x-text="autoSlug ? 'Otomatis' : 'Manual'"
                        ></button>
                    </div>
                    <flux:input 
                        name="slug" 
                        x-model="slug" 
                        x-on:input="autoSlug = false" 
                        placeholder="pendaftaran-perkara-gugatan" 
                        required 
                    />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label class="font-semibold">Queue Pool Antrean</flux:label>
                    <flux:select name="queue_pool_id" required>
                        <flux:select.option value="">Pilih Pool Antrean</flux:select.option>
                        @foreach ($queuePools as $pool)
                            <flux:select.option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->letter_code ?? '-' }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="queue_pool_id" />
                </flux:field>

                <flux:field>
                    <flux:label class="font-semibold">Urutan Tampilan</flux:label>
                    <flux:input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" required />
                    <flux:error name="sort_order" />
                </flux:field>

                <flux:field>
                    <flux:label class="font-semibold">Kuota Harian</flux:label>
                    <flux:input type="number" name="daily_quota" value="{{ old('daily_quota') }}" placeholder="Kosongkan jika tak terbatas" />
                    <flux:error name="daily_quota" />
                </flux:field>

                <!-- Channel Toggles Section -->
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 p-4 md:col-span-2 space-y-3">
                    <div class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pengaturan Status & Kanal</div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="flex items-center gap-2 p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80">
                            <input type="hidden" name="is_active" value="0">
                            <flux:checkbox name="is_active" value="1" :checked="(bool) old('is_active', 1)" label="Layanan Aktif" />
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80">
                            <input type="hidden" name="booking_enabled" value="0">
                            <flux:checkbox name="booking_enabled" value="1" :checked="(bool) old('booking_enabled', 1)" label="Terima Booking" />
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80">
                            <input type="hidden" name="walk_in_enabled" value="0">
                            <flux:checkbox name="walk_in_enabled" value="1" :checked="(bool) old('walk_in_enabled', 1)" label="Terima Walk-in" />
                        </div>
                    </div>
                </div>

                <flux:field class="md:col-span-2">
                    <flux:label class="font-semibold">Deskripsi Layanan</flux:label>
                    <flux:textarea name="description" rows="2" placeholder="Penjelasan singkat mengenai layanan untuk pemohon...">{{ old('description') }}</flux:textarea>
                    <flux:error name="description" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label class="font-semibold">Persyaratan Berkas</flux:label>
                    <flux:textarea name="requirements" rows="2" placeholder="Daftar berkas atau syarat yang perlu dibawa pemohon...">{{ old('requirements') }}</flux:textarea>
                    <flux:error name="requirements" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 border-t border-zinc-100 dark:border-zinc-800 pt-3">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" x-bind:disabled="submitting" class="font-bold shadow-md shadow-cyan-600/25">
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
            <form 
                method="POST" 
                action="{{ route('admin.layanan.update', $service) }}" 
                class="space-y-5" 
                x-data="{ submitting: false }" 
                @submit="submitting = true"
            >
                @csrf
                @method('PUT')
                
                <div class="flex items-center gap-3 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Edit Layanan: {{ $service->name }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Perbarui konfigurasi data master layanan.</flux:text>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label class="font-semibold">Nama Layanan</flux:label>
                        <flux:input name="name" value="{{ old('name', $service->name) }}" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="font-semibold">Kode Layanan</flux:label>
                        <flux:input name="code" value="{{ old('code', $service->code) }}" required />
                        <flux:error name="code" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="font-semibold">Slug URL</flux:label>
                        <flux:input name="slug" value="{{ old('slug', $service->slug) }}" required />
                        <flux:error name="slug" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="font-semibold">Queue Pool</flux:label>
                        <flux:select name="queue_pool_id" value="{{ old('queue_pool_id', $service->queue_pool_id) }}" required>
                            @foreach ($queuePools as $pool)
                                <flux:select.option value="{{ $pool->id }}">{{ $pool->name }} ({{ $pool->letter_code ?? '-' }})</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="queue_pool_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="font-semibold">Urutan Tampilan</flux:label>
                        <flux:input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}" required />
                        <flux:error name="sort_order" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="font-semibold">Kuota Harian</flux:label>
                        <flux:input type="number" name="daily_quota" value="{{ old('daily_quota', $service->daily_quota) }}" placeholder="Kosongkan jika tak terbatas" />
                        <flux:error name="daily_quota" />
                    </flux:field>

                    <!-- Channel Toggles Section -->
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 p-4 md:col-span-2 space-y-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pengaturan Status & Kanal</div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="flex items-center gap-2 p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80">
                                <input type="hidden" name="is_active" value="0">
                                <flux:checkbox name="is_active" value="1" :checked="(bool) old('is_active', $service->is_active)" label="Layanan Aktif" />
                            </div>
                            <div class="flex items-center gap-2 p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80">
                                <input type="hidden" name="booking_enabled" value="0">
                                <flux:checkbox name="booking_enabled" value="1" :checked="(bool) old('booking_enabled', $service->booking_enabled)" label="Terima Booking" />
                            </div>
                            <div class="flex items-center gap-2 p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80">
                                <input type="hidden" name="walk_in_enabled" value="0">
                                <flux:checkbox name="walk_in_enabled" value="1" :checked="(bool) old('walk_in_enabled', $service->walk_in_enabled)" label="Terima Walk-in" />
                            </div>
                        </div>
                    </div>

                    <flux:field class="md:col-span-2">
                        <flux:label class="font-semibold">Deskripsi Layanan</flux:label>
                        <flux:textarea name="description" rows="2">{{ old('description', $service->description) }}</flux:textarea>
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label class="font-semibold">Persyaratan Berkas</flux:label>
                        <flux:textarea name="requirements" rows="2">{{ old('requirements', $service->requirements) }}</flux:textarea>
                        <flux:error name="requirements" />
                    </flux:field>
                </div>

                <div class="flex justify-end gap-2 border-t border-zinc-100 dark:border-zinc-800 pt-3">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" x-bind:disabled="submitting" class="font-bold shadow-md shadow-cyan-600/25">
                        <span x-show="!submitting">Simpan Perubahan</span>
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
                    <div>
                        <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-white">Hapus Layanan</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Konfirmasi tindakan penghapusan data.</flux:text>
                    </div>
                </div>

                <flux:callout icon="exclamation-circle" color="red">
                    Apakah Anda yakin ingin menghapus layanan <strong class="font-bold">{{ $service->name }}</strong> (Kode: {{ $service->code }})? Tindakan ini tidak dapat dibatalkan jika tidak memiliki relasi antrean.
                </flux:callout>

                <form method="POST" action="{{ route('admin.layanan.destroy', $service) }}" class="flex justify-end gap-2 pt-2">
                    @csrf
                    @method('DELETE')
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger" icon="trash" class="font-bold">Ya, Hapus</flux:button>
                </form>
            </div>
        </flux:modal>
    @endforeach
</x-layouts::app>
