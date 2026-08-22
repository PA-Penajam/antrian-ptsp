<x-layouts::app :title="__('Laporan Antrian')">
    <div class="w-full space-y-6">
        <div>
            <flux:heading size="xl" level="1">Laporan Antrian</flux:heading>
            <flux:subheading class="mt-1">Periode: {{ $from }} s.d. {{ $to }}</flux:subheading>
        </div>

        {{-- Filter Tanggal --}}
        <flux:card class="p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                        <flux:icon.funnel class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="sm">Filter Periode</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Pilih rentang tanggal laporan</flux:text>
                    </div>
                </div>
                <form method="GET" action="{{ url('/laporan/antrian') }}" class="grid w-full grid-cols-1 gap-3 sm:w-auto sm:grid-cols-2 md:flex md:items-end">
                    <flux:field class="w-full">
                        <flux:label>Dari</flux:label>
                        <flux:input type="date" name="from" value="{{ $from }}" class="w-full" />
                    </flux:field>
                    <flux:field class="w-full">
                        <flux:label>Sampai</flux:label>
                        <flux:input type="date" name="to" value="{{ $to }}" class="w-full" />
                    </flux:field>
                    <flux:button type="submit" variant="primary" icon="funnel" class="w-full sm:w-auto">Filter</flux:button>
                </form>
            </div>
        </flux:card>

        {{-- Laporan Grid --}}
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
            {{-- By Service --}}
            <flux:card class="admin-card-elevated">
                <div class="flex items-center gap-3 mb-4">
                    <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <flux:icon.clipboard-document-list class="size-5" />
                    </div>
                    <flux:heading size="lg">Berdasarkan Layanan</flux:heading>
                </div>
                @if (count($report['by_service']) > 0)
                    <div class="mt-4 overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Layanan</flux:table.column>
                                <flux:table.column>Jumlah</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($report['by_service'] as $name => $count)
                                    <flux:table.row>
                                        <flux:table.cell class="font-medium whitespace-nowrap">{{ $name }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            <flux:badge size="sm">{{ $count }}</flux:badge>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @else
                    <flux:text class="mt-4 text-zinc-500">Tidak ada data.</flux:text>
                @endif
            </flux:card>

            {{-- By Counter --}}
            <flux:card class="admin-card-elevated">
                <div class="flex items-center gap-3 mb-4">
                    <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                        <flux:icon.building-office class="size-5" />
                    </div>
                    <flux:heading size="lg">Berdasarkan Loket</flux:heading>
                </div>
                @if (count($report['by_counter']) > 0)
                    <div class="mt-4 overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Loket</flux:table.column>
                                <flux:table.column>Jumlah</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($report['by_counter'] as $name => $count)
                                    <flux:table.row>
                                        <flux:table.cell class="font-medium whitespace-nowrap">{{ $name }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            <flux:badge size="sm">{{ $count }}</flux:badge>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @else
                    <flux:text class="mt-4 text-zinc-500">Tidak ada data.</flux:text>
                @endif
            </flux:card>

            {{-- By Officer --}}
            <flux:card class="admin-card-elevated">
                <div class="flex items-center gap-3 mb-4">
                    <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                        <flux:icon.user class="size-5" />
                    </div>
                    <flux:heading size="lg">Berdasarkan Petugas</flux:heading>
                </div>
                @if (count($report['by_officer']) > 0)
                    <div class="mt-4 overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Petugas</flux:table.column>
                                <flux:table.column>Jumlah</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($report['by_officer'] as $name => $count)
                                    <flux:table.row>
                                        <flux:table.cell class="font-medium whitespace-nowrap">{{ $name }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            <flux:badge size="sm">{{ $count }}</flux:badge>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @else
                    <flux:text class="mt-4 text-zinc-500">Tidak ada data.</flux:text>
                @endif
            </flux:card>

            {{-- By Status --}}
            <flux:card class="admin-card-elevated">
                <div class="flex items-center gap-3 mb-4">
                    <div class="admin-icon-box bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                        <flux:icon.signal class="size-5" />
                    </div>
                    <flux:heading size="lg">Berdasarkan Status</flux:heading>
                </div>
                @if (count($report['by_status']) > 0)
                    <div class="mt-4 overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column>Jumlah</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($report['by_status'] as $status => $count)
                                    <flux:table.row>
                                        <flux:table.cell class="whitespace-nowrap">
                                            <flux:badge size="sm" variant="pill">{{ $status }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            <flux:badge size="sm">{{ $count }}</flux:badge>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @else
                    <flux:text class="mt-4 text-zinc-500">Tidak ada data.</flux:text>
                @endif
            </flux:card>

            <flux:card class="admin-card-elevated lg:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <div class="admin-icon-box bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-900/50 dark:text-fuchsia-400">
                        <flux:icon.chart-bar class="size-5" />
                    </div>
                    <flux:heading size="lg">Distribusi Petugas x Layanan</flux:heading>
                </div>
                @if (count($report['officer_service_distribution'] ?? []) > 0)
                    <div class="mt-4 overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column class="whitespace-nowrap">Petugas</flux:table.column>
                                <flux:table.column>Distribusi Layanan</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach (($report['officer_service_distribution'] ?? []) as $officer => $services)
                                    <flux:table.row>
                                        <flux:table.cell class="font-medium whitespace-nowrap">{{ $officer }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($services as $service => $count)
                                                    <flux:badge size="sm">{{ $service }}: {{ $count }}</flux:badge>
                                                @endforeach
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @else
                    <flux:text class="mt-4 text-zinc-500">Tidak ada data.</flux:text>
                @endif
            </flux:card>
        </div>
    </div>
</x-layouts::app>
