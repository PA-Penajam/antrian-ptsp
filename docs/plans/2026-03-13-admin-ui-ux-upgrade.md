# Admin UI/UX Upgrade to A+ Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Menaikkan kualitas UI/UX halaman admin dari grade B- ke A+ — setara dengan kualitas halaman publik yang sudah premium.

**Architecture:** Upgrade visual menggunakan Flux UI components yang sudah ada (card, badge, chart, table) dengan penambahan gradient backgrounds, icon decorators, stat cards yang lebih kaya, dan consistent color language. Tidak ada library baru — hanya memaksimalkan Flux UI + Tailwind CSS yang sudah terinstall. Semua perubahan di blade views dan CSS saja, tanpa perubahan backend/controller.

**Tech Stack:** Laravel 12, Livewire 3, Flux UI 2, Tailwind CSS 4, Alpine.js

---

## Prinsip Desain (Referensi dari Halaman Publik)

Halaman publik (welcome.blade.php, booking.blade.php) menggunakan pattern ini yang TIDAK ada di admin:

1. **Gradient backgrounds** pada card utama — bukan flat white
2. **Icon decorators** — rounded icon boxes (bg-cyan-100 text-cyan-700) di samping heading
3. **Badge labels** — flux:badge color di atas heading sebagai section label
4. **Visual hierarchy** — heading + subheading + description text berbeda opacity
5. **Card shadows** — shadow-[0_24px_60px_-48px_rgba(...)]
6. **Color-coded sections** — setiap section punya accent color berbeda
7. **Stat cards dengan gradient** — bukan hanya angka + label flat

Halaman admin saat ini:
- Semua `<flux:card>` polos tanpa gradient/shadow
- Stat cards hanya `<flux:heading size="sm">` + `<p class="text-3xl font-bold">`
- Tabel tanpa row hover/highlight
- Empty states inkonsisten
- Tidak ada icon decorator di heading
- Warna monoton (zinc everywhere)

---

## Phase 1: Design System Foundation

### Task 1: Tambah Admin Theme Variables di app.css

**Files:**
- Modify: `resources/css/app.css`

**Step 1: Tambah admin color utilities dan card styles**

```css
/* Tambah di akhir file resources/css/app.css */

/* === Admin Panel Design System === */

@layer utilities {
    /* Stat card gradients */
    .admin-stat-total {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-color: #bae6fd;
    }
    .admin-stat-success {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-color: #bbf7d0;
    }
    .admin-stat-warning {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-color: #fde68a;
    }
    .admin-stat-info {
        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
        border-color: #e9d5ff;
    }
    .admin-stat-danger {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-color: #fecaca;
    }

    /* Section card with subtle shadow */
    .admin-card-elevated {
        box-shadow: 0 24px 60px -48px rgba(15, 23, 42, 0.18);
    }

    /* Icon decorator box */
    .admin-icon-box {
        @apply flex size-10 shrink-0 items-center justify-center rounded-2xl;
    }
}

/* Admin dark mode overrides */
@layer theme {
    .dark .admin-stat-total {
        background: linear-gradient(135deg, #0c1929 0%, #0f2338 100%);
        border-color: #1e3a5f;
    }
    .dark .admin-stat-success {
        background: linear-gradient(135deg, #052e16 0%, #0a3622 100%);
        border-color: #166534;
    }
    .dark .admin-stat-warning {
        background: linear-gradient(135deg, #1c1303 0%, #2c1f08 100%);
        border-color: #854d0e;
    }
    .dark .admin-stat-info {
        background: linear-gradient(135deg, #1a0533 0%, #250a42 100%);
        border-color: #6b21a8;
    }
    .dark .admin-stat-danger {
        background: linear-gradient(135deg, #2a0a0a 0%, #3b1010 100%);
        border-color: #991b1b;
    }
}
```

**Step 2: Verifikasi CSS compiles**

Run: `cd E:\workspace\antrian-ptsp && npx vite build 2>&1 | head -20`
Expected: Build successful

