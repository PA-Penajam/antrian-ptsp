<x-layouts::app :title="__('Izin Layanan')">
    <flux:main container>
        <div class="mx-auto max-w-5xl space-y-6">
            <div>
                <flux:heading size="xl" level="1">Izin Layanan</flux:heading>
                <flux:subheading>Observasi mapping layanan yang diizinkan per user.</flux:subheading>
            </div>

            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>User</flux:table.column>
                        <flux:table.column>Role</flux:table.column>
                        <flux:table.column>Layanan Diizinkan</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($users as $user)
                            <flux:table.row>
                                <flux:table.cell>{{ $user->name }}</flux:table.cell>
                                <flux:table.cell>{{ $user->role?->value ?? '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->services as $service)
                                            <flux:badge size="sm">{{ $service->name }}</flux:badge>
                                        @empty
                                            <flux:text class="text-zinc-500">Belum ada izin layanan</flux:text>
                                        @endforelse
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>
