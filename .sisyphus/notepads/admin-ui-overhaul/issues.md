# Issues & Gotchas

## 2026-03-08 Session: ses_336972af3ffeNRQxCYruthTCWe

### Known Issues (from Metis review in plan)
- AdminDashboard classless component pattern — Livewire class naming must match dashboard.admin-dashboard
- User model does NOT have counterSessions() relationship — must query CounterSession directly
- Delete policy: block on relations (not hard delete) to prevent breaking queue history
- Search/filter must reset pagination when result set changes
- Browser TV might block autoplay/audio/animations — needs graceful fallback
- Shared password rotation must invalidate active sessions
- Theme toggle must NOT persist globally on shared devices (kiosk/TV)

### Technical Notes
- Middleware 'role' already registered in bootstrap/app.php:14
- Admin routes in routes/web.php lines 48-61 (no names)
- Public routes lines 14-19 already use ->name()
- QueueDisplay.php uses polling — copy pattern for TV Display

## 2026-03-08 - F1 plan compliance audit
- `routes/web.php` still leaves `/admin/roles` and `/admin/izin-layanan` unnamed, so "named routes for all admin endpoints" is only partial.
- `resources/views/pages/admin/loket/index.blade.php` and `resources/views/pages/admin/users/index.blade.php` do not implement search, pagination, or sorting; `resources/views/pages/admin/layanan/index.blade.php` has search + pagination but no sorting.
- `app/Http/Middleware/CheckModulePassword.php` checks only the timestamp and does not verify the `*_authenticated` flag, so the password gate is weaker than intended.
- `resources/views/livewire/tv-display.blade.php` renders duplicated header and duplicated history sections, which reduces polish for the TV display module.
- Branding is inconsistent with the DoD phrase: `resources/views/components/app-logo.blade.php` uses `Antrian PTSP`, while the full `Antrian PTSP Pengadilan Agama Penajam` string is not applied broadly across admin views.
- `php artisan test --compact` cannot complete in the current environment because `tests/Pest.php` blocks non-test MySQL databases (`db_antrian_ptsp`).
