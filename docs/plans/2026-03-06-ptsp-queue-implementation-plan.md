# PTSP Queue MVP Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build the MVP PTSP queue application for Pengadilan Agama with hybrid intake, shared general counters, dedicated payment and Posbakum counters, operational dashboards, and SPBE-ready audit/reporting foundations.

**Architecture:** Use Laravel 12 with Livewire 4 full-page components and Flux UI Pro for the back-office and public-facing queue flows. Model the domain around services, queue pools, counters, tickets, counter sessions, and activity logs so public service reporting remains accurate even when multiple services share one operational queue pool.

**Tech Stack:** Laravel 12, Livewire 4, Flux UI Pro 2, Pest 4, SQLite for local development, Tailwind CSS 4

---

## Implementation Notes

- Keep the MVP web-only and responsive.
- Use full-page Livewire routes where possible instead of adding controllers for interactive pages.
- Use `php artisan make:` commands when generating Laravel classes.
- Keep queue numbering per pool per service date, not global across all pools.
- Treat `service`, `queue_pool`, and `counter` as separate data dimensions.
- Use seed data for the five MVP services and five active counters.
- Use Pest feature tests and Livewire tests as the primary verification method.

## Proposed Domain Model

- `Service`
  - fields: `name`, `code`, `slug`, `description`, `requirements`, `queue_pool_id`, `is_active`, `booking_enabled`, `walk_in_enabled`, `daily_quota`, `sort_order`
- `QueuePool`
  - fields: `name`, `code`, `description`, `is_active`
- `Counter`
  - fields: `name`, `code`, `queue_pool_id`, `is_active`, `sort_order`
- `QueueTicket`
  - fields: `service_id`, `queue_pool_id`, `counter_id`, `created_by`, `channel`, `ticket_number`, `sequence_number`, `service_date`, `visitor_name`, `visitor_identifier`, `visitor_phone`, `notes`, `status`, `checked_in_at`, `called_at`, `started_at`, `completed_at`, `cancelled_at`
- `CounterSession`
  - fields: `counter_id`, `user_id`, `opened_at`, `closed_at`, `status`
- `QueueActivity`
  - fields: `queue_ticket_id`, `user_id`, `counter_id`, `action`, `meta`, `created_at`

## Status and Channel Constants

- Ticket status:
  - `booked`
  - `waiting`
  - `called`
  - `serving`
  - `completed`
  - `skipped`
  - `cancelled`
  - `no_show`
- Ticket channel:
  - `online_booking`
  - `assisted_same_day`
  - `walk_in_kiosk`

## Route Map

- Public:
  - `/` keep existing welcome page or replace with PTSP landing later
  - `/antrian` public booking page
  - `/antrian/cek` public ticket lookup page
  - `/display` public queue display page
- Authenticated:
  - `/dashboard` keep as authenticated landing, repurpose into internal PTSP summary
  - `/admin/layanan`
  - `/admin/pool-antrean`
  - `/admin/loket`
  - `/frontdesk/antrian`
  - `/petugas/loket/{counter}`
  - `/laporan/antrian`

## Seeded MVP Configuration

- Queue pools:
  - `UMUM`
  - `BAYAR`
  - `POSBAKUM`
- Services:
  - `Pendaftaran` -> `UMUM`
  - `Informasi/Pengaduan` -> `UMUM`
  - `Pengambilan Produk Hukum` -> `UMUM`
  - `Pembayaran` -> `BAYAR`
  - `Posbakum` -> `POSBAKUM`
- Counters:
  - `Loket Umum 1` -> `UMUM`
  - `Loket Umum 2` -> `UMUM`
  - `Loket Umum 3` -> `UMUM`
  - `Loket Pembayaran` -> `BAYAR`
  - `Loket Posbakum` -> `POSBAKUM`

### Task 1: Create the queue domain schema

**Files:**
- Create: `database/migrations/2026_03_06_000003_create_queue_pools_table.php`
- Create: `database/migrations/2026_03_06_000004_create_services_table.php`
- Create: `database/migrations/2026_03_06_000005_create_counters_table.php`
- Create: `database/migrations/2026_03_06_000006_create_queue_tickets_table.php`
- Create: `database/migrations/2026_03_06_000007_create_counter_sessions_table.php`
- Create: `database/migrations/2026_03_06_000008_create_queue_activities_table.php`
- Test: `tests/Feature/Database/QueueSchemaTest.php`

**Step 1: Write the failing test**

