# Admin UI/UX Overhaul — Antrian PTSP Pengadilan Agama Penajam

## TL;DR

> **Quick Summary**: Overhaul seluruh admin panel dari UI sederhana (2 sidebar items, no CRUD, empty dashboard) menjadi panel profesional lengkap dengan analytics dashboard (Flux UI Pro charts), full CRUD + modals untuk semua entity, 2 modul standalone baru (Kiosk touchscreen + TV Display landscape), dan branding yang benar.
> 
> **Deliverables**:
> - Rich sidebar navigation dengan semua halaman admin terekspos
> - Analytics dashboard dengan stat cards + Flux UI Pro charts (date range, per layanan, per loket, per channel)
> - Full CRUD (Create/Read/Edit modal/Delete with relation-block) untuk Layanan, Loket, Users
> - Merge Roles & Izin Layanan ke halaman Users (old routes redirect 301)
> - Kiosk Module: custom touchscreen UI, card layout, animasi feedback, password-protected
> - TV Display Module: custom landscape UI, call animations, password-protected
> - Light/dark toggle (admin), breadcrumbs, empty states, activity log widget
> - Branding "Antrian PTSP Pengadilan Agama Penajam" di seluruh app
> - TDD: Pest v4 tests untuk semua fitur baru
> 
> **Estimated Effort**: XL
> **Parallel Execution**: YES — 6 waves
> **Critical Path**: Task 1 (routes) → Task 2 (nav) → Task 5 (dashboard) → Task 14 (kiosk middleware) → Task 15 (kiosk UI) → Task 18 (TV UI) → Final Verification

---

## Context

### Original Request
User mengeluh admin interface sangat sederhana — sidebar hanya 2 item, dashboard kosong, tidak ada full CRUD. Minta overhaul total termasuk modul Kiosk dan TV Display terpisah dengan password protection.

### Interview Summary
**Key Discussions**:
- **Scope**: Semua prioritas (Kritis + Mayor + Enhancement) dikerjakan sekaligus
- **Branding**: "Antrian PTSP Pengadilan Agama Penajam" (bukan "Laravel Starter Kit")
- **Dashboard**: Full analytics — total harian + date range picker + per layanan + per loket + per channel (online_booking/assisted_same_day/walk_in_kiosk)
- **Charts**: Gunakan Flux UI Pro built-in charts (TIDAK install library eksternal)
- **Roles/Izin**: Digabung ke Users page, old routes redirect 301
- **Kiosk**: UI khusus touchscreen (card UI besar, font besar, animasi tombol)
- **TV Display**: UI khusus landscape (animasi panggilan)
- **Password**: Satu shared password untuk Kiosk dan TV Display, stored di `.env` + `config/kiosk.php`
- **Delete**: Block jika entity punya active relations (tampilkan error message)
- **Activity Log**: Dari `queue_activities` table saja
- **Tests**: TDD approach dengan Pest v4
- **Theme**: Light/dark toggle admin only, localStorage persistence
- **Session**: Kiosk/TV default 24 jam

**Research Findings**:
- Flux UI Pro v2.13.0 punya komponen chart lengkap (`<flux:chart>`, line, bar, area, stack, legend, tooltip, cursor, viewport, zero-line, summary, group)
- AdminDashboard adalah classless Livewire/Volt-style component (`resources/views/components/dashboard/admin-dashboard.blade.php`), bukan missing class
- 4 dari 5 halaman admin tersembunyi (tidak ada entry di sidebar)
- Admin routes TIDAK punya named routes
- Middleware `role` sudah registered di `bootstrap/app.php:14`
- Existing `/display` menggunakan Livewire `QueueDisplay` (5 active + 10 recent + TTS)
- Existing `/antrian` menggunakan 3-step wizard (Flux UI + Alpine.js)

### Metis Review
**Identified Gaps** (addressed):
- Classless component pattern validated — AdminDashboard Blade file exists, mereferensi `<livewire:dashboard.admin-dashboard />`
- Delete policy: block on relations (bukan hard delete) untuk cegah break queue history
- Search/filter harus reset pagination saat result set berubah
- Empty states wajib untuk charts, tables, dan activity log
- Browser TV mungkin block autoplay/audio/animations — perlu fallback graceful
- Shared password rotation harus invalidate active sessions
- Theme toggle di shared device (kiosk/TV) tidak boleh persist globally

---

## Work Objectives

### Core Objective
Transformasi admin panel dari prototype sederhana menjadi fully-featured professional interface dengan rich analytics, full CRUD operations, dan 2 modul standalone (Kiosk + TV Display).

### Concrete Deliverables
- `resources/views/layouts/app/sidebar.blade.php` — Expanded admin navigation
- `resources/views/layouts/app/header.blade.php` — Synced mobile navigation
- `resources/views/components/app-logo.blade.php` — Updated branding
- `resources/views/components/dashboard/admin-dashboard.blade.php` — Rich analytics dashboard
- `app/Livewire/Dashboard/AdminDashboard.php` — Dashboard Livewire component with chart data
- Admin CRUD views with edit modals, delete with relation check, pagination, search, sort
- `config/kiosk.php` — Shared password configuration
- `app/Http/Middleware/CheckModulePassword.php` — Password gate middleware
- `resources/views/pages/kiosk/` — Custom touchscreen UI
- `resources/views/pages/tv-display/` — Custom landscape TV UI
- Pest test files for all new features

### Definition of Done
- [ ] `php artisan test --compact` → all tests pass, 0 failures
- [ ] All admin sidebar items visible and navigable
- [ ] Dashboard shows stat cards + charts with real data
- [ ] All tables have pagination, search, sort, empty states
- [ ] Edit modal and delete (with relation block) work for all entities
- [ ] `/kiosk` and `/tv-display` require password, show custom UI
- [ ] Old routes `/admin/roles` and `/admin/izin-layanan` redirect 301 to `/admin/users`
- [ ] Branding shows "Antrian PTSP Pengadilan Agama Penajam" everywhere
- [ ] Light/dark toggle works with localStorage persistence

### Must Have
- Named routes untuk semua admin endpoints
- Sidebar dan header navigation sinkron
- Charts menggunakan Flux UI Pro components saja (no external libs)
- Delete entity harus block jika ada relasi aktif
- Password middleware shared antara Kiosk dan TV Display
- Kiosk UI: card layout touchscreen-friendly, animasi feedback tombol
- TV Display: landscape layout, animasi panggilan
- TDD: Pest test sebelum implementasi
- Empty states untuk semua tabel dan chart kosong

### Must NOT Have (Guardrails)
- ❌ JANGAN ubah public pages existing (`/`, `/antrian`, `/display`)
- ❌ JANGAN install chart/table library eksternal (gunakan Flux UI Pro built-in)
- ❌ JANGAN ubah database schema (kecuali config file baru)
- ❌ JANGAN ubah queue engine / nomor antrian logic
- ❌ JANGAN buat audit trail baru (cukup `queue_activities`)
- ❌ JANGAN hard-delete entity yang punya relasi aktif
- ❌ JANGAN ubah role/permission model/enum
- ❌ JANGAN integrate WebSocket/Reverb
- ❌ JANGAN implement print/hardware integration
- ❌ JANGAN buat generic variable names (data/result/item/temp)
- ❌ JANGAN tambah over-documentation/JSDoc berlebihan
- ❌ JANGAN buat abstraction prematur yang tidak diminta

---

## Verification Strategy (MANDATORY)

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.

### Test Decision
- **Infrastructure exists**: YES — Pest v4 sudah terinstall (`pestphp/pest v4`)
- **Automated tests**: TDD (test first, then implement)
- **Framework**: Pest v4 (`php artisan test --compact`)
- **Each task follows**: RED (failing test) → GREEN (minimal impl) → REFACTOR

### QA Policy
Every task MUST include agent-executed QA scenarios.
Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

- **Frontend/UI**: Use Playwright (playwright skill) — Navigate, interact, assert DOM, screenshot
- **Backend/API**: Use Bash (curl/tinker) — Send requests, assert status + response fields
- **Middleware**: Use Bash (curl) — Test protected routes, verify redirects
- **Livewire Components**: Use Pest Livewire testing (`Livewire::test()`)

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Foundation — routes, config, branding, types):
├── Task 1: Named routes + route restructuring [quick]
├── Task 2: Branding update (app-logo) [quick]
├── Task 3: config/kiosk.php + .env entries [quick]
├── Task 4: CheckModulePassword middleware [coding]
└── Task 5: Light/dark theme toggle mechanism [quick]

Wave 2 (Navigation + Dashboard — depends on Wave 1):
├── Task 6: Sidebar + header navigation overhaul (depends: 1) [visual-engineering]
├── Task 7: AdminDashboard Livewire component + stat cards (depends: 1) [coding]
├── Task 8: Dashboard charts — line/bar/area with date range (depends: 7) [visual-engineering]
└── Task 9: Activity log widget (depends: 7) [coding]

Wave 3 (CRUD Overhaul — depends on Wave 1):
├── Task 10: Layanan CRUD — edit modal + delete + pagination + search + sort (depends: 1) [coding]
├── Task 11: Loket CRUD — full CRUD + pagination + search + sort (depends: 1) [coding]
├── Task 12: Users CRUD — edit modal + delete + merge roles/permissions (depends: 1) [coding]
└── Task 13: Route redirects — /admin/roles & /admin/izin-layanan → /admin/users (depends: 1, 12) [quick]

Wave 4 (Kiosk Module — depends on Wave 1):
├── Task 14: Kiosk password gate — login view + session (depends: 3, 4) [coding]
├── Task 15: Kiosk booking UI — 3-step touchscreen wizard (depends: 14) [visual-engineering]
└── Task 16: Kiosk animations + UX polish (depends: 15) [visual-engineering]

Wave 5 (TV Display Module — depends on Wave 1):
├── Task 17: TV Display password gate — login view + session (depends: 3, 4) [coding]
├── Task 18: TV Display landscape UI — queue board + call animation (depends: 17) [visual-engineering]
└── Task 19: TV Display auto-refresh + graceful fallbacks (depends: 18) [coding]

Wave 6 (Integration + Polish — depends on all):
├── Task 20: Breadcrumbs component + empty states audit (depends: 6-19) [visual-engineering]
├── Task 21: Cross-module integration test (depends: all) [deep]

Wave FINAL (After ALL tasks — independent review, 4 parallel):
├── Task F1: Plan compliance audit (oracle)
├── Task F2: Code quality review (unspecified-high)
├── Task F3: Real manual QA (unspecified-high)
└── Task F4: Scope fidelity check (deep)

