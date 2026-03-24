# Performance Optimization Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reduce database query count and server load across all Livewire polling components, add missing indexes, optimize asset bundling, and move expensive PHP aggregations to the database layer.

**Architecture:** Each task targets one component/concern independently. Tasks 1-4 are high-priority (query reduction in polling components). Task 5 is a database migration. Task 6 optimizes frontend bundle splitting. Tasks are ordered by impact — highest first.

**Tech Stack:** Laravel 12, Livewire 4, MySQL, Vite, Pest 4

---

## Chunk 1: Database Query Optimization

### Task 1: Optimize AdminDashboard — Consolidate Queries & Add Caching

**Files:**
- Modify: `app/Livewire/Dashboard/AdminDashboard.php`
- Modify: `resources/views/livewire/dashboard/admin-dashboard.blade.php:262` (poll target)
- Test: `tests/Feature/Dashboard/AdminDashboardTest.php`

**Problem:** 9+ separate `#[Computed]` queries with no `persist` caching, all re-executed on every 30s poll. `trendData()` runs 7 COUNT queries in a PHP loop.

- [ ] **Step 1: Write test for consolidated dashboard stats**

Create a feature test that verifies the dashboard returns correct aggregate data from a single method call instead of 9 separate computed properties.

```php
// tests/Feature/Dashboard/AdminDashboardTest.php — add new test
it('loads dashboard stats efficiently', function () {
    // Arrange: seed known tickets
    $service = \App\Models\Service::factory()->create();
    \App\Models\QueueTicket::factory()
        ->count(3)
        ->for($service)
        ->create([
            'service_date' => today(),
            'status' => \App\Enums\QueueStatus::Completed,
            'channel' => 'online_booking',
            'called_at' => now()->subMinutes(10),
            'completed_at' => now(),
        ]);
    \App\Models\QueueTicket::factory()->create([
        'service_date' => today(),
        'status' => \App\Enums\QueueStatus::Waiting,
    ]);

    $user = \App\Models\User::factory()->create();
    $user->assignRole('admin');

    // Act & Assert
    \Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Dashboard\AdminDashboard::class)
        ->assertSet('startDate', today()->toDateString())
        ->assertViewHas('this');
});
```

- [ ] **Step 2: Run test to verify it passes with current implementation**

Run: `php artisan test --compact --filter=AdminDashboard`

- [ ] **Step 3: Replace `trendData()` loop with single GROUP BY query**

In `app/Livewire/Dashboard/AdminDashboard.php`, replace the `trendData()` method:

```php
#[Computed]
public function trendData(): array
{
    $days = 7;
    $start = today()->subDays($days - 1);
    $end = today();

    $counts = QueueTicket::query()
        ->selectRaw('DATE(service_date) as date, COUNT(*) as total')
        ->whereBetween('service_date', [$start, $end])
        ->groupByRaw('DATE(service_date)')
        ->pluck('total', 'date');

    $data = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = today()->subDays($i)->format('Y-m-d');
        $data[] = [
            'date' => $date,
            'total' => $counts[$date] ?? 0,
        ];
    }

    return $data;
}
```

- [ ] **Step 4: Replace `todayAvgWaitMinutes()` with database aggregation**

Replace the method that loads all completed tickets into PHP:

```php
#[Computed]
public function todayAvgWaitMinutes(): float
{
    $avg = QueueTicket::query()
        ->whereBetween('service_date', [$this->startDate, $this->endDate])
        ->where('status', QueueStatus::Completed)
        ->whereNotNull('called_at')
        ->whereNotNull('completed_at')
        ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, called_at, completed_at)) as avg_minutes')
        ->value('avg_minutes');

    return round((float) ($avg ?? 0), 1);
}
```

- [ ] **Step 5: Scope the `wire:poll` to only refresh the activity card**

In `resources/views/livewire/dashboard/admin-dashboard.blade.php`, the `wire:poll.30s` at line 262 currently re-renders the entire component. Instead, extract recent activities into a child Livewire component or use `wire:poll.30s="$refresh"` only on the activity section and mark all stat computed properties with `persist`:

Add `persist: true` to every `#[Computed]` that computes aggregate stats (not `recentActivities`):

```php
#[Computed(persist: true)]
public function todayTotal(): int { ... }

#[Computed(persist: true)]
public function todayServed(): int { ... }

#[Computed(persist: true)]
public function todayWaiting(): int { ... }

#[Computed(persist: true)]
public function todayAvgWaitMinutes(): float { ... }

#[Computed(persist: true)]
public function bookingSuccess(): int { ... }

#[Computed(persist: true)]
public function bookingFailed(): int { ... }

#[Computed(persist: true)]
public function byService(): array { ... }

#[Computed(persist: true)]
public function byCounter(): array { ... }

#[Computed(persist: true)]
public function byChannel(): array { ... }

#[Computed(persist: true)]
public function trendData(): array { ... }
```

Then add a manual refresh action that clears persisted cache:

```php
public function filterByDate(): void
{
    unset(
        $this->todayTotal,
        $this->todayServed,
        $this->todayWaiting,
        $this->todayAvgWaitMinutes,
        $this->bookingSuccess,
        $this->bookingFailed,
        $this->byService,
        $this->byCounter,
        $this->byChannel,
        $this->trendData,
    );
}
```

Wire the date inputs to call `filterByDate()` on update.

- [ ] **Step 6: Run tests**

Run: `php artisan test --compact --filter=AdminDashboard`
Expected: All tests PASS

- [ ] **Step 7: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Dashboard/AdminDashboard.php resources/views/livewire/dashboard/admin-dashboard.blade.php tests/Feature/Dashboard/AdminDashboardTest.php
git commit -m "perf: optimize AdminDashboard — single GROUP BY for trend, DB avg, persist computed"
```

---

### Task 2: Optimize PetugasDashboard — Reduce Query Duplication in syncBoard

**Files:**
- Modify: `resources/views/components/dashboard/⚡petugas-dashboard.blade.php`
- Test: existing tests in `tests/Feature/`

**Problem:** `syncBoard()` runs ~8 queries every 10s per officer. `resolveSelectedCounter()` duplicates `user->services()->pluck('queue_pool_id')` on every action (called in `callNext`, `recall`, `skip`, `complete`, `cancel`).

- [ ] **Step 1: Write test for counter resolution using cached data**

```php
// tests/Feature/Dashboard/PetugasDashboardTest.php
it('resolves selected counter from board state without extra queries', function () {
    // Ensure resolveSelectedCounter doesn't re-query services
    // when counters are already loaded in $this->counters
});
```

- [ ] **Step 2: Run test to confirm baseline**

Run: `php artisan test --compact --filter=PetugasDashboard`

- [ ] **Step 3: Refactor `resolveSelectedCounter()` to use cached `$this->counters`**

In `⚡petugas-dashboard.blade.php`, replace `resolveSelectedCounter()`:

```php
private function resolveSelectedCounter(): ?Counter
{
    if ($this->selectedCounterId === null) {
        return null;
    }

    // Use already-loaded counters array instead of re-querying
    $cached = collect($this->counters)
        ->firstWhere('id', $this->selectedCounterId);

    if ($cached === null) {
        return null;
    }

    return Counter::query()
        ->where('is_active', true)
        ->find($this->selectedCounterId);
}
```

This eliminates the `$user->services()->pluck('queue_pool_id')` query from every action call. The authorization check is already implicit — `$this->counters` only contains counters the user has access to (populated by `syncBoard`).

- [ ] **Step 4: Optimize `syncBoard()` — reuse base query with clone**

The method already uses `clone $queueQuery` which is good. But `PetugasStats::build()` re-queries `queue_activities` separately. Cache the stats result on the component for the polling interval:

```php
private ?array $cachedStats = null;
private ?string $cachedStatsDate = null;

