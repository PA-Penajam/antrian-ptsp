# AdminDashboard Livewire Component - Task 7 Learnings

## Date: 2026-03-07

### Completed
- Created `app/Livewire/Dashboard/AdminDashboard.php` with:
  - 4 stat cards: todayTotal, todayServed, todayWaiting, todayAvgWaitMinutes
  - Date range filter (startDate, endDate) with wire:model.live
  - Computed properties using #[Computed] attribute (Livewire 4 pattern)
  - Chart data methods: byService(), byCounter(), byChannel(), trendData()
  
- Created `resources/views/livewire/dashboard/admin-dashboard.blade.php` with:
  - Flux UI card components for stat cards
  - Date range inputs with wire:model.live
  - Placeholders for Task 8 (charts) and Task 9 (activity log)

- Created `tests/Feature/Dashboard/AdminDashboardTest.php` with 9 tests:
  - Component renders successfully
  - Stat cards display correct data
  - Date range filter changes data correctly
  - byService, byCounter, byChannel return correct data
  - trendData returns 7 days data
  - Average wait time handles zero case
  - Default date range is today

### Key Implementation Details

#### Livewire 4 Computed Properties Pattern
```php
use Livewire\Attributes\Computed;

#[Computed]
public function todayTotal(): int
{
    return QueueTicket::query()
        ->whereBetween('service_date', [$this->startDate, $this->endDate])
        ->count();
}
```

#### Database-Agnostic Average Calculation
SQLite doesn't support TIMESTAMPDIFF, so calculated average in PHP:
```php
$tickets = QueueTicket::query()
    ->whereNotNull('called_at')
    ->whereNotNull('completed_at')
    ->get(['called_at', 'completed_at']);

$totalMinutes = $tickets->sum(fn ($t) => $t->called_at->diffInMinutes($t->completed_at));
return $tickets->isEmpty() ? 0.0 : round($totalMinutes / $tickets->count(), 1);
```

#### Test Data Creation with Unique Constraints
The queue_tickets table has unique constraint on (queue_pool_id, service_date, sequence_number). Tests must use explicit sequence_number to avoid collisions:
```php
QueueTicket::factory()->count(5)->create([
    'service_date' => $today,
    'sequence_number' => $seq++,  // Increment to avoid unique constraint
]);
```

### Test Results
- AdminDashboard tests: 9/9 PASS
- Full test suite: 120 passed, 4 failed (unrelated to this task)
- Pint: PASS (no issues)

### Notes
- The 4 failing tests in other files expect old admin dashboard content ("Shortcut Manajemen", "Booking Berhasil Hari Ini")
- These are legacy tests that will need updating when full admin dashboard content is implemented
- Component properly uses existing `resources/views/components/dashboard/admin-dashboard.blade.php` which already had `<livewire:dashboard.admin-dashboard />`

## Task 8 Learnings

### Flux Chart API specifics
- `flux:chart` in Flux Pro 2.x accepts either `wire:model` or `:value`; `:value` can consume an array of associative arrays directly from computed properties.
- Chart primitives such as `flux:chart.line`, `flux:chart.bar`, `flux:chart.axis`, and `flux:chart.cursor` must live inside `flux:chart.svg`.
- `flux:chart.viewport` is useful when the chart needs a constrained height or when rendering siblings like tooltip/legend outside the SVG area.
- Tooltips are declared as siblings of `flux:chart.svg` inside `flux:chart`, using `flux:chart.tooltip.heading` and `flux:chart.tooltip.value`.
- Associative datasets like `byService`, `byCounter`, and `byChannel` must be converted into indexed arrays of keyed objects before passing them to `:value`.

### Dashboard implementation notes
- Empty state is safer when checking both item count and summed metric value, especially for fixed-key datasets like `byChannel` and 7-day `trendData`.
- Mobile responsiveness is handled with `grid grid-cols-1 gap-4 lg:grid-cols-2`, so stacked cards stay readable on small screens.

## Task 9 Learnings - Activity Log

