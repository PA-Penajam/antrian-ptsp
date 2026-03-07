<x-layouts::app :title="__('Laporan Antrian')">
    <flux:main container>
        <div class="max-w-5xl mx-auto space-y-6">
            <div>
                <flux:heading size="xl" level="1">Laporan Antrian</flux:heading>
                <flux:subheading>Periode: {{ $from }} s.d. {{ $to }}</flux:subheading>
            </div>

            {{-- Filter Tanggal --}}
            <flux:card>
                <flux:heading size="lg">Filter Periode</flux:heading>
                <form method="GET" action="{{ url('/laporan/antrian') }}" class="mt-4 flex flex-wrap items-end gap-4">
                    <flux:field>
                        <flux:label>Dari Tanggal</flux:label>
                        <flux:input type="date" name="from" value="{{ $from }}" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Sampai Tanggal</flux:label>
                        <flux:input type="date" name="to" value="{{ $to }}" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" icon="funnel">Filter</flux:button>
                </form>
            </flux:card>

            {{-- Laporan Grid --}}
            <div class="grid gap-6 sm:grid-cols-2">
                {{-- By Service --}}
                <flux:card>
                    <flux:heading size="lg">Berdasarkan Layanan</flux:heading>
                    @if (count($report['by_service']) > 0)
                        <div class="mt-4">
                            <flux:table>
                                <flux:table.columns>
                                        <flux:table.column>Layanan</flux:table.column>
                                        <flux:table.column>Jumlah</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($report['by_service'] as $name => $count)
                                        <flux:table.row>
                                            <flux:table.cell>{{ $name }}</flux:table.cell>
                                            <flux:table.cell>
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
                <flux:card>
                    <flux:heading size="lg">Berdasarkan Loket</flux:heading>
                    @if (count($report['by_counter']) > 0)
                        <div class="mt-4">
                            <flux:table>
                                <flux:table.columns>
                                        <flux:table.column>Loket</flux:table.column>
                                        <flux:table.column>Jumlah</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($report['by_counter'] as $name => $count)
                                        <flux:table.row>
                                            <flux:table.cell>{{ $name }}</flux:table.cell>
                                            <flux:table.cell>
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
                <flux:card>
                    <flux:heading size="lg">Berdasarkan Petugas</flux:heading>
                    @if (count($report['by_officer']) > 0)
                        <div class="mt-4">
                            <flux:table>
                                <flux:table.columns>
                                        <flux:table.column>Petugas</flux:table.column>
                                        <flux:table.column>Jumlah</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($report['by_officer'] as $name => $count)
                                        <flux:table.row>
                                            <flux:table.cell>{{ $name }}</flux:table.cell>
                                            <flux:table.cell>
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
                <flux:card>
                    <flux:heading size="lg">Berdasarkan Status</flux:heading>
                    @if (count($report['by_status']) > 0)
                        <div class="mt-4">
                            <flux:table>
                                <flux:table.columns>
                                        <flux:table.column>Status</flux:table.column>
                                        <flux:table.column>Jumlah</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($report['by_status'] as $status => $count)
                                        <flux:table.row>
                                            <flux:table.cell>
                                                <flux:badge size="sm" variant="pill">{{ $status }}</flux:badge>
                                            </flux:table.cell>
                                            <flux:table.cell>
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

                <flux:card class="sm:col-span-2">
                    <flux:heading size="lg">Distribusi Petugas x Layanan</flux:heading>
                    @if (count($report['officer_service_distribution'] ?? []) > 0)
                        <div class="mt-4">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Petugas</flux:table.column>
                                    <flux:table.column>Distribusi Layanan</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach (($report['officer_service_distribution'] ?? []) as $officer => $services)
                                        <flux:table.row>
                                            <flux:table.cell>{{ $officer }}</flux:table.cell>
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
    </flux:main>
</x-layouts::app>
