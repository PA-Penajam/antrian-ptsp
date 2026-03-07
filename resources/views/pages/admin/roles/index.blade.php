<x-layouts::app :title="__('Manajemen Role')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div>
            <flux:heading size="xl" level="1">Manajemen Role</flux:heading>
            <flux:subheading>Ringkasan distribusi role user internal.</flux:subheading>
        </div>

        <flux:card class="space-y-4">
            <flux:heading size="lg">Distribusi Role</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Role</flux:table.column>
                    <flux:table.column>Total User</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($roleCounts as $role => $total)
                        <flux:table.row>
                            <flux:table.cell class="font-medium text-zinc-900 dark:text-white">{{ str($role)->headline() }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm">{{ $total }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts::app>
