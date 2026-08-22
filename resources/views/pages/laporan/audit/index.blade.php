<x-layouts::app :title="__('Audit Trail')">
    <div class="w-full space-y-6">
        {{-- Print-Only Formal Header --}}
        <div class="hidden print:block mb-6 border-b-2 border-zinc-900 pb-4 text-center">
            <h1 class="text-xl font-bold uppercase tracking-wider text-zinc-900">{{ config('institution.name', 'Pengadilan Agama') }}</h1>
            <h2 class="text-base font-semibold uppercase tracking-wide text-zinc-800 mt-1">Audit Trail & Log Aktivitas Sistem PTSP</h2>
            <p class="text-xs text-zinc-600 mt-1">
                Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                @if ($search) &bull; Filter Pencarian: "{{ $search }}" @endif
                &bull; Dicetak: {{ now()->translatedFormat('d/m/Y H:i') }}
            </p>
        </div>

        {{-- Breadcrumbs & Header --}}
        <div class="animate-fade-in-up flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" aria-label="Beranda" />
                    <flux:breadcrumbs.item>Laporan</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Audit Trail</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl" level="1" class="font-extrabold tracking-tight">Audit Trail</flux:heading>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30">
                        <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Log
                    </span>
                </div>
                <flux:subheading class="text-zinc-600 dark:text-zinc-400">
                    Rekam jejak kronologis aktivitas tiket antrian, panggilan loket, mutasi, dan aksi operator.
                </flux:subheading>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <flux:button 
                    type="button" 
                    variant="filled" 
                    icon="printer" 
                    class="font-semibold text-xs shadow-2xs cursor-pointer"
                    onclick="window.print()"
                    aria-label="Cetak log audit trail ke PDF atau printer"
                >
                    Cetak Log
                </flux:button>
            </div>
        </div>

        {{-- Filter Card & Date Navigator --}}
        <div class="animate-fade-in-up print:hidden" style="animation-delay: 75ms;">
            <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900" x-data="{
                date: '{{ $date }}',
                filtering: false,
                setPreset(daysAgo) {
                    const d = new Date();
                    d.setDate(d.getDate() - daysAgo);
                    this.date = d.toISOString().split('T')[0];
                    this.$nextTick(() => this.$refs.auditFilterForm.submit());
                }
            }">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/70 dark:text-cyan-300">
                            <flux:icon.clock class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold">Filter Aktivitas</flux:heading>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-zinc-600 dark:text-zinc-400">Tanggal terpilih:</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-bold text-cyan-800 ring-1 ring-cyan-200/60 dark:bg-cyan-950/50 dark:text-cyan-300 dark:ring-cyan-800/40">
                                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form 
                        x-ref="auditFilterForm"
                        method="GET" 
                        action="{{ url('/laporan/audit') }}" 
                        class="flex flex-col gap-3 sm:flex-row sm:items-end"
                        x-bind:aria-busy="filtering"
                        @submit="filtering = true"
                    >
                        <flux:field class="w-full sm:w-44">
                            <flux:label class="text-xs font-semibold">Pilih Tanggal</flux:label>
                            <flux:input type="date" name="date" x-model="date" required class="w-full" />
                        </flux:field>

                        <flux:field class="w-full sm:w-64">
                            <flux:label class="text-xs font-semibold">Cari Log</flux:label>
                            <flux:input
                                name="search"
                                value="{{ $search }}"
                                placeholder="Tiket, nama petugas, loket..."
                                icon="magnifying-glass"
                                class="w-full"
                            />
                        </flux:field>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <flux:button 
                                type="submit" 
                                variant="primary" 
                                class="w-full sm:w-auto bg-cyan-700 font-bold text-white shadow-md shadow-cyan-700/20 hover:bg-cyan-600 dark:bg-cyan-700 dark:text-white dark:hover:bg-cyan-600 px-5 cursor-pointer"
                                x-bind:disabled="filtering"
                            >
                                <span x-show="!filtering" class="flex items-center gap-1.5">
                                    <flux:icon.funnel class="size-4" />
                                    Filter
                                </span>
                                <span x-show="filtering" class="flex items-center gap-1.5" style="display: none;">
                                    <svg class="size-4 animate-spin motion-reduce:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memuat...
                                </span>
                            </flux:button>

                            @if ($search || $date !== now()->toDateString())
                                <a 
                                    href="{{ url('/laporan/audit') }}" 
                                    class="inline-flex items-center justify-center rounded-xl bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 transition-colors"
                                    title="Reset filter ke hari ini"
                                >
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Quick Presets --}}
                <div class="mt-4 pt-3.5 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-zinc-500 font-medium mr-1">Preset Cepat:</span>
                    @if ($date === now()->toDateString())
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-cyan-700 text-white dark:bg-cyan-600 dark:text-white shadow-2xs cursor-pointer" @click="setPreset(0)">
                            Hari Ini
                        </button>
                    @else
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 cursor-pointer" @click="setPreset(0)">
                            Hari Ini
                        </button>
                    @endif

                    @if ($date === now()->subDay()->toDateString())
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-cyan-700 text-white dark:bg-cyan-600 dark:text-white shadow-2xs cursor-pointer" @click="setPreset(1)">
                            Kemarin
                        </button>
                    @else
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 cursor-pointer" @click="setPreset(1)">
                            Kemarin
                        </button>
                    @endif

                    @if ($date === now()->subDays(2)->toDateString())
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-cyan-700 text-white dark:bg-cyan-600 dark:text-white shadow-2xs cursor-pointer" @click="setPreset(2)">
                            2 Hari Lalu
                        </button>
                    @else
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 cursor-pointer" @click="setPreset(2)">
                            2 Hari Lalu
                        </button>
                    @endif

                    @if ($date === now()->subDays(7)->toDateString())
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-cyan-700 text-white dark:bg-cyan-600 dark:text-white shadow-2xs cursor-pointer" @click="setPreset(7)">
                            7 Hari Lalu
                        </button>
                    @else
                        <button type="button" class="rounded-xl px-2.5 py-1 font-semibold bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 cursor-pointer" @click="setPreset(7)">
                            7 Hari Lalu
                        </button>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- Executive KPI Metrics (Delight Top Bar) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 animate-fade-in-up print:grid-cols-3 print:gap-3" style="animation-delay: 150ms;">
            {{-- Total Aktivitas --}}
            <flux:card class="admin-stat-total admin-card-elevated rounded-3xl p-5 relative overflow-hidden print:shadow-none print:border print:border-zinc-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Total Log Aktivitas</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-sky-200/70 text-sky-700 dark:bg-sky-900/60 dark:text-sky-300 print:hidden">
                        <flux:icon.clock class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($activities->total()) }}</span>
                    <span class="text-xs font-medium text-sky-800 dark:text-sky-300">aktivitas tercatat</span>
                </div>
            </flux:card>

            {{-- Tiket Aktif Terlibat --}}
            @php
                $uniqueTickets = $activities->pluck('queue_ticket_id')->filter()->unique()->count();
                $uniqueActors = $activities->pluck('user_id')->filter()->unique()->count();
            @endphp
            <flux:card class="admin-stat-success admin-card-elevated rounded-3xl p-5 relative overflow-hidden print:shadow-none print:border print:border-zinc-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Tiket Terlibat</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-emerald-200/70 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300 print:hidden">
                        <flux:icon.ticket class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($uniqueTickets) }}</span>
                    <span class="text-xs font-medium text-emerald-800 dark:text-emerald-300">tiket berproses (halaman ini)</span>
                </div>
            </flux:card>

            {{-- Petugas Aktif --}}
            <flux:card class="admin-stat-info admin-card-elevated rounded-3xl p-5 relative overflow-hidden print:shadow-none print:border print:border-zinc-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-violet-800 dark:text-violet-300">Petugas Terlibat</span>
                    <div class="admin-icon-box size-8 rounded-xl bg-violet-200/70 text-violet-700 dark:bg-violet-900/60 dark:text-violet-300 print:hidden">
                        <flux:icon.user-group class="size-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ number_format($uniqueActors) }}</span>
                    <span class="text-xs font-medium text-violet-800 dark:text-violet-300">petugas aktif (halaman ini)</span>
                </div>
            </flux:card>
        </div>

        {{-- Activity Timeline Table Card --}}
        <div class="animate-fade-in-up" style="animation-delay: 225ms;">
            <flux:card class="admin-card-elevated rounded-3xl border border-zinc-200 bg-white p-5 sm:p-7 dark:border-zinc-800 dark:bg-zinc-900 print:shadow-none print:border-zinc-300">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800 print:border-zinc-300">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300 print:hidden">
                            <flux:icon.list-bullet class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold">Log Rincian Aktivitas</flux:heading>
                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">Urutan kronologis aksi dari yang paling mutakhir.</flux:text>
                        </div>
                    </div>
                    <div class="text-xs text-zinc-500 font-mono">
                        Menampilkan {{ $activities->firstItem() ?? 0 }} - {{ $activities->lastItem() ?? 0 }} dari {{ $activities->total() }} log
                    </div>
                </div>

                @if ($activities->count() > 0)
                    <div class="admin-table-scroll mt-4 overflow-x-auto">
                        <flux:table aria-label="Tabel log audit trail aktivitas antrian">
                            <flux:table.columns class="bg-zinc-50/50 dark:bg-zinc-800/40 print:bg-transparent print:table-header-group">
                                <flux:table.column class="text-xs font-bold uppercase tracking-wider">Waktu</flux:table.column>
                                <flux:table.column class="text-xs font-bold uppercase tracking-wider">Aksi</flux:table.column>
                                <flux:table.column class="text-xs font-bold uppercase tracking-wider">Nomor Tiket</flux:table.column>
                                <flux:table.column class="text-xs font-bold uppercase tracking-wider">Layanan / Loket</flux:table.column>
                                <flux:table.column class="text-xs font-bold uppercase tracking-wider">Pelaku (Aktor)</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows class="admin-row-stagger">
                                @foreach ($activities as $activity)
                                    @php
                                        $actionColor = match($activity->action) {
                                            'created' => 'blue',
                                            'called' => 'amber',
                                            'completed' => 'emerald',
                                            'skipped' => 'zinc',
                                            'cancelled' => 'red',
                                            'recalled' => 'cyan',
                                            'transferred' => 'violet',
                                            default => 'slate',
                                        };
                                        $actionLabel = match($activity->action) {
                                            'created' => 'Dibuat',
                                            'called' => 'Dipanggil',
                                            'completed' => 'Selesai',
                                            'skipped' => 'Dilewati',
                                            'cancelled' => 'Dibatalkan',
                                            'recalled' => 'Panggil Ulang',
                                            'transferred' => 'Ditransfer',
                                            default => Str::title(str_replace('_', ' ', $activity->action)),
                                        };
                                    @endphp
                                    <flux:table.row class="admin-row-enter transition-colors hover:bg-zinc-50/60 dark:hover:bg-zinc-800/60" style="--stagger-i: {{ $loop->index }}">
                                        {{-- Waktu --}}
                                        <flux:table.cell class="whitespace-nowrap">
                                            <div class="font-mono text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                                {{ $activity->created_at->format('H:i:s') }}
                                            </div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </div>
                                        </flux:table.cell>

                                        {{-- Aksi & Meta --}}
                                        <flux:table.cell class="whitespace-nowrap">
                                            <flux:badge size="sm" :color="$actionColor" class="font-bold">
                                                {{ $actionLabel }}
                                            </flux:badge>
                                            @if (!empty($activity->meta))
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach ($activity->meta as $metaKey => $metaVal)
                                                        @if (is_scalar($metaVal))
                                                            <span class="inline-flex rounded-md bg-zinc-100 px-1.5 py-0.5 text-xs font-mono text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                                {{ $metaKey }}: {{ $metaVal }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </flux:table.cell>

                                        {{-- Nomor Tiket --}}
                                        <flux:table.cell class="whitespace-nowrap">
                                            @if ($activity->queueTicket)
                                                <div class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-100 px-2.5 py-1 text-xs font-bold font-mono text-zinc-900 dark:bg-zinc-800 dark:text-white print:border print:border-zinc-300">
                                                    <flux:icon.ticket class="size-3.5 text-zinc-500 print:hidden" />
                                                    <span>{{ $activity->queueTicket->ticket_number }}</span>
                                                </div>
                                            @else
                                                <span class="text-zinc-400 font-mono">-</span>
                                            @endif
                                        </flux:table.cell>

                                        {{-- Layanan / Loket --}}
                                        <flux:table.cell>
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-100 whitespace-nowrap text-sm">
                                                {{ $activity->queueTicket?->service?->name ?? '-' }}
                                            </div>
                                            <div class="flex items-center gap-1 text-xs text-zinc-500 mt-0.5 whitespace-nowrap">
                                                <flux:icon.building-office class="size-3 text-zinc-400 print:hidden" />
                                                <span>{{ $activity->counter?->name ?? 'Kiosk / Sistem Mandiri' }}</span>
                                            </div>
                                        </flux:table.cell>

                                        {{-- Pelaku --}}
                                        <flux:table.cell class="whitespace-nowrap">
                                            @if ($activity->user)
                                                <div class="flex items-center gap-2">
                                                    <div class="flex size-7 items-center justify-center rounded-lg bg-cyan-100 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300 font-bold text-xs print:border print:border-zinc-300">
                                                        {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100 text-xs">
                                                            {{ $activity->user->name }}
                                                        </div>
                                                        <div class="text-xs text-zinc-500">
                                                            {{ is_string($activity->user->role) ? Str::title($activity->user->role) : ($activity->user->role?->label() ?? 'Petugas') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-1.5 text-zinc-500 text-xs">
                                                    <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 print:border print:border-zinc-300">
                                                        <flux:icon.cpu-chip class="size-3.5" />
                                                    </div>
                                                    <span>Sistem / Pengunjung</span>
                                                </div>
                                            @endif
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>

                    @if ($activities->hasPages())
                        <div class="mt-6 border-t border-zinc-100 pt-4 dark:border-zinc-800 print:hidden">
                            {{ $activities->links() }}
                        </div>
                    @endif
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="admin-empty-icon flex size-14 items-center justify-center rounded-3xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <flux:icon.inbox class="size-7" />
                        </div>
                        <p class="mt-4 text-base font-bold text-zinc-900 dark:text-zinc-100">Tidak ada log aktivitas</p>
                        <p class="mt-1 text-xs text-zinc-500 max-w-sm">
                            Belum ada catatan transaksi aktivitas pada tanggal {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                            @if ($search) dengan kata kunci "{{ $search }}" @endif.
                        </p>
                        @if ($search || $date !== now()->toDateString())
                            <a 
                                href="{{ url('/laporan/audit') }}" 
                                class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-cyan-700 px-4 py-2 text-xs font-bold text-white shadow-md shadow-cyan-700/20 hover:bg-cyan-600 transition-all dark:bg-cyan-700 dark:hover:bg-cyan-600 cursor-pointer"
                            >
                                <flux:icon.arrow-path class="size-3.5" />
                                Lihat Hari Ini
                            </a>
                        @endif
                    </div>
                @endif
            </flux:card>
        </div>
    </div>
</x-layouts::app>


