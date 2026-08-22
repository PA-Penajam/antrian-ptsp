<x-layouts::app :title="__('Manajemen User')">
    <div class="w-full space-y-6" x-data="{ tab: '{{ old('_method') !== 'PUT' && $errors->any() ? 'create' : $tab }}', searchUser: '' }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:breadcrumbs class="mb-1">
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Users</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <flux:heading size="xl" level="1">Manajemen User</flux:heading>
                <flux:subheading>Kelola akun internal, pembagian role, dan izin layanan petugas.</flux:subheading>
            </div>
            
            <div class="flex items-center gap-2">
                <flux:modal.trigger name="create-user">
                    <flux:button variant="primary" icon="plus" class="w-full sm:w-auto">
                        Tambah User Baru
                    </flux:button>
                </flux:modal.trigger>
            </div>
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
        <div class="flex w-full overflow-x-auto p-1 gap-1 rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50 max-w-fit">
            <button
                type="button"
                x-on:click="tab = 'list'"
                :class="tab === 'list' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all whitespace-nowrap"
            >
                <flux:icon.users class="size-4" />
                Semua Users
            </button>
            <button
                type="button"
                x-on:click="tab = 'roles'"
                :class="tab === 'roles' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all whitespace-nowrap"
            >
                <flux:icon.shield-check class="size-4" />
                Role & Izin
            </button>
        </div>


        {{-- Tab 1: Semua Users --}}
        <div x-show="tab === 'list'" x-cloak>
            <flux:card class="space-y-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                            <flux:icon.users class="size-5" />
                        </div>
                        <flux:heading size="lg">Daftar User</flux:heading>
                    </div>
                    <flux:input
                        x-model="searchUser"
                        placeholder="Cari nama, email, atau role..."
                        icon="magnifying-glass"
                        clearable
                        class="w-full sm:w-64"
                    />
                </div>

                @php
                    $otherUsers = $users->filter(fn ($user) => $user->id !== auth()->id());
                @endphp

                @if ($otherUsers->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <flux:icon name="users" class="h-12 w-12 text-zinc-400" />
                        <flux:heading size="md" class="mt-4">Belum ada user selain Anda</flux:heading>
                        <flux:text class="mt-2 text-zinc-500">
                            Tambahkan user baru melalui tombol di atas untuk mengelola tim Anda.
                        </flux:text>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Nama</flux:table.column>
                                <flux:table.column>Email</flux:table.column>
                                <flux:table.column>Role</flux:table.column>
                                <flux:table.column>Aksi</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($otherUsers as $user)
                                    <flux:table.row x-show="!searchUser || '{{ strtolower(addslashes($user->name . ' ' . $user->email . ' ' . $user->role->label())) }}'.includes(searchUser.toLowerCase())">
                                        <flux:table.cell class="font-medium whitespace-nowrap">{{ $user->name }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">{{ $user->email }}</flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" color="{{ $user->role->color() }}">
                                                {{ $user->role->label() }}
                                            </flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                                <flux:modal.trigger name="edit-user-{{ $user->id }}">
                                                    <flux:button size="sm" variant="filled" icon="pencil">Edit</flux:button>
                                                </flux:modal.trigger>
                                                <flux:modal.trigger name="delete-user-{{ $user->id }}">
                                                    <flux:button size="sm" variant="danger" icon="trash">
                                                        Hapus
                                                    </flux:button>
                                                </flux:modal.trigger>
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Tab 2: Role & Izin --}}
        <div x-show="tab === 'roles'" x-cloak>
            <flux:card class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <flux:icon.shield-check class="size-5" />
                    </div>
                    <flux:heading size="lg">Role & Izin Layanan</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Nama</flux:table.column>
                            <flux:table.column>Email</flux:table.column>
                            <flux:table.column>Role & Aksi</flux:table.column>
                            <flux:table.column>Layanan</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                        @foreach ($users as $user)
                            <flux:table.row>
                                <flux:table.cell>{{ $user->name }}</flux:table.cell>
                                <flux:table.cell>{{ $user->email }}</flux:table.cell>
                                <flux:table.cell>
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid min-w-0 gap-3 xl:grid-cols-[minmax(0,10rem)_auto] xl:items-start" x-data="{ role: '{{ $user->role?->value }}' }">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" value="{{ $user->name }}">
                                        <input type="hidden" name="email" value="{{ $user->email }}">

                                        <flux:select name="role" size="sm" x-model="role">
                                            <flux:select.option value="admin" :selected="$user->role?->value === 'admin'">Admin</flux:select.option>
                                            <flux:select.option value="frontdesk" :selected="$user->role?->value === 'frontdesk'">Frontdesk</flux:select.option>
                                            <flux:select.option value="officer" :selected="$user->role?->value === 'officer'">Officer</flux:select.option>
                                            <flux:select.option value="monitor" :selected="$user->role?->value === 'monitor'">Monitor</flux:select.option>
                                        </flux:select>

                                        <flux:button type="submit" variant="filled" size="sm" class="xl:self-start">Update</flux:button>

                                        <div class="xl:col-span-2" x-show="role === 'officer'" x-cloak>
                                            <flux:select
                                                name="service_id"
                                                size="sm"
                                                placeholder="Pilih layanan..."
                                            >
                                                <flux:select.option value="">Pilih layanan...</flux:select.option>
                                                @foreach ($services as $service)
                                                    <flux:select.option
                                                        value="{{ $service->id }}"
                                                        :selected="$user->services->first()?->id == $service->id"
                                                    >
                                                        {{ $service->name }}
                                                    </flux:select.option>
                                                @endforeach
                                            </flux:select>
                                        </div>
                                    </form>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($user->services->isNotEmpty())
                                        <flux:badge size="sm">{{ $user->services->first()->name }}</flux:badge>
                                    @else
                                        <flux:text class="text-zinc-500">-</flux:text>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- Create User Modal --}}
        <flux:modal name="create-user" class="w-full max-w-lg">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4" x-data="{ role: '{{ old('role', 'admin') }}' }">
                @csrf
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
                        <flux:icon.user-plus class="size-5" />
                    </div>
                    <flux:heading size="lg">Tambah User Baru</flux:heading>
                </div>

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
                    <flux:select name="role" x-model="role">
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

                <flux:field x-show="role === 'officer'" x-cloak>
                    <flux:label>Layanan / Lokasi</flux:label>
                    <flux:select
                        name="service_id"
                        placeholder="Pilih layanan tempat petugas berdiri"
                    >
                        <flux:select.option value="">Pilih layanan...</flux:select.option>
                        @foreach ($services as $service)
                            <flux:select.option value="{{ $service->id }}">
                                {{ $service->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="service_id" />
                </flux:field>

                <div class="flex justify-end gap-2 pt-4">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Simpan User</flux:button>
                </div>
            </form>
        </flux:modal>

        {{-- Per-User Edit Modals --}}
        @foreach ($users as $user)
            <flux:modal name="edit-user-{{ $user->id }}" class="w-full max-w-lg">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4" x-data="{ role: '{{ $user->role?->value }}' }">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                            <flux:icon.pencil-square class="size-5" />
                        </div>
                        <flux:heading size="lg">Edit User: {{ $user->name }}</flux:heading>
                    </div>

                    <flux:field>
                        <flux:label>Nama</flux:label>
                        <flux:input name="name" value="{{ $user->name }}" required />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" name="email" value="{{ $user->email }}" required />
                    </flux:field>

                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:select name="role" x-model="role">
                            <flux:select.option value="admin" :selected="$user->role?->value === 'admin'">Admin</flux:select.option>
                            <flux:select.option value="frontdesk" :selected="$user->role?->value === 'frontdesk'">Frontdesk</flux:select.option>
                            <flux:select.option value="officer" :selected="$user->role?->value === 'officer'">Officer</flux:select.option>
                            <flux:select.option value="monitor" :selected="$user->role?->value === 'monitor'">Monitor</flux:select.option>
                        </flux:select>
                    </flux:field>

                    <flux:field x-show="role === 'officer'" x-cloak>
                        <flux:label>Layanan / Lokasi</flux:label>
                        <flux:select
                            name="service_id"
                            placeholder="Pilih layanan tempat petugas berdiri"
                        >
                            <flux:select.option value="">Pilih layanan...</flux:select.option>
                            @foreach ($services as $service)
                                <flux:select.option
                                    value="{{ $service->id }}"
                                    :selected="$user->services->first()?->id == $service->id"
                                >
                                    {{ $service->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <div class="flex justify-end gap-2 pt-4">
                        <flux:modal.close>
                            <flux:button type="button" variant="ghost">Batal</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endforeach

        {{-- Delete User Confirmation Modals --}}
        @foreach ($otherUsers as $user)
            <flux:modal name="delete-user-{{ $user->id }}" class="w-full max-w-md">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                            <flux:icon.trash class="size-5" />
                        </div>
                        <flux:heading size="lg">Hapus User</flux:heading>
                    </div>

                    <flux:callout icon="exclamation-circle" color="red">
                        Apakah Anda yakin ingin menghapus user <strong>{{ $user->name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                    </flux:callout>

                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="flex justify-end gap-2 pt-2">
                        @csrf
                        @method('DELETE')
                        <flux:modal.close>
                            <flux:button type="button" variant="ghost">Batal</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="danger" icon="trash">Hapus</flux:button>
                    </form>
                </div>
            </flux:modal>
        @endforeach
    </div>
</x-layouts::app>
