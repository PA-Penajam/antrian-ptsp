# Issues & Gotchas — public-ui-overhaul

## [2026-03-07] Known Issues

### welcome.blade.php
- Has `dark:` icon classes (e.g. `dark:text-zinc-400`) — remove all
- Uses `<x-layouts::public>` correctly ✅
- Currently NO service catalog — just 3 static cards
- Controller `index()` method doesn't exist yet (added in Task 8)

### booking.blade.php
- Has `dark:bg-green-900/20`, `dark:border-green-800`, etc. — remove all
- Shows ticket inline after POST — MUST redirect to confirmation page instead
- Single-step form — needs multi-step with Alpine.js
- `storeBooking()` currently returns view, needs to redirect

### lookup.blade.php
- Has `dark:bg-blue-900/20`, `dark:border-blue-200`, etc. — remove all
- Uses raw `ucfirst($ticket->status)` — MUST use `$ticket->status->label()` + `->color()`
- Uses string match for badge colors — replace with enum colors

### routes/web.php
- `Route::view('/', 'welcome')->name('home')` — MUST change to controller method
- Missing confirmation route

### PublicQueueController
- `booking()` only queries `booking_enabled=true` — correct for booking
- `storeBooking()` returns view — MUST redirect to confirmation
- `lookup()` doesn't eager load `service`, `counter`, `queuePool` — needs update
- Missing `index()` method (for landing page data)
- Missing `confirmation()` method
- Missing `calculateQueuePosition()` helper