Create a schema test that asserts the six tables exist and that `queue_tickets` contains the key columns for service, pool, counter, source channel, ticket number, service date, and lifecycle timestamps.

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Database/QueueSchemaTest.php`

Expected: FAIL because the queue tables do not exist.

**Step 3: Write minimal implementation**

- Generate the migrations with `php artisan make:migration --no-interaction`.
- Add foreign keys for `service_id`, `queue_pool_id`, `counter_id`, and `created_by`.
- Use string columns for `status`, `channel`, and `ticket_number`.
- Use JSON column `meta` on `queue_activities`.
- Add indexes on `service_date`, `status`, `queue_pool_id`, and `ticket_number`.

**Step 4: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/Database/QueueSchemaTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add database/migrations tests/Feature/Database/QueueSchemaTest.php
git commit -m "feat: add queue domain schema"
```

### Task 2: Add Eloquent models, factories, and seeded MVP configuration

**Files:**
- Create: `app/Models/QueuePool.php`
- Create: `app/Models/Service.php`
- Create: `app/Models/Counter.php`
- Create: `app/Models/QueueTicket.php`
- Create: `app/Models/CounterSession.php`
- Create: `app/Models/QueueActivity.php`
- Create: `database/factories/QueuePoolFactory.php`
- Create: `database/factories/ServiceFactory.php`
- Create: `database/factories/CounterFactory.php`
- Create: `database/factories/QueueTicketFactory.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `database/seeders/QueueMvpSeeder.php`
- Test: `tests/Feature/Models/QueueRelationshipsTest.php`
- Test: `tests/Feature/Seeders/QueueMvpSeederTest.php`

**Step 1: Write the failing tests**

- Add a relationship test that asserts:
  - a service belongs to a queue pool;
  - a counter belongs to a queue pool;
  - a queue ticket belongs to a service, pool, and counter;
  - a queue ticket has many activity records.
- Add a seeder test that runs `QueueMvpSeeder` and asserts the expected pools, services, and counters exist.

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Models/QueueRelationshipsTest.php tests/Feature/Seeders/QueueMvpSeederTest.php`

Expected: FAIL because the models and seeder do not exist.

**Step 3: Write minimal implementation**

- Generate models with `php artisan make:model --factory --no-interaction`.
- Add explicit relationship methods with return types.
- Implement `casts()` methods for boolean, datetime, and JSON fields.
- Create `QueueMvpSeeder` to seed the three pools, five services, and five counters.
- Register the seeder in `DatabaseSeeder`.

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Models/QueueRelationshipsTest.php tests/Feature/Seeders/QueueMvpSeederTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add app/Models database/factories database/seeders tests/Feature/Models tests/Feature/Seeders
git commit -m "feat: add queue models and MVP seed data"
```

### Task 3: Add roles and route protection for internal PTSP pages

**Files:**
- Create: `app/Enums/UserRole.php`
- Create: `app/Http/Middleware/EnsureUserHasRole.php`
- Modify: `app/Models/User.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Auth/PtspAuthorizationTest.php`

**Step 1: Write the failing test**

Create an authorization test that verifies:
- guests are redirected from internal PTSP pages;
- authenticated users without the correct role receive forbidden access;
- users with the right role can access frontdesk, officer, and reporting pages.

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Auth/PtspAuthorizationTest.php`

Expected: FAIL because the middleware, enum, and protected routes do not exist.

**Step 3: Write minimal implementation**

- Add a `role` column to users via a new migration if missing.
- Create a `UserRole` enum with `Admin`, `Frontdesk`, `Officer`, and `Monitor`.
- Add helper methods on `User` for role checks.
- Register a middleware alias in `bootstrap/app.php`.
- Protect route groups in `routes/web.php` using `auth`, `verified`, and role middleware.

**Step 4: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/Auth/PtspAuthorizationTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add app/Enums app/Http/Middleware app/Models/User.php bootstrap/app.php routes/web.php tests/Feature/Auth/PtspAuthorizationTest.php database/migrations
git commit -m "feat: add PTSP roles and route authorization"
```

### Task 4: Implement ticket numbering and ticket creation service

**Files:**
- Create: `app/Actions/Queue/CreateQueueTicket.php`
- Create: `app/Actions/Queue/GenerateTicketNumber.php`
- Create: `app/Actions/Queue/LogQueueActivity.php`
- Test: `tests/Feature/Queue/CreateQueueTicketTest.php`
- Test: `tests/Unit/Queue/GenerateTicketNumberTest.php`

**Step 1: Write the failing tests**

- Unit test the numbering action:
  - first ticket in `UMUM` for a date gets sequence `1`;
  - next ticket in the same pool and date increments to `2`;
  - a different pool on the same date starts at `1`;
  - a new date resets the sequence.
- Feature test ticket creation:
  - online booking creates `booked`;
  - same-day assisted and walk-in kiosk create `waiting`;
  - activity log is written;
  - the ticket stores service, queue pool, and channel correctly.

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Unit/Queue/GenerateTicketNumberTest.php tests/Feature/Queue/CreateQueueTicketTest.php`