Critical Path: Task 1 → Task 6 → Task 7 → Task 8 → Task 20 → Task 21 → F1-F4
Parallel Speedup: ~65% faster than sequential
Max Concurrent: 5 (Waves 2+3 partially overlap after dependencies met)
```

### Dependency Matrix

| Task | Depends On | Blocks | Wave |
|------|-----------|--------|------|
| 1 | — | 6,7,10,11,12,13 | 1 |
| 2 | — | — | 1 |
| 3 | — | 14,17 | 1 |
| 4 | — | 14,17 | 1 |
| 5 | — | 6 | 1 |
| 6 | 1,5 | 20 | 2 |
| 7 | 1 | 8,9 | 2 |
| 8 | 7 | 20 | 2 |
| 9 | 7 | 20 | 2 |
| 10 | 1 | 21 | 3 |
| 11 | 1 | 21 | 3 |
| 12 | 1 | 13,21 | 3 |
| 13 | 1,12 | 21 | 3 |
| 14 | 3,4 | 15 | 4 |
| 15 | 14 | 16 | 4 |
| 16 | 15 | 21 | 4 |
| 17 | 3,4 | 18 | 5 |
| 18 | 17 | 19 | 5 |
| 19 | 18 | 21 | 5 |
| 20 | 6,8,9,10,11,12 | 21 | 6 |
| 21 | all | F1-F4 | 6 |

### Agent Dispatch Summary

- **Wave 1**: 5 tasks — T1→`quick`, T2→`quick`, T3→`quick`, T4→`coding`, T5→`quick`
- **Wave 2**: 4 tasks — T6→`visual-engineering`, T7→`coding`, T8→`visual-engineering`, T9→`coding`
- **Wave 3**: 4 tasks — T10→`coding`, T11→`coding`, T12→`coding`, T13→`quick`
- **Wave 4**: 3 tasks — T14→`coding`, T15→`visual-engineering`, T16→`visual-engineering`
- **Wave 5**: 3 tasks — T17→`coding`, T18→`visual-engineering`, T19→`coding`
- **Wave 6**: 2 tasks — T20→`visual-engineering`, T21→`deep`
- **FINAL**: 4 tasks — F1→`oracle`, F2→`unspecified-high`, F3→`unspecified-high`, F4→`deep`

---

## TODOs

> Implementation + Test = ONE Task. Never separate.
> EVERY task MUST have: Recommended Agent Profile + Parallelization info + QA Scenarios.

### Wave 1: Foundation

- [ ] 1. Named Routes + Route Restructuring

  **What to do**:
  - Add `->name()` to ALL admin routes in `routes/web.php`
  - Naming convention: `admin.layanan.index`, `admin.layanan.store`, `admin.layanan.update`, `admin.layanan.destroy`
  - Same pattern for loket, users
  - Add DELETE routes: `Route::delete('/admin/layanan/{service}', ...)->name('admin.layanan.destroy')`
  - Add DELETE routes for loket and users
  - Add new route groups for kiosk and tv-display (placeholders — implementation in later tasks)
  - Write Pest tests FIRST: test route names exist, test route methods, test middleware applied

  **Must NOT do**:
  - Do NOT change any controller logic yet
  - Do NOT modify public routes (`/`, `/antrian`, `/display`)
  - Do NOT add new controllers yet (just routes)

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Straightforward route definition, no complex logic
  - **Skills**: [`pest-testing`]
    - `pest-testing`: Writing route assertion tests
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: No Livewire work in route definitions

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 2, 3, 4, 5)
  - **Blocks**: Tasks 6, 7, 10, 11, 12, 13
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `routes/web.php:48-61` — Current admin route group (no names, no delete routes). Follow the existing `Route::middleware([...])` grouping pattern.
  - `routes/web.php:14-19` — Public routes already have `->name()`. Follow same naming style.
  - `routes/web.php:29-33` — Frontdesk routes. Note: they also lack names. Don't fix these (out of scope).

  **API/Type References**:
  - `app/Enums/UserRole.php` — Role enum values used in route middleware
  - `app/Http/Controllers/Admin/ServiceManagementController.php` — Existing controller with index/store/update methods. DELETE method will be added in Task 10.
  - `app/Http/Controllers/Admin/CounterManagementController.php` — index/update only. DELETE will be added in Task 11.
  - `app/Http/Controllers/Admin/UserManagementController.php` — index/store/update/roles/servicePermissions. DELETE will be added in Task 12.

  **External References**:
  - Flux UI docs are not needed here; pure Laravel routing.

  **WHY Each Reference Matters**:
  - `routes/web.php:48-61`: This is THE file being modified. Executor must see current structure to add names and delete routes without breaking existing route order.
  - Controllers: Executor needs to know which methods exist already to correctly map `->name()` to controller actions.

  **Acceptance Criteria**:
  - [ ] `php artisan route:list --path=admin` shows all routes with names
  - [ ] Test file: `tests/Feature/Admin/AdminRouteTest.php`
  - [ ] `php artisan test --compact --filter=AdminRouteTest` → PASS

  **QA Scenarios:**

  ```
  Scenario: All admin routes have names
    Tool: Bash
    Preconditions: Application routes loaded
    Steps:
      1. Run `php artisan route:list --path=admin --columns=method,uri,name`
      2. Assert every row has a non-empty name column
      3. Assert names include: admin.layanan.index, admin.layanan.store, admin.layanan.update, admin.layanan.destroy
      4. Assert names include: admin.loket.index, admin.loket.update, admin.loket.destroy
      5. Assert names include: admin.users.index, admin.users.store, admin.users.update, admin.users.destroy
    Expected Result: All admin routes listed with proper names, no unnamed routes
    Failure Indicators: Empty name column, missing routes, or artisan error
    Evidence: .sisyphus/evidence/task-1-route-names.txt

  Scenario: Delete routes respond with correct method
    Tool: Bash (curl)
    Preconditions: App running, admin user authenticated
    Steps:
      1. curl -X DELETE /admin/layanan/999 (non-existent) with CSRF — expect 404 or redirect (not 405 Method Not Allowed)
      2. curl -X DELETE /admin/loket/999 — expect 404 or redirect (not 405)
      3. curl -X DELETE /admin/users/999 — expect 404 or redirect (not 405)
    Expected Result: DELETE method accepted (not 405), 404 for non-existent resources
    Failure Indicators: 405 Method Not Allowed
    Evidence: .sisyphus/evidence/task-1-delete-routes.txt
  ```

  **Commit**: YES (groups with Wave 1)
  - Message: `chore(admin): add named routes with delete endpoints for all admin entities`
  - Files: `routes/web.php`, `tests/Feature/Admin/AdminRouteTest.php`
  - Pre-commit: `php artisan test --compact --filter=AdminRouteTest`

- [ ] 2. Branding Update

  **What to do**:
  - Replace "Laravel Starter Kit" with "Antrian PTSP" in `resources/views/components/app-logo.blade.php` (lines 6 and 12)
  - Verify no other files reference "Laravel Starter Kit"
  - Write Pest test to verify branding appears in rendered HTML

  **Must NOT do**:
  - Do NOT change logo icon/SVG
  - Do NOT modify layout structure

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: 2-line string replacement + 1 search
  - **Skills**: [`pest-testing`]
    - `pest-testing`: Writing view assertion test
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: No styling changes

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 3, 4, 5)
  - **Blocks**: None directly
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `resources/views/components/app-logo.blade.php:6` — `<flux:sidebar.brand name="Laravel Starter Kit">` → change to `name="Antrian PTSP"`
  - `resources/views/components/app-logo.blade.php:12` — `<flux:brand name="Laravel Starter Kit">` → change to `name="Antrian PTSP"`

  **WHY Each Reference Matters**:
  - These are the ONLY two locations where the brand name string appears. Both must be updated simultaneously.

  **Acceptance Criteria**:
  - [ ] `grep -r "Laravel Starter Kit" resources/` returns 0 results
  - [ ] Brand displays "Antrian PTSP" in sidebar and header

  **QA Scenarios:**

  ```
  Scenario: Branding shows correct name
    Tool: Bash (grep)
    Preconditions: Files modified
    Steps:
      1. Run `grep -r "Laravel Starter Kit" resources/views/` — expect 0 matches
      2. Run `grep -r "Antrian PTSP" resources/views/components/app-logo.blade.php` — expect 2 matches
    Expected Result: Zero occurrences of old branding, 2 occurrences of new branding
    Failure Indicators: Any match for "Laravel Starter Kit"
    Evidence: .sisyphus/evidence/task-2-branding-grep.txt

  Scenario: Branding renders in browser
    Tool: Playwright
    Preconditions: App running, logged in as admin
    Steps:
      1. Navigate to /dashboard
      2. Assert sidebar contains text "Antrian PTSP"
      3. Screenshot sidebar area
    Expected Result: "Antrian PTSP" visible in sidebar brand area
    Failure Indicators: "Laravel Starter Kit" visible or brand text missing
    Evidence: .sisyphus/evidence/task-2-branding-screenshot.png
  ```

  **Commit**: YES (groups with Wave 1)
  - Message: `fix(branding): replace Laravel Starter Kit with Antrian PTSP`
  - Files: `resources/views/components/app-logo.blade.php`
  - Pre-commit: `grep -r "Laravel Starter Kit" resources/`

- [ ] 3. Kiosk/TV Display Configuration

  **What to do**:
  - Create `config/kiosk.php` with:
    ```php
    return [
        'password' => env('MODULE_PASSWORD', 'ptsp2024'),
        'session_lifetime' => env('MODULE_SESSION_LIFETIME', 1440), // 24 hours in minutes
    ];
    ```
  - Add `MODULE_PASSWORD` and `MODULE_SESSION_LIFETIME` to `.env.example`
  - Write Pest test: config values readable, defaults work

  **Must NOT do**:
  - Do NOT add entries to `.env` (only `.env.example`)
  - Do NOT create middleware yet (that's Task 4)

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Single config file creation
  - **Skills**: [`pest-testing`]
    - `pest-testing`: Config value test
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: No UI work

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 4, 5)
  - **Blocks**: Tasks 14, 17
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `config/` directory — Follow existing config file patterns (e.g. `config/app.php`, `config/auth.php`)
  - `.env.example` — Follow existing env var naming convention (uppercase, underscored)

  **WHY Each Reference Matters**:
  - Existing config files show the return-array pattern to follow. `.env.example` shows the naming convention.

  **Acceptance Criteria**:
  - [ ] `config/kiosk.php` exists with correct structure
  - [ ] `config('kiosk.password')` returns default value
  - [ ] `config('kiosk.session_lifetime')` returns 1440
  - [ ] `.env.example` has MODULE_PASSWORD and MODULE_SESSION_LIFETIME

  **QA Scenarios:**

  ```
  Scenario: Config file returns correct defaults
    Tool: Bash (tinker)
    Preconditions: config/kiosk.php created
    Steps:
      1. Run `php artisan tinker --execute="echo config('kiosk.password');"`
      2. Assert output is "ptsp2024"
      3. Run `php artisan tinker --execute="echo config('kiosk.session_lifetime');"`
      4. Assert output is "1440"
    Expected Result: Default password and session lifetime returned correctly
    Failure Indicators: null or empty output
    Evidence: .sisyphus/evidence/task-3-config-defaults.txt
  ```

  **Commit**: YES (groups with Wave 1)
  - Message: `chore(config): add kiosk module configuration with password and session settings`
  - Files: `config/kiosk.php`, `.env.example`
  - Pre-commit: `php artisan test --compact --filter=KioskConfig`

- [ ] 4. CheckModulePassword Middleware

  **What to do**:
  - Create `app/Http/Middleware/CheckModulePassword.php`
  - Logic: Middleware accepts a `module` parameter (e.g., `module.password:kiosk` or `module.password:tv-display`). Session key pattern: `{module}_authenticated` (e.g., `kiosk_authenticated`, `tv_display_authenticated`). Check session key — if not set or expired, redirect to `/{module}/login`
  - Session lifetime: configurable via `config('kiosk.session_lifetime')`
  - Password validation: compare with `config('kiosk.password')`
  - On password change (env update): invalidate session by storing password hash timestamp
  - Register middleware alias `module.password` in `bootstrap/app.php`
  - Write Pest tests FIRST: unauthenticated redirect, session-set redirect pass-through, session expiry redirect

  **Must NOT do**:
  - Do NOT create password form views (that's Task 14/17)
  - Do NOT add routes yet

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Middleware logic with session handling and security considerations
  - **Skills**: [`pest-testing`, `developing-with-fortify`]
    - `pest-testing`: TDD for middleware behavior
    - `developing-with-fortify`: Authentication/middleware patterns in Laravel
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: No UI, just backend middleware

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 3, 5)
  - **Blocks**: Tasks 14, 17
  - **Blocked By**: None (middleware standalone, config values can use fallback)

  **References**:

  **Pattern References**:
  - `app/Http/Middleware/EnsureUserHasRole.php` — Existing custom middleware pattern. Follow same structure for constructor, handle method, and registration approach.
  - `bootstrap/app.php:14-17` — Where to register middleware alias. Add `'module.password' => CheckModulePassword::class` to the alias array.

  **API/Type References**:
  - `config('kiosk.password')` — From Task 3's config file
  - `config('kiosk.session_lifetime')` — Session duration in minutes

  **WHY Each Reference Matters**:
  - `EnsureUserHasRole.php`: Shows the project's middleware conventions (namespace, handle signature, registration pattern). Copy this structure.
  - `bootstrap/app.php`: Shows exactly where and how to register middleware aliases.

  **Acceptance Criteria**:
  - [ ] Middleware class exists at `app/Http/Middleware/CheckModulePassword.php`
  - [ ] Middleware registered as `module.password` in bootstrap/app.php
  - [ ] Test file: `tests/Feature/Middleware/CheckModulePasswordTest.php`
  - [ ] Middleware accepts module parameter: `module.password:kiosk` uses session key `kiosk_authenticated`
  - [ ] Middleware accepts module parameter: `module.password:tv-display` uses session key `tv_display_authenticated`
  - [ ] Session expiry based on `config('kiosk.session_lifetime')` (1440 min default)
  - [ ] `php artisan test --compact --filter=CheckModulePassword` → PASS

  **QA Scenarios:**

  ```
  Scenario: Unauthenticated request redirected to password form
    Tool: Pest (Livewire test)
    Preconditions: No session set, middleware applied to test route
    Steps:
      1. Create a test route with module.password middleware
      2. GET the protected route without session
      3. Assert redirect to module password form
    Expected Result: 302 redirect to password form URL
    Failure Indicators: 200 OK (bypassed middleware) or 500 error
    Evidence: .sisyphus/evidence/task-4-middleware-redirect.txt

  Scenario: Authenticated session passes through middleware
    Tool: Pest
    Preconditions: Middleware applied to test route, session key '{module}_authenticated' manually set with valid timestamp
    Steps:
      1. Manually set session('kiosk_authenticated' => now()->timestamp) in test
      2. GET protected route
      3. Assert 200 OK (not redirect)
    Expected Result: Request passes through middleware when session key is present and not expired
    Failure Indicators: 302 redirect despite valid session
    Evidence: .sisyphus/evidence/task-4-middleware-passthrough.txt

  Scenario: Expired session redirects to login
    Tool: Pest
    Preconditions: Middleware applied, session key set with timestamp older than session_lifetime
    Steps:
      1. Manually set session('kiosk_authenticated' => now()->subMinutes(1441)->timestamp) in test
      2. GET protected route
      3. Assert 302 redirect to /{module}/login
    Expected Result: Expired session treated as unauthenticated
    Failure Indicators: 200 OK despite expired session
    Evidence: .sisyphus/evidence/task-4-middleware-expired.txt
  ```

  **Commit**: YES (groups with Wave 1)
  - Message: `feat(middleware): add CheckModulePassword for kiosk and TV display protection`
  - Files: `app/Http/Middleware/CheckModulePassword.php`, `bootstrap/app.php`, `tests/Feature/Middleware/CheckModulePasswordTest.php`
  - Pre-commit: `php artisan test --compact --filter=CheckModulePassword`

- [ ] 5. Light/Dark Theme Toggle

  **What to do**:
  - Remove hardcoded `class="dark"` from `resources/views/layouts/app/sidebar.blade.php` line 2
  - Add Alpine.js theme toggle mechanism: read from `localStorage('theme')`, default to `'light'`
  - Replace `<html ... class="dark">` with `<html ... x-data="{ theme: localStorage.getItem('theme') || 'light' }" :class="theme">`
  - Create a `<flux:button>` toggle component in sidebar (moon/sun icon) that flips theme and persists to localStorage
  - Add same toggle button to mobile header
  - IMPORTANT: Theme toggle ONLY for admin layout — kiosk and TV display have their own fixed themes
  - Write Pest test: page renders without hardcoded dark class

  **Must NOT do**:
  - Do NOT add theme toggle to public pages or kiosk/TV display
  - Do NOT use server-side theme storage
  - Do NOT modify Tailwind config

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Small Alpine.js + Blade change
  - **Skills**: [`tailwindcss-development`]
    - `tailwindcss-development`: Dark mode class strategy understanding
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: This is pure Alpine.js + Blade, no Livewire

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 3, 4)
  - **Blocks**: Task 6 (navigation needs toggle button placement)
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `resources/views/layouts/app/sidebar.blade.php:2` — Current: `<html lang="..." class="dark">`. Remove hardcoded `dark`, add Alpine.js x-data binding.
  - `resources/views/layouts/app/sidebar.blade.php:56-66` — Spacer + auth section area where toggle button should be placed (before user menu).
  - `resources/views/layouts/app/sidebar.blade.php:70-126` — Mobile header section where mobile toggle button goes.

  **External References**:
  - Tailwind CSS v4 dark mode: uses `class` strategy (add/remove `dark` class on `<html>`)
  - Alpine.js: already included via Flux UI Pro's `@fluxScripts`

  **WHY Each Reference Matters**:
  - Line 2: THE exact line to modify. Executor must see current `class="dark"` to know what to replace.
  - Lines 56-66: Shows the spacer/auth area where toggle fits naturally in the sidebar UI flow.

  **Acceptance Criteria**:
  - [ ] No hardcoded `class="dark"` in sidebar.blade.php
  - [ ] Theme toggle button visible in sidebar and mobile header
  - [ ] localStorage persists theme choice
  - [ ] Page renders correctly in both light and dark modes

  **QA Scenarios:**

  ```
  Scenario: Theme toggle switches between light and dark
    Tool: Playwright
    Preconditions: App running, logged in as admin
    Steps:
      1. Navigate to /dashboard
      2. Check initial theme — `document.documentElement.classList` should NOT contain 'dark' (default light)
      3. Click theme toggle button (sun/moon icon in sidebar)
      4. Assert `document.documentElement.classList.contains('dark')` is true
      5. Assert `localStorage.getItem('theme')` === 'dark'
      6. Refresh page
      7. Assert theme persists — `document.documentElement.classList.contains('dark')` still true
    Expected Result: Theme toggles and persists across page loads
    Failure Indicators: Theme resets on refresh, or toggle button missing
    Evidence: .sisyphus/evidence/task-5-theme-toggle.png

  Scenario: Default theme is light (no hardcoded dark)
    Tool: Bash (grep)
    Preconditions: sidebar.blade.php modified
    Steps:
      1. Run `grep 'class="dark"' resources/views/layouts/app/sidebar.blade.php`
      2. Assert 0 matches
    Expected Result: No hardcoded dark class found
    Failure Indicators: Match found for hardcoded dark class
    Evidence: .sisyphus/evidence/task-5-no-hardcoded-dark.txt
  ```

  **Commit**: YES (groups with Wave 1)
  - Message: `feat(theme): add light/dark toggle with localStorage persistence`
  - Files: `resources/views/layouts/app/sidebar.blade.php`
  - Pre-commit: `php artisan test --compact`

### Wave 2: Navigation + Dashboard

- [ ] 6. Sidebar + Header Navigation Overhaul

  **What to do**:
  - Expand admin sidebar section in `sidebar.blade.php` (line 47-51): replace single "Admin" link with grouped sub-items:
    - Dashboard (icon: `chart-pie`, href: route('dashboard'))
    - Layanan (icon: `clipboard-document-list`, href: route('admin.layanan.index'))
    - Loket (icon: `building-office`, href: route('admin.loket.index'))
    - Users (icon: `users`, href: route('admin.users.index'))
    - Kiosk (icon: `device-tablet`, href: route('kiosk.index'))
    - TV Display (icon: `tv`, href: route('tv-display.index'))
  - Use `<flux:sidebar.group heading="Admin">` with `<flux:sidebar.item>` children
  - Add `:current` binding using `request()->routeIs('admin.layanan.*')` pattern for each item
  - Sync mobile header in `header.blade.php` with same navigation items
  - Place theme toggle button (from Task 5) before spacer in sidebar, and in mobile header
  - Write Pest test: admin user sees all sidebar items

  **Must NOT do**:
  - Do NOT change guest/public sidebar items
  - Do NOT change frontdesk/officer/monitor nav items
  - Do NOT restructure layout file beyond navigation

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Navigation UI layout work with Flux UI components
  - **Skills**: [`fluxui-development`, `tailwindcss-development`]
    - `fluxui-development`: Flux sidebar.group, sidebar.item components
    - `tailwindcss-development`: Responsive styling for mobile/desktop sync
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Navigation is Blade, not Livewire reactive

  **Parallelization**:
  - **Can Run In Parallel**: NO (sequential within Wave 2)
  - **Parallel Group**: Wave 2 (first in sequence)
  - **Blocks**: Task 20 (breadcrumbs)
  - **Blocked By**: Task 1 (named routes), Task 5 (theme toggle mechanism)

  **References**:

  **Pattern References**:
  - `resources/views/layouts/app/sidebar.blade.php:13-54` — Current nav structure. Lines 29-52 show the `@auth` section with role-based items. Expand the admin `@if` block (line 47-51) into a sidebar.group with multiple items.
  - `resources/views/layouts/app/sidebar.blade.php:15-26` — Guest sidebar group pattern to follow: `<flux:sidebar.group :heading="..." class="grid">` with nested `<flux:sidebar.item>` children.
  - `resources/views/layouts/app/sidebar.blade.php:70-126` — Mobile header. Must add matching navigation items here.
  - `resources/views/layouts/app/header.blade.php` — If separate file, check for mobile nav duplication.

  **API/Type References**:
  - Named routes from Task 1: `admin.layanan.index`, `admin.loket.index`, `admin.users.index`
  - `app/Enums/UserRole.php` — `Admin` enum value for role check

  **WHY Each Reference Matters**:
  - Lines 47-51: THE block being replaced. Single "Admin" link becomes a group with sub-items. Executor must see the current structure.
  - Lines 15-26: Shows the sidebar.group pattern already used in the codebase (guest section). Copy this exact pattern for the admin group.
  - Lines 70-126: Mobile header where equivalent items must be added to keep desktop/mobile in sync.

  **Acceptance Criteria**:
  - [ ] Admin user sees 6+ sidebar items (Dashboard, Layanan, Loket, Users, Kiosk, TV Display)
  - [ ] Active page highlighted correctly in sidebar
  - [ ] Mobile header shows same navigation items
  - [ ] Theme toggle button visible in both desktop sidebar and mobile header

  **QA Scenarios:**

  ```
  Scenario: Admin sidebar shows all navigation items
    Tool: Playwright
    Preconditions: App running, logged in as admin user
    Steps:
      1. Navigate to /dashboard
      2. Assert sidebar contains text "Dashboard" with link to /dashboard
      3. Assert sidebar contains text "Layanan" with link containing /admin/layanan
      4. Assert sidebar contains text "Loket" with link containing /admin/loket
      5. Assert sidebar contains text "Users" with link containing /admin/users
      6. Assert sidebar contains text "Kiosk"
      7. Assert sidebar contains text "TV Display"
      8. Screenshot full sidebar
    Expected Result: All 6 navigation items visible in sidebar with correct links
    Failure Indicators: Missing items, broken links, items visible to non-admin
    Evidence: .sisyphus/evidence/task-6-sidebar-nav.png

  Scenario: Active page highlighted correctly
    Tool: Playwright
    Preconditions: App running, logged in as admin
    Steps:
      1. Navigate to /admin/layanan
      2. Assert "Layanan" sidebar item has active/current state (CSS class check)
      3. Navigate to /admin/users
      4. Assert "Users" sidebar item has active/current state
    Expected Result: Current page's sidebar item is highlighted
    Failure Indicators: No highlight, wrong item highlighted
    Evidence: .sisyphus/evidence/task-6-sidebar-active.png
  ```

  **Commit**: YES (groups with Wave 2)
  - Message: `feat(nav): expand admin sidebar with all management pages and theme toggle`
  - Files: `resources/views/layouts/app/sidebar.blade.php`, `resources/views/layouts/app/header.blade.php`
  - Pre-commit: `php artisan test --compact`

- [ ] 7. AdminDashboard Livewire Component + Stat Cards

  **What to do**:
  - Create `app/Livewire/Dashboard/AdminDashboard.php` Livewire component class
  - Move content from classless component (`resources/views/components/dashboard/admin-dashboard.blade.php`) to proper Livewire component
  - Update `resources/views/components/dashboard/admin-dashboard.blade.php` to use the Livewire component OR create `resources/views/livewire/dashboard/admin-dashboard.blade.php`
  - Dashboard data properties (computed):
    - `$todayTotal` — total tickets today
    - `$todayServed` — completed today
    - `$todayWaiting` — waiting today
    - `$todayAvgWaitMinutes` — average wait time today
    - `$byService` — array: service name → count (for chart)
    - `$byCounter` — array: counter name → count (for chart)
    - `$byChannel` — array: ['online_booking' => N, 'assisted_same_day' => N, 'walk_in_kiosk' => N] (for chart, uses actual channel values from queue_tickets)
    - `$dateRange` — date range filter (default: today)
    - `$trendData` — daily totals for last 7/14/30 days (for line chart)
  - Stat cards using `<flux:card>` showing: Total Hari Ini, Sudah Dilayani, Menunggu, Rata-rata Tunggu
  - Date range picker using `<flux:date-picker>` with wire:model for `$dateRange`
  - When dateRange changes, all computed properties recalculate
  - Write Pest tests: component renders, stat cards show correct data, date range filter works

  **Must NOT do**:
  - Do NOT implement chart visualizations (that's Task 8)
  - Do NOT implement activity log (that's Task 9)
  - Do NOT change queue engine logic
  - Do NOT modify database schema

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Livewire component with Eloquent queries and computed properties
  - **Skills**: [`livewire-development`, `pest-testing`]
    - `livewire-development`: Livewire 4 component patterns, wire:model, computed properties
    - `pest-testing`: Livewire::test() assertions
  - **Skills Evaluated but Omitted**:
    - `fluxui-development`: Stat cards are simple Flux cards, core work is backend data

  **Parallelization**:
  - **Can Run In Parallel**: YES (can start when Task 1 done)
  - **Parallel Group**: Wave 2 (parallel with Task 6)
  - **Blocks**: Tasks 8, 9
  - **Blocked By**: Task 1 (named routes for navigation context)

  **References**:

  **Pattern References**:
  - `resources/views/components/dashboard/admin-dashboard.blade.php:1-8` — Current classless component. Shows that `<livewire:dashboard.admin-dashboard />` is referenced. New Livewire class must match this naming.
  - `app/Livewire/QueueDisplay.php` — Existing Livewire component pattern. Follow same namespace, structure, and property patterns.
  - `resources/views/components/dashboard/petugas-dashboard.blade.php` — Another dashboard component. Check for shared patterns.
  - `resources/views/dashboard.blade.php:11-12` — Where AdminDashboard is invoked: `<x-dashboard.admin-dashboard />`

  **API/Type References**:
  - `app/Models/QueueTicket.php` — Ticket model for counting today's totals, by service, by channel
  - `app/Models/QueueActivity.php` — Activity model for completion times, wait time calculation
  - `app/Models/Service.php` — For per-service breakdown
  - `app/Models/Counter.php` — For per-counter breakdown
  - `queue_tickets` table — columns: `service_id`, `counter_id`, `channel` (online_booking/assisted_same_day/walk_in_kiosk), `status`, `called_at`, `completed_at`, `created_at`
  - `queue_activities` table — columns: `queue_ticket_id`, `user_id`, `counter_id`, `action`, `meta` (JSON), `created_at`

  **WHY Each Reference Matters**:
  - `admin-dashboard.blade.php`: Shows how the component is currently referenced. The Livewire class must match `dashboard.admin-dashboard` naming.
  - `QueueDisplay.php`: Only existing full Livewire component — shows the project's Livewire coding style, property declaration, and computed property approach.
  - Models/tables: Executor needs to know exact table columns to write correct Eloquent queries for dashboard stats.

  **Acceptance Criteria**:
  - [ ] `app/Livewire/Dashboard/AdminDashboard.php` exists
  - [ ] Dashboard shows 4 stat cards with today's data
  - [ ] Date range picker changes data when selected
  - [ ] Test file: `tests/Feature/Dashboard/AdminDashboardTest.php`
  - [ ] `php artisan test --compact --filter=AdminDashboard` → PASS

  **QA Scenarios:**

  ```
  Scenario: Dashboard stat cards display correct data
    Tool: Pest (Livewire::test)
    Preconditions: Seed database with 5 tickets today (3 completed, 2 waiting)
    Steps:
      1. Livewire::test(AdminDashboard::class)
      2. Assert see "5" (total today)
      3. Assert see "3" (served)
      4. Assert see "2" (waiting)
    Expected Result: Stat cards reflect seeded data
    Failure Indicators: Wrong counts, missing cards, component render error
    Evidence: .sisyphus/evidence/task-7-stat-cards.txt

  Scenario: Date range filter updates data
    Tool: Pest (Livewire::test)
    Preconditions: Seed 3 tickets for today, 7 tickets for yesterday
    Steps:
      1. Livewire::test(AdminDashboard::class)
      2. Assert initial total is 3 (today only)
      3. Set dateRange to yesterday-today range
      4. Assert total becomes 10 (3+7)
    Expected Result: Data recalculates when date range changes
    Failure Indicators: Data doesn't change, or total incorrect
    Evidence: .sisyphus/evidence/task-7-date-range.txt

  Scenario: Dashboard renders in browser
    Tool: Playwright
    Preconditions: App running, logged in as admin, some queue data exists
    Steps:
      1. Navigate to /dashboard
      2. Assert page contains stat card elements
      3. Assert date picker is visible and interactive
      4. Screenshot full dashboard
    Expected Result: Dashboard renders with stat cards and date picker
    Failure Indicators: Empty page, Livewire error, missing components
    Evidence: .sisyphus/evidence/task-7-dashboard-render.png
  ```

  **Commit**: YES (groups with Wave 2)
  - Message: `feat(dashboard): add AdminDashboard Livewire component with stat cards and date range`
  - Files: `app/Livewire/Dashboard/AdminDashboard.php`, `resources/views/livewire/dashboard/admin-dashboard.blade.php`, `resources/views/components/dashboard/admin-dashboard.blade.php`, `tests/Feature/Dashboard/AdminDashboardTest.php`
  - Pre-commit: `php artisan test --compact --filter=AdminDashboard`

- [ ] 8. Dashboard Charts — Line/Bar/Area with Date Range

  **What to do**:
  - Add Flux UI Pro chart components to the admin dashboard Blade view
  - Charts to implement:
    1. **Trend Line Chart**: Daily ticket count over selected date range using `<flux:chart>` + `<flux:chart.line>`
    2. **Per-Service Bar Chart**: Horizontal bar chart showing tickets per service using `<flux:chart.bar>`
    3. **Per-Counter Bar Chart**: Tickets per counter using `<flux:chart.bar>`
    4. **Channel Distribution**: Booking vs Walk-in comparison using `<flux:chart.bar>` grouped
  - All charts receive data from AdminDashboard component properties (set up in Task 7)
  - Charts use `:value` prop or `wire:model` binding for data
  - Add `<flux:chart.legend>`, `<flux:chart.tooltip>`, `<flux:chart.cursor>` for interactivity
  - Implement empty state: when no data, show placeholder message instead of empty chart
  - Charts must be responsive (stack on mobile, grid on desktop)
  - Write Pest test: charts render, empty state shows when no data

  **Must NOT do**:
  - Do NOT install Chart.js, ApexCharts, or any external chart library
  - Do NOT modify AdminDashboard.php data logic (only consume existing properties)
  - Do NOT change dashboard stat cards from Task 7

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Chart layout, responsive grid, Flux UI Pro chart components
  - **Skills**: [`fluxui-development`, `tailwindcss-development`, `livewire-development`]
    - `fluxui-development`: Flux chart components API (`<flux:chart>`, line, bar, legend, tooltip)
    - `tailwindcss-development`: Responsive grid layout for chart cards
    - `livewire-development`: wire:model binding for chart data from Livewire component
  - **Skills Evaluated but Omitted**:
    - `pest-testing`: Tests are view-level assertions, not complex test logic

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends on Task 7 component data)
  - **Parallel Group**: Wave 2 (after Task 7)
  - **Blocks**: Task 20
  - **Blocked By**: Task 7 (AdminDashboard component must provide data properties)

  **References**:

  **Pattern References**:
  - `resources/views/livewire/dashboard/admin-dashboard.blade.php` — View file from Task 7 where charts will be added
  - `app/Livewire/Dashboard/AdminDashboard.php` — Component from Task 7 providing `$trendData`, `$byService`, `$byCounter`, `$byChannel` properties

  **External References**:
  - Flux UI Pro chart docs — `<flux:chart>`, `<flux:chart.line>`, `<flux:chart.bar>`, `<flux:chart.area>`, `<flux:chart.legend>`, `<flux:chart.tooltip>`, `<flux:chart.cursor>`, `<flux:chart.viewport>`, `<flux:chart.axis>`, `<flux:chart.summary>`
  - MUST use `search-docs` tool with queries: `['chart', 'chart line bar', 'flux chart']` to get exact Flux UI Pro chart API

  **WHY Each Reference Matters**:
  - Dashboard Blade view: Where charts are rendered. Executor must see existing stat cards layout to integrate charts below them.
  - AdminDashboard.php: Provides data properties. Executor must know exact property names and data shapes.
  - Flux chart docs: Executor MUST search docs first — chart components have specific data format requirements.

  **Acceptance Criteria**:
  - [ ] 4 charts visible on dashboard: trend line, per-service bar, per-counter bar, channel distribution
  - [ ] Charts render with Flux UI Pro components (no external libs)
  - [ ] Charts show empty state when no data
  - [ ] Charts responsive on mobile (stack vertically)
  - [ ] `php artisan test --compact --filter=AdminDashboard` → PASS

  **QA Scenarios:**

  ```
  Scenario: Charts render with data
    Tool: Playwright
    Preconditions: App running, admin logged in, seeded queue data for last 7 days
    Steps:
      1. Navigate to /dashboard
      2. Scroll past stat cards
      3. Assert presence of SVG elements within flux:chart containers
      4. Assert trend chart container exists
      5. Assert per-service chart container exists
      6. Assert per-counter chart container exists
      7. Assert channel chart container exists
      8. Screenshot charts area
    Expected Result: 4 chart containers with SVG content visible
    Failure Indicators: Empty containers, missing charts, JS errors in console
    Evidence: .sisyphus/evidence/task-8-charts-render.png

  Scenario: Empty state when no data
    Tool: Playwright
    Preconditions: App running, admin logged in, NO queue data
    Steps:
      1. Navigate to /dashboard
      2. Assert chart areas show empty state message (e.g., "Belum ada data")
      3. Assert no broken SVG or error
    Expected Result: Graceful empty state message in chart areas
    Failure Indicators: Broken chart, JS error, blank space with no message
    Evidence: .sisyphus/evidence/task-8-charts-empty.png
  ```

  **Commit**: YES (groups with Wave 2)
  - Message: `feat(dashboard): add analytics charts using Flux UI Pro chart components`
  - Files: `resources/views/livewire/dashboard/admin-dashboard.blade.php`
  - Pre-commit: `php artisan test --compact --filter=AdminDashboard`

- [ ] 9. Activity Log Widget

  **What to do**:
  - Add activity log section to admin dashboard view (below charts)
  - Query `queue_activities` table for latest 20 activities
  - Display as a timeline/list using `<flux:table>` or styled list:
    - Timestamp, Action (called/completed/skipped/cancelled), Ticket number, Service name, Counter name
  - Add real-time update via Livewire polling (30s interval, matching existing pattern in QueueDisplay)
  - Add empty state: "Belum ada aktivitas hari ini"
  - Add "Lihat Semua" link if more than 20 entries
  - Write Pest test: component renders log entries, empty state shows when no data

  **Must NOT do**:
  - Do NOT create new activity log entries (only READ existing)
  - Do NOT track admin CRUD actions (only queue activities)
  - Do NOT create a separate page for log (it's a dashboard widget)

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Livewire data fetching + Blade rendering
  - **Skills**: [`livewire-development`, `pest-testing`]
    - `livewire-development`: Polling, component lifecycle
    - `pest-testing`: Activity log query assertions
  - **Skills Evaluated but Omitted**:
    - `fluxui-development`: Simple table/list, not complex Flux components

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends on Task 7 component)
  - **Parallel Group**: Wave 2 (after Task 7, parallel with Task 8)
  - **Blocks**: Task 20
  - **Blocked By**: Task 7 (AdminDashboard component structure)

  **References**:

  **Pattern References**:
  - `app/Livewire/QueueDisplay.php` — Existing polling pattern. Uses `#[Computed] public function activeQueues()` with `5` second poll. Use 30s for activity log.
  - `resources/views/livewire/queue-display.blade.php` — Shows how QueueDisplay renders a list of queue items. Similar list pattern for activity log.
  - `resources/views/livewire/dashboard/admin-dashboard.blade.php` — Dashboard view where activity log widget will be added (below charts from Task 8)

  **API/Type References**:
  - `app/Models/QueueActivity.php` — Activity model with relationships to ticket, counter
  - `queue_activities` table columns: `id`, `queue_ticket_id`, `user_id`, `counter_id`, `action`, `meta` (JSON cast), `created_at`, `updated_at`
  - `app/Models/QueueTicket.php` — For ticket number and service relationship

  **WHY Each Reference Matters**:
  - `QueueDisplay.php`: Shows the polling pattern to follow. 5s poll for display, use 30s for less-critical activity log.
  - `queue_activities` table: Executor needs exact column names to write correct query (especially `action`, `created_at` for timestamp, `user_id` for who performed, `meta` for additional data).

  **Acceptance Criteria**:
  - [ ] Activity log visible on dashboard below charts
  - [ ] Shows latest 20 activities with timestamp, action, ticket, service, counter
  - [ ] Empty state shown when no activities
  - [ ] Auto-refreshes every 30 seconds
  - [ ] `php artisan test --compact --filter=AdminDashboard` → PASS

  **QA Scenarios:**

  ```
  Scenario: Activity log shows recent entries
    Tool: Pest (Livewire::test)
    Preconditions: Seed 5 queue_activities records for today
    Steps:
      1. Livewire::test(AdminDashboard::class)
      2. Assert see activity action text (e.g., "called", "completed")
      3. Assert 5 activity entries rendered
    Expected Result: Activity log displays seeded records
    Failure Indicators: Empty log, missing records, render error
    Evidence: .sisyphus/evidence/task-9-activity-log.txt

  Scenario: Empty activity log state
    Tool: Pest (Livewire::test)
    Preconditions: No queue_activities records
    Steps:
      1. Livewire::test(AdminDashboard::class)
      2. Assert see text "Belum ada aktivitas"
    Expected Result: Empty state message displayed
    Failure Indicators: Blank space, error, or misleading content
    Evidence: .sisyphus/evidence/task-9-activity-empty.txt
  ```

  **Commit**: YES (groups with Wave 2)
  - Message: `feat(dashboard): add activity log widget with polling from queue_activities`
  - Files: `resources/views/livewire/dashboard/admin-dashboard.blade.php`, `app/Livewire/Dashboard/AdminDashboard.php`
  - Pre-commit: `php artisan test --compact --filter=AdminDashboard`


