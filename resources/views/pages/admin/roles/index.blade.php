<x-layouts::app :title="__('Role Management')">
    <flux:main container>
        <div class="mx-auto max-w-4xl space-y-6">
            <div>
                <flux:heading size="xl" level="1">Role Management</flux:heading>
                <flux:subheading>Ringkasan distribusi role user internal.</flux:subheading>
            </div>

            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Role</flux:table.column>
                        <flux:table.column>Total User</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($roleCounts as $role => $total)
                            <flux:table.row>
                                <flux:table.cell>{{ $role }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge>{{ $total }}</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::app>