private function syncBoard(PetugasStats $petugasStats): void
{
    // ... existing logic up to the stats call ...

    $today = now()->toDateString();
    // Only rebuild stats if date changed or explicitly refreshing
    if ($this->cachedStatsDate !== $today || $this->cachedStats === null) {
        $this->stats = $petugasStats->build($user, $today);
        $this->cachedStats = $this->stats;
        $this->cachedStatsDate = $today;
    } else {
        $this->stats = $this->cachedStats;
    }
}
```

Note: Stats are still refreshed on actions (callNext, etc.) via `refreshBoard()` which will rebuild naturally.

- [ ] **Step 5: Run tests and commit**

```bash
php artisan test --compact --filter=PetugasDashboard
vendor/bin/pint --dirty --format agent
git add resources/views/components/dashboard/⚡petugas-dashboard.blade.php tests/Feature/Dashboard/
git commit -m "perf: reduce PetugasDashboard queries — cache counter resolution, optimize stats"
```

---

### Task 3: Optimize QueueReportBuilder — Move Aggregation to Database

**Files:**
- Modify: `app/Support/Reports/QueueReportBuilder.php`
- Test: `tests/Feature/Reports/` (create if not exists)

**Problem:** `build()` loads ALL tickets for a date range into memory with `->get()`, then groups/counts in PHP using Collections. For large date ranges, this is unbounded memory usage.

- [ ] **Step 1: Write test for report builder output correctness**

```php
// tests/Feature/Reports/QueueReportBuilderTest.php
it('returns correct aggregations from database queries', function () {
    $service = \App\Models\Service::factory()->create();
    $counter = \App\Models\Counter::factory()->create();
    $user = \App\Models\User::factory()->create();

    \App\Models\QueueTicket::factory()->count(5)->create([
        'service_id' => $service->id,
        'counter_id' => $counter->id,
        'created_by' => $user->id,
        'service_date' => today(),
        'status' => \App\Enums\QueueStatus::Completed,
    ]);

    $builder = new \App\Support\Reports\QueueReportBuilder;
    $result = $builder->build(today()->toDateString(), today()->toDateString());

    expect($result['by_service'])->toHaveKey($service->name)
        ->and($result['by_service'][$service->name])->toBe(5)
        ->and($result['by_counter'])->toHaveKey($counter->name)
        ->and($result['by_status'])->toHaveKey('completed');
});
```

- [ ] **Step 2: Run test with current implementation**

Run: `php artisan test --compact --filter=QueueReportBuilder`

- [ ] **Step 3: Replace `build()` with database GROUP BY queries**

```php
public function build(string $from, string $to): array
{
    $dateScope = fn ($query) => $query
        ->whereDate('service_date', '>=', $from)
        ->whereDate('service_date', '<=', $to);

    return [
        'by_service' => QueueTicket::query()
            ->tap($dateScope)
            ->join('services', 'queue_tickets.service_id', '=', 'services.id')
            ->selectRaw('services.name, COUNT(*) as count')
            ->groupBy('services.name')
            ->orderBy('services.name')
            ->pluck('count', 'services.name')
            ->toArray(),

        'by_counter' => QueueTicket::query()
            ->tap($dateScope)
            ->whereNotNull('counter_id')
            ->join('counters', 'queue_tickets.counter_id', '=', 'counters.id')
            ->selectRaw('counters.name, COUNT(*) as count')
            ->groupBy('counters.name')
            ->orderBy('counters.name')
            ->pluck('count', 'counters.name')
            ->toArray(),

        'by_officer' => QueueTicket::query()
            ->tap($dateScope)
            ->join('users', 'queue_tickets.created_by', '=', 'users.id')
            ->selectRaw('users.name, COUNT(*) as count')
            ->groupBy('users.name')
            ->orderBy('users.name')
            ->pluck('count', 'users.name')
            ->toArray(),

        'by_status' => QueueTicket::query()
            ->tap($dateScope)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray(),

        'officer_service_distribution' => $this->buildOfficerServiceDistribution($from, $to),
    ];
}
```

Remove the `groupAndCount()` private method (no longer needed).

- [ ] **Step 4: Run tests and commit**

```bash
php artisan test --compact --filter=QueueReportBuilder
vendor/bin/pint --dirty --format agent
git add app/Support/Reports/QueueReportBuilder.php tests/Feature/Reports/
git commit -m "perf: QueueReportBuilder uses DB GROUP BY instead of loading all tickets into memory"
```

---

### Task 4: Cache TvDisplay Video List & KioskBooking wilayahOptions

**Files:**
- Modify: `app/Livewire/TvDisplay.php`
- Modify: `app/Livewire/KioskBooking.php`
- Test: `tests/Feature/TvDisplay/`

**Problem:** TvDisplay scans filesystem every 5s. KioskBooking runs a LIKE query on `wilayah` table on every render.

- [ ] **Step 1: Cache video list in TvDisplay**

In `app/Livewire/TvDisplay.php`, replace `videos()`:

```php
use Illuminate\Support\Facades\Cache;