### Flux Table API specifics
- Flux UI Pro 2.x table uses dot-notation subcomponents:
  - `flux:table.columns` contains `flux:table.column` headers
  - `flux:table.rows` contains `flux:table.row` elements
  - Each row has `flux:table.cell` for individual cells
- Badge colors supported: `blue`, `green`, `red`, `zinc`, etc.
- Table syntax example:
```blade
<flux:table>
    <flux:table.columns>
        <flux:table.column>Waktu</flux:table.column>
        <flux:table.column>Aksi</flux:table.column>
    </flux:table.columns>
    <flux:table.rows>
        @foreach($activities as $activity)
        <flux:table.row>
            <flux:table.cell>{{ $activity->created_at->diffForHumans() }}</flux:table.cell>
            <flux:table.cell>
                <flux:badge size="sm" color="green">{{ $activity->action }}</flux:badge>
            </flux:table.cell>
        </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
```

### Livewire Polling
- `wire:poll.30s` on component wrapper triggers auto-refresh every 30 seconds
- Alternative: `#[Poll(30000)]` attribute on Livewire component class

### Implementation Details
- Added `recentActivities()` computed property using `#[Computed]` attribute
- Eager loaded relationships: `queueTicket.service`, `user`, `counter`
- Helper methods `actionLabel()` and `actionColor()` map action types to human-readable labels and badge colors
- Empty state shows "Belum ada aktivitas" when no activities exist
- Null-safe operator (`?->`) handles missing relationships gracefully

### Test Coverage
- Created `tests/Feature/Dashboard/ActivityLogTest.php` with 6 tests:
  - Returns last 20 activities ordered by created_at desc
  - Shows empty state message when no activities exist
  - Renders activity log section with activity data
  - Returns correct action labels for all known actions
  - Returns correct action colors for all known actions
  - Handles null relationships gracefully
- Test results: 6/6 PASS
- Pint: PASS (no issues)

## Task 14 Learnings - Kiosk Password Gate

### Route and middleware behavior
- `module.password:kiosk` checks `kiosk_authenticated_at` for expiry and redirects guests to `/kiosk/login`, so only `/kiosk` needs the middleware group while login/logout stay public.
- The session keys used by kiosk auth are `kiosk_authenticated` and `kiosk_authenticated_at`; they align with `CheckModulePassword`'s `str_replace('-', '_', $module)` pattern.

### Flux layout notes
- Flux UI v2 no longer uses `@fluxStyles`; kiosk layout should keep `@vite(...)`, `@fluxAppearance`, and `@fluxScripts` for the correct asset + appearance pipeline.
- If a page requirement specifically asks for `<x-layouts.kiosk>`, add a small wrapper component at `resources/views/components/layouts/kiosk.blade.php` that delegates to `resources/views/layouts/kiosk.blade.php`.

### Test coverage
- Added `tests/Feature/Kiosk/KioskAuthTest.php` to cover guest redirect, login page rendering, successful login, failed login, logout session clearing, and middleware-protected kiosk access.
- Targeted verification command: `php artisan test --compact --filter=KioskAuth`.
## Task 18: TV Display Queue Livewire Component

- Created app/Livewire/TvDisplay.php component mirroring QueueDisplay pattern
- Uses #[Layout(layouts.tv-display)] and #[Title(Monitor Antrian)] attributes
- Auto-polls every 5 seconds via wire:poll.5s
- Shows current called tickets (up to 6) and recent calls (up to 20)
- Full-screen TV layout with dark bg-zinc-950 theme and high contrast
- Created resources/views/livewire/tv-display.blade.php with TV-optimized styling
- Updated resources/views/pages/tv-display/index.blade.php to use <livewire:tv-display />
- All 4 tests pass (TvDisplayQueueTest.php)
- Pint clean


## Task 15: Kiosk Booking Wizard - Learnings

### Livewire Component Testing with Session
- Use `session()` helper to set session data before calling `Livewire::test()`
- Example: `session(['kiosk_authenticated' => true]); Livewire::test(Component::class)`
- Do NOT use `Livewire::withSession()` - it doesn't exist

