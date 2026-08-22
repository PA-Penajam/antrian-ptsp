<x-layouts::app :title="__('Manajemen Role')">
    <div class="w-full space-y-6">
        <div class="space-y-1">
            <flux:breadcrumbs class="mb-1">
                <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                <flux:breadcrumbs.item :href="route('admin.users.index')">Users</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Roles</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl" level="1">Manajemen Role</flux:heading>
            <flux:subheading>Ringkasan distribusi role user internal.</flux:subheading>
        </div>

        <flux:card class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                    <flux:icon.shield-check class="size-5" />
                </div>
                <flux:heading size="lg">Distribusi Role</flux:heading>
            </div>
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Role</flux:table.column>
                        <flux:table.column>Total User</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($roleCounts as $role => $total)
                            <flux:table.row>
                                <flux:table.cell class="font-medium text-zinc-900 dark:text-white whitespace-nowrap">{{ str($role)->headline() }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm">{{ $total }}</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    </div>
</x-layouts::app>