**Step 3: Commit**

```bash
git add resources/css/app.css
git commit -m "feat(ui): add admin design system CSS utilities (stat gradients, icon box, elevated cards)"
```

---

## Phase 2: Admin Dashboard Upgrade

### Task 2: Upgrade Admin Dashboard Stat Cards + Visual Polish

**Files:**
- Modify: `resources/views/livewire/dashboard/admin-dashboard.blade.php`

**Step 1: Replace flat stat cards dengan gradient stat cards + icon decorators**

Ganti seluruh bagian `{{-- Stat Cards --}}` (baris 2-23) dengan:

```blade
{{-- Stat Cards --}}
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <flux:card class="admin-stat-total admin-card-elevated p-5">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold tracking-[0.16em] text-sky-700 uppercase dark:text-sky-300">Total Hari Ini</flux:text>
                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $this->todayTotal }}</p>
            </div>
            <div class="admin-icon-box bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
                <flux:icon.ticket class="size-5" />
            </div>
        </div>
    </flux:card>

    <flux:card class="admin-stat-success admin-card-elevated p-5">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase dark:text-emerald-300">Sudah Dilayani</flux:text>
                <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-400">{{ $this->todayServed }}</p>
            </div>
            <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                <flux:icon.check-circle class="size-5" />
            </div>
        </div>
    </flux:card>

    <flux:card class="admin-stat-warning admin-card-elevated p-5">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold tracking-[0.16em] text-amber-700 uppercase dark:text-amber-300">Menunggu</flux:text>
                <p class="text-3xl font-bold text-amber-700 dark:text-amber-400">{{ $this->todayWaiting }}</p>
            </div>
            <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                <flux:icon.clock class="size-5" />
            </div>
        </div>
    </flux:card>

    <flux:card class="admin-stat-info admin-card-elevated p-5">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold tracking-[0.16em] text-violet-700 uppercase dark:text-violet-300">Rata-rata Tunggu</flux:text>
                <p class="text-3xl font-bold text-violet-700 dark:text-violet-400">{{ $this->todayAvgWaitMinutes }}<span class="ml-1 text-base font-medium">mnt</span></p>
            </div>
            <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                <flux:icon.chart-bar class="size-5" />
            </div>
        </div>
    </flux:card>
</div>
```

**Step 2: Upgrade Date Range Filter card**

Ganti bagian `{{-- Date Range Filter --}}` (baris 25-38) dengan:

```blade
{{-- Date Range Filter --}}
<flux:card class="p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                <flux:icon.funnel class="size-5" />
            </div>
            <div>
                <flux:heading size="sm">Filter Periode</flux:heading>
                <flux:text class="text-xs text-zinc-500">Pilih rentang tanggal untuk analisis data</flux:text>
            </div>
        </div>
        <div class="flex items-end gap-3">
            <flux:field>
                <flux:label>Dari</flux:label>
                <flux:input type="date" wire:model.live="startDate" />
            </flux:field>
            <flux:field>
                <flux:label>Sampai</flux:label>
                <flux:input type="date" wire:model.live="endDate" />
            </flux:field>
        </div>
    </div>
</flux:card>
```

**Step 3: Upgrade Chart section headings**

Untuk setiap `<flux:card class="p-4">` yang berisi chart, ganti `class="p-4"` menjadi `class="admin-card-elevated p-5"` dan ganti setiap `<flux:heading size="sm">Judul</flux:heading>` dalam chart cards dengan pattern:

```blade
<div class="flex items-center gap-3 mb-3">
    <div class="admin-icon-box bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
        <flux:icon.chart-bar class="size-5" />
    </div>
    <flux:heading size="sm">Tren 7 Hari Terakhir</flux:heading>
</div>
```

Gunakan warna berbeda untuk setiap chart:
- Tren 7 Hari: sky
- Per Layanan: emerald
- Per Loket: amber
- Distribusi Kanal: fuchsia

**Step 4: Upgrade Activity Log heading**