Expected: FAIL because the actions do not exist.

**Step 3: Write minimal implementation**

- `GenerateTicketNumber` should use pool code plus per-pool per-date sequence.
- `CreateQueueTicket` should derive the queue pool from the selected service.
- Ticket creation should assign initial status based on channel:
  - `online_booking` => `booked`
  - `assisted_same_day` => `waiting`
  - `walk_in_kiosk` => `waiting`
- `LogQueueActivity` should record the action and context metadata.

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Unit/Queue/GenerateTicketNumberTest.php tests/Feature/Queue/CreateQueueTicketTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add app/Actions/Queue tests/Unit/Queue tests/Feature/Queue
git commit -m "feat: add queue ticket creation workflow"
```

### Task 5: Implement the public booking and public lookup pages

**Files:**
- Create: `resources/views/pages/antrian/⚡index.blade.php`
- Create: `resources/views/pages/antrian/⚡cek.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Public/PublicQueueBookingPageTest.php`
- Test: `tests/Feature/Public/PublicQueueLookupPageTest.php`

**Step 1: Write the failing tests**

- Booking page test:
  - public user can open `/antrian`;
  - active services are visible;
  - user can submit booking data and receive a booking confirmation containing ticket number and service date.
- Lookup page test:
  - public user can open `/antrian/cek`;
  - user can search by ticket number and service date;
  - ticket status is shown.

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Public/PublicQueueBookingPageTest.php tests/Feature/Public/PublicQueueLookupPageTest.php`

Expected: FAIL because the routes and pages do not exist.

**Step 3: Write minimal implementation**

- Create Livewire full-page components as Volt-style Blade pages under `resources/views/pages/antrian`.
- Use Flux form components for the booking form and status lookup form.
- Validate minimal public fields only.
- Reuse `CreateQueueTicket` to create booking records.
- Add public routes in `routes/web.php`.

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Public/PublicQueueBookingPageTest.php tests/Feature/Public/PublicQueueLookupPageTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add resources/views/pages/antrian routes/web.php tests/Feature/Public
git commit -m "feat: add public queue booking and lookup pages"
```

### Task 6: Implement frontdesk assisted-entry and arrival check-in

**Files:**
- Create: `resources/views/pages/frontdesk/⚡antrian.blade.php`
- Create: `app/Actions/Queue/CheckInQueueTicket.php`
- Test: `tests/Feature/Frontdesk/AssistedQueueEntryTest.php`
- Test: `tests/Feature/Frontdesk/QueueCheckInTest.php`

**Step 1: Write the failing tests**

- Assisted entry test:
  - frontdesk user can create same-day tickets for all five services;
  - created tickets enter the correct pool and `waiting` status.
- Check-in test:
  - booked online tickets can be checked in by frontdesk;
  - check-in changes status from `booked` to `waiting`;
  - `checked_in_at` is stored;
  - activity is logged.

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Frontdesk/AssistedQueueEntryTest.php tests/Feature/Frontdesk/QueueCheckInTest.php`

Expected: FAIL because the frontdesk page and check-in action do not exist.

**Step 3: Write minimal implementation**

- Build a frontdesk page with:
  - service selection;
  - visitor basic data entry;
  - booking search;
  - check-in action.
