<x-layouts::app :title="__('Manajemen Loket')">
    <flux:main container>
        <div class="mx-auto max-w-6xl space-y-6">
            <div>
                <flux:heading size="xl" level="1">Manajemen Loket</flux:heading>
                <flux:subheading>Perbarui mapping loket terhadap pool dan status aktif.</flux:subheading>
            </div>

            @if (session('status'))
                <flux:callout icon="check-circle" color="green">
                    {{ session('status') }}
                </flux:callout>
            @endif

            <flux:card class="space-y-4">
                <flux:heading size="lg">Perbarui Loket</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Loket</flux:table.column>
                        <flux:table.column>Pool</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Aksi</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($counters as $counter)
                            <flux:table.row>
                                <flux:table.cell>{{ $counter->name }} ({{ $counter->code }})</flux:table.cell>
                                <flux:table.cell>{{ $counter->queuePool?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    @if ($counter->is_active)
                                        <flux:badge color="green">Aktif</flux:badge>
                                    @else
                                        <flux:badge color="red">Nonaktif</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <form method="POST" action="/admin/loket/{{ $counter->id }}" class="flex flex-wrap gap-2">
                                        @csrf
                                        @method('PUT')

                                        <select name="queue_pool_id" class="rounded border border-zinc-300 px-2 py-1 text-sm">
                                            @foreach ($queuePools as $pool)
                                                <option value="{{ $pool->id }}" @selected($counter->queue_pool_id === $pool->id)>
                                                    {{ $pool->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <input type="hidden" name="name" value="{{ $counter->name }}">
                                        <input type="hidden" name="code" value="{{ $counter->code }}">
                                        <input type="hidden" name="sort_order" value="{{ $counter->sort_order }}">
                                        <input type="hidden" name="is_active" value="{{ $counter->is_active ? 0 : 1 }}">

                                        <flux:button type="submit" variant="filled" size="sm">Simpan</flux:button>
                                    </form>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>