Ganti heading activity log dengan:

```blade
<div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="admin-icon-box bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
            <flux:icon.clock class="size-5" />
        </div>
        <div>
            <flux:heading size="sm">Aktivitas Terkini</flux:heading>
            <flux:text class="text-xs text-zinc-500">Auto-refresh setiap 30 detik</flux:text>
        </div>
    </div>
    <flux:badge size="sm" color="green" variant="pill">Live</flux:badge>
</div>
```

**Step 5: Upgrade Failure Operasional section**

Ganti bagian `{{-- Ringkasan Failure Operasional --}}` (baris 242-254) dengan:

```blade
{{-- Ringkasan Operasional --}}
<div class="grid grid-cols-2 gap-4">
    <flux:card class="admin-stat-success p-5">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold tracking-[0.16em] text-emerald-700 uppercase dark:text-emerald-300">Booking Berhasil</flux:text>
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $this->bookingSuccess }}</p>
                <flux:text class="text-xs text-emerald-600/70 dark:text-emerald-400/70">Hari ini</flux:text>
            </div>
            <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                <flux:icon.check class="size-5" />
            </div>
        </div>
    </flux:card>

    <flux:card class="admin-stat-danger p-5">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <flux:text class="text-xs font-semibold tracking-[0.16em] text-red-700 uppercase dark:text-red-300">Booking Gagal</flux:text>
                <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $this->bookingFailed }}</p>
                <flux:text class="text-xs text-red-600/70 dark:text-red-400/70">Hari ini</flux:text>
            </div>
            <div class="admin-icon-box bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                <flux:icon.x-circle class="size-5" />
            </div>
        </div>
    </flux:card>
</div>
```

**Step 6: Upgrade Shortcut Manajemen**

Ganti bagian `{{-- Shortcut Manajemen --}}` (baris 257-266) dengan:

```blade
{{-- Shortcut Manajemen --}}
<flux:card class="p-5">
    <div class="flex items-center gap-3 mb-4">
        <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
            <flux:icon.squares-2x2 class="size-5" />
        </div>
        <flux:heading size="sm">Shortcut Manajemen</flux:heading>
    </div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <flux:button :href="route('admin.layanan.index')" variant="filled" icon="clipboard-document-list" class="justify-center">Layanan</flux:button>
        <flux:button :href="route('admin.loket.index')" variant="filled" icon="building-office" class="justify-center">Loket</flux:button>
        <flux:button :href="route('admin.users.index')" variant="filled" icon="users" class="justify-center">Users</flux:button>
        <flux:button :href="route('admin.wilayah.index')" variant="filled" icon="map" class="justify-center">Wilayah</flux:button>
        <flux:button :href="url('/frontdesk/antrian')" variant="filled" icon="ticket" class="justify-center">Frontdesk</flux:button>
    </div>
</flux:card>
```

**Step 7: Verifikasi halaman dashboard**

Run: `cd E:\workspace\antrian-ptsp && php artisan view:cache 2>&1 | head -5`
Expected: No errors

**Step 8: Commit**

```bash
git add resources/views/livewire/dashboard/admin-dashboard.blade.php
git commit -m "feat(ui): upgrade admin dashboard — gradient stat cards, icon decorators, visual hierarchy"
```

---

## Phase 3: Admin CRUD Pages Upgrade

### Task 3: Upgrade Halaman Manajemen Layanan

**Files:**
- Modify: `resources/views/pages/admin/layanan/index.blade.php`

**Step 1: Tambah page header dengan icon decorator dan badge**

Ganti baris 1-11 (header + breadcrumbs) dengan:

```blade
<x-layouts::app :title="__('Manajemen Layanan')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-3">
                <flux:badge color="cyan" rounded>Admin Panel</flux:badge>
                <div>
                    <flux:heading size="xl" level="1">Manajemen Layanan</flux:heading>
                    <flux:subheading class="mt-1">Kelola layanan aktif dan konfigurasi kanal layanan.</flux:subheading>
                </div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Layanan</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge size="sm" color="green">{{ $services->total() ?? $services->count() }} layanan</flux:badge>
            </div>
        </div>
```