### Flux Button Size Limitation
- `<flux:button>` does NOT support `size="lg"` - only specific sizes are valid
- For larger buttons, use CSS classes like `py-6 text-xl` instead of `size` prop
- Valid sizes appear to be limited (e.g., `size="sm"` works in admin pages)

### Kiosk Session Pattern
- Kiosk uses middleware `module.password:kiosk` for authentication
- Session keys: `kiosk_authenticated`, `kiosk_authenticated_at`
- Helper function for tests: `session(['kiosk_authenticated' => true, 'kiosk_authenticated_at' => now()->timestamp])`

### CreateQueueTicket Action Usage
- Channel `walk_in_kiosk` creates ticket with `Waiting` status (not `Booked`)
- Always use `CarbonImmutable::today()` for `service_date`
- Set `created_by` to `null` for kiosk bookings (no authenticated user)

### Component Structure
- Use `#[Layout('layouts.kiosk')]` for kiosk-specific layout
- Use `#[Title('...')]` for page title
- 4-step wizard: Service Selection → Visitor Data → Confirmation → Ticket Display

### Auto-Reset Pattern
- Use Alpine.js `x-data` and `setInterval` for countdown
- Call `$wire.resetWizard()` after countdown expires
- Use `resetWizard()` method name (NOT `reset()` - conflicts with Livewire's built-in)

## Task 16: Kiosk Animations + UX Polish

### Livewire loading UX
- Scope `wire:loading` and `wire:loading.attr="disabled"` with `wire:target` on submit buttons so spinner feedback only appears for the intended action, not on every field update.
- Inline SVG spinner works cleanly inside `flux:button` and keeps the button width stable when paired with `wire:loading.remove`.

### Tailwind motion notes
- Step containers can use `animate-in fade-in duration-300` directly for light transition polish without adding JavaScript.
- Adding `wire:key` per conditional step helps Livewire treat each wizard pane as a distinct DOM node so entry animations re-run when the step changes.
- Service cards feel more tactile with `hover:scale-105`, `active:scale-95`, and a slightly brighter cyan border on hover.

### Verification
- `php artisan test --compact --filter=KioskBooking` passed after the Blade-only UX update.
- Full suite `php artisan test --compact` also passed, so the polish did not introduce regressions.


## Task 19: TV Display Auto-refresh + Graceful Fallbacks

### Error Handling Pattern
Added try/catch to data fetching methods for graceful degradation when database is unavailable:
```php
protected function currentCalls(): Collection
{
    try {
        return QueueTicket::query()
            ->with(['counter', 'service'])
            ->where('status', QueueStatus::Called)
            ->whereDate('service_date', today())
            ->orderByDesc('called_at')
            ->limit(6)
            ->get();
    } catch (\Throwable $e) {
        return new Collection();
    }
}
```

### Livewire Connection Status (Alpine.js)
Livewire 4 dispatches `livewire:connecting` and `livewire:connected` events on window:
```blade
<div x-data="{ connected: true }"
     x-on:livewire:connecting.window="connected = false"
     x-on:livewire:connected.window="connected = true"
     x-show="!connected"
     class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-red-900 text-red-200 px-6 py-3 rounded-lg z-50">
    ⚠ Koneksi terputus, menghubungkan ulang...
</div>
```

### Alpine.js Live Clock
Replace server-rendered clock with client-side Alpine.js for smooth updates without Livewire polling:
```blade
<span x-data="{ time: '' }"
      x-init="setInterval(() => { time = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'}) }, 1000); time = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'})"
      x-text="time"
      class="text-zinc-400 text-lg font-mono"></span>
```

### Empty States
- "Sedang Dipanggil" section: Shows "Belum ada panggilan" when empty
- "Riwayat Panggilan" section: Shows "Belum ada riwayat hari ini" when empty

### Test Coverage
Added 4 new tests to TvDisplayQueueTest.php:
- TV display shows recent calls empty state when no history
- TV display renders with current date
- TV display includes connection status indicator
- TV display uses Alpine.js for live clock

All 14 TvDisplay tests pass (44 assertions).

## Task 20: Admin Breadcrumbs + Empty State Audit

### Flux breadcrumbs notes
- Flux UI Pro 2.x uses `<flux:breadcrumbs>` with child `<flux:breadcrumbs.item>` entries.
- `flux:breadcrumbs.item` accepts `href` for clickable items and `icon` for icon-only entries, so the dashboard crumb can stay as `<flux:breadcrumbs.item :href="route('dashboard')" icon="home" />`.

### Admin view audit
- Added breadcrumb blocks directly below each admin page heading in `resources/views/pages/admin/layanan/index.blade.php`, `resources/views/pages/admin/loket/index.blade.php`, and `resources/views/pages/admin/users/index.blade.php`.
- Confirmed existing empty states remain present: layanan uses `@forelse/@empty` with `Belum ada layanan`, loket uses `@forelse/@empty` with `Belum ada loket`, and users keeps the existing `Belum ada user selain Anda` state for the list tab.


## Task 21: Cross-module Integration Tests

### Integration Test Coverage
Created comprehensive integration tests at `tests/Feature/Integration/AdminOverhaulIntegrationTest.php` covering 9 test groups:

1. **Admin Dashboard** (5 tests)
   - Admin can access dashboard with stat cards
   - Non-admin roles (Officer, Frontdesk, Monitor) cannot access admin routes (403)
   - Unauthenticated users redirected to login

2. **Admin CRUD Pages** (4 tests)
   - Layanan, Loket, Users pages load with breadcrumbs
   - All pages contain Flux breadcrumbs component

3. **Old Route Redirects** (3 tests)
   - `/admin/roles` → `/admin/users` (301 redirect)
   - `/admin/izin-layanan` → `/admin/users` (301 redirect)

4. **Kiosk Module** (6 tests)
   - Requires password authentication (redirects to login)
   - Login page accessible
   - Shows booking wizard when authenticated
   - Correct password authenticates successfully
   - Wrong password rejected with error
   - Logout clears session properly

5. **TV Display Module** (6 tests)
   - Requires password authentication
   - Login page accessible with "Monitor Antrian"
   - Shows queue board when authenticated
   - Correct password authenticates successfully
   - Wrong password rejected with error
   - Logout clears session properly

6. **Theme Toggle** (4 tests)
   - Sidebar contains theme toggle button
   - Uses localStorage for persistence
   - Uses Flux button component
   - No hardcoded `class="dark"` on html element

7. **Named Routes** (3 tests)
   - All admin CRUD routes exist (12 routes)
   - All kiosk and TV-display routes exist (8 routes)
   - Dashboard route exists

8. **Navigation Integration** (3 tests)
   - Admin sidebar contains all menu items
   - Navigation links use correct route() helpers
   - Non-admin users don't see admin menu items

9. **Cross-Module Access Control** (2 tests)
   - Admin can access all CRUD operations
   - Admin can access kiosk and TV display with proper session

### Test Helper Functions
```php
function createAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
}

function createNonAdmin(UserRole $role): User
{
    return User::factory()->create([
        'role' => $role->value,
        'email_verified_at' => now(),
    ]);
}
```

### Key Testing Patterns
- Use `from(route('kiosk.login'))` before `post()` to set referrer for proper redirect handling
- Session-based auth: `withSession(['kiosk_authenticated' => true, 'kiosk_authenticated_at' => now()->timestamp])`
- Route existence check: `Route::has($routeName)`
- Assert breadcrumbs: `assertSee('data-flux-breadcrumbs', false)`

### Test Results
- Integration tests: 36/36 PASS (117 assertions)
- Full test suite: 220/220 PASS (780 assertions)
- Pint: PASS (no issues)

## 2026-03-08 - F1 audit learnings
- The admin overhaul has strong coverage for dashboard analytics, activity log, kiosk flow, TV display polling, and cross-module Pest tests.
- The main compliance drift is not missing modules, but incomplete finish quality: unnamed legacy redirects, inconsistent branding scope, weaker password middleware checks, and table UX gaps on counters/users.
- `grep -r "Laravel Starter Kit" resources/views/` returns no matches, so the legacy starter-kit branding string is removed from Blade views.
