<x-layouts::app :title="__('Izin Layanan')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div>
            <flux:heading size="xl" level="1">Izin Layanan</flux:heading>
            <flux:subheading>Observasi mapping layanan yang diizinkan per user.</flux:subheading>
        </div>

        <flux:card class="space-y-4">
            <flux:heading size="lg">Matriks Izin User</flux:heading>
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
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ str($user->role?->value ?? '-')->headline() }}</flux:badge>
                            </flux:table.cell>
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
</x-layouts::app>