### Wave 3 — CRUD Overhaul (depends on Wave 1 named routes + Wave 2 nav)

- [ ] 10. Layanan (Service) Full CRUD — Edit Modal + Delete + Pagination + Search

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/Admin/ServiceManagementTest.php`
    - Test `GET /admin/layanan` returns 200 with paginated services
    - Test edit modal renders with pre-filled data for existing service
    - Test `PUT /admin/layanan/{service}` updates all fields (name, code, slug, description, requirements, queue_pool_id, sort_order, is_active, booking_enabled, walk_in_enabled, daily_quota, letter_code)
    - Test `DELETE /admin/layanan/{service}` succeeds when no queue_tickets exist
    - Test `DELETE /admin/layanan/{service}` returns 403/error when queue_tickets exist for service
    - Test search filters services by name (Livewire `$search` property)
    - Test pagination shows 10 per page with page navigation
    - Test sort by name, code, sort_order columns (ascending/descending)
  - **Controller changes (GREEN)**:
    - Add `destroy(Service $service)` method to `ServiceManagementController`
    - In `destroy()`: check `$service->queueTickets()->exists()` — if true, redirect back with error flash "Layanan tidak dapat dihapus karena masih memiliki tiket antrian aktif"
    - If no relations, `$service->delete()` and redirect with success flash
    - Add `edit(Service $service)` method OR handle edit via Livewire modal (prefer Livewire modal to match existing pattern)
    - Update `index()` to use `Service::query()->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))->orderBy($sortField, $sortDirection)->paginate(10)`
  - **Route changes**:
    - Add `Route::delete('/admin/layanan/{service}', [ServiceManagementController::class, 'destroy'])->name('admin.layanan.destroy')`
  - **View overhaul** (`layanan/index.blade.php`):
    - Convert existing table to use `<flux:pagination :paginator="$services" />` (requires Livewire pagination)
    - Add search input above table: `<flux:input wire:model.live.debounce.300ms="search" placeholder="Cari layanan..." icon="magnifying-glass" />`
    - Make columns sortable: `<flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortDirection" wire:click="sort('name')">Nama</flux:table.column>`
    - Add Edit button per row → opens `<flux:modal name="edit-service-{{ $service->id }}">` with pre-filled form
    - Add Delete button per row → opens confirmation `<flux:modal name="delete-service-{{ $service->id }}">` with warning text
    - Edit modal fields: name, code, slug, description (textarea), requirements (textarea), queue_pool select, sort_order, is_active toggle, booking_enabled toggle, walk_in_enabled toggle, daily_quota input, letter_code input (must match `StoreServiceRequest` fields at `app/Http/Requests/StoreServiceRequest.php:25-41`)
    - Keep existing create form at top (card layout) as-is, just ensure consistency with new modal fields
  - **Create Livewire component** (if not using classless Volt):
    - If pagination/search/sort requires a Livewire class component, create `app/Livewire/Admin/ServiceManagement.php`
    - Include properties: `$search`, `$sortField = 'sort_order'`, `$sortDirection = 'asc'`, `$editingService`
    - Include methods: `sort($field)`, `editService($id)`, `updateService()`, `deleteService($id)`
    - Alternative: use classless Volt pattern like admin-dashboard if possible (check if pagination works with classless)

  **Must NOT do**:
  - Do NOT change the create form's existing field set or validation rules in `StoreServiceRequest`
  - Do NOT modify the `services` table schema
  - Do NOT add bulk delete or bulk actions (out of scope)
  - Do NOT touch `UpdateServiceRequest` validation rules unless needed for modal edit

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: CRUD overhaul involves controller logic, Livewire component, Blade view, and Pest tests — multi-concern task requiring careful coordination
  - **Skills**: [`livewire-development`, `pest-testing`, `fluxui-development`]
    - `livewire-development`: Pagination, search, sort all require Livewire reactivity (wire:model, polling, component lifecycle)
    - `pest-testing`: TDD approach — write Pest feature tests first, then implement
    - `fluxui-development`: Edit/delete modals, pagination component, sortable table columns all use Flux UI Pro components
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: Styling is minimal (reusing existing table patterns, modals are Flux-managed)

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 11, 12, 13)
  - **Blocks**: Task 21 (integration test needs CRUD working)
  - **Blocked By**: Task 1 (named routes), Task 6 (sidebar nav links to this page)

  **References**:

  **Pattern References** (existing code to follow):
  - `resources/views/pages/admin/layanan/index.blade.php:1-115` — Current view with create form + table (toggle-only). Copy table structure, enhance with edit/delete buttons
  - `app/Http/Controllers/Admin/ServiceManagementController.php:1-49` — Current controller with index/store/update. Add destroy() method following same pattern
  - `app/Http/Requests/StoreServiceRequest.php` — Existing validation for create. Reference for edit modal validation
  - `app/Http/Requests/UpdateServiceRequest.php` — Existing validation for update. Reuse for edit modal
  - `resources/views/components/dashboard/admin-dashboard.blade.php` — Classless Livewire/Volt pattern example (if using classless for pagination)

  **API/Type References** (contracts to implement against):
  - `app/Models/Service.php` — Service model with relationships (queuePool, queueTickets, users). Check `queueTickets()` relationship exists for delete guard
  - `app/Models/QueuePool.php` — Queue pool for select dropdown in edit modal

  **Test References** (testing patterns to follow):
  - `tests/Feature/` — Check existing test files for assertion patterns, factory usage, authentication helpers

  **External References**:
  - Flux UI Pro docs: `<flux:modal>` component API, `<flux:pagination>` component, `<flux:table>` sortable columns — use `search-docs` tool with queries ["modal", "pagination", "table sortable"]

  **WHY Each Reference Matters**:
  - `layanan/index.blade.php` — This IS the file being overhauled. Executor must read it first to understand current structure before modifying
  - `ServiceManagementController.php` — Must add `destroy()` following the same redirect/flash pattern used in `store()` and `update()`
  - `Service.php` model — Must verify `queueTickets()` relationship exists; if not, create it before implementing delete guard

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=ServiceManagement` → ALL PASS
  - [ ] Edit modal opens with all fields pre-filled for any service row
  - [ ] Delete button shows confirmation modal; blocks deletion if queue_tickets exist
  - [ ] Pagination shows 10 items per page with navigation controls
  - [ ] Search filters services by name in real-time (debounced)
  - [ ] Sort by clicking column headers toggles asc/desc

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Edit a service via modal
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin, at least 1 service exists in DB
    Steps:
      1. Navigate to /admin/layanan
      2. Click edit button (selector: `button[data-flux-modal-trigger="edit-service-{id}"]` or equivalent flux modal trigger) on first service row
      3. Wait for modal to appear (selector: `[data-flux-modal="edit-service-{id}"]`)
      4. Assert modal contains pre-filled input with current service name
      5. Change name field to "Layanan Test Edited"
      6. Click save/update button in modal
      7. Assert modal closes
      8. Assert table row shows updated name "Layanan Test Edited"
      9. Assert flash message contains "berhasil" or "updated"
    Expected Result: Service name updated in table, success flash shown
    Failure Indicators: Modal doesn't open, fields not pre-filled, validation error, name not updated in table
    Evidence: .sisyphus/evidence/task-10-edit-service.png

  Scenario: Delete service blocked by existing queue tickets
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin, service exists WITH associated queue_tickets in DB
    Steps:
      1. Navigate to /admin/layanan
      2. Click delete button on the service that has queue tickets
      3. Confirm deletion in confirmation modal
      4. Assert error message appears containing "tidak dapat dihapus" or "tiket antrian aktif"
      5. Assert service still exists in table (not removed)
    Expected Result: Deletion blocked with clear Indonesian error message
    Failure Indicators: Service deleted despite having tickets, no error message, generic error
    Evidence: .sisyphus/evidence/task-10-delete-blocked.png

  Scenario: Search and pagination
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin, at least 15 services exist (to trigger pagination)
    Steps:
      1. Navigate to /admin/layanan
      2. Assert pagination controls visible (selector: `nav[role="navigation"]` or flux pagination)
      3. Assert showing 10 items on first page
      4. Type "Sidang" in search input (selector: `input[wire\:model\.live\.debounce]` or search input)
      5. Wait 500ms for debounce
      6. Assert table shows only services containing "Sidang" in name
      7. Clear search input
      8. Assert full list restored with pagination
    Expected Result: Search filters in real-time, pagination works correctly
    Failure Indicators: Search doesn't filter, pagination absent, page doesn't update
    Evidence: .sisyphus/evidence/task-10-search-pagination.png
  ```

  **Commit**: YES (groups with Wave 3)
  - Message: `feat(admin): add full CRUD for layanan — edit modal, delete with relation guard, pagination, search, sort`
  - Files: `app/Http/Controllers/Admin/ServiceManagementController.php`, `resources/views/pages/admin/layanan/index.blade.php`, `app/Livewire/Admin/ServiceManagement.php` (if created), `routes/web.php`, `tests/Feature/Admin/ServiceManagementTest.php`
  - Pre-commit: `php artisan test --compact --filter=ServiceManagement`