**Step 2: Upgrade form "Tambah Layanan" card**

Ganti `<flux:card class="space-y-4">` (baris 25) dan heading (baris 26) dengan:

```blade
        <flux:card class="admin-card-elevated space-y-4 border-cyan-200 dark:border-cyan-800">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
                    <flux:icon.plus-circle class="size-5" />
                </div>
                <flux:heading size="lg">Tambah Layanan Baru</flux:heading>
            </div>
```

**Step 3: Upgrade "Daftar Layanan" card heading**

Ganti `<flux:heading size="lg">Daftar Layanan</flux:heading>` (baris 89) dengan:

```blade
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.clipboard-document-list class="size-5" />
                    </div>
                    <flux:heading size="lg">Daftar Layanan</flux:heading>
                </div>
```

**Step 4: Verifikasi halaman**

Buka `http://localhost:8000/admin/layanan` — pastikan layout tidak pecah.

**Step 5: Commit**

```bash
git add resources/views/pages/admin/layanan/index.blade.php
git commit -m "feat(ui): upgrade layanan page — gradient card, icon decorators, badge counter"
```

---

### Task 4: Upgrade Halaman Manajemen Loket

**Files:**
- Modify: `resources/views/pages/admin/loket/index.blade.php`

**Step 1: Upgrade header dengan badge + icon decorator pattern**

Ganti baris 1-11 (header) dengan:

```blade
<x-layouts::app :title="__('Manajemen Loket')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-3">
                <flux:badge color="amber" rounded>Admin Panel</flux:badge>
                <div>
                    <flux:heading size="xl" level="1">Manajemen Loket</flux:heading>
                    <flux:subheading class="mt-1">Kelola loket antrian, mapping pool, dan status aktif.</flux:subheading>
                </div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Loket</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>
        </div>
```

**Step 2: Upgrade form card dan daftar card**

Ganti `<flux:card class="space-y-4">` pertama (Tambah Loket) dengan:

```blade
        <flux:card class="admin-card-elevated space-y-4 border-amber-200 dark:border-amber-800">
            <div class="flex items-center gap-3">
                <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                    <flux:icon.plus-circle class="size-5" />
                </div>
                <flux:heading size="lg">Tambah Loket Baru</flux:heading>
            </div>
```

Ganti form grid `lg:grid-cols-5` menjadi `sm:grid-cols-2 lg:grid-cols-4` dan pindahkan button ke bawah:

```blade
            <form method="POST" action="{{ route('admin.loket.store') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
```

Ganti `<flux:heading size="lg">Daftar Loket</flux:heading>` dengan:

```blade
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <flux:icon.building-office class="size-5" />
                    </div>
                    <flux:heading size="lg">Daftar Loket</flux:heading>
                </div>
```

**Step 3: Commit**

```bash
git add resources/views/pages/admin/loket/index.blade.php
git commit -m "feat(ui): upgrade loket page — icon decorators, responsive grid, elevated cards"
```

---

### Task 5: Upgrade Halaman Manajemen User

**Files:**
- Modify: `resources/views/pages/admin/users/index.blade.php`

**Step 1: Upgrade header**

Ganti baris 1-11 dengan:

```blade
<x-layouts::app :title="__('Manajemen User')">
    <div class="mx-auto w-full max-w-6xl space-y-6" x-data="{ tab: 'list', editUser: null }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-3">
                <flux:badge color="violet" rounded>Admin Panel</flux:badge>
                <div>
                    <flux:heading size="xl" level="1">Manajemen User</flux:heading>
                    <flux:subheading class="mt-1">Kelola role dan izin layanan setiap user internal.</flux:subheading>
                </div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('dashboard')" icon="home" />
                    <flux:breadcrumbs.item>Users</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>
        </div>
```

**Step 2: Replace custom tab buttons dengan Flux tabs pattern**

