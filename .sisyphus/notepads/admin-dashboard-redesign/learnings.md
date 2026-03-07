# Learnings - Admin Dashboard Redesign

## 2026-03-07 Session Init

### Codebase Conventions
- **Worktree**: `/home/moohard/.config/superpowers/worktrees/antrian-ptsp/feat-admin-dashboard-redesign-20260307`
- **Layout files**: `resources/views/layouts/app/sidebar.blade.php` dan `header.blade.php`
- **Dashboard component**: `resources/views/components/dashboard/⚡admin-dashboard.blade.php` (Livewire Volt)
- **AdminStats**: `app/Support/Dashboard/AdminStats.php` - class dengan method `build()`

### Flux UI Patterns
- Sidebar groups: `<flux:sidebar.group :heading="..." expandable :expanded="...">`
- Sidebar items: `<flux:sidebar.item icon="..." href="..." :current="request()->is(...)">`
- Cards: `<flux:card>` dengan class Tailwind untuk warna
- Badges: `<flux:badge color="...">`

### Role Check Pattern
```php
auth()->user()?->hasRole(\App\Enums\UserRole::Admin)
```

### Current Sidebar Structure (sidebar.blade.php)
- Lines 47-51: Admin menu (single item, hanya `/admin/layanan`)
- Lines 101-105: Header mobile Admin menu (sama, single item)

### Current Dashboard Structure (⚡admin-dashboard.blade.php)
- Lines 40-61: 5 KPI cards (flat, tanpa warna)
- Lines 63-89: 2 cards (Aktivitas Pengguna + Ringkasan Failure) → AKAN DIHAPUS
- Lines 91-100: Shortcut Manajemen (button group) → AKAN DIGANTI grid cards

### AdminStats.build() Returns
```php
[
  'booking_success_today' => int,
  'booking_failed_today' => int,
  'tickets_created_today' => int,
  'tickets_cancelled_today' => int,
  'tickets_completed_today' => int,
  'failure_summary' => ['cancelled' => int, 'skipped' => int],
  'public_activity' => array<string, int>
]
```

### Database Tables
- `queue_tickets` - tiket antrian
- `queue_activities` - aktivitas/events
- `QueueStatus` enum: `Cancelled`, `Completed`, dll

### Color Mapping (Confirmed)
- Green: success (Booking Berhasil, Tiket Selesai)
- Red: fail (Booking Gagal)
- Blue: neutral (Tiket Dibuat)
- Amber: warning (Tiket Batal)
- Purple: roles

### Tailwind Color Pattern (dari plan)
- Background: `bg-{color}-50 dark:bg-{color}-900/20`
- Border: `border-{color}-200 dark:border-{color}-800`
- Text: `text-{color}-700 dark:text-{color}-300`
