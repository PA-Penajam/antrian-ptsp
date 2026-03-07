<x-layouts::app :title="__('Manajemen User')">
    <flux:main container>
        <div class="mx-auto max-w-6xl space-y-6">
            <div>
                <flux:heading size="xl" level="1">Manajemen User</flux:heading>
                <flux:subheading>Kelola role dan izin layanan setiap user internal.</flux:subheading>
            </div>

            @if (session('status'))
                <flux:callout icon="check-circle" color="green">
                    {{ session('status') }}
                </flux:callout>
            @endif

            <flux:card class="space-y-4">
                <flux:heading size="lg">Tambah User</flux:heading>
                <form method="POST" action="/admin/users" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <flux:field>
                        <flux:label>Nama</flux:label>
                        <flux:input name="name" value="{{ old('name') }}" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" name="email" value="{{ old('email') }}" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:select name="role">
                            <option value="admin">Admin</option>
                            <option value="frontdesk">Frontdesk</option>
                            <option value="officer">Officer</option>
                            <option value="monitor">Monitor</option>
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Password</flux:label>
                        <flux:input type="password" name="password" />
                    </flux:field>
                    <flux:field class="md:col-span-2">
                        <flux:label>Izin Layanan</flux:label>
                        <select name="services[]" multiple class="w-full rounded border border-zinc-300 px-3 py-2 text-sm">
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </flux:field>
                    <flux:button type="submit" variant="primary" class="md:col-span-2">Simpan User</flux:button>
                </form>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">Daftar User</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nama</flux:table.column>
                        <flux:table.column>Email</flux:table.column>
                        <flux:table.column>Role</flux:table.column>
                        <flux:table.column>Izin Layanan</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($users as $user)
                            <flux:table.row>
                                <flux:table.cell>{{ $user->name }}</flux:table.cell>
                                <flux:table.cell>{{ $user->email }}</flux:table.cell>
                                <flux:table.cell>
                                    <form method="POST" action="/admin/users/{{ $user->id }}" class="flex flex-wrap gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" value="{{ $user->name }}">
                                        <input type="hidden" name="email" value="{{ $user->email }}">
                                        <select name="role" class="rounded border border-zinc-300 px-2 py-1 text-sm">
                                            <option value="admin" @selected($user->role?->value === 'admin')>admin</option>
                                            <option value="frontdesk" @selected($user->role?->value === 'frontdesk')>frontdesk</option>
                                            <option value="officer" @selected($user->role?->value === 'officer')>officer</option>
                                            <option value="monitor" @selected($user->role?->value === 'monitor')>monitor</option>
                                        </select>

                                        <select name="services[]" multiple class="rounded border border-zinc-300 px-2 py-1 text-sm">
                                            @foreach ($services as $service)
                                                <option value="{{ $service->id }}" @selected($user->services->contains('id', $service->id))>
                                                    {{ $service->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <flux:button type="submit" variant="filled" size="sm">Update</flux:button>
                                    </form>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->services as $service)
                                            <flux:badge size="sm">{{ $service->name }}</flux:badge>
                                        @empty
                                            <flux:text class="text-zinc-500">-</flux:text>
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
