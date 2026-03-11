# Decisions — nextjs-public-pages

## [2026-03-09] Architecture Decisions

### API Route Registration
- Use `->withRouting(api: __DIR__.'/../routes/api.php')` in bootstrap/app.php
- NO Sanctum install, NO auth middleware on public routes
- CORS: Use Laravel built-in HandleCors middleware, configure via config/cors.php

### Resource Design
- ServiceResource: expose `id` (needed for booking payload), name, code, slug, description, requirements, booking_enabled, daily_quota, remaining_quota (computed)
- QueueTicketResource: expose ticket_number (NOT id), service_date, visitor_name, status (raw value), status_label, service (ServiceResource), queue_position (null unless Waiting), counter_name, timestamps

### API Controller Naming
- `App\Http\Controllers\Api\PublicServiceController` — institution + services endpoints
- `App\Http\Controllers\Api\PublicQueueController` — booking + lookup + ticket detail endpoints

### Rate Limiting
- `api:booking` → 10 requests/minute
- `api:read` → 60 requests/minute
- Configure in bootstrap/app.php or routes/api.php using ->middleware('throttle:10,1')

### Booking Validation
- service_date: after_or_equal:today (same-day allowed), before_or_equal:+14 days, WeekdayOnly rule
- Quota check: use withValidator() closure, count non-cancelled tickets for service+date
- is_active and booking_enabled: check in withValidator() after resolving the service

### TypeScript API Client
- Base URL: process.env.NEXT_PUBLIC_API_URL (default: http://localhost:8000/api)
- Use native fetch (no axios)
- Throw ApiError on non-2xx responses
- All functions typed with return types

### Worktree
- Work directly in main worktree: /media/moohard/windows/laragon/www/antrian-ptsp
- No new worktree needed (no active branches in conflict)
- Keputusan audit F1: gunakan eksekusi test via laravel-boost_tinker sesuai requirement task, dengan env override SQLite in-memory agar aman dan deterministik.