protected function videos(): array
{
    try {
        return Cache::remember('tv-display:videos', 60, function (): array {
            $files = Storage::disk('public')->files('videos');
            $allowed = ['mp4', 'webm', 'ogg'];

            return collect($files)
                ->filter(fn (string $file): bool => in_array(
                    strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                    $allowed,
                    true,
                ))
                ->map(fn (string $file): string => asset('storage/'.$file))
                ->sort()
                ->values()
                ->all();
        });
    } catch (\Throwable $e) {
        return [];
    }
}
```

- [ ] **Step 2: Add `persist: true` to KioskBooking wilayahOptions**

In `app/Livewire/KioskBooking.php`:

```php
#[Computed(persist: true)]
public function wilayahOptions(): Collection
{
    // ... existing implementation unchanged ...
}
```

- [ ] **Step 3: Move inline `<style>` from TvDisplay to app.css**

Move the keyframe animations from `resources/views/livewire/tv-display.blade.php` lines 1-12 to `resources/css/app.css`:

```css
/* TV Display animations */
@keyframes pulse-gentle {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
    50% { box-shadow: 0 0 0 12px rgba(245, 158, 11, 0); }
}
.animate-pulse-gentle { animation: pulse-gentle 2s ease-in-out infinite; }

@keyframes marquee {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}
.animate-marquee { animation: marquee 20s linear infinite; }
```

Remove the `<style>` block from the blade template.

- [ ] **Step 4: Run tests and commit**

```bash
php artisan test --compact --filter=TvDisplay
vendor/bin/pint --dirty --format agent
git add app/Livewire/TvDisplay.php app/Livewire/KioskBooking.php resources/views/livewire/tv-display.blade.php resources/css/app.css
git commit -m "perf: cache TvDisplay videos, persist wilayahOptions, move inline styles to app.css"
```

---

## Chunk 2: Database Index & Asset Optimization

### Task 5: Add Missing Database Index on queue_activities

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_user_id_created_at_index_to_queue_activities_table.php`

**Problem:** `PetugasStats::build()` queries `WHERE user_id = ? AND DATE(created_at) = ?` — the existing `user_id` foreign key index only covers the first column. A composite index `(user_id, created_at)` will cover this query as a covering index.

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration add_user_id_created_at_index_to_queue_activities_table --table=queue_activities --no-interaction
```

- [ ] **Step 2: Write migration content**

```php
public function up(): void
{
    Schema::table('queue_activities', function (Blueprint $table) {
        $table->index(['user_id', 'created_at'], 'queue_activities_user_id_created_at_index');
    });
}

public function down(): void
{
    Schema::table('queue_activities', function (Blueprint $table) {
        $table->dropIndex('queue_activities_user_id_created_at_index');
    });
}
```

- [ ] **Step 3: Run migration**

```bash
php artisan migrate --no-interaction
```

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "perf: add composite index (user_id, created_at) on queue_activities"
```

---