- [ ] 11. Loket (Counter) Full CRUD — Add Create Form + Edit Modal + Delete + Pagination + Search

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/Admin/CounterManagementTest.php`
    - Test `GET /admin/loket` returns 200 with paginated counters
    - Test `POST /admin/loket` creates a new counter with name, code, queue_pool_id, sort_order, is_active
    - Test store validation: name required, code required+unique, queue_pool_id required+exists
    - Test edit modal renders with pre-filled data
    - Test `PUT /admin/loket/{counter}` updates all fields
    - Test `DELETE /admin/loket/{counter}` succeeds when no counter_sessions exist
    - Test `DELETE /admin/loket/{counter}` returns error when counter_sessions exist
    - Test search filters counters by name
    - Test pagination with 10 per page
  - **Controller changes (GREEN)**:
    - Add `store(StoreCounterRequest $request)` method to `CounterManagementController` — create Counter with validated data, redirect with success flash
    - Add `destroy(Counter $counter)` method — check `$counter->sessions()->exists()`, block if true with error "Loket tidak dapat dihapus karena masih memiliki sesi aktif"
    - Create `StoreCounterRequest` form request class with validation rules: name (required|string|max:255), code (required|string|max:50|unique:counters), queue_pool_id (required|exists:queue_pools,id), sort_order (nullable|integer|min:0), is_active (boolean)
    - Update `index()` to support search + pagination like Task 10
  - **Route changes**:
    - Add `Route::post('/admin/loket', [CounterManagementController::class, 'store'])->name('admin.loket.store')`
    - Add `Route::delete('/admin/loket/{counter}', [CounterManagementController::class, 'destroy'])->name('admin.loket.destroy')`
  - **View overhaul** (`loket/index.blade.php`):
    - Add create form at top (card layout) matching layanan page pattern: fields for name, code, queue_pool select, sort_order, is_active toggle
    - Convert inline form-per-row table to proper table with action buttons
    - Add edit modal per row (same fields as create + pre-filled values)
    - Add delete button per row with confirmation modal
    - Add search input + sortable columns + pagination (same pattern as Task 10)
    - Remove inline form-per-row pattern (currently each row is a separate `<form>` — replace with Livewire actions)

  **Must NOT do**:
  - Do NOT change the `counters` table schema
  - Do NOT modify queue pool assignment logic beyond what exists
  - Do NOT add counter session management (that's the officer/frontdesk domain)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: Similar complexity to Task 10 — controller + form request + view overhaul + tests
  - **Skills**: [`livewire-development`, `pest-testing`, `fluxui-development`]
    - `livewire-development`: Pagination, search, sort, Livewire actions for CRUD
    - `pest-testing`: TDD — write feature tests before implementation
    - `fluxui-development`: Modal, table, pagination, form components
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: Reusing existing table/card patterns

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 10, 12, 13)
  - **Blocks**: Task 21 (integration test)
  - **Blocked By**: Task 1 (named routes), Task 6 (sidebar nav)

  **References**:

  **Pattern References**:
  - `resources/views/pages/admin/loket/index.blade.php:1-62` — Current view with inline form-per-row pattern. This entire view needs restructuring to match the layanan page create-form-at-top + table pattern
  - `app/Http/Controllers/Admin/CounterManagementController.php:1-39` — Current controller with only index/update. Needs store() and destroy() added
  - `resources/views/pages/admin/layanan/index.blade.php` — REFERENCE for target layout: create form card at top + table below. Copy this pattern for loket
  - `app/Http/Requests/StoreServiceRequest.php` — REFERENCE for form request class structure. Copy pattern for StoreCounterRequest

  **API/Type References**:
  - `app/Models/Counter.php` — Counter model with relationships (`sessions()`, `queuePool()`). Use `sessions()` for delete guard
  - `app/Models/QueuePool.php` — For queue pool select dropdown

  **Test References**:
  - Task 10's test file (when created) — Follow same test structure for consistency

  **External References**:
  - Flux UI Pro docs: `<flux:modal>` for edit/delete modals — use `search-docs` tool

  **WHY Each Reference Matters**:
  - `loket/index.blade.php` — The file being overhauled. Current inline form pattern must be completely replaced
  - `CounterManagementController.php` — Must add store() and destroy() following same pattern as ServiceManagementController
  - `layanan/index.blade.php` — Target pattern reference. Loket page should look consistent with Layanan page

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=CounterManagement` → ALL PASS
  - [ ] Create form at top creates new counter successfully
  - [ ] Edit modal opens with pre-filled data for any counter row
  - [ ] Delete blocks if counter_sessions exist, succeeds otherwise
  - [ ] Search + pagination + sortable columns all functional

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Create a new counter
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin, at least 1 queue_pool exists
    Steps:
      1. Navigate to /admin/loket
      2. Fill "Nama" field with "Loket Baru Test"
      3. Fill "Kode" field with "LBT-01"
      4. Select first queue pool from dropdown
      5. Click submit/create button
      6. Assert flash message contains "berhasil" or "created"
      7. Assert table now contains row with "Loket Baru Test"
    Expected Result: New counter appears in table with success message
    Failure Indicators: Validation error, counter not in table, no flash message
    Evidence: .sisyphus/evidence/task-11-create-counter.png

  Scenario: Delete counter blocked by active sessions
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin, counter exists WITH counter_sessions records
    Steps:
      1. Navigate to /admin/loket
      2. Click delete button on counter with active sessions
      3. Confirm in confirmation modal
      4. Assert error message containing "tidak dapat dihapus" or "sesi aktif"
      5. Assert counter still in table
    Expected Result: Deletion blocked with Indonesian error message
    Failure Indicators: Counter deleted, no error shown, generic error message
    Evidence: .sisyphus/evidence/task-11-delete-blocked.png
  ```

  **Commit**: YES (groups with Wave 3)
  - Message: `feat(admin): add full CRUD for loket — create form, edit modal, delete with relation guard, pagination, search, sort`
  - Files: `app/Http/Controllers/Admin/CounterManagementController.php`, `resources/views/pages/admin/loket/index.blade.php`, `app/Http/Requests/StoreCounterRequest.php`, `app/Livewire/Admin/CounterManagement.php` (if created), `routes/web.php`, `tests/Feature/Admin/CounterManagementTest.php`
  - Pre-commit: `php artisan test --compact --filter=CounterManagement`

- [ ] 12. Users Full CRUD — Edit Modal + Delete + Merge Roles/Permissions Sections

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/Admin/UserManagementTest.php`
    - Test `GET /admin/users` returns 200 with paginated users AND role distribution section AND service permissions matrix
    - Test edit modal renders with pre-filled user data (name, email, role, assigned services)
    - Test `PUT /admin/users/{user}` updates role + syncs services
    - Test `DELETE /admin/users/{user}` succeeds when user has no active counter_sessions
    - Test `DELETE /admin/users/{user}` returns error when user has active counter_sessions
    - Test `DELETE /admin/users/{user}` prevents deleting yourself (self-delete guard)
    - Test search filters users by name or email
    - Test pagination with 10 per page
  - **Controller changes (GREEN)**:
    - Add `destroy(User $user)` method to `UserManagementController`
    - In `destroy()`: prevent self-deletion (`abort_if($user->id === auth()->id(), 403)`)
    - Check `$user->sessions()->where('closed_at', null)->exists()` via Counter model (User does not have direct counterSessions relationship — query through CounterSession::where('user_id', $user->id)->whereNull('closed_at')->exists()) — block if active sessions
    - Error message: "User tidak dapat dihapus karena masih memiliki sesi loket aktif"
    - On success: detach services (`$user->services()->detach()`), delete user, redirect with success flash
    - **Merge roles() content into index()**: Query role distribution counts (`User::query()->selectRaw('role, count(*) as count')->groupBy('role')->get()`) and pass to view
    - **Merge servicePermissions() content into index()**: The current permissions view shows user-service assignment matrix. Include this data in the users index view
  - **Route changes**:
    - Add `Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy')`
  - **View overhaul** (`users/index.blade.php`):
    - Keep existing create form at top
    - Replace inline role-select + services-multiselect per row with proper Edit button → modal
    - Edit modal: name (readonly/disabled), email (readonly/disabled), role select, services multi-select checkboxes
    - Add Delete button per row with confirmation modal + self-delete prevention (hide button for current user)
    - Add search + sortable columns + pagination
    - **Add "Distribusi Role" section** below table: card showing role counts (Admin: X, Frontdesk: Y, Officer: Z, Monitor: W) — content currently at `/admin/roles`
    - **Add "Izin Layanan" section** below role distribution: matrix/table showing which users have which service permissions — content currently at `/admin/izin-layanan`
    - Use Flux tabs or accordion to organize: Tab 1 "Daftar User" (table), Tab 2 "Distribusi Role" (counts), Tab 3 "Izin Layanan" (matrix)

  **Must NOT do**:
  - Do NOT change the `users` table schema
  - Do NOT modify authentication logic or password handling
  - Do NOT allow editing user email or name through the edit modal (readonly fields — user manages their own profile)
  - Do NOT allow deleting yourself (current authenticated user)

  **Recommended Agent Profile**:
  - **Category**: `deep`
    - Reason: Most complex CRUD task — merging 3 views into 1, tabbed interface, self-delete guard, detaching relations, role distribution query
  - **Skills**: [`livewire-development`, `pest-testing`, `fluxui-development`]
    - `livewire-development`: Tabbed interface, pagination, search, modals with Livewire actions
    - `pest-testing`: TDD with complex test scenarios (self-delete, relation guards)
    - `fluxui-development`: Tabs component, modals, multi-select, table
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: Styling handled by Flux components
    - `developing-with-fortify`: Not touching auth logic, only user CRUD

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 10, 11, 13)
  - **Blocks**: Task 13 (route redirects depend on merged UI being ready), Task 21 (integration test)
  - **Blocked By**: Task 1 (named routes), Task 6 (sidebar nav)

  **References**:

  **Pattern References**:
  - `resources/views/pages/admin/users/index.blade.php:1-127` — Current view with create form + inline editing table. Must be restructured with edit modal + merged sections
  - `app/Http/Controllers/Admin/UserManagementController.php:1-100` — Current controller. Has `roles()` at line 70 and `servicePermissions()` at line 82 — these methods' logic must be merged into `index()`
  - `resources/views/pages/admin/roles/index.blade.php:1-28` — Roles distribution view. Content to be replicated in users page "Distribusi Role" tab/section
  - `resources/views/pages/admin/roles/permissions.blade.php:1-38` — Permissions matrix view. Content to be replicated in users page "Izin Layanan" tab/section
  - Task 10 pattern — Follow same edit modal + delete confirmation pattern for consistency

  **API/Type References**:
  - `app/Models/User.php` — User model with relationships (`services()`). NOTE: User does NOT have a `counterSessions()` relationship. For delete guard, query `CounterSession::where('user_id', $user->id)->whereNull('closed_at')->exists()` directly
  - `app/Enums/UserRole.php` — UserRole enum (Admin, Frontdesk, Officer, Monitor) for role select dropdown and distribution display
  - `app/Models/Service.php` — For service permissions multi-select

  **Test References**:
  - Tasks 10-11 test files — Follow same structure for consistency

  **External References**:
  - Flux UI Pro docs: `<flux:tabs>` / `<flux:tab>` component for tabbed interface — use `search-docs` tool with queries ["tabs", "tab panel"]

  **WHY Each Reference Matters**:
  - `users/index.blade.php` — The file being overhauled. Must understand current inline editing pattern to replace it
  - `UserManagementController.php` — Lines 70-100 contain `roles()` and `servicePermissions()` methods whose logic must be absorbed into `index()`
  - `roles/index.blade.php` and `roles/permissions.blade.php` — Content being merged INTO users page. Must replicate their output faithfully
  - `UserRole.php` enum — Needed for role distribution counts and role select dropdown values

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=UserManagement` → ALL PASS
  - [ ] Users page has 3 tabs/sections: Daftar User, Distribusi Role, Izin Layanan
  - [ ] Edit modal shows readonly name/email + editable role + service checkboxes
  - [ ] Delete blocks if user has active counter_sessions
  - [ ] Cannot delete yourself (button hidden or disabled for current user)
  - [ ] Role distribution shows correct counts per role
  - [ ] Service permissions matrix shows user-service assignments

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Edit user role and services via modal
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin, at least 2 users exist, at least 2 services exist
    Steps:
      1. Navigate to /admin/users
      2. Click edit button on a non-admin user row
      3. Wait for edit modal to appear
      4. Assert name and email fields are readonly/disabled
      5. Change role select to "Officer"
      6. Toggle a service checkbox (add or remove a service)
      7. Click save/update button
      8. Assert modal closes, table updated with new role
      9. Assert flash message shows success
    Expected Result: User role and services updated correctly
    Failure Indicators: Modal doesn't show readonly fields, role not updated, services not synced
    Evidence: .sisyphus/evidence/task-12-edit-user.png

  Scenario: Delete self is prevented
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin
    Steps:
      1. Navigate to /admin/users
      2. Find your own user row (match by authenticated user's email)
      3. Assert delete button is NOT present or is disabled for your own row
    Expected Result: No delete button visible for current authenticated user
    Failure Indicators: Delete button exists and is clickable for self
    Evidence: .sisyphus/evidence/task-12-self-delete-guard.png

  Scenario: Verify merged Distribusi Role tab
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin, users exist with various roles
    Steps:
      1. Navigate to /admin/users
      2. Click "Distribusi Role" tab
      3. Assert section shows role names (Admin, Frontdesk, Officer, Monitor)
      4. Assert each role shows a count number
      5. Assert total count matches number of users in "Daftar User" tab
    Expected Result: Role distribution displayed with accurate counts
    Failure Indicators: Tab missing, counts are zero or incorrect, roles not listed
    Evidence: .sisyphus/evidence/task-12-role-distribution.png
  ```

  **Commit**: YES (groups with Wave 3)
  - Message: `feat(admin): add full CRUD for users — edit modal, delete with guards, merge roles & permissions into tabbed UI`
  - Files: `app/Http/Controllers/Admin/UserManagementController.php`, `resources/views/pages/admin/users/index.blade.php`, `app/Livewire/Admin/UserManagement.php` (if created), `routes/web.php`, `tests/Feature/Admin/UserManagementTest.php`
  - Pre-commit: `php artisan test --compact --filter=UserManagement`

