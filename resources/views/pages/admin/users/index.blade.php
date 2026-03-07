<x-layouts::app :title="__('Manajemen User')">
    <div class="mx-auto w-full max-w-6xl space-y-6" x-data="{ tab: 'list', editUser: null }">
        <div>
            <flux:heading size="xl" level="1">Manajemen User</flux:heading>
            <flux:subheading>Kelola role dan izin layanan setiap user internal.</flux:subheading>
        </div>

        @if (session('status'))
            <flux:callout icon="check-circle" color="green">
                {{ session('status') }}
            </flux:callout>
        @endif

        @if (session('error'))
            <flux:callout icon="exclamation-circle" color="red">
                {{ session('error') }}
            </flux:callout>
        @endif

        {{-- Tabs Navigation --}}
        <flux:card>
            <div class="flex gap-1 border-b border-zinc-200 pb-1 dark:border-zinc-700">
                <button
                    type="button"
                    x-on:click="tab = 'list'"
                    :class="tab === 'list' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Semua Users
                </button>
                <button
                    type="button"
                    x-on:click="tab = 'roles'"
                    :class="tab === 'roles' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Role & Izin
                </button>
                <button
                    type="button"
                    x-on:click="tab = 'create'"
                    :class="tab === 'create' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800'"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                >
                    Tambah User
                </button>
            </div>
        </flux:card>


        {{-- Tab 1: Semua Users --}}
        <div x-show="tab === 'list'" x-cloak>
            <flux:card class="space-y-4">
                <flux:heading size="lg">Daftar User</flux:heading>

                @php
                    $otherUsers = $users->filter(fn ($user) => $user->id !== auth()->id());
                @endphp

                @if ($otherUsers->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <flux:icon name="users" class="h-12 w-12 text-zinc-400" />
                        <flux:heading size="md" class="mt-4">Belum ada user selain Anda</flux:heading>
                        <flux:text class="mt-2 text-zinc-500">
                            Tambahkan user baru melalui tab "Tambah User" untuk mengelola tim Anda.
                        </flux:text>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Nama</flux:table.column>
                            <flux:table.column>Email</flux:table.column>
                            <flux:table.column>Role</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($otherUsers as $user)
                                <flux:table.row>
                                    <flux:table.cell>{{ $user->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" color="{{ $user->role->color() }}">
                                            {{ $user->role->label() }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            <flux:button
                                                size="sm"
                                                variant="filled"
                                                icon="pencil"
                                                x-on:click="editUser = {{ json_encode([
                                                    'id' => $user->id,
                                                    'name' => $user->name,
                                                    'email' => $user->email,
                                                    'role' => $user->role->value,
                                                    'services' => $user->services->pluck('id')->toArray(),
                                                ]) }}"
                                            >
                                                Edit
                                            </flux:button>
                                            <form
                                                method="POST"
                                                action="{{ route('admin.users.destroy', $user) }}"
                                                class="inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" size="sm" variant="danger" icon="trash">
                                                    Hapus
                                                </flux:button>
                                            </form>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </flux:card>
        </div>

        {{-- Tab 2: Role & Izin --}}
        <div x-show="tab === 'roles'" x-cloak>
            <flux:card class="space-y-4">
                <flux:heading size="lg">Role & Izin Layanan</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nama</flux:table.column>
                        <flux:table.column>Email</flux:table.column>
                        <flux:table.column>Role & Aksi</flux:table.column>
                        <flux:table.column>Izin Layanan</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($users as $user)
                            <flux:table.row>
                                <flux:table.cell>{{ $user->name }}</flux:table.cell>
                                <flux:table.cell>{{ $user->email }}</flux:table.cell>
                                <flux:table.cell>
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid min-w-0 gap-3 xl:grid-cols-[minmax(0,10rem)_auto] xl:items-start">
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

        {{-- Tab 3: Tambah User --}}
        <div x-show="tab === 'create'" x-cloak>
            <flux:card class="space-y-4">
                <flux:heading size="lg">Tambah User</flux:heading>
                <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <flux:field>
                        <flux:label>Nama</flux:label>
                        <flux:input name="name" value="{{ old('name') }}" />
                        <flux:error name="name" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" name="email" value="{{ old('email') }}" />
                        <flux:error name="email" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:select name="role">
                            <flux:select.option value="admin">Admin</flux:select.option>
                            <flux:select.option value="frontdesk">Frontdesk</flux:select.option>
                            <flux:select.option value="officer">Officer</flux:select.option>
                            <flux:select.option value="monitor">Monitor</flux:select.option>
                        </flux:select>
                        <flux:error name="role" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Password</flux:label>
                        <flux:input type="password" name="password" />
                        <flux:error name="password" />
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
                        <flux:error name="services" />
                    </flux:field>
                    <flux:button type="submit" variant="primary" class="md:col-span-2">Simpan User</flux:button>
                </form>
            </flux:card>
        </div>

        {{-- Edit Modal --}}
        <template x-teleport="body">
            <div
                x-show="editUser !== null"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center"
                style="display: none;"
            >
                <div x-on:click="editUser = null" class="absolute inset-0 bg-black/50"></div>
                <div class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-800">
                    <flux:heading size="lg">Edit User</flux:heading>

                    <form x-bind:action="editUser ? '{{ route('admin.users.index') }}/' + editUser.id : '#'" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <flux:field>
                            <flux:label>Nama</flux:label>
                            <flux:input name="name" x-bind:value="editUser?.name" required />
                        </flux:field>

                        <flux:field>
                            <flux:label>Email</flux:label>
                            <flux:input type="email" name="email" x-bind:value="editUser?.email" required />
                        </flux:field>

                        <flux:field>
                            <flux:label>Role</flux:label>
                            <flux:select name="role" x-model="editUser?.role">
                                <flux:select.option value="admin">Admin</flux:select.option>
                                <flux:select.option value="frontdesk">Frontdesk</flux:select.option>
                                <flux:select.option value="officer">Officer</flux:select.option>
                                <flux:select.option value="monitor">Monitor</flux:select.option>
                            </flux:select>
                        </flux:field>

                        <div class="flex justify-end gap-2 pt-4">
                            <flux:button type="button" variant="ghost" x-on:click="editUser = null">Batal</flux:button>
                            <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-layouts::app>