- Keep kiosk support within the same MVP surface by treating kiosk-assisted entry as frontdesk-assisted creation with a dedicated channel value.
- Use Flux cards, fields, and tables for the layout.

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Frontdesk/AssistedQueueEntryTest.php tests/Feature/Frontdesk/QueueCheckInTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add resources/views/pages/frontdesk app/Actions/Queue/CheckInQueueTicket.php tests/Feature/Frontdesk
git commit -m "feat: add frontdesk queue entry and check-in"
```

### Task 7: Implement officer counter operation pages

**Files:**
- Create: `resources/views/pages/petugas/loket/⚡show.blade.php`
- Create: `app/Actions/Queue/CallNextTicket.php`
- Create: `app/Actions/Queue/RecallTicket.php`
- Create: `app/Actions/Queue/SkipTicket.php`
- Create: `app/Actions/Queue/CompleteTicket.php`
- Create: `app/Actions/Queue/CancelTicket.php`
- Test: `tests/Feature/Officer/CounterQueueWorkflowTest.php`

**Step 1: Write the failing test**

Create a workflow test that verifies:
- an officer on a general counter can call from the `UMUM` pool only;
- a payment counter can call from `BAYAR` only;
- a Posbakum counter can call from `POSBAKUM` only;
- status transitions follow the lifecycle;
- called and completed timestamps are stored;
- activity logs are written for each action.

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Feature/Officer/CounterQueueWorkflowTest.php`

Expected: FAIL because the officer page and queue actions do not exist.

**Step 3: Write minimal implementation**

- Build the officer full-page component with:
  - current serving ticket;
  - next waiting tickets in the same pool;
  - buttons for call next, recall, skip, complete, cancel.
- Ensure pool filtering is based on the counter route parameter.
- Record the serving counter on the ticket when first called.

**Step 4: Run test to verify it passes**

Run: `php artisan test --compact tests/Feature/Officer/CounterQueueWorkflowTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add resources/views/pages/petugas/loket app/Actions/Queue tests/Feature/Officer
git commit -m "feat: add officer counter queue workflow"
```

### Task 8: Implement queue display and internal dashboard summary

**Files:**
- Create: `resources/views/pages/display/⚡index.blade.php`
- Modify: `resources/views/dashboard.blade.php`
- Test: `tests/Feature/Public/QueueDisplayTest.php`
- Test: `tests/Feature/Dashboard/PtspDashboardTest.php`

**Step 1: Write the failing tests**

- Display test:
  - `/display` is public;
  - it shows the current called tickets and their counters;
  - it shows recent call history.
- Dashboard test:
  - authenticated internal users can see summary cards for tickets by status;
  - summary values reflect current-day tickets.

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Public/QueueDisplayTest.php tests/Feature/Dashboard/PtspDashboardTest.php`

Expected: FAIL because the display page and PTSP dashboard summary do not exist.

**Step 3: Write minimal implementation**

- Build `/display` as a read-only Livewire page optimized for a waiting-room screen.
- Repurpose `dashboard.blade.php` into a simple internal PTSP summary view rather than leaving the starter placeholder.

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Public/QueueDisplayTest.php tests/Feature/Dashboard/PtspDashboardTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add resources/views/pages/display resources/views/dashboard.blade.php tests/Feature/Public/QueueDisplayTest.php tests/Feature/Dashboard/PtspDashboardTest.php
git commit -m "feat: add queue display and PTSP dashboard"
```

### Task 9: Implement admin master-data pages for services and counters

**Files:**
- Create: `resources/views/pages/admin/layanan/⚡index.blade.php`
- Create: `resources/views/pages/admin/pool-antrean/⚡index.blade.php`
- Create: `resources/views/pages/admin/loket/⚡index.blade.php`
- Test: `tests/Feature/Admin/ManageServicesTest.php`
- Test: `tests/Feature/Admin/ManageCountersTest.php`

**Step 1: Write the failing tests**

- Service management test:
  - admin can list services;
  - admin can create and update a service;
  - admin cannot assign a service to an inactive pool.
- Counter management test:
  - admin can list counters;
  - admin can update active status and pool assignment;
  - non-admin cannot access these pages.

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Admin/ManageServicesTest.php tests/Feature/Admin/ManageCountersTest.php`

Expected: FAIL because the admin pages do not exist.

**Step 3: Write minimal implementation**

- Build simple CRUD-style full-page components using Flux forms and tables.
- Defer advanced bulk editing and modal-heavy UX to post-MVP.
- Keep pool management read-write if you want operational flexibility; otherwise make `QueuePool` read-only after seed and expose only list view.

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Admin/ManageServicesTest.php tests/Feature/Admin/ManageCountersTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add resources/views/pages/admin tests/Feature/Admin
git commit -m "feat: add PTSP master data management pages"
```

### Task 10: Implement queue reporting

**Files:**
- Create: `resources/views/pages/laporan/antrian/⚡index.blade.php`
- Create: `app/Support/Reports/QueueReportBuilder.php`
- Test: `tests/Feature/Reports/QueueReportPageTest.php`
- Test: `tests/Unit/Reports/QueueReportBuilderTest.php`