- [ ] 13. Route Redirects — Deprecate Old Roles/Permissions Routes + Cleanup

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/Admin/RouteRedirectTest.php`
    - Test `GET /admin/roles` returns 301 redirect to `/admin/users`
    - Test `GET /admin/izin-layanan` returns 301 redirect to `/admin/users`
    - Test old routes no longer render their own views
  - **Route changes (GREEN)**:
    - In `routes/web.php`: Replace `Route::get('/admin/roles', ...)` with `Route::redirect('/admin/roles', '/admin/users', 301)`
    - Replace `Route::get('/admin/izin-layanan', ...)` with `Route::redirect('/admin/izin-layanan', '/admin/users', 301)`
  - **Controller cleanup**:
    - Remove `roles()` method from `UserManagementController` (logic already merged into `index()` by Task 12)
    - Remove `servicePermissions()` method from `UserManagementController`
  - **View cleanup**:
    - Delete `resources/views/pages/admin/roles/index.blade.php`
    - Delete `resources/views/pages/admin/roles/permissions.blade.php`
    - Delete `resources/views/pages/admin/roles/` directory if empty
  - **Sidebar/header cleanup**:
    - Ensure no navigation links point to `/admin/roles` or `/admin/izin-layanan` (should already be handled by Task 6, but verify)

  **Must NOT do**:
  - Do NOT remove the `/admin/users` route or modify it
  - Do NOT change the redirect status code (must be 301 permanent)
  - Do NOT delete controller methods until Task 12 has merged their logic into index()

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Simple route changes, file deletions, method removal — straightforward cleanup task
  - **Skills**: [`pest-testing`]
    - `pest-testing`: Write redirect assertion tests
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: No Livewire involved
    - `fluxui-development`: No UI components

  **Parallelization**:
  - **Can Run In Parallel**: PARTIALLY — route redirect definitions can be done in parallel with Tasks 10-11, BUT controller method removal and view deletion MUST wait for Task 12
  - **Parallel Group**: Wave 3 (with Tasks 10, 11, 12) — but Task 13 should execute AFTER Task 12 completes
  - **Blocks**: None directly
  - **Blocked By**: Task 12 (must merge roles/permissions logic into users page BEFORE removing old methods/views)

  **References**:

  **Pattern References**:
  - `routes/web.php:54-55` — Current routes for `/admin/roles` and `/admin/izin-layanan` that will be replaced with redirects
  - `app/Http/Controllers/Admin/UserManagementController.php:70-100` — Methods `roles()` and `servicePermissions()` to be removed

  **API/Type References**:
  - `resources/views/pages/admin/roles/` — Directory containing views to delete (index.blade.php, permissions.blade.php)

  **WHY Each Reference Matters**:
  - `routes/web.php:54-55` — Exact lines to replace. Executor must target these specific lines
  - `UserManagementController.php:70-100` — Exact methods to remove. Only safe after Task 12 absorbs their logic

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=RouteRedirect` → ALL PASS
  - [ ] `curl -I /admin/roles` returns 301 with Location: /admin/users
  - [ ] `curl -I /admin/izin-layanan` returns 301 with Location: /admin/users
  - [ ] `roles/index.blade.php` and `roles/permissions.blade.php` files deleted
  - [ ] `UserManagementController` no longer has `roles()` or `servicePermissions()` methods

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Verify 301 redirects for old routes
    Tool: Bash (curl)
    Preconditions: Application running, admin routes accessible
    Steps:
      1. Run: curl -sI http://localhost:8000/admin/roles -H "Cookie: {admin_session_cookie}" | head -5
      2. Assert response contains "HTTP/1.1 301 Moved Permanently"
      3. Assert response contains "Location:" header pointing to /admin/users
      4. Run: curl -sI http://localhost:8000/admin/izin-layanan -H "Cookie: {admin_session_cookie}" | head -5
      5. Assert response contains "HTTP/1.1 301 Moved Permanently"
      6. Assert Location header points to /admin/users
    Expected Result: Both old routes return 301 to /admin/users
    Failure Indicators: 404, 200 (still rendering old view), 302 (temporary redirect instead of permanent)
    Evidence: .sisyphus/evidence/task-13-redirects.txt

  Scenario: Old view files deleted
    Tool: Bash
    Preconditions: Task completed
    Steps:
      1. Run: test -f resources/views/pages/admin/roles/index.blade.php && echo "EXISTS" || echo "DELETED"
      2. Assert output is "DELETED"
      3. Run: test -f resources/views/pages/admin/roles/permissions.blade.php && echo "EXISTS" || echo "DELETED"
      4. Assert output is "DELETED"
    Expected Result: Both files no longer exist
    Failure Indicators: Files still exist
    Evidence: .sisyphus/evidence/task-13-files-deleted.txt
  ```

  **Commit**: YES (groups with Wave 3)
  - Message: `refactor(admin): redirect /admin/roles and /admin/izin-layanan to /admin/users, remove deprecated views and controller methods`
  - Files: `routes/web.php`, `app/Http/Controllers/Admin/UserManagementController.php`, deleted: `resources/views/pages/admin/roles/index.blade.php`, `resources/views/pages/admin/roles/permissions.blade.php`
  - Pre-commit: `php artisan test --compact --filter=RouteRedirect`


### Wave 4 — Kiosk Module (depends on Wave 1 middleware + config)

- [ ] 14. Kiosk Password Gate — Login Page + Session Auth + Routes

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/Kiosk/KioskAuthTest.php`
    - Test `GET /kiosk` without session redirects to `/kiosk/login`
    - Test `GET /kiosk/login` returns 200 with password form
    - Test `POST /kiosk/login` with correct password (from config) sets session and redirects to `/kiosk`
    - Test `POST /kiosk/login` with wrong password returns back with error
    - Test `POST /kiosk/logout` clears session and redirects to `/kiosk/login`
    - Test session expires after configured TTL (default 24 hours)
    - Test middleware `module.password:kiosk` blocks unauthenticated access
  - **Create Kiosk routes** in `routes/web.php`:
    - `Route::get('/kiosk/login', [KioskController::class, 'showLogin'])->name('kiosk.login')`
    - `Route::post('/kiosk/login', [KioskController::class, 'login'])->name('kiosk.authenticate')`
    - `Route::post('/kiosk/logout', [KioskController::class, 'logout'])->name('kiosk.logout')`
    - Route group with `module.password:kiosk` middleware:
      - `Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index')`
      - (Booking routes added in Task 15)
  - **Create KioskController** (`app/Http/Controllers/KioskController.php`):
    - `showLogin()` — return kiosk login view
    - `login(Request $request)` — validate password against `config('kiosk.password')`, set `session(['kiosk_authenticated' => true, 'kiosk_authenticated_at' => now()])`, redirect to `/kiosk`
    - `logout()` — forget session keys, redirect to `/kiosk/login`
    - `index()` — return kiosk main view (booking wizard)
  - **Create Kiosk login view** (`resources/views/pages/kiosk/login.blade.php`):
    - Full-page centered card layout
    - Password input field (large, touchscreen-friendly: text-2xl, p-6)
    - Submit button (large: py-4 px-8, text-xl)
    - Branding: "Antrian PTSP Pengadilan Agama Penajam" logo/text
    - Error message display for wrong password
    - NO username/email field — password only
    - Use a minimal layout (not the admin sidebar layout)
  - **Create Kiosk layout** (`resources/views/layouts/kiosk.blade.php`):
    - Minimal layout without sidebar/header — just content area
    - Include Livewire/Flux UI assets
    - Include Tailwind CSS
    - Full-screen, no scrollbar aesthetics for touchscreen

  **Must NOT do**:
  - Do NOT reuse admin authentication — Kiosk uses its own session-based password check
  - Do NOT store password in database — it comes from `.env` via `config/kiosk.php` (Task 3)
  - Do NOT add rate limiting (out of scope, can be added later)
  - Do NOT create a User model/record for kiosk access

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: New module creation with routes, controller, middleware integration, views, and tests
  - **Skills**: [`pest-testing`, `developing-with-fortify`, `fluxui-development`]
    - `pest-testing`: TDD for auth flow testing
    - `developing-with-fortify`: Authentication patterns (session management, redirect flows) — not using Fortify directly but similar auth concepts
    - `fluxui-development`: Login form UI with Flux components (input, button, card)
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Login page is simple form, no Livewire reactivity needed
    - `tailwindcss-development`: Touchscreen sizing classes are straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 17 — TV Display gate is similar but independent)
  - **Parallel Group**: Wave 4 (with Tasks 15, 16) — but Tasks 15-16 depend on Task 14
  - **Blocks**: Tasks 15, 16 (need routes and auth gate before building booking UI)
  - **Blocked By**: Task 3 (config/kiosk.php), Task 4 (CheckModulePassword middleware)

  **References**:

  **Pattern References**:
  - `app/Http/Middleware/CheckModulePassword.php` (created in Task 4) — The middleware this task integrates with. Must use same session keys and config path
  - `config/kiosk.php` (created in Task 3) — Config file with password and session_lifetime values
  - `resources/views/layouts/app.blade.php` or equivalent — Reference for layout structure (assets inclusion). Kiosk layout is simpler but needs same asset stack
  - `app/Http/Controllers/PublicQueueController.php` — Reference for non-authenticated controller pattern

  **API/Type References**:
  - `config('kiosk.password')` — The shared password value
  - `config('kiosk.session_lifetime')` — Session expiry (default 1440 minutes = 24 hours)

  **External References**:
  - Laravel session docs — use `search-docs` with queries ["session", "session put forget"]

  **WHY Each Reference Matters**:
  - `CheckModulePassword.php` — Must match session key names (`kiosk_authenticated`, `kiosk_authenticated_at`) used by middleware
  - `config/kiosk.php` — Controller reads password from here, must match key path exactly
  - Layout reference — Kiosk layout needs same Vite/Livewire/Flux asset stack

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=KioskAuth` → ALL PASS
  - [ ] `/kiosk` without session → redirected to `/kiosk/login`
  - [ ] Correct password → session set, redirected to `/kiosk`
  - [ ] Wrong password → error shown, stays on login page
  - [ ] `/kiosk/logout` clears session

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Kiosk login with correct password
    Tool: Playwright (playwright skill)
    Preconditions: Application running, MODULE_PASSWORD set in .env (e.g. "kiosk123")
    Steps:
      1. Navigate to /kiosk
      2. Assert redirected to /kiosk/login (URL contains "/kiosk/login")
      3. Assert page shows password input (selector: `input[type="password"]`)
      4. Assert page shows branding text "Antrian PTSP" or "Pengadilan Agama Penajam"
      5. Fill password input with "kiosk123"
      6. Click submit button (selector: `button[type="submit"]`)
      7. Assert redirected to /kiosk (URL is exactly "/kiosk")
      8. Assert kiosk main page content visible
    Expected Result: Password accepted, session created, user lands on kiosk main page
    Failure Indicators: Login rejected with correct password, redirect loop, session not set
    Evidence: .sisyphus/evidence/task-14-kiosk-login-success.png

  Scenario: Kiosk login with wrong password
    Tool: Playwright (playwright skill)
    Preconditions: Application running, MODULE_PASSWORD set in .env
    Steps:
      1. Navigate to /kiosk/login
      2. Fill password input with "wrong-password"
      3. Click submit button
      4. Assert still on /kiosk/login page
      5. Assert error message visible (selector: contains text "password" and "salah" or "incorrect")
    Expected Result: Login rejected, error message shown, stays on login page
    Failure Indicators: Login accepted with wrong password, no error message, redirect to kiosk
    Evidence: .sisyphus/evidence/task-14-kiosk-login-fail.png
  ```

  **Commit**: YES (groups with Wave 4)
  - Message: `feat(kiosk): add password-protected kiosk module with login gate and session auth`
  - Files: `app/Http/Controllers/KioskController.php`, `resources/views/pages/kiosk/login.blade.php`, `resources/views/layouts/kiosk.blade.php`, `routes/web.php`, `tests/Feature/Kiosk/KioskAuthTest.php`
  - Pre-commit: `php artisan test --compact --filter=KioskAuth`

