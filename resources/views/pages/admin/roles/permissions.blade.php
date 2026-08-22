<x-layouts::app :title="__('Izin Layanan')">
    <div class="w-full space-y-6">
        <div class="space-y-1">
            <flux:breadcrumbs class="mb-1">
                <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                <flux:breadcrumbs.item :href="route('admin.users.index', ['tab' => 'roles'])">Role & Izin</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Matriks Izin</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl" level="1">Izin Layanan</flux:heading>
            <flux:subheading>Observasi mapping layanan yang diizinkan per user.</flux:subheading>
        </div>

        <flux:card class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                    <flux:icon.key class="size-5" />
                </div>
                <flux:heading size="lg">Matriks Izin User</flux:heading>
            </div>
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>User</flux:table.column>
                        <flux:table.column>Role</flux:table.column>
                        <flux:table.column>Layanan Diizinkan</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($users as $user)
                            <flux:table.row>
                                <flux:table.cell class="font-medium whitespace-nowrap">{{ $user->name }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
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
            </div>
        </flux:card>
    </div>
</x-layouts::app>