Ganti bagian `{{-- Tabs Navigation --}}` card (baris 26-53) dengan:

```blade
        {{-- Tabs Navigation --}}
        <div class="flex gap-1 rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800/50">
            <button
                type="button"
                x-on:click="tab = 'list'"
                :class="tab === 'list' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all"
            >
                <flux:icon.users class="size-4" />
                Semua Users
            </button>
            <button
                type="button"
                x-on:click="tab = 'roles'"
                :class="tab === 'roles' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all"
            >
                <flux:icon.shield-check class="size-4" />
                Role & Izin
            </button>
            <button
                type="button"
                x-on:click="tab = 'create'"
                :class="tab === 'create' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all"
            >
                <flux:icon.user-plus class="size-4" />
                Tambah User
            </button>
        </div>
```

**Step 3: Upgrade card headings per tab**

Tab list — ganti `<flux:heading size="lg">Daftar User</flux:heading>` dengan:

```blade
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                        <flux:icon.users class="size-5" />
                    </div>
                    <flux:heading size="lg">Daftar User</flux:heading>
                </div>
```

Tab roles — ganti `<flux:heading size="lg">Role & Izin Layanan</flux:heading>` dengan:

```blade
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <flux:icon.shield-check class="size-5" />
                    </div>
                    <flux:heading size="lg">Role & Izin Layanan</flux:heading>
                </div>
```

Tab create — ganti `<flux:heading size="lg">Tambah User</flux:heading>` dengan:

```blade
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
                        <flux:icon.user-plus class="size-5" />
                    </div>
                    <flux:heading size="lg">Tambah User Baru</flux:heading>
                </div>
```

**Step 4: Replace custom modal dengan Flux modal**

Ganti bagian `<template x-teleport="body">` (baris 254-296) dengan:

```blade
        {{-- Edit Modal --}}
        <flux:modal name="edit-user-modal" class="w-full max-w-lg">
            <form x-bind:action="editUser ? '{{ route('admin.users.index') }}/' + editUser.id : '#'" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <flux:heading size="lg">Edit User</flux:heading>
                </div>

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
                    <flux:button type="button" variant="ghost" x-on:click="$dispatch('close-modal', 'edit-user-modal')">Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
                </div>
            </form>
        </flux:modal>
```

Dan ubah trigger edit button dari `x-on:click="editUser = ..."` menjadi juga dispatch modal:

```blade
x-on:click="editUser = {{ json_encode([...]) }}; $dispatch('open-modal', 'edit-user-modal')"
```

**Step 5: Commit**

```bash
git add resources/views/pages/admin/users/index.blade.php
git commit -m "feat(ui): upgrade users page — pill tabs, icon decorators, Flux modal"
```

---

### Task 6: Upgrade Halaman Setting Wilayah

**Files:**
- Modify: `resources/views/pages/admin/wilayah/index.blade.php`

**Step 1: Upgrade header dan "Kabupaten Aktif" card**

Terapkan pattern yang sama:
- Badge "Admin Panel" color="emerald"
- Icon decorator di heading
- Card "Kabupaten Aktif Saat Ini" dengan `admin-stat-success` gradient

**Step 2: Commit**

```bash
git add resources/views/pages/admin/wilayah/index.blade.php
git commit -m "feat(ui): upgrade wilayah page — gradient active card, icon decorators"
```

---

## Phase 4: Laporan & Frontdesk Upgrade

### Task 7: Upgrade Halaman Laporan Antrian

**Files:**
- Modify: `resources/views/pages/laporan/antrian/index.blade.php`

**Step 1: Fix double flux:main (bug)**

Hapus `<flux:main container>` dan `</flux:main>` — layout parent sudah menyediakan wrapper.

**Step 2: Upgrade header**

```blade
<x-layouts::app :title="__('Laporan Antrian')">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="space-y-3">
            <flux:badge color="blue" rounded>Laporan</flux:badge>
            <div>
                <flux:heading size="xl" level="1">Laporan Antrian</flux:heading>
                <flux:subheading class="mt-1">Periode: {{ $from }} s.d. {{ $to }}</flux:subheading>
            </div>
        </div>
```