- [ ] 15. Kiosk Booking UI — Touchscreen-Optimized 3-Step Wizard

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/Kiosk/KioskBookingTest.php`
    - Test `GET /kiosk` (authenticated) returns 200 with service selection step
    - Test service cards display all active services with available quota
    - Test `POST /kiosk/booking` creates a queue ticket with channel='walk_in_kiosk' and correct service_id (matches `CreateQueueTicket` action contract)
    - Test booking validation: service_id required, service must be active, quota not exceeded
    - Test step navigation: step 1 (service) → step 2 (data form) → step 3 (confirmation) → submit
    - Test success page shows ticket number after booking
  - **Route additions** (inside kiosk middleware group):
    - `Route::post('/kiosk/booking', [KioskController::class, 'storeBooking'])->name('kiosk.booking.store')`
  - **Controller additions**:
    - Add `storeBooking(Request $request)` to `KioskController` — reuse same booking logic as `PublicQueueController@storeBooking` (or call shared service class)
    - Validate: service_id required, service exists + is_active, daily quota not exceeded
    - Create QueueTicket with channel='walk_in_kiosk' (must match `CreateQueueTicket` action's allowed channels: 'online_booking', 'assisted_same_day', 'walk_in_kiosk'), queue_number from existing logic
  - **Create Kiosk booking view** (`resources/views/pages/kiosk/index.blade.php`):
    - **Step 1 — Pilih Layanan**: Grid of service cards (2 columns on tablet, 1 on phone)
      - Each card: large service name (text-2xl), description, available quota badge
      - Card dimensions: min-h-[120px], p-8, gap-6 for touch targets
      - Disabled/grayed out cards for services with 0 quota remaining
    - **Step 2 — Data Pemohon**: Data entry form (same fields as booking.blade.php wizard)
      - Large input fields: text-xl, py-4 for touchscreen
      - Clear labels above each field
      - Back button + Next button (both large: py-4 px-8)
    - **Step 3 — Konfirmasi**: Review all entered data
      - Summary card with service name, applicant data
      - Large "Ambil Nomor Antrian" submit button (primary color, py-6, text-2xl)
      - Back button to edit
    - **Success screen**: Large ticket number display (text-6xl font), service name, estimated wait
      - Auto-redirect to Step 1 after 10 seconds (for next user)
    - Use Alpine.js `x-data` wizard pattern (reference: booking.blade.php `bookingWizard()` function)
    - All text in Bahasa Indonesia
  - **Shared booking logic**:
    - If `PublicQueueController@storeBooking` has reusable logic, extract to a service class `app/Services/BookingService.php` OR duplicate the logic in KioskController (prefer service class for DRY)
    - Queue number generation must use same algorithm as public booking

  **Must NOT do**:
  - Do NOT modify the existing `/antrian` booking flow or `PublicQueueController`
  - Do NOT add new fields to the booking form that don't exist in public booking
  - Do NOT change queue_tickets table schema
  - Do NOT use channel='online' or channel='walk-in' — kiosk bookings MUST use channel='walk_in_kiosk' (per `CreateQueueTicket` contract at `app/Actions/Queue/CreateQueueTicket.php:40-44`)
  - Do NOT add print functionality (out of scope)

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Primary focus is touchscreen UI/UX — large cards, touch targets, responsive grid, step transitions
  - **Skills**: [`fluxui-development`, `tailwindcss-development`, `livewire-development`, `pest-testing`]
    - `fluxui-development`: Card components, buttons, inputs, badges for service cards and form
    - `tailwindcss-development`: Touchscreen-specific sizing (text-2xl, p-8, min-h-[120px], gap-6), responsive grid
    - `livewire-development`: If booking wizard uses Livewire for server-side step validation
    - `pest-testing`: TDD for booking flow tests
  - **Skills Evaluated but Omitted**:
    - `developing-with-fortify`: Not related to auth at this point

  **Parallelization**:
  - **Can Run In Parallel**: NO — depends on Task 14 (routes + auth gate)
  - **Parallel Group**: Wave 4 sequential after Task 14 (but Task 16 animations can run after Task 15)
  - **Blocks**: Task 16 (animations added on top of this UI), Task 21 (integration test)
  - **Blocked By**: Task 14 (kiosk routes + auth must exist first)

  **References**:

  **Pattern References**:
  - `resources/views/pages/public/antrian/booking.blade.php:1-647` — **PRIMARY REFERENCE**. The existing 3-step booking wizard with Alpine.js `bookingWizard()` function. Kiosk UI replicates this flow with enhanced touchscreen UX. Copy the Alpine.js data structure and step logic, but rebuild the HTML/CSS for large touch targets
  - `app/Http/Controllers/PublicQueueController.php` — `storeBooking()` method contains the booking logic (queue number generation, validation, ticket creation). Either reuse via service class or replicate in KioskController
  - `resources/views/pages/kiosk/login.blade.php` (created in Task 14) — Kiosk layout reference for consistent branding and styling

  **API/Type References**:
  - `app/Models/QueueTicket.php` — Queue ticket model. Check fillable fields and relationships
  - `app/Models/Service.php` — Service model for card display (name, description, is_active)
  - `app/Enums/QueueStatus.php` — Queue status enum for ticket creation

  **External References**:
  - Flux UI Pro docs: card, badge, button sizing — use `search-docs` with queries ["card", "badge", "button size"]

  **WHY Each Reference Matters**:
  - `booking.blade.php` — **THE** reference for the entire kiosk flow. Contains Alpine.js wizard logic, form fields, validation, API calls. Executor MUST read this file thoroughly before building kiosk version
  - `PublicQueueController.php` — Contains queue number generation algorithm that kiosk must reuse exactly (same numbering sequence)

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=KioskBooking` → ALL PASS
  - [ ] 3-step wizard flows: Service selection → Data form → Confirmation → Success
  - [ ] Service cards are large and touch-friendly (min 120px height, 2xl text)
  - [ ] Input fields are large (text-xl, py-4) for touchscreen
  - [ ] Submit creates queue ticket with channel='walk_in_kiosk'
  - [ ] Success screen shows ticket number in large text (text-6xl)
  - [ ] Auto-redirect to step 1 after 10 seconds

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Complete kiosk booking flow
    Tool: Playwright (playwright skill)
    Preconditions: Kiosk authenticated (session set), at least 2 active services with available quota
    Steps:
      1. Navigate to /kiosk
      2. Assert step 1 visible: service cards displayed in grid
      3. Assert each card has service name in large text (font-size >= 1.5rem)
      4. Assert disabled card exists for service with 0 quota (if any)
      5. Click on first available service card
      6. Assert step 2 visible: data form with input fields
      7. Assert input fields have large padding (computed padding >= 16px)
      8. Fill in required fields (name, etc. — same as public booking)
      9. Click "Selanjutnya" / Next button
      10. Assert step 3 visible: confirmation with service name and entered data
      11. Click "Ambil Nomor Antrian" submit button
      12. Assert success screen shows ticket number in very large text (font-size >= 3rem)
      13. Assert success screen shows service name
      14. Wait 10 seconds
      15. Assert auto-redirected back to step 1 (service selection)
    Expected Result: Full flow completes, ticket created, auto-reset for next user
    Failure Indicators: Step navigation broken, fields not large enough, ticket not created, no auto-redirect
    Evidence: .sisyphus/evidence/task-15-kiosk-booking-flow.png

  Scenario: Service with zero quota is disabled
    Tool: Playwright (playwright skill)
    Preconditions: Kiosk authenticated, at least 1 service with daily quota fully used
    Steps:
      1. Navigate to /kiosk
      2. Find service card for the fully-booked service
      3. Assert card has disabled/grayed-out appearance (opacity or muted colors)
      4. Click on disabled card
      5. Assert nothing happens (no navigation to step 2)
    Expected Result: Cannot select a service with no remaining quota
    Failure Indicators: Card is clickable and advances to step 2, no visual disabled state
    Evidence: .sisyphus/evidence/task-15-kiosk-quota-disabled.png
  ```

  **Commit**: YES (groups with Wave 4)
  - Message: `feat(kiosk): add touchscreen-optimized 3-step booking wizard with large cards and touch-friendly inputs`
  - Files: `resources/views/pages/kiosk/index.blade.php`, `app/Http/Controllers/KioskController.php`, `app/Services/BookingService.php` (if extracted), `routes/web.php`, `tests/Feature/Kiosk/KioskBookingTest.php`
  - Pre-commit: `php artisan test --compact --filter=KioskBooking`

- [ ] 16. Kiosk Animations — Button Press Feedback + Step Transitions

  **What to do**:
  - **Add CSS animations** for button press feedback:
    - **Scale-down on press**: `active:scale-95` with `transition-transform duration-150` on all interactive cards and buttons
    - **Color flash on press**: `active:bg-{color}-600` (darker shade) with transition for visual feedback
    - **Ripple effect** (optional enhancement): Alpine.js ripple directive or CSS-only ripple on tap
  - **Add step transition animations** using Alpine.js:
    - **Slide transition**: `x-transition:enter="transform transition ease-out duration-300"` with `x-transition:enter-start="translate-x-full opacity-0"` and `x-transition:enter-end="translate-x-0 opacity-100"`
    - **Fade out for previous step**: `x-transition:leave="transform transition ease-in duration-200"` with `x-transition:leave-start="translate-x-0 opacity-100"` and `x-transition:leave-end="-translate-x-full opacity-0"`
  - **Add success confirmation animation**:
    - **Checkmark animation**: CSS animated checkmark (SVG circle + check path with stroke-dasharray animation) on success screen
    - **Ticket number reveal**: Fade-in + scale-up animation for the large ticket number (from scale-50 opacity-0 to scale-100 opacity-100)
    - **Progress indicator**: Countdown bar or timer showing 10-second auto-redirect (shrinking bar or circular progress)
  - **Touch state indicators**:
    - Service cards: slight shadow lift on hover/touch (`hover:shadow-lg active:shadow-sm`)
    - Form inputs: clear focus ring (`focus:ring-4 focus:ring-blue-300`) for visibility
  - **Pest Tests** (visual behavior via feature tests):
    - Test that animation CSS classes exist on interactive elements (check rendered HTML for transition/animation classes)
    - Test that Alpine.js `x-transition` attributes are present on step containers

  **Must NOT do**:
  - Do NOT use external animation libraries (GSAP, Framer Motion, etc.) — CSS + Alpine.js only
  - Do NOT add animations that delay user interaction (keep transitions under 300ms)
  - Do NOT add sound effects
  - Do NOT modify the booking logic (Task 15) — only add visual enhancements

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Pure UI/animation work — CSS transitions, Alpine.js transitions, SVG animations
  - **Skills**: [`tailwindcss-development`, `fluxui-development`]
    - `tailwindcss-development`: Tailwind transition utilities, active/hover states, animation classes
    - `fluxui-development`: Ensure animations don't conflict with Flux UI component behavior
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: No Livewire reactivity changes, only CSS/Alpine additions
    - `pest-testing`: Minimal test additions (class presence checks)

  **Parallelization**:
  - **Can Run In Parallel**: NO — must be applied on top of Task 15's UI
  - **Parallel Group**: Wave 4 sequential (after Task 15)
  - **Blocks**: Task 21 (integration test)
  - **Blocked By**: Task 15 (kiosk booking UI must exist first to add animations)

  **References**:

  **Pattern References**:
  - `resources/views/pages/kiosk/index.blade.php` (created in Task 15) — The file to enhance with animations. Add CSS classes and Alpine.js transition directives to existing elements
  - `resources/views/pages/public/antrian/booking.blade.php` — Check if existing public wizard has any transitions to be consistent with or improve upon

  **External References**:
  - Alpine.js transitions: `x-transition` directive — use `search-docs` with queries ["alpine transition", "x-transition"]
  - Tailwind CSS transitions: `transition`, `duration`, `ease` utilities — use `search-docs` with queries ["transition", "animation", "transform"]
  - CSS checkmark animation: SVG stroke-dasharray technique for animated checkmark

  **WHY Each Reference Matters**:
  - `kiosk/index.blade.php` — THE file being enhanced. All animations are added to elements in this file
  - Alpine.js transitions — Step transitions use Alpine's `x-transition` system, must follow correct syntax

  **Acceptance Criteria**:
  - [ ] Button press shows visible scale-down effect (active:scale-95)
  - [ ] Step transitions animate smoothly (slide left/right)
  - [ ] Success screen has animated checkmark
  - [ ] Ticket number fades in with scale animation
  - [ ] 10-second countdown visible (progress bar or timer)
  - [ ] All animations complete under 300ms (no sluggish UI)

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Button press animation feedback
    Tool: Playwright (playwright skill)
    Preconditions: Kiosk authenticated, on step 1 (service selection)
    Steps:
      1. Navigate to /kiosk
      2. Locate first service card
      3. Assert card has CSS classes containing "transition" and "active:scale"
      4. Mouse down on card (to trigger :active state)
      5. Take screenshot during active state
      6. Mouse up
      7. Assert card returns to normal scale
    Expected Result: Card visibly scales down on press and returns
    Failure Indicators: No transition classes, no visual change on press
    Evidence: .sisyphus/evidence/task-16-button-animation.png

  Scenario: Step transition animation
    Tool: Playwright (playwright skill)
    Preconditions: Kiosk authenticated, on step 1
    Steps:
      1. Navigate to /kiosk
      2. Select a service to advance to step 2
      3. Assert step container has x-transition attributes in HTML
      4. Take screenshot during transition (if capturable)
      5. Assert step 2 is visible after transition completes
    Expected Result: Smooth slide/fade transition between steps
    Failure Indicators: Instant switch (no animation), x-transition attributes missing
    Evidence: .sisyphus/evidence/task-16-step-transition.png

  Scenario: Success screen checkmark animation
    Tool: Playwright (playwright skill)
    Preconditions: Complete a booking to reach success screen
    Steps:
      1. Complete full booking flow (steps 1-3, submit)
      2. Assert success screen visible
      3. Assert SVG checkmark element exists (selector: `svg.checkmark` or similar)
      4. Assert countdown/progress indicator visible
      5. Take screenshot of success screen
    Expected Result: Animated checkmark + ticket number + countdown visible
    Failure Indicators: No checkmark animation, no countdown, static display
    Evidence: .sisyphus/evidence/task-16-success-animation.png
  ```

  **Commit**: YES (groups with Wave 4)
  - Message: `feat(kiosk): add touch feedback animations, step transitions, and success screen animations`
  - Files: `resources/views/pages/kiosk/index.blade.php`
  - Pre-commit: `php artisan test --compact --filter=Kiosk`


### Wave 5 — TV Display Module (depends on Wave 1 middleware + config)