### Task 6: Vite Code Splitting — Separate Entry Points Per Layout

**Files:**
- Modify: `vite.config.js`
- Create: `resources/css/tv-display.css` (minimal CSS for TV layout)
- Create: `resources/js/tv-display.js` (minimal JS for TV layout)
- Create: `resources/css/kiosk.css` (minimal CSS for kiosk layout)
- Create: `resources/js/kiosk.js` (minimal JS for kiosk layout)
- Modify: `resources/views/layouts/tv-display.blade.php`
- Modify: `resources/views/layouts/kiosk.blade.php`

**Problem:** TV Display and Kiosk pages load the full admin bundle (Flux UI Pro JS/CSS, all admin components). They need far less code.

- [ ] **Step 1: Create minimal entry points for TV Display**

`resources/css/tv-display.css`:
```css
@import "tailwindcss";
```

`resources/js/tv-display.js`:
```js
// Minimal JS for TV display — only Livewire, no Flux UI Pro
```

- [ ] **Step 2: Create minimal entry points for Kiosk**

`resources/css/kiosk.css`:
```css
@import "tailwindcss";
```

`resources/js/kiosk.js`:
```js
// Minimal JS for kiosk — only Livewire
```

- [ ] **Step 3: Update `vite.config.js` with multiple entry points**

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/tv-display.css',
                'resources/js/tv-display.js',
                'resources/css/kiosk.css',
                'resources/js/kiosk.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: true,
        cors: true,
        hmr: {
            host: '192.168.9.11',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        emptyOutDir: false,
    },
});
```

- [ ] **Step 4: Update TV Display layout to use its own entry**

In `resources/views/layouts/tv-display.blade.php`, change:
```blade
@vite(['resources/css/tv-display.css', 'resources/js/tv-display.js'])
```

- [ ] **Step 5: Update Kiosk layout to use its own entry**

In `resources/views/layouts/kiosk.blade.php`, change:
```blade
@vite(['resources/css/kiosk.css', 'resources/js/kiosk.js'])
```

- [ ] **Step 6: Build and test**

```bash
npm run build
```

Verify TV display and kiosk pages still work. Check that Livewire reactivity functions correctly with the lighter bundles.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add vite.config.js resources/css/ resources/js/ resources/views/layouts/
git commit -m "perf: split Vite entry points for TV Display and Kiosk layouts"
```

---

### Task 7: Production Cache Optimization

**Files:**
- No file changes — runtime commands only

**Problem:** Standard Laravel production optimizations may not be running.

- [ ] **Step 1: Run Laravel optimize command on production**

```bash
php artisan optimize
```

This runs `config:cache`, `route:cache`, `view:cache`, and `event:cache`.

- [ ] **Step 2: Verify caches are created**

```bash
php artisan optimize:status
```

- [ ] **Step 3: Document in deployment**

Ensure `php artisan optimize` is in the deployment script. No commit needed — this is an ops task.

---

## Expected Impact Summary

| Task | Before | After | Queries Saved |
|------|--------|-------|---------------|
| Task 1: AdminDashboard | 9+ queries/30s | 9 queries on load, persisted on poll | ~9 per poll cycle |
| Task 1: trendData | 7 queries | 1 query | 6 per render |
| Task 1: avgWait | N rows loaded + PHP math | 1 AVG() query | N-1 rows eliminated |
| Task 2: PetugasDashboard | 8 queries/10s + duplicated per action | ~6 queries/10s, no duplication | ~2-4 per action |
| Task 3: QueueReportBuilder | All rows loaded to PHP | 4 GROUP BY queries | Memory: O(rows) → O(groups) |
| Task 4: TvDisplay videos | Disk scan every 5s | Cached 60s | ~11 disk scans/min eliminated |
| Task 5: DB Index | Full table scan on queue_activities | Index seek | Major on growing tables |
| Task 6: Vite splitting | Full bundle on all pages | ~40-60% smaller bundles for TV/Kiosk | Faster page load |
