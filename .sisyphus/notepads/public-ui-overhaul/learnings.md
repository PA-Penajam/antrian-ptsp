# Learnings — public-ui-overhaul

## [2026-03-07] Wave 1 Complete

### Architecture Facts
- Layout: `<x-layouts::public>` (double colon syntax) — file at `resources/views/layouts/public.blade.php`
- Layout uses `{{ $slot }}` + `@yield('content')` — use slot/component syntax
- `config('institution.name')` → "Pengadilan Agama" ✅
- `config('institution.operating_hours')` → from .env INSTITUTION_OPERATING_HOURS
- `QueueStatus` enum: 6 cases — booked, waiting, called, completed, cancelled, skipped
  - Use `$ticket->status->label()` for Indonesian labels
  - Use `$ticket->status->color()` for Flux badge colors
- Ticket format: `A0001` (letter_code + 4-digit sequence_number)
- `sequence_number` is pool-scoped (NOT globally unique)
- `ticket_number` is unique per pool+date combo

### Controllers & Routes
- `PublicQueueController` at `app/Http/Controllers/PublicQueueController.php`
- Route `/` is `Route::view('/', 'welcome')->name('home')` — MUST change to controller method
- Route `/antrian` → `booking()` / `storeBooking()`
- Route `/antrian/cek` → `lookup()`
- NEW route needed: `GET /antrian/konfirmasi/{queueTicket}` named `queue.confirmation`

### Models
- `Service` model: `is_active`, `booking_enabled`, `walk_in_enabled`, `description`, `requirements`, `daily_quota`, `sort_order`, `letter_code`
- `QueueTicket` model: `ticket_number`, `status` (cast to QueueStatus enum), `service_id`, `service_date`, `visitor_name`, `sequence_number`, `counter_id`, `called_at`
- `QueuePool` model: pools are per-service

### Booking Flow
- `CreateQueueTicket` action handles ticket creation
- After booking → redirect to confirmation page (NOT return view directly)
- `StorePublicQueueBookingRequest` has validation rules

### Lookup
- Requires BOTH `ticket_number` AND `service_date` (no defaults)
- `LookupQueueTicketRequest` has validation rules (no regex on ticket_number)
- Queue position: count waiting tickets with lower sequence_number in same pool+date

### Guardrails
- ZERO `dark:` classes in public views
- NO PII on display page (no visitor name/phone)
- Status `booked` = no queue position shown (not checked in yet)
- `daily_quota`: informational only, NO hard enforcement

## [2026-03-07] Task 9 — Queue display

### Display Page
- `/display` can be routed directly to `App\Livewire\QueueDisplay` as a full-page Livewire component via `Route::get('/display', QueueDisplay::class)`
- Livewire 4 polling syntax works as `wire:poll.5000ms` on the component root for 5-second refreshes
- `QueueDisplay` should query current calls with `QueueStatus::Called` and keep history limited to `called_at` rows only
- `resources/views/pages/display/index.blade.php` can stay as a thin `<livewire:queue-display />` wrapper for compatibility, even when the route points to the full-page component