- [ ] 17. TV Display Password Gate — Login Page + Session Auth + Routes

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/TvDisplay/TvDisplayAuthTest.php`
    - Test `GET /tv-display` without session redirects to `/tv-display/login`
    - Test `GET /tv-display/login` returns 200 with password form
    - Test `POST /tv-display/login` with correct password sets session and redirects to `/tv-display`
    - Test `POST /tv-display/login` with wrong password returns back with error
    - Test `POST /tv-display/logout` clears session and redirects to login
    - Test middleware `module.password:tv-display` blocks unauthenticated access
  - **Create TV Display routes** in `routes/web.php`:
    - `Route::get('/tv-display/login', [TvDisplayController::class, 'showLogin'])->name('tv-display.login')`
    - `Route::post('/tv-display/login', [TvDisplayController::class, 'login'])->name('tv-display.authenticate')`
    - `Route::post('/tv-display/logout', [TvDisplayController::class, 'logout'])->name('tv-display.logout')`
    - Route group with `module.password:tv-display` middleware:
      - `Route::get('/tv-display', [TvDisplayController::class, 'index'])->name('tv-display.index')`
  - **Create TvDisplayController** (`app/Http/Controllers/TvDisplayController.php`):
    - `showLogin()` — return TV display login view
    - `login(Request $request)` — validate password against `config('kiosk.password')` (shared password), set session keys `tv_display_authenticated` + `tv_display_authenticated_at`
    - `logout()` — forget session keys, redirect to `/tv-display/login`
    - `index()` — return TV display main view
  - **Create TV Display login view** (`resources/views/pages/tv-display/login.blade.php`):
    - Similar to kiosk login but branded for TV Display
    - "Mode Display Antrian" heading
    - Password input + submit button
    - Branding: "Antrian PTSP Pengadilan Agama Penajam"
  - **Create TV Display layout** (`resources/views/layouts/tv-display.blade.php`):
    - Landscape-optimized minimal layout
    - No sidebar, no header — full-screen content area
    - Include Livewire/Flux UI assets + Tailwind CSS
    - Overflow hidden for TV display (no scrollbars)

  **Must NOT do**:
  - Do NOT create a separate password — reuse same `config('kiosk.password')`
  - Do NOT modify existing `/display` public route (that stays as-is)
  - Do NOT add user authentication — password-only session

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: New module creation with routes, controller, views, layout, and tests
  - **Skills**: [`pest-testing`, `developing-with-fortify`, `fluxui-development`]
    - `pest-testing`: TDD for auth flow
    - `developing-with-fortify`: Session-based auth patterns
    - `fluxui-development`: Login form UI
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Login page doesn't need Livewire
    - `tailwindcss-development`: Minimal styling on login page

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 14 — Kiosk gate is similar but independent)
  - **Parallel Group**: Wave 5 (with Tasks 18, 19) — but Tasks 18-19 depend on Task 17
  - **Blocks**: Tasks 18, 19 (need routes and auth gate before building display UI)
  - **Blocked By**: Task 3 (config/kiosk.php), Task 4 (CheckModulePassword middleware)

  **References**:

  **Pattern References**:
  - `app/Http/Controllers/KioskController.php` (created in Task 14) — **PRIMARY REFERENCE**. TV Display auth follows identical pattern — copy login/logout logic, change session key names from `kiosk_*` to `tv_display_*`
  - `resources/views/pages/kiosk/login.blade.php` (created in Task 14) — Copy login view structure, adjust heading text
  - `resources/views/layouts/kiosk.blade.php` (created in Task 14) — Copy layout, adjust for landscape optimization
  - `app/Http/Middleware/CheckModulePassword.php` (created in Task 4) — Same middleware, different module parameter

  **API/Type References**:
  - `config('kiosk.password')` — Shared password for both modules
  - `config('kiosk.session_lifetime')` — Same session lifetime

  **WHY Each Reference Matters**:
  - `KioskController.php` — Almost identical auth flow. Copy and adjust session key names to avoid conflicts
  - `CheckModulePassword.php` — Middleware uses module parameter to determine which session keys to check

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=TvDisplayAuth` → ALL PASS
  - [ ] `/tv-display` without session → redirected to `/tv-display/login`
  - [ ] Correct password → session set, redirected to `/tv-display`
  - [ ] Wrong password → error shown, stays on login page
  - [ ] Uses SAME password as kiosk (shared config)

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: TV Display login with correct password
    Tool: Playwright (playwright skill)
    Preconditions: Application running, MODULE_PASSWORD set in .env
    Steps:
      1. Navigate to /tv-display
      2. Assert redirected to /tv-display/login
      3. Assert page shows password input and "Mode Display Antrian" or similar heading
      4. Fill password input with correct password (same as kiosk)
      5. Click submit
      6. Assert redirected to /tv-display
      7. Assert TV display main content visible
    Expected Result: Shared password works for TV Display, session created
    Failure Indicators: Different password required, redirect loop, session conflict with kiosk
    Evidence: .sisyphus/evidence/task-17-tv-login-success.png

  Scenario: TV Display login with wrong password
    Tool: Playwright (playwright skill)
    Preconditions: Application running
    Steps:
      1. Navigate to /tv-display/login
      2. Fill password with "wrong-password"
      3. Click submit
      4. Assert still on /tv-display/login
      5. Assert error message visible
    Expected Result: Login rejected with error message
    Failure Indicators: Login accepted with wrong password
    Evidence: .sisyphus/evidence/task-17-tv-login-fail.png
  ```

  **Commit**: YES (groups with Wave 5)
  - Message: `feat(tv-display): add password-protected TV display module with login gate and session auth`
  - Files: `app/Http/Controllers/TvDisplayController.php`, `resources/views/pages/tv-display/login.blade.php`, `resources/views/layouts/tv-display.blade.php`, `routes/web.php`, `tests/Feature/TvDisplay/TvDisplayAuthTest.php`
  - Pre-commit: `php artisan test --compact --filter=TvDisplayAuth`

- [ ] 18. TV Display Landscape UI — Active Calls + Queue Status Board

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/TvDisplay/TvDisplayTest.php`
    - Test `GET /tv-display` (authenticated) returns 200 with active calls section
    - Test Livewire component loads and displays current queue calls
    - Test empty state shows "Belum ada antrian dipanggil" when no active calls
  - **Create Livewire component** `app/Livewire/TvDisplay/QueueBoard.php`:
    - Reuse query logic from existing `QueueDisplay.php` (lines 24-43): `currentCalls()` and `recentCalls()`
    - `currentCalls()`: Active queue tickets with Called status, ordered by called_at desc, limit 5
    - `recentCalls()`: Recently called tickets (regardless of current status), ordered by `called_at` desc, limit 10 (same query as `QueueDisplay.php:35-42` — uses `whereNotNull('called_at')->orderByDesc('called_at')`)
    - Properties for `$currentCalls`, `$recentCalls`
    - Use Livewire polling: `#[Polling('5s')]` or `wire:poll.5s` for real-time updates
  - **Create TV Display main view** (`resources/views/pages/tv-display/index.blade.php`):
    - **Landscape layout** (optimized for 16:9 TV screens):
      - Left section (60% width): Active calls board
        - Large header: "ANTRIAN SAAT INI" (text-4xl)
        - Call cards: queue number (text-8xl, bold, primary color), service name (text-2xl), counter name (text-3xl, "Loket X")
        - Show up to 5 active calls in a grid/stack
      - Right section (40% width): Recent calls + info
        - "RIWAYAT PANGGILAN" header (text-2xl)
        - Table/list of last 10 completed calls (smaller text: text-lg)
        - Each row: queue number, service, counter, time called
      - Bottom bar: date/time display, branding "Antrian PTSP Pengadilan Agama Penajam"
    - **Call highlight animation**: When a new call appears at the top, flash/pulse animation (CSS `@keyframes pulse` or Tailwind `animate-pulse`) for 5 seconds
    - **Large, readable fonts**: Everything must be readable from 5+ meters distance
    - **Dark background recommended**: Dark bg with white/bright text for TV readability (`bg-gray-900 text-white`)
    - **No scrolling**: Content fits in viewport, overflow hidden
  - **Wrap in Livewire component** in the view:
    - `<livewire:tv-display.queue-board />` inside TV display layout

  **Must NOT do**:
  - Do NOT modify existing `QueueDisplay.php` Livewire component or `/display` public route
  - Do NOT add TTS (text-to-speech) — existing public display already has TTS
  - Do NOT add queue management actions (call next, complete, etc.) — this is display-only
  - Do NOT change queue_tickets or queue_activities table schema

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Primary focus is landscape TV UI — large fonts, grid layout, dark theme, call animations, readability from distance
  - **Skills**: [`livewire-development`, `tailwindcss-development`, `fluxui-development`, `pest-testing`]
    - `livewire-development`: Livewire polling component, reactive data updates
    - `tailwindcss-development`: TV-optimized layout (landscape grid, large text, dark theme, animation keyframes)
    - `fluxui-development`: Card/badge components for call display
    - `pest-testing`: TDD for component tests
  - **Skills Evaluated but Omitted**:
    - `developing-with-fortify`: Not related to auth

  **Parallelization**:
  - **Can Run In Parallel**: NO — depends on Task 17 (auth gate + routes)
  - **Parallel Group**: Wave 5 sequential after Task 17 (but can run in parallel with Task 19 auto-refresh)
  - **Blocks**: Task 19 (auto-refresh builds on this), Task 21 (integration test)
  - **Blocked By**: Task 17 (TV Display routes + auth must exist)

  **References**:

  **Pattern References**:
  - `app/Livewire/QueueDisplay.php:1-44` — **PRIMARY REFERENCE**. Contains `currentCalls()` at lines 24-34 and `recentCalls()` at lines 36-43 with exact Eloquent queries. Copy these queries into the new QueueBoard component
  - `resources/views/livewire/queue-display.blade.php` — Existing display view. Reference for data structure rendered (ticket number, service name, counter name, timestamps)
  - `resources/views/layouts/tv-display.blade.php` (created in Task 17) — Layout for the TV display page

  **API/Type References**:
  - `app/Models/QueueTicket.php` — Queue ticket model with relationships (service, counter)
  - `app/Enums/QueueStatus.php` — Called status enum value for filtering active calls
  - `app/Models/Service.php` — Service name for display
  - `app/Models/Counter.php` — Counter name for "Loket X" display

  **External References**:
  - Livewire polling docs — use `search-docs` with queries ["polling", "wire:poll"]

  **WHY Each Reference Matters**:
  - `QueueDisplay.php` — Contains the EXACT Eloquent queries for current and recent calls. Must reuse same query logic to ensure data consistency between old public display and new TV display
  - `QueueStatus.php` — Need the exact enum value for 'Called' status filtering

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=TvDisplay` → ALL PASS
  - [ ] Landscape layout with left (active calls) and right (recent calls) sections
  - [ ] Active call shows queue number in very large text (text-8xl)
  - [ ] Up to 5 active calls displayed, 10 recent calls
  - [ ] Dark background with high-contrast text
  - [ ] New call triggers pulse/flash animation
  - [ ] No scrollbars visible on TV screen

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: TV Display shows active calls
    Tool: Playwright (playwright skill)
    Preconditions: TV Display authenticated, at least 2 queue tickets with Called status in DB
    Steps:
      1. Navigate to /tv-display
      2. Assert left section shows "ANTRIAN SAAT INI" or similar header
      3. Assert at least 2 call cards visible
      4. Assert first call card has queue number in very large text (font-size >= 4rem)
      5. Assert call card shows service name and counter name ("Loket X")
      6. Assert right section shows "RIWAYAT PANGGILAN" header
      7. Assert recent calls list has entries
      8. Assert bottom bar shows date/time and branding
      9. Assert page has dark background (computed bg color is dark)
      10. Assert no horizontal or vertical scrollbar (overflow hidden)
    Expected Result: Professional TV display with active calls prominently shown
    Failure Indicators: Small text, scrollbars visible, no call data, light theme
    Evidence: .sisyphus/evidence/task-18-tv-display-layout.png

  Scenario: TV Display empty state
    Tool: Playwright (playwright skill)
    Preconditions: TV Display authenticated, NO queue tickets with Called status
    Steps:
      1. Navigate to /tv-display
      2. Assert empty state message visible: "Belum ada antrian dipanggil" or similar
      3. Assert layout still renders correctly (no broken UI)
    Expected Result: Clean empty state message instead of blank space
    Failure Indicators: Blank area, error, broken layout
    Evidence: .sisyphus/evidence/task-18-tv-empty-state.png
  ```

  **Commit**: YES (groups with Wave 5)
  - Message: `feat(tv-display): add landscape queue board with active calls, recent history, and call highlight animations`
  - Files: `app/Livewire/TvDisplay/QueueBoard.php`, `resources/views/livewire/tv-display/queue-board.blade.php`, `resources/views/pages/tv-display/index.blade.php`, `tests/Feature/TvDisplay/TvDisplayTest.php`
  - Pre-commit: `php artisan test --compact --filter=TvDisplay`

