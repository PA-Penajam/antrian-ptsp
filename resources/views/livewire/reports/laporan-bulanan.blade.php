<div class="space-y-6">
    {{-- Header --}}
    <div class="space-y-3">
        <flux:badge color="blue" rounded>Laporan Bulanan</flux:badge>
        <div>
            <flux:heading size="xl" level="1">Laporan Bulanan</flux:heading>
            <flux:subheading class="mt-1">
                {{ Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM YYYY') }}
            </flux:subheading>
        </div>
    </div>

    {{-- Filter Bulan & Tahun --}}
    <flux:card class="p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                    <flux:icon.funnel class="size-5" />
                </div>
                <div>
                    <flux:heading size="sm">Filter Periode</flux:heading>
                    <flux:text class="text-xs text-zinc-500">Pilih bulan dan tahun laporan</flux:text>
                </div>
            </div>
            <div class="flex items-end gap-3">
                <flux:field>
                    <flux:label>Bulan</flux:label>
                    <flux:select wire:model.live="bulan">
                        @foreach (range(1, 12) as $m)
                            <option wire:key="bulan-{{ $m }}" value="{{ $m }}">
                                {{ Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                            </option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Tahun</flux:label>
                    <flux:select wire:model.live="tahun">
                        @foreach (range(today()->year, today()->subYears(5)->year) as $th)
                            <option wire:key="tahun-{{ $th }}" value="{{ $th }}">{{ $th }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
                @if ($report['ringkasan']['total'] > 0)
                    <flux:button icon="document-text" variant="primary" wire:click="downloadExcel">
                        Excel
                    </flux:button>
                    <flux:button icon="printer" variant="outline" wire:click="downloadPdf">
                        PDF
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:card>

    @if ($report['ringkasan']['total'] > 0)
        {{-- Ringkasan Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <flux:card class="p-5">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <flux:text class="text-xs font-semibold tracking-[0.16em] text-sky-700 uppercase dark:text-sky-300">
                            Total Pendaftar
                        </flux:text>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white">
                            {{ $report['ringkasan']['total'] }}
                        </p>
                    </div>
                    <div class="admin-icon-box bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                        <flux:icon.ticket class="size-5" />
                    </div>
                </div>
            </flux:card>

            <flux:card class="p-5">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <flux:text class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase dark:text-emerald-300">
                            Selesai
                        </flux:text>
                        <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-400">
                            {{ $report['ringkasan']['completed'] }}
                        </p>
                    </div>
                    <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <flux:icon.check-circle class="size-5" />
                    </div>
                </div>
            </flux:card>

            <flux:card class="p-5">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <flux:text class="text-xs font-semibold tracking-[0.16em] text-amber-700 uppercase dark:text-amber-300">
                            Menunggu
                        </flux:text>
                        <p class="text-3xl font-bold text-amber-700 dark:text-amber-400">
                            {{ $report['ringkasan']['waiting'] }}
                        </p>
                    </div>
                    <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                        <flux:icon.clock class="size-5" />
                    </div>
                </div>
            </flux:card>

            <flux:card class="p-5">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <flux:text class="text-xs font-semibold tracking-[0.16em] text-red-700 uppercase dark:text-red-300">
                            Dibatalkan
                        </flux:text>
                        <p class="text-3xl font-bold text-red-700 dark:text-red-400">
                            {{ $report['ringkasan']['cancelled'] }}
                        </p>
                    </div>
                    <div class="admin-icon-box bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                        <flux:icon.x-circle class="size-5" />
                    </div>
                </div>
            </flux:card>
        </div>

        {{-- Per Layanan --}}
        <flux:card class="p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                    <flux:icon.clipboard-document-list class="size-5" />
                </div>
                <flux:heading size="lg">Per Layanan</flux:heading>
            </div>

            @if (count($report['per_layanan']) > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Layanan</flux:table.column>
                        <flux:table.column>Total</flux:table.column>
                        <flux:table.column>Selesai</flux:table.column>
                        <flux:table.column>Dibatalkan</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($report['per_layanan'] as $item)
                            <flux:table.row wire:key="layanan-{{ $item['id'] }}">
                                <flux:table.cell>{{ $item['name'] }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm">{{ $item['total'] }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="green">{{ $item['completed'] }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="red">{{ $item['cancelled'] }}</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <flux:text class="text-zinc-500">Tidak ada data layanan.</flux:text>
            @endif
        </flux:card>

        {{-- Per Hari --}}
        <flux:card class="p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="admin-icon-box bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                    <flux:icon.calendar-days class="size-5" />
                </div>
                <flux:heading size="lg">Per Hari</flux:heading>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Tanggal</flux:table.column>
                    <flux:table.column>Hari</flux:table.column>
                    <flux:table.column>Total</flux:table.column>
                    <flux:table.column>Online</flux:table.column>
                    <flux:table.column>Kiosk</flux:table.column>
                    <flux:table.column>Langsung</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($report['per_hari'] as $item)
                        <flux:table.row wire:key="hari-{{ $item['date'] }}">
                            <flux:table.cell>{{ $item['date'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['nama_hari'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm">{{ $item['total'] }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $item['online'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['kiosk'] }}</flux:table.cell>
                            <flux:table.cell>{{ $item['assisted'] }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>

        {{-- Per Channel --}}
        <flux:card class="p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="admin-icon-box bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-900/50 dark:text-fuchsia-400">
                    <flux:icon.signal class="size-5" />
                </div>
                <flux:heading size="lg">Per Kanal</flux:heading>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Kanal</flux:table.column>
                    <flux:table.column>Total</flux:table.column>
                    <flux:table.column>Persentase</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($report['per_channel'] as $item)
                        <flux:table.row wire:key="channel-{{ $item['channel'] }}">
                            <flux:table.cell>
                                {{ $item['channel'] }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm">{{ $item['total'] }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $item['persen'] }}%</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @else
        {{-- Empty State --}}
        <flux:card class="p-12">
            <div class="flex flex-col items-center justify-center text-center space-y-4">
                <div class="admin-icon-box bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500 size-16">
                    <flux:icon.inbox class="size-8" />
                </div>
                <div>
                    <flux:heading size="lg">Tidak ada data pendaftar untuk bulan ini.</flux:heading>
                    <flux:text class="mt-2 text-zinc-500">
                        Silakan pilih bulan dan tahun lain untuk melihat laporan.
                    </flux:text>
                </div>
            </div>
        </flux:card>
    @endif
</div>