**Step 1: Write the failing tests**

- Report builder unit test:
  - aggregates by service;
  - aggregates by counter;
  - aggregates by officer;
  - aggregates status totals for a date range.
- Report page test:
  - monitoring users can open the report page;
  - date filter affects the result set;
  - shared general pool still reports separate service totals.

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Unit/Reports/QueueReportBuilderTest.php tests/Feature/Reports/QueueReportPageTest.php`

Expected: FAIL because the report builder and page do not exist.

**Step 3: Write minimal implementation**

- Build the report builder as a dedicated support class rather than inline page queries.
- Support at least day-based filtering in MVP.
- Return grouped datasets for service, counter, officer, and status summaries.

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Unit/Reports/QueueReportBuilderTest.php tests/Feature/Reports/QueueReportPageTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add app/Support/Reports resources/views/pages/laporan/antrian tests/Unit/Reports tests/Feature/Reports
git commit -m "feat: add queue reporting"
```

### Task 11: Finish audit logging, seed roles, and polish navigation

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Modify: `resources/views/layouts/app/header.blade.php`
- Modify: `resources/views/welcome.blade.php`
- Test: `tests/Feature/Navigation/PtspNavigationTest.php`
- Test: `tests/Feature/Audit/QueueAuditLogTest.php`

**Step 1: Write the failing tests**

- Navigation test:
  - internal users see menu items relevant to their role;
  - public users can reach booking and display pages from the landing page.
- Audit test:
  - each queue lifecycle action creates one audit entry;
  - audit entries store actor and optional counter context.

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Navigation/PtspNavigationTest.php tests/Feature/Audit/QueueAuditLogTest.php`

Expected: FAIL because navigation and complete audit coverage are not finished.

**Step 3: Write minimal implementation**

- Add role-aware navigation links to the app sidebar and header.
- Update the public landing page to link to booking, lookup, and display.
- Ensure every queue action goes through `LogQueueActivity`.
- Extend seeders with sensible default users if local development convenience is needed.

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Navigation/PtspNavigationTest.php tests/Feature/Audit/QueueAuditLogTest.php`

Expected: PASS.

**Step 5: Commit**

```bash
git add database/seeders resources/views/layouts/app resources/views/welcome.blade.php tests/Feature/Navigation tests/Feature/Audit
git commit -m "feat: finalize PTSP navigation and audit logging"
```

### Task 12: Final verification and cleanup

**Files:**
- Modify: `docs/plans/2026-03-06-ptsp-queue-prd.md`
- Modify: `docs/plans/2026-03-06-ptsp-queue-implementation-plan.md`

**Step 1: Run the focused application test suite**

Run:

```bash
php artisan test --compact tests/Feature/Database/QueueSchemaTest.php tests/Feature/Models/QueueRelationshipsTest.php tests/Feature/Seeders/QueueMvpSeederTest.php tests/Feature/Auth/PtspAuthorizationTest.php tests/Feature/Public/PublicQueueBookingPageTest.php tests/Feature/Public/PublicQueueLookupPageTest.php tests/Feature/Frontdesk/AssistedQueueEntryTest.php tests/Feature/Frontdesk/QueueCheckInTest.php tests/Feature/Officer/CounterQueueWorkflowTest.php tests/Feature/Public/QueueDisplayTest.php tests/Feature/Dashboard/PtspDashboardTest.php tests/Feature/Admin/ManageServicesTest.php tests/Feature/Admin/ManageCountersTest.php tests/Feature/Reports/QueueReportPageTest.php tests/Feature/Navigation/PtspNavigationTest.php tests/Feature/Audit/QueueAuditLogTest.php tests/Unit/Queue/GenerateTicketNumberTest.php tests/Unit/Reports/QueueReportBuilderTest.php
```

Expected: PASS.

**Step 2: Run formatter**

Run: `vendor/bin/pint --dirty`

Expected: PASS with no remaining formatting changes.

**Step 3: Run a final smoke suite**

Run: `php artisan test --compact`

Expected: PASS.

**Step 4: Update plan notes if implementation deviated**

- Record any necessary implementation deltas back into the PRD and this plan.
- Do not expand MVP scope during cleanup.

**Step 5: Commit**

```bash
git add docs/plans
git commit -m "docs: finalize PTSP queue implementation notes"
```