- [ ] 19. TV Display Auto-Refresh + Fullscreen Mode

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Add to `tests/Feature/TvDisplay/TvDisplayTest.php`
    - Test that Livewire component uses polling (check for wire:poll attribute in rendered HTML)
    - Test fullscreen toggle button exists in UI
  - **Livewire polling** (may already be set up in Task 18):
    - Verify `QueueBoard` component uses `#[Polling('5s')]` attribute or `wire:poll.5s` in Blade
    - If Livewire connection drops, show reconnection indicator (Livewire handles this natively but verify)
  - **Fullscreen mode toggle**:
    - Add small "Fullscreen" button in bottom bar (unobtrusive, only visible on hover/for setup)
    - Use JavaScript Fullscreen API: `document.documentElement.requestFullscreen()`
    - Alpine.js component: `x-data="{ fullscreen: false }"` with toggle method
    - Auto-hide cursor after 3 seconds of inactivity (CSS: `cursor: none` via Alpine timeout)
  - **Graceful error handling**:
    - If Livewire polling fails (network issue), show subtle "Koneksi terputus" indicator instead of breaking UI
    - Auto-retry connection (Livewire handles this, but add visual indicator)
  - **Clock display**:
    - Real-time clock in bottom bar using Alpine.js `setInterval` (updates every second)
    - Format: "Senin, 8 Maret 2026 — 10:30:45 WIB"

  **Must NOT do**:
  - Do NOT add remote control functionality
  - Do NOT add configuration panel on TV display (configuration is via admin)
  - Do NOT modify Livewire polling interval without reason (5s is standard)

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Enhancement task — adding fullscreen toggle + clock + error indicator on existing UI. Small additions
  - **Skills**: [`livewire-development`, `tailwindcss-development`]
    - `livewire-development`: Verify polling setup, connection state handling
    - `tailwindcss-development`: Cursor hide, hover states for fullscreen button
  - **Skills Evaluated but Omitted**:
    - `pest-testing`: Minimal test additions (attribute checks)
    - `fluxui-development`: Simple button addition, no complex Flux components

  **Parallelization**:
  - **Can Run In Parallel**: PARTIALLY — can be done alongside Task 18 if coordinated, but safer after
  - **Parallel Group**: Wave 5 (after Task 18)
  - **Blocks**: Task 21 (integration test)
  - **Blocked By**: Task 18 (TV Display UI must exist first)

  **References**:

  **Pattern References**:
  - `app/Livewire/TvDisplay/QueueBoard.php` (created in Task 18) — Component to verify polling on
  - `resources/views/pages/tv-display/index.blade.php` (created in Task 18) — View to add fullscreen button and clock to
  - `app/Livewire/QueueDisplay.php:22` — Reference for existing polling pattern (`WithoutLazy` or polling attribute)

  **External References**:
  - MDN Fullscreen API: `Element.requestFullscreen()` — standard browser API
  - Livewire polling docs — use `search-docs` with queries ["polling", "wire:poll"]

  **WHY Each Reference Matters**:
  - `QueueBoard.php` — Must verify this component polls correctly
  - `QueueDisplay.php` — Reference for how existing display handles polling (copy pattern)

  **Acceptance Criteria**:
  - [ ] Livewire component polls every 5 seconds (wire:poll.5s present)
  - [ ] Fullscreen toggle button works (enters/exits fullscreen mode)
  - [ ] Real-time clock updates every second in bottom bar
  - [ ] Cursor auto-hides after 3 seconds of inactivity
  - [ ] Connection loss shows "Koneksi terputus" indicator

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Auto-refresh polling works
    Tool: Playwright (playwright skill)
    Preconditions: TV Display authenticated with active calls
    Steps:
      1. Navigate to /tv-display
      2. Note current call data displayed
      3. Via tinker/DB: add a new Called queue ticket
      4. Wait 6 seconds (polling interval + buffer)
      5. Assert new call appears in the active calls section without page reload
    Expected Result: New call appears automatically via Livewire polling
    Failure Indicators: New call doesn't appear, requires manual refresh, error shown
    Evidence: .sisyphus/evidence/task-19-polling-refresh.txt

  Scenario: Fullscreen mode toggle
    Tool: Playwright (playwright skill)
    Preconditions: TV Display authenticated
    Steps:
      1. Navigate to /tv-display
      2. Hover over bottom bar area to reveal fullscreen button
      3. Click fullscreen button (selector: `button` containing "Fullscreen" or fullscreen icon)
      4. Assert browser enters fullscreen mode (viewport matches screen resolution)
      5. Press Escape or click toggle again
      6. Assert browser exits fullscreen mode
    Expected Result: Fullscreen mode toggles correctly
    Failure Indicators: Button not found, fullscreen doesn't activate, no exit option
    Evidence: .sisyphus/evidence/task-19-fullscreen-toggle.png
  ```

  **Commit**: YES (groups with Wave 5)
  - Message: `feat(tv-display): add auto-refresh polling, fullscreen mode, real-time clock, and connection indicator`
  - Files: `app/Livewire/TvDisplay/QueueBoard.php`, `resources/views/pages/tv-display/index.blade.php`, `tests/Feature/TvDisplay/TvDisplayTest.php`
  - Pre-commit: `php artisan test --compact --filter=TvDisplay`


### Wave 6 — Polish & Integration (depends on ALL previous waves)

- [ ] 20. Breadcrumbs + Empty States Audit

  **What to do**:
  - **Pest Tests FIRST (TDD RED)**:
    - Create `tests/Feature/Admin/BreadcrumbTest.php`
    - Test each admin page (`/admin/layanan`, `/admin/loket`, `/admin/users`, `/dashboard`) renders breadcrumb component
    - Test breadcrumb shows correct hierarchy (e.g., "Dashboard > Layanan" on layanan page)
    - Test empty states show appropriate messages when tables have no data
  - **Create breadcrumb Blade component** (`resources/views/components/breadcrumb.blade.php`):
    - Accept `$items` array prop: `[['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Layanan']]`
    - Last item is current page (no link, bold/active style)
    - Use `<flux:breadcrumbs>` if Flux UI Pro has one, otherwise create with `<nav aria-label="Breadcrumb">` + `<ol>` pattern
    - Separator: chevron or slash between items
    - Responsive: truncate on mobile if too long
  - **Add breadcrumbs to ALL admin pages**:
    - `/dashboard` (Dashboard): `Dashboard` (single item, no parent)
    - `/admin/layanan`: `Dashboard > Layanan`
    - `/admin/loket`: `Dashboard > Loket`
    - `/admin/users`: `Dashboard > Users`
  - **Empty states audit** — check all tables and charts:
    - Layanan table: "Belum ada layanan. Buat layanan pertama di form di atas."
    - Loket table: "Belum ada loket. Buat loket pertama di form di atas."
    - Users table: "Belum ada user selain Anda."
    - Dashboard stat cards: Show "0" with muted style (not blank/error)
    - Dashboard charts: "Belum ada data untuk ditampilkan" placeholder inside chart area
    - Activity log: "Belum ada aktivitas" (already handled in Task 9, verify)
    - Role distribution: "Belum ada data role" (edge case)
  - **Use `<flux:table>` empty slot** if Flux supports it, otherwise wrap table body in `@forelse` / `@empty`

  **Must NOT do**:
  - Do NOT add breadcrumbs to Kiosk or TV Display pages (they have their own minimal layouts)
  - Do NOT add breadcrumbs to public pages
  - Do NOT create complex nested breadcrumb logic — admin is max 2 levels deep

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Breadcrumb is a simple component + adding it to 4 pages. Empty states are `@forelse`/`@empty` additions. Low complexity.
  - **Skills**: [`fluxui-development`, `pest-testing`]
    - `fluxui-development`: Check for Flux breadcrumb component, table empty state pattern
    - `pest-testing`: Assert breadcrumb presence in rendered HTML
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Breadcrumbs are static Blade components, no reactivity
    - `tailwindcss-development`: Minimal styling (separator, active state)

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 21)
  - **Parallel Group**: Wave 6 (with Task 21)
  - **Blocks**: None
  - **Blocked By**: Tasks 6-12 (admin pages must exist with their final structure before adding breadcrumbs/empty states)

  **References**:

  **Pattern References**:
  - `resources/views/pages/admin/layanan/index.blade.php` (updated by Task 10) — Add breadcrumb at top + verify empty state in table
  - `resources/views/pages/admin/loket/index.blade.php` (updated by Task 11) — Same
  - `resources/views/pages/admin/users/index.blade.php` (updated by Task 12) — Same
  - `resources/views/dashboard.blade.php` — Dashboard page, add breadcrumb
  - `resources/views/livewire/dashboard/admin-dashboard.blade.php` (updated by Tasks 7-9) — Verify empty states in charts and stat cards

  **External References**:
  - Flux UI Pro docs: check for `<flux:breadcrumbs>` component — use `search-docs` with queries ["breadcrumbs", "breadcrumb"]
  - Flux UI Pro docs: table empty state — use `search-docs` with queries ["table empty", "no results"]

  **WHY Each Reference Matters**:
  - All admin views — Must add breadcrumb component to each page, requires knowing their current layout structure
  - Admin dashboard component — Must audit charts and stat cards for empty state handling

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=Breadcrumb` → ALL PASS
  - [ ] Every admin page shows breadcrumb navigation
  - [ ] Breadcrumb links work (Dashboard link navigates to /admin)
  - [ ] All tables show Indonesian empty state message when no data
  - [ ] Dashboard charts show placeholder when no data
  - [ ] Stat cards show "0" (not blank) when no data

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Breadcrumbs on admin pages
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin
    Steps:
      1. Navigate to /admin/layanan
      2. Assert breadcrumb visible (selector: `nav[aria-label="Breadcrumb"]` or flux breadcrumb)
      3. Assert breadcrumb shows "Dashboard" link + "Layanan" current item
      4. Click "Dashboard" in breadcrumb
      5. Assert navigated to /admin (dashboard page)
      6. Navigate to /admin/loket
      7. Assert breadcrumb shows "Dashboard" + "Loket"
      8. Navigate to /admin/users
      9. Assert breadcrumb shows "Dashboard" + "Users"
    Expected Result: All admin pages have working breadcrumb navigation
    Failure Indicators: Breadcrumb missing, wrong labels, links broken
    Evidence: .sisyphus/evidence/task-20-breadcrumbs.png

  Scenario: Empty states for tables
    Tool: Playwright (playwright skill)
    Preconditions: Logged in as Admin, empty database (no services, no counters, only admin user)
    Steps:
      1. Navigate to /admin/layanan
      2. Assert table shows empty state message containing "Belum ada layanan"
      3. Navigate to /admin/loket
      4. Assert empty state message containing "Belum ada loket"
      5. Navigate to /admin/users
      6. Assert empty state or single row (admin user)
      7. Navigate to /admin (dashboard)
      8. Assert stat cards show "0" values (not blank)
      9. Assert chart areas show placeholder text ("Belum ada data")
    Expected Result: Clean empty states with Indonesian messages everywhere
    Failure Indicators: Blank spaces, errors, English messages, broken layout with no data
    Evidence: .sisyphus/evidence/task-20-empty-states.png
  ```

  **Commit**: YES (groups with Wave 6)
  - Message: `feat(admin): add breadcrumb navigation to all admin pages and audit empty states with Indonesian messages`
  - Files: `resources/views/components/breadcrumb.blade.php`, `resources/views/pages/admin/layanan/index.blade.php`, `resources/views/pages/admin/loket/index.blade.php`, `resources/views/pages/admin/users/index.blade.php`, `resources/views/dashboard.blade.php`, `tests/Feature/Admin/BreadcrumbTest.php`
  - Pre-commit: `php artisan test --compact --filter=Breadcrumb`

- [ ] 21. Cross-Module Integration Test

  **What to do**:
  - **Create comprehensive integration test** `tests/Feature/IntegrationTest.php`:
    - **Full flow test**: Create service → Create counter → Assign service to user → Book via kiosk → Officer calls queue → Verify TV display shows call → Verify dashboard stats updated
    - Test steps:
      1. Create a Service (POST /admin/layanan) → assert service exists
      2. Create a Counter (POST /admin/loket) → assert counter exists, assigned to same pool
      3. Create a User with Officer role + assign service (POST /admin/users) → assert user exists
      4. Authenticate kiosk session (POST /kiosk/login with correct password)
      5. Book a queue ticket via kiosk (POST /kiosk/booking with service_id) → assert ticket created with status 'Waiting'
      6. Simulate officer calling the queue (via existing call mechanism) → assert ticket status changes to 'Called'
      7. GET /tv-display (authenticated) → assert response contains the queue ticket number in active calls
      8. GET /admin (dashboard, as admin) → assert stat cards show updated counts (at least 1 ticket today)
    - **Nav consistency test**: Assert sidebar and header have identical navigation links
    - **Theme toggle test**: Toggle dark mode, refresh, assert localStorage persists, toggle back
    - **Route redirect test**: Verify /admin/roles and /admin/izin-layanan return 301 to /admin/users
  - **Use factories** for data setup where possible
  - **Use `actingAs()` for admin routes**, session manipulation for kiosk/tv-display routes

  **Must NOT do**:
  - Do NOT test individual CRUD operations in detail (that's Tasks 10-12)
  - Do NOT test Livewire component internals (that's unit test territory)
  - Focus on CROSS-MODULE data flow only

  **Recommended Agent Profile**:
  - **Category**: `deep`
    - Reason: Integration test requires understanding all modules and their data flow. Must coordinate admin, kiosk, officer, and TV display flows in a single test
  - **Skills**: [`pest-testing`, `livewire-development`]
    - `pest-testing`: Complex multi-step feature test with factory setup, session manipulation, and cross-route assertions
    - `livewire-development`: Testing Livewire component rendering in integration context
  - **Skills Evaluated but Omitted**:
    - `fluxui-development`: Not testing UI components, testing data flow
    - `tailwindcss-development`: Not testing styles

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 20)
  - **Parallel Group**: Wave 6 (with Task 20)
  - **Blocks**: Final Verification Wave (F1-F4)
  - **Blocked By**: ALL previous tasks (Tasks 1-19 must be complete — this tests the whole system)

  **References**:

  **Pattern References**:
  - ALL controllers created/modified in Tasks 10-15, 17-18 — Integration test exercises all of them in sequence
  - `app/Http/Controllers/PublicQueueController.php` — Reference for booking logic (kiosk reuses this)
  - `app/Livewire/TvDisplay/QueueBoard.php` (Task 18) — Component queried in integration test
  - `tests/Feature/` — Follow existing test patterns for factory usage and authentication helpers

  **API/Type References**:
  - `app/Models/Service.php`, `app/Models/Counter.php`, `app/Models/User.php`, `app/Models/QueueTicket.php` — All models involved in the integration flow
  - `app/Enums/QueueStatus.php` — Status transitions: Waiting → Called → Completed
  - `app/Enums/UserRole.php` — Role assignment for officer user
  - `config('kiosk.password')` — For kiosk/tv-display session setup

  **Test References**:
  - All test files from Tasks 10-19 — Individual module tests. Integration test builds on top of these

  **WHY Each Reference Matters**:
  - All controllers/models — Integration test chains operations across ALL modules. Must know each endpoint's request format
  - `QueueStatus.php` — Must know exact status values to assert state transitions
  - Existing test patterns — Must match factory usage and auth helper conventions

  **Acceptance Criteria**:
  - [ ] `php artisan test --compact --filter=Integration` → ALL PASS
  - [ ] Full flow test: admin create → kiosk book → officer call → tv display → dashboard stats
  - [ ] Nav consistency verified (sidebar matches header links)
  - [ ] Theme toggle persists across refresh
  - [ ] Route redirects return 301

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Full end-to-end flow verification
    Tool: Bash (php artisan test)
    Preconditions: All previous tasks (1-20) completed, app running
    Steps:
      1. Run: php artisan test --compact --filter=IntegrationTest
      2. Assert all tests pass (0 failures)
      3. Assert test output shows the full flow test completed
    Expected Result: All integration tests pass, confirming cross-module data flow works
    Failure Indicators: Any test failure indicates broken integration between modules
    Evidence: .sisyphus/evidence/task-21-integration-test.txt

  Scenario: Manual cross-module verification via Playwright
    Tool: Playwright (playwright skill)
    Preconditions: Admin logged in, kiosk password set, at least 1 service + 1 counter configured
    Steps:
      1. Login as Admin, navigate to /admin
      2. Assert dashboard loads with stat cards
      3. Navigate to /admin/layanan, assert CRUD controls visible
      4. Navigate to /admin/loket, assert CRUD controls visible
      5. Navigate to /admin/users, assert tabbed UI with 3 sections
      6. Open new tab, navigate to /kiosk/login
      7. Login with kiosk password
      8. Assert kiosk step 1 shows service cards
      9. Open new tab, navigate to /tv-display/login
      10. Login with same password
      11. Assert TV display shows landscape layout
      12. Back to admin tab, verify dashboard reflects any bookings made
    Expected Result: All modules accessible and functional end-to-end
    Failure Indicators: Any page fails to load, auth issues, data not reflecting across modules
    Evidence: .sisyphus/evidence/task-21-e2e-manual.png
  ```

  **Commit**: YES (groups with Wave 6)
  - Message: `test: add cross-module integration test covering admin → kiosk → officer → tv-display → dashboard flow`
  - Files: `tests/Feature/IntegrationTest.php`
  - Pre-commit: `php artisan test --compact --filter=Integration`

---

## Final Verification Wave (MANDATORY — after ALL implementation tasks)

> 4 review agents run in PARALLEL. ALL must APPROVE. Rejection → fix → re-run.

- [ ] F1. **Plan Compliance Audit** — `oracle`
  Read the plan end-to-end. For each "Must Have": verify implementation exists (read file, curl endpoint, run command). For each "Must NOT Have": search codebase for forbidden patterns — reject with file:line if found. Check evidence files exist in .sisyphus/evidence/. Compare deliverables against plan.
  Output: `Must Have [N/N] | Must NOT Have [N/N] | Tasks [N/N] | VERDICT: APPROVE/REJECT`

- [ ] F2. **Code Quality Review** — `unspecified-high`
  Run `vendor/bin/pint --dirty --format agent` + `php artisan test --compact`. Review all changed files for: `as any`/`@ts-ignore` (N/A for PHP), empty catches, `dd()`/`dump()`/`ray()` in prod, commented-out code, unused imports. Check AI slop: excessive comments, over-abstraction, generic names (data/result/item/temp).
  Output: `Pint [PASS/FAIL] | Tests [N pass/N fail] | Files [N clean/N issues] | VERDICT`

- [ ] F3. **Real Manual QA** — `unspecified-high` (+ `playwright` skill for UI)
  Start from clean state. Execute EVERY QA scenario from EVERY task — follow exact steps, capture evidence. Test cross-task integration (features working together, not isolation). Test edge cases: empty state, invalid input, rapid actions. Save to `.sisyphus/evidence/final-qa/`.
  Output: `Scenarios [N/N pass] | Integration [N/N] | Edge Cases [N tested] | VERDICT`

- [ ] F4. **Scope Fidelity Check** — `deep`
  For each task: read "What to do", read actual diff (git log/diff). Verify 1:1 — everything in spec was built (no missing), nothing beyond spec was built (no creep). Check "Must NOT do" compliance. Detect cross-task contamination: Task N touching Task M's files. Flag unaccounted changes.
  Output: `Tasks [N/N compliant] | Contamination [CLEAN/N issues] | Unaccounted [CLEAN/N files] | VERDICT`

---

## Commit Strategy

| Wave | Commit Message | Files | Pre-commit |
|------|---------------|-------|-----------|
| 1 | `chore(admin): add named routes, branding, kiosk config, theme toggle` | routes/web.php, app-logo.blade.php, config/kiosk.php, middleware, .env | `php artisan test --compact` |
| 2 | `feat(admin): add dashboard analytics with charts and activity log` | admin-dashboard.blade.php, AdminDashboard.php, sidebar.blade.php, header.blade.php | `php artisan test --compact` |
| 3 | `feat(admin): add full CRUD with modals, delete protection, pagination` | layanan/index.blade.php, loket/index.blade.php, users/index.blade.php, controllers, form requests | `php artisan test --compact` |
| 4 | `feat(kiosk): add password-protected touchscreen booking module` | pages/kiosk/*, middleware, routes | `php artisan test --compact` |
| 5 | `feat(tv-display): add password-protected landscape display module` | pages/tv-display/*, routes | `php artisan test --compact` |
| 6 | `feat(admin): add breadcrumbs, empty states, integration polish` | breadcrumbs component, various views | `php artisan test --compact` |

---

## Success Criteria

### Verification Commands
```bash
php artisan test --compact                    # Expected: all tests pass, 0 failures
vendor/bin/pint --dirty --format agent        # Expected: no formatting issues
php artisan route:list --path=admin           # Expected: all admin routes named
php artisan route:list --path=kiosk           # Expected: kiosk routes with middleware
php artisan route:list --path=tv-display      # Expected: tv-display routes with middleware
```

### Final Checklist
- [ ] All "Must Have" present
- [ ] All "Must NOT Have" absent
- [ ] All tests pass
- [ ] Branding updated everywhere
- [ ] Sidebar shows all admin links
- [ ] Dashboard has charts and stat cards
- [ ] All tables have pagination + search + sort + empty states
- [ ] Edit modals work for all entities
- [ ] Delete blocks on active relations
- [ ] Kiosk requires password, shows touchscreen UI with animations
- [ ] TV Display requires password, shows landscape UI with call animations
- [ ] Light/dark toggle works
- [ ] Old role/permission routes redirect 301