**Step 3: Upgrade Filter card**

Ganti `<flux:heading size="lg">Filter Periode</flux:heading>` dan form dengan:

```blade
        <flux:card class="p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="admin-icon-box bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                        <flux:icon.funnel class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="sm">Filter Periode</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Pilih rentang tanggal laporan</flux:text>
                    </div>
                </div>
                <form method="GET" action="{{ url('/laporan/antrian') }}" class="flex items-end gap-3">
                    <flux:field>
                        <flux:label>Dari</flux:label>
                        <flux:input type="date" name="from" value="{{ $from }}" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Sampai</flux:label>
                        <flux:input type="date" name="to" value="{{ $to }}" />
                    </flux:field>
                    <flux:button type="submit" variant="primary" icon="funnel">Filter</flux:button>
                </form>
            </div>
        </flux:card>
```

**Step 4: Upgrade setiap report card heading**

Terapkan icon decorator per section:
- Berdasarkan Layanan: icon clipboard-document-list, color emerald
- Berdasarkan Loket: icon building-office, color amber
- Berdasarkan Petugas: icon user, color violet
- Berdasarkan Status: icon signal, color sky
- Distribusi Petugas x Layanan: icon chart-bar, color fuchsia

Pattern per card:

```blade
<flux:card class="admin-card-elevated">
    <div class="flex items-center gap-3 mb-4">
        <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
            <flux:icon.clipboard-document-list class="size-5" />
        </div>
        <flux:heading size="lg">Berdasarkan Layanan</flux:heading>
    </div>
    {{-- table content tetap sama --}}
</flux:card>
```

**Step 5: Commit**

```bash
git add resources/views/pages/laporan/antrian/index.blade.php
git commit -m "feat(ui): upgrade laporan page — fix double flux:main, icon decorators, elevated cards"
```

---

### Task 8: Upgrade Halaman Frontdesk Antrian

**Files:**
- Modify: `resources/views/pages/frontdesk/antrian.blade.php`

**Step 1: Upgrade page structure**

Tambah badge + heading pattern di awal:

```blade
<x-layouts::app :title="__('Frontdesk Antrian')">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="space-y-3">
            <flux:badge color="cyan" rounded>Frontdesk</flux:badge>
            <div>
                <flux:heading size="xl" level="1">Frontdesk Antrian</flux:heading>
                <flux:subheading class="mt-1">Buat tiket antrian baru atau lakukan check-in tiket yang sudah ada.</flux:subheading>
            </div>
        </div>
```

**Step 2: Upgrade "Buat Tiket" dan "Check-in" card headings**

```blade
{{-- Form Buat Tiket Baru --}}
<flux:card class="admin-card-elevated">
    <div class="flex items-center gap-3 mb-4">
        <div class="admin-icon-box bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400">
            <flux:icon.plus-circle class="size-5" />
        </div>
        <flux:heading size="lg">Buat Tiket Antrian Baru</flux:heading>
    </div>
    {{-- form tetap sama --}}
</flux:card>

{{-- Form Check-in --}}
<flux:card class="admin-card-elevated">
    <div class="flex items-center gap-3 mb-4">
        <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
            <flux:icon.check-circle class="size-5" />
        </div>
        <flux:heading size="lg">Check-in Tiket</flux:heading>
    </div>
    {{-- form tetap sama --}}
</flux:card>
```

**Step 3: Upgrade success callouts**

Ganti tiket berhasil card (baris 15-25) dengan:

```blade
@if ($ticket)
    <flux:card class="admin-stat-success admin-card-elevated p-5">
        <div class="flex items-start gap-4">
            <div class="admin-icon-box bg-emerald-200 text-emerald-700 dark:bg-emerald-800 dark:text-emerald-300">
                <flux:icon.check-circle class="size-6" />
            </div>
            <div class="space-y-1">
                <flux:heading size="lg" class="text-emerald-800 dark:text-emerald-200">Tiket Berhasil Dibuat</flux:heading>
                <flux:text class="text-emerald-700 dark:text-emerald-300"><strong>Nomor Antrian:</strong> {{ $ticket->ticket_number }}</flux:text>
                <flux:text class="text-emerald-700 dark:text-emerald-300"><strong>Status:</strong>
                    <flux:badge size="sm" color="{{ $ticket->status->color() }}">{{ $ticket->status->label() }}</flux:badge>
                </flux:text>
            </div>
        </div>
    </flux:card>
@endif
```

Terapkan pattern serupa untuk checkedInTicket card (gunakan admin-stat-info, warna blue).

**Step 4: Commit**

```bash
git add resources/views/pages/frontdesk/antrian.blade.php
git commit -m "feat(ui): upgrade frontdesk page — badge header, icon decorators, gradient success cards"
```

---

## Phase 5: Sidebar Navigation Polish

### Task 9: Polish Sidebar Navigation

**Files:**
- Modify: `resources/views/layouts/app/sidebar.blade.php`

**Step 1: Tambah section divider dan group headings yang lebih visual**

Upgrade sidebar groups agar punya icon di heading:

Ganti `<flux:sidebar.group :heading="__('Manajemen Internal')" class="grid mt-4">` dengan:

```blade
<flux:sidebar.group :heading="__('Manajemen Internal')" expandable class="grid mt-4">
```

Ganti `<flux:sidebar.group :heading="__('Admin')" class="grid mt-4">` dengan:

```blade
<flux:sidebar.group :heading="__('Admin')" expandable class="grid mt-4">
```

**Step 2: Commit**

```bash
git add resources/views/layouts/app/sidebar.blade.php
git commit -m "feat(ui): polish sidebar — expandable groups"
```

---

## Phase 6: Verifikasi Akhir

### Task 10: Final Verification

**Step 1: Build assets**

Run: `cd E:\workspace\antrian-ptsp && npx vite build`
Expected: Build successful tanpa error

**Step 2: Clear caches**

Run: `cd E:\workspace\antrian-ptsp && php artisan view:clear && php artisan cache:clear`
Expected: Cache cleared

**Step 3: Jalankan tests**

Run: `cd E:\workspace\antrian-ptsp && php artisan test`
Expected: Semua test pass (kecuali pre-existing failures yang tidak terkait)

**Step 4: Verifikasi semua halaman**

Buka dan periksa secara visual:
- `http://localhost:8000/dashboard` (admin role)
- `http://localhost:8000/admin/layanan`
- `http://localhost:8000/admin/loket`
- `http://localhost:8000/admin/users`
- `http://localhost:8000/admin/wilayah`
- `http://localhost:8000/laporan/antrian`
- `http://localhost:8000/frontdesk/antrian`

Pastikan:
- Gradient stat cards tampil dengan benar
- Icon decorators muncul di setiap heading
- Dark mode masih berfungsi
- Mobile layout tidak pecah
- Tidak ada double `<flux:main>` nesting

**Step 5: Commit tag**

```bash
git tag -a v1.2.0-ui-upgrade -m "Admin UI/UX upgrade to A+ grade"
```

---

## Checklist Ringkasan

| # | Task | Area | Impact |
|---|------|------|--------|
| 1 | Admin CSS design system | Foundation | High |
| 2 | Dashboard stat cards + charts | Dashboard | Critical |
| 3 | Layanan page upgrade | Admin CRUD | High |
| 4 | Loket page upgrade | Admin CRUD | High |
| 5 | Users page upgrade | Admin CRUD | High |
| 6 | Wilayah page upgrade | Admin CRUD | Medium |
| 7 | Laporan page upgrade + fix bug | Laporan | High |
| 8 | Frontdesk page upgrade | Frontdesk | High |
| 9 | Sidebar navigation polish | Navigation | Medium |
| 10 | Final verification | QA | Critical |
