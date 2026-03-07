<x-layouts::app :title="__('Manajemen User')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
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
                        <flux:select.option value="admin">Admin</flux:select.option>
                        <flux:select.option value="frontdesk">Frontdesk</flux:select.option>
                        <flux:select.option value="officer">Officer</flux:select.option>
                        <flux:select.option value="monitor">Monitor</flux:select.option>
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Password</flux:label>
                    <flux:input type="password" name="password" />
                </flux:field>
                <flux:field class="md:col-span-2">
                    <flux:label>Izin Layanan</flux:label>
                    <flux:select
                        name="services[]"
                        variant="listbox"
                        multiple
                        searchable
                        selected-suffix="layanan"
                        placeholder="Pilih izin layanan"
                    >
                        @foreach ($services as $service)
                            <flux:select.option value="{{ $service->id }}">
                                {{ $service->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
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
                    <flux:table.column>Role &amp; Aksi</flux:table.column>
                    <flux:table.column>Izin Layanan</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($users as $user)
                        <flux:table.row>
                            <flux:table.cell>{{ $user->name }}</flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>
                                <form method="POST" action="/admin/users/{{ $user->id }}" class="grid min-w-0 gap-3 xl:grid-cols-[minmax(0,10rem)_auto] xl:items-start">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="email" value="{{ $user->email }}">

                                    <flux:select name="role" size="sm">
                                        <flux:select.option value="admin" :selected="$user->role?->value === 'admin'">Admin</flux:select.option>
                                        <flux:select.option value="frontdesk" :selected="$user->role?->value === 'frontdesk'">Frontdesk</flux:select.option>
                                        <flux:select.option value="officer" :selected="$user->role?->value === 'officer'">Officer</flux:select.option>
                                        <flux:select.option value="monitor" :selected="$user->role?->value === 'monitor'">Monitor</flux:select.option>
                                    </flux:select>

                                    <flux:button type="submit" variant="filled" size="sm" class="xl:self-start">Update</flux:button>

                                    <div class="xl:col-span-2">
                                        <flux:select
                                            name="services[]"
                                            variant="listbox"
                                            multiple
                                            searchable
                                            size="sm"
                                            selected-suffix="layanan"
                                            placeholder="Pilih izin layanan"
                                        >
                                            @foreach ($services as $service)
                                                <flux:select.option
                                                    value="{{ $service->id }}"
                                                    :selected="$user->services->contains('id', $service->id)"
                                                >
                                                    {{ $service->name }}
                                                </flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    </div>
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
</x-layouts::app>
