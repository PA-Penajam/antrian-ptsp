# Next.js Public Pages + Laravel REST API — Antrian PTSP

## TL;DR

> **Quick Summary**: Membuat halaman publik sistem antrian PTSP menggunakan Next.js 16 (template Finris) yang mengkonsumsi REST API baru dari Laravel backend. Meliputi 4 halaman publik dan 6 endpoint API.
>
> **Deliverables**:
> - 6 REST API endpoints di Laravel (`routes/api.php`)
> - API Resource classes untuk response formatting (ServiceResource, QueueTicketResource)
> - API Controller dengan business rule enforcement (quota, active check, booking_enabled)
> - CORS & rate limiting configuration
> - Pest feature tests untuk semua API endpoints
> - 4 halaman Next.js: Homepage, Booking Wizard, Ticket Lookup, Confirmation
> - Shared API client module di Next.js
>
> **Estimated Effort**: Large
> **Parallel Execution**: YES — 4 waves
> **Critical Path**: Task 1 → Task 2 → Task 5 → Task 8 → Task 11 → Task 15 → Final Wave

---

## Context

### Original Request
User ingin memisahkan halaman publik dari sistem antrian PTSP ke Next.js untuk performa, SEO, dan pemisahan arsitektur yang lebih baik. Laravel tetap menjadi backend utama, Next.js hanya sebagai presentation layer untuk 4 halaman publik.

### Interview Summary
**Key Discussions**:
- **Scope**: Hanya 4 halaman publik — Homepage, Booking, Lookup, Confirmation
- **Architecture**: Repo terpisah, Next.js di `frontend-public/`
- **Template**: Finris (Next.js 16, custom CSS, framer-motion, swiper) — bukan Tailwind
- **Testing**: API Tests Only (Pest di Laravel), tidak ada frontend test suite
- **Auth**: Tidak diperlukan — semua endpoint publik tanpa Sanctum
- **Deployment**: Terpisah — Laravel dan Next.js di-deploy independen

**Research Findings**:
- Laravel belum memiliki `routes/api.php` — perlu dibuat manual dan didaftarkan di `bootstrap/app.php`
- `CreateQueueTicket` action TIDAK memvalidasi `daily_quota`, `booking_enabled`, `is_active`, atau tanggal — semua validasi bisnis harus ditambahkan di API layer
- `GenerateTicketNumber` juga TIDAK cek quota — hanya generate nomor berikutnya
- Service slug memiliki unique constraint di database ✅
- `PublicQueueController` sudah memfilter `is_active` dan `booking_enabled` di query — pattern ini harus diikuti di API
- Confirmation menggunakan route model binding dengan `QueueTicket` model
- Queue position hanya dihitung untuk status `Waiting`, bukan `Booked`

### Metis Review
**Identified Gaps** (addressed):
- Business-rule gap di CreateQueueTicket → Validasi ditambahkan di API FormRequest + controller level
- Ticket ID exposure → Default: gunakan `ticket_number` + `service_date` untuk lookup publik
- Duplicate submission risk → Tambahkan pengecekan duplikat
- Quota enforcement tidak ada → Tambahkan validasi quota sebelum CreateQueueTicket
- CORS & rate limiting belum dikonfigurasi → Masuk sebagai task eksplisit
- Date validation → Weekday only (Senin-Jumat), same-day booking diizinkan, maksimal 14 hari ke depan

---

## Work Objectives

### Core Objective
Membangun 6 REST API endpoint di Laravel dan 4 halaman publik di Next.js yang terintegrasi, menggunakan template Finris sebagai base UI.

### Concrete Deliverables
- `routes/api.php` — public API route file
- `app/Http/Controllers/Api/PublicServiceController.php` — service & institution endpoints
- `app/Http/Controllers/Api/PublicQueueController.php` — booking, lookup, ticket endpoints
- `app/Http/Resources/ServiceResource.php` — service JSON resource
- `app/Http/Resources/QueueTicketResource.php` — ticket JSON resource
- `app/Http/Resources/ServiceResource.php` — service JSON resource
- `app/Http/Resources/QueueTicketResource.php` — ticket JSON resource
- `app/Http/Requests/Api/StoreBookingRequest.php` — API booking validation dengan business rules
- `app/Http/Requests/Api/LookupTicketRequest.php` — API lookup validation
- CORS & throttle configuration di `bootstrap/app.php`
- Pest feature tests di `tests/Feature/Api/`
- `frontend-public/src/lib/api.ts` — shared API client
- `frontend-public/src/types/` — TypeScript type definitions
- `frontend-public/src/app/page.tsx` — Homepage (adapted Finris)
- `frontend-public/src/app/antrian/page.tsx` — Booking Wizard
- `frontend-public/src/app/antrian/cek/page.tsx` — Ticket Lookup
- `frontend-public/src/app/antrian/konfirmasi/[ticket]/page.tsx` — Confirmation Page

### Definition of Done
- [ ] `php artisan test --compact` → ALL PASS (0 failures)
- [ ] `cd frontend-public && pnpm build` → exit code 0
- [ ] `curl -s http://localhost:8000/api/services | jq .` → returns JSON array of services
- [ ] `curl -s http://localhost:8000/api/institution | jq .` → returns institution config
- [ ] All 4 Next.js routes return 200 when served

### Must Have
- API endpoints mengikuti RESTful conventions
- Reuse `CreateQueueTicket` action untuk booking (jangan duplikasi logic)
- Business rule enforcement: `is_active`, `booking_enabled`, `daily_quota` check
- Proper CORS configuration dengan env-driven origins
- Rate limiting per endpoint group
- JSON error responses konsisten (validation 422, not-found 404, throttle 429)
- Next.js pages menggunakan Finris template components/styling
- Booking wizard dengan 3-step flow (client-side state)
- Mobile-responsive (mengikuti Finris responsive patterns)

### Must NOT Have (Guardrails)
- ❌ JANGAN install Sanctum atau tambahkan auth ke public API
- ❌ JANGAN ubah halaman Blade/public Laravel yang sudah ada
- ❌ JANGAN duplikasi business logic — reuse Action classes
- ❌ JANGAN tambahkan frontend test suite (scope: API tests only)
- ❌ JANGAN tambahkan CMS, admin API, notifications, PDF, atau payments
- ❌ JANGAN gunakan Tailwind di Next.js — ikuti Finris custom CSS patterns
- ❌ JANGAN tambahkan WebSocket/real-time/polling features
- ❌ JANGAN gunakan `DB::` — pakai `Model::query()` atau Eloquent
- ❌ JANGAN gunakan `env()` di luar config files
- ❌ JANGAN tambahkan SEO structured data / JSON-LD (out of scope)
- ❌ JANGAN buat lebih dari 4 halaman di Next.js
- ❌ JANGAN set CORS wildcard `*` di production

---

## Verification Strategy (MANDATORY)

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.
> Acceptance criteria requiring "user manually tests/confirms" are FORBIDDEN.

### Test Decision
- **Infrastructure exists**: YES (Pest already configured)
- **Automated tests**: TDD — write Pest tests FIRST, then implement
- **Framework**: Pest v4
- **Each API task follows**: RED (failing test) → GREEN (minimal impl) → REFACTOR

### QA Policy
Every task MUST include agent-executed QA scenarios.
Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

- **API/Backend**: Use Bash (curl) — Send requests, assert status + response fields
- **Frontend/UI**: Use Playwright (playwright skill) — Navigate, interact, assert DOM, screenshot
- **Configuration**: Use Bash — Run artisan/config commands, verify output

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Foundation — dapat dimulai segera, SEMUA PARALEL):
├── Task 1: API route registration + CORS + throttle config [quick]
├── Task 2: API Resource classes (Service, QueueTicket, Institution) [quick]
├── Task 3: Next.js TypeScript types + API client module [quick]
└── Task 4: Pest test setup + factory verification [quick]

Wave 2 (API Implementation — TDD, setelah Wave 1):
├── Task 5: GET /api/institution endpoint (test → impl) [quick]
├── Task 6: GET /api/services + GET /api/services/{slug} (test → impl) [coding]
├── Task 7: API FormRequest with business rules (StoreBookingRequest) [coding]
└── Task 8: POST /api/queue/booking endpoint (test → impl) [deep]

Wave 3 (API Completion + Frontend Start — setelah Wave 2):
├── Task 9: GET /api/queue/lookup endpoint (test → impl) [coding]
├── Task 10: GET /api/queue/ticket/{ticket_number} endpoint (test → impl) [coding]
├── Task 11: Next.js Homepage — adapt Finris template [visual-engineering]
└── Task 12: Next.js Booking Wizard — 3-step form [visual-engineering]

Wave 4 (Frontend Completion — setelah Wave 3):
├── Task 13: Next.js Ticket Lookup page [visual-engineering]
├── Task 14: Next.js Confirmation page [visual-engineering]
└── Task 15: Frontend build verification + API integration smoke test [coding]

Wave FINAL (Independent Review — 4 parallel):
├── Task F1: Plan compliance audit (deep)
├── Task F2: Code quality review (unspecified-high)
├── Task F3: Real manual QA (unspecified-high)
└── Task F4: Scope fidelity check (deep)

Critical Path: T1 → T2 → T5/T6 → T8 → T9/T10 → T11/T12 → T15 → Final
Parallel Speedup: ~60% faster than sequential
Max Concurrent: 4 (Waves 1, 3, 4, Final)
```

### Dependency Matrix

| Task | Depends On | Blocks | Wave |
|------|-----------|--------|------|
| 1 | — | 5,6,7,8,9,10 | 1 |
| 2 | — | 5,6,8,9,10 | 1 |
| 3 | — | 11,12,13,14 | 1 |
| 4 | — | 5,6,8,9,10 | 1 |
| 5 | 1,2,4 | 11 | 2 |
| 6 | 1,2,4 | 11,12 | 2 |
| 7 | — | 8 | 2 |
| 8 | 1,2,4,7 | 12 | 2 |
| 9 | 1,2,4 | 13 | 3 |
| 10 | 1,2,4 | 14 | 3 |
| 11 | 3,5,6 | 15 | 3 |
| 12 | 3,6,8 | 15 | 3 |
| 13 | 3,9 | 15 | 4 |
| 14 | 3,10 | 15 | 4 |
| 15 | 11,12,13,14 | Final | 4 |
| F1-F4 | 15 | — | Final |

### Agent Dispatch Summary

- **Wave 1**: **4** — T1 → `quick`, T2 → `quick`, T3 → `quick`, T4 → `quick`
- **Wave 2**: **4** — T5 → `quick`, T6 → `coding`, T7 → `coding`, T8 → `deep`
- **Wave 3**: **4** — T9 → `coding`, T10 → `coding`, T11 → `visual-engineering`, T12 → `visual-engineering`
- **Wave 4**: **3** — T13 → `visual-engineering`, T14 → `visual-engineering`, T15 → `coding`
- **FINAL**: **4** — F1 → `deep`, F2 → `unspecified-high`, F3 → `unspecified-high`, F4 → `deep`

---

## TODOs

- [x] 1. API Route Registration + CORS + Throttle Configuration

  **What to do**:
  - Buat file `routes/api.php` dengan route group untuk public endpoints
  - Daftarkan `routes/api.php` di `bootstrap/app.php` menggunakan `->withRouting(api: __DIR__.'/../routes/api.php')`
  - Konfigurasi CORS di `bootstrap/app.php` — izinkan origins dari env variable `FRONTEND_URL` (default: `http://localhost:3000`)
  - Tambahkan throttle middleware: `api:booking` (10 requests/menit) dan `api:read` (60 requests/menit)
  - Tambahkan `FRONTEND_URL=http://localhost:3000` ke `.env.example`
  - Route structure:
    ```
    GET    /api/institution
    GET    /api/services
    GET    /api/services/{slug}
    POST   /api/queue/booking        (throttle: api:booking)
    GET    /api/queue/lookup
    GET    /api/queue/ticket/{ticket_number}
    ```

  **Must NOT do**:
  - JANGAN gunakan `php artisan install:api` (itu install Sanctum)
  - JANGAN tambahkan auth middleware ke route group
  - JANGAN set CORS wildcard `*` — gunakan env-driven origin list

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Konfigurasi file saja, tidak ada business logic kompleks
  - **Skills**: [`livewire-development`]
    - `livewire-development`: Memahami Laravel bootstrap dan middleware configuration
  - **Skills Evaluated but Omitted**:
    - `pest-testing`: Belum menulis test di task ini

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 2, 3, 4)
  - **Blocks**: Tasks 5, 6, 7, 8, 9, 10
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `bootstrap/app.php` — Current app configuration, tambahkan `api:` parameter di `withRouting()`
  - `routes/web.php` — Existing route patterns untuk naming conventions

  **API/Type References**:
  - `config/cors.php` — Jika ada, periksa konfigurasi default CORS Laravel

  **External References**:
  - Gunakan `laravel-boost_search-docs` dengan queries: `["api routing", "CORS configuration", "rate limiting throttle"]`

  **WHY Each Reference Matters**:
  - `bootstrap/app.php`: Ini satu-satunya tempat route registration di Laravel 12 — harus menambahkan `api:` tanpa merusak web/console yang sudah ada
  - `routes/web.php`: Melihat naming convention yang sudah dipakai (`queue.booking`, `queue.lookup`) agar API route names konsisten

  **Acceptance Criteria**:
  - [ ] File `routes/api.php` exists dan berisi route definitions
  - [ ] `php artisan route:list --path=api` menampilkan semua 6 endpoint
  - [ ] `vendor/bin/pint --dirty --format agent` → no issues

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: API routes terdaftar dengan benar
    Tool: Bash
    Preconditions: Laravel app running
    Steps:
      1. Run `php artisan route:list --path=api --columns=method,uri,name`
      2. Assert output contains: GET api/institution
      3. Assert output contains: GET api/services
      4. Assert output contains: GET api/services/{slug}
      5. Assert output contains: POST api/queue/booking
      6. Assert output contains: GET api/queue/lookup
      7. Assert output contains: GET api/queue/ticket/{ticket_number}
    Expected Result: All 6 routes listed with correct HTTP methods
    Failure Indicators: Missing routes, wrong methods, 500 error on route:list
    Evidence: .sisyphus/evidence/task-1-routes-listed.txt

  Scenario: CORS headers present untuk frontend origin
    Tool: Bash (curl)
    Preconditions: Laravel app serving on localhost:8000
    Steps:
      1. Run `curl -I -X OPTIONS http://localhost:8000/api/services -H "Origin: http://localhost:3000" -H "Access-Control-Request-Method: GET" 2>/dev/null`
      2. Assert response contains header `Access-Control-Allow-Origin: http://localhost:3000`
      3. Assert response status is 200 or 204
    Expected Result: CORS preflight succeeds with correct origin header
    Failure Indicators: Missing Access-Control-Allow-Origin header, 403/405 response
    Evidence: .sisyphus/evidence/task-1-cors-preflight.txt

  Scenario: CORS rejects unauthorized origin
    Tool: Bash (curl)
    Preconditions: Laravel app serving on localhost:8000
    Steps:
      1. Run `curl -I -X OPTIONS http://localhost:8000/api/services -H "Origin: http://evil.com" -H "Access-Control-Request-Method: GET" 2>/dev/null`
      2. Assert response does NOT contain `Access-Control-Allow-Origin: http://evil.com`
    Expected Result: CORS preflight does not allow unauthorized origin
    Failure Indicators: Access-Control-Allow-Origin header present with evil.com
    Evidence: .sisyphus/evidence/task-1-cors-rejected.txt
  ```

  **Commit**: YES
  - Message: `chore(api): register public api routes with CORS and throttle config`
  - Files: `bootstrap/app.php`, `routes/api.php`, `.env.example`
  - Pre-commit: `php artisan route:list --path=api`

- [x] 2. API Resource Classes (ServiceResource, QueueTicketResource)

  **What to do**:
  - Buat `app/Http/Resources/ServiceResource.php`:
    ```php
    // Fields: id, name, code, slug, description, requirements, booking_enabled, daily_quota, remaining_quota (computed)
    ```
  - Buat `app/Http/Resources/QueueTicketResource.php`:
    ```php
    // Fields: ticket_number, service_date, visitor_name, status, status_label (human-readable),
    //         service (ServiceResource), queue_position (when Waiting), counter_name (when Called/Started),
    //         checked_in_at, called_at, completed_at, cancelled_at
    ```
  - Catatan: InstitutionResource TIDAK diperlukan — endpoint `/api/institution` langsung return `response()->json(config('institution'))` (lihat Task 5)
  - `remaining_quota` pada ServiceResource: hitung `daily_quota - today's ticket count` untuk tanggal hari ini
  - `queue_position` pada QueueTicketResource: hitung jumlah tiket `Waiting` dengan `sequence_number` lebih kecil

  **Must NOT do**:
  - JANGAN expose `id` numerik pada QueueTicketResource — gunakan `ticket_number` sebagai identifier
  - JANGAN include relasi yang tidak dibutuhkan (users, counter sessions)
  - JANGAN gunakan `DB::` — pakai Eloquent query

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: File-file Resource standar Laravel, mengikuti pattern yang sudah ada
  - **Skills**: []
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Resource classes bukan Livewire-specific

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 3, 4)
  - **Blocks**: Tasks 5, 6, 8, 9, 10
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `app/Models/Service.php` — Fillable fields dan relationships yang tersedia untuk ServiceResource
  - `app/Models/QueueTicket.php` — Fields dan relationships untuk QueueTicketResource
  - `app/Enums/QueueStatus.php` — Status values untuk human-readable labels

  **API/Type References**:
  - `config/institution.php` — Structure config yang langsung di-return oleh endpoint `/api/institution` (Task 5)
  - `app/Http/Controllers/PublicQueueController.php:64-76` — Queue position calculation pattern (copy logic ini)

  **External References**:
  - Gunakan `laravel-boost_search-docs` dengan queries: `["eloquent api resources", "conditional resource attributes"]`

  **WHY Each Reference Matters**:
  - `PublicQueueController.php:64-76`: Queue position calculation sudah diimplementasikan di sini — HARUS diikuti pattern-nya persis untuk konsistensi
  - `QueueStatus.php`: Enum values menentukan label yang ditampilkan di response

  **Acceptance Criteria**:
  - [ ] `php artisan tinker --execute="new App\Http\Resources\ServiceResource(App\Models\Service::first())"` → no error
  - [ ] `vendor/bin/pint --dirty --format agent` → no issues
  - [ ] ServiceResource menghasilkan JSON dengan keys: id, name, code, slug, description, requirements, booking_enabled, daily_quota
  - [ ] QueueTicketResource menghasilkan JSON dengan keys: ticket_number, service_date, visitor_name, status, status_label, service

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: ServiceResource menghasilkan format JSON yang benar
    Tool: Bash (artisan tinker)
    Preconditions: Database di-seed dengan minimal 1 service
    Steps:
      1. Run tinker: `App\Http\Resources\ServiceResource::make(App\Models\Service::query()->where('is_active', true)->first())->resolve()`
      2. Assert result contains keys: 'id', 'name', 'slug', 'description', 'booking_enabled'
      3. Assert 'booking_enabled' is boolean
    Expected Result: Resource resolves to array with all expected keys and correct types
    Failure Indicators: Missing keys, type errors, exception thrown
    Evidence: .sisyphus/evidence/task-2-service-resource.txt

  Scenario: QueueTicketResource tidak expose numeric ID
    Tool: Bash (artisan tinker)
    Preconditions: Database di-seed dengan minimal 1 ticket
    Steps:
      1. Run tinker: `$t = App\Models\QueueTicket::query()->with(['service'])->first(); App\Http\Resources\QueueTicketResource::make($t)->resolve()`
      2. Assert result contains key 'ticket_number'
      3. Assert result does NOT contain key 'id'
    Expected Result: ticket_number present, id absent from JSON output
    Failure Indicators: 'id' key present in output
    Evidence: .sisyphus/evidence/task-2-ticket-resource-no-id.txt
  ```

  **Commit**: YES
  - Message: `feat(api): add API resource classes for services, tickets, institution`
  - Files: `app/Http/Resources/ServiceResource.php`, `app/Http/Resources/QueueTicketResource.php`
  - Pre-commit: `vendor/bin/pint --dirty --format agent`

- [x] 3. Next.js TypeScript Types + Shared API Client Module

  **What to do**:
  - Buat `frontend-public/src/types/api.ts` — TypeScript interfaces yang match dengan API Resource output:
    ```typescript
    interface Service { id: number; name: string; code: string; slug: string; description: string | null; requirements: string | null; booking_enabled: boolean; daily_quota: number | null; remaining_quota: number | null; }
    interface QueueTicket { ticket_number: string; service_date: string; visitor_name: string; status: string; status_label: string; service: Service; queue_position: number | null; counter_name: string | null; checked_in_at: string | null; called_at: string | null; completed_at: string | null; cancelled_at: string | null; }
    interface Institution { name: string; address: string; phone: string; operating_hours: string; logo_path: string; }
    interface BookingPayload { service_id: number; service_date: string; visitor_name: string; visitor_identifier?: string; visitor_phone?: string; notes?: string; }
    interface ApiError { message: string; errors?: Record<string, string[]>; }
    interface ApiResponse<T> { data: T; }
    ```
  - Buat `frontend-public/src/lib/api.ts` — API client menggunakan native `fetch`:
    ```typescript
    // Base URL dari env: NEXT_PUBLIC_API_URL (default: http://localhost:8000/api)
    // Functions: getInstitution(), getServices(), getServiceBySlug(slug), createBooking(payload), lookupTicket(ticketNumber, serviceDate), getTicketDetail(ticketNumber)
    // Semua function return typed responses
    // Error handling: throw ApiError untuk non-2xx responses
    ```
  - Buat `frontend-public/.env.local.example` dengan `NEXT_PUBLIC_API_URL=http://localhost:8000/api`

  **Must NOT do**:
  - JANGAN install axios atau library HTTP tambahan — gunakan native fetch
  - JANGAN tambahkan state management library (zustand, redux, dll)
  - JANGAN buat mock/stub API — langsung ke real backend

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Membuat 3 file kecil (types, api client, env example)
  - **Skills**: []
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: Next.js project pakai custom CSS, bukan Tailwind

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 4)
  - **Blocks**: Tasks 11, 12, 13, 14
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `frontend-public/src/app/layout.tsx` — Existing Next.js project structure dan import patterns
  - `frontend-public/src/contents/` — How static data is currently structured di Finris template

  **API/Type References**:
  - Task 2's Resource classes — TypeScript types MUST mirror these exactly

  **External References**:
  - `frontend-public/package.json` — Verify no existing HTTP client library installed
  - Next.js 16 App Router docs: server components vs client components fetch patterns

  **WHY Each Reference Matters**:
  - `layout.tsx`: Menentukan import convention dan module resolution paths yang sudah dipakai
  - Task 2 Resources: TypeScript types harus 1:1 match dengan Laravel Resource output — kalau tidak, frontend akan error

  **Acceptance Criteria**:
  - [ ] `cd frontend-public && npx tsc --noEmit` → no type errors
  - [ ] File `frontend-public/src/types/api.ts` exists dengan semua interfaces
  - [ ] File `frontend-public/src/lib/api.ts` exists dengan semua functions
  - [ ] File `frontend-public/.env.local.example` exists

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: TypeScript types compile tanpa error
    Tool: Bash
    Preconditions: frontend-public/ exists dengan pnpm installed
    Steps:
      1. Run `cd frontend-public && npx tsc --noEmit`
      2. Assert exit code 0
    Expected Result: No type errors
    Failure Indicators: Type errors in api.ts or types/api.ts
    Evidence: .sisyphus/evidence/task-3-tsc-check.txt

  Scenario: API client module exports semua functions
    Tool: Bash
    Preconditions: frontend-public/ exists
    Steps:
      1. Run `grep -c "export" frontend-public/src/lib/api.ts`
      2. Assert count >= 6 (getInstitution, getServices, getServiceBySlug, createBooking, lookupTicket, getTicketDetail)
    Expected Result: At least 6 exported functions
    Failure Indicators: Missing exports
    Evidence: .sisyphus/evidence/task-3-api-exports.txt
  ```

  **Commit**: YES
  - Message: `feat(frontend): add TypeScript types and API client module`
  - Files: `frontend-public/src/types/api.ts`, `frontend-public/src/lib/api.ts`, `frontend-public/.env.local.example`
  - Pre-commit: `cd frontend-public && npx tsc --noEmit`

- [x] 4. Pest Test Infrastructure Setup + Factory Verification

  **What to do**:
  - Buat directory `tests/Feature/Api/`
  - Verifikasi bahwa `ServiceFactory`, `QueueTicketFactory`, `QueuePoolFactory` sudah ada dan berfungsi
  - Buat test helper/base jika diperlukan — pastikan semua API test menggunakan `RefreshDatabase`
  - Buat file `tests/Feature/Api/ExampleApiTest.php` sebagai smoke test:
    ```php
    test('api route prefix works', function () {
        $response = $this->getJson('/api/institution');
        $response->assertStatus(200)->assertJsonStructure(['name']);
    });
    ```
    (Test ini akan FAIL di Wave 1 — itu expected, karena endpoint belum ada. Tapi test infrastructure harus PASS setup.)
  - Verifikasi factories bisa membuat Service dan QueueTicket tanpa error

  **Must NOT do**:
  - JANGAN buat test untuk endpoint yang belum ada — cukup verify infrastructure
  - JANGAN modifikasi existing test files

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Setup directory dan verifikasi factory saja
  - **Skills**: [`pest-testing`]
    - `pest-testing`: Memahami Pest 4 conventions dan test setup
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Tidak testing Livewire components

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 3)
  - **Blocks**: Tasks 5, 6, 8, 9, 10
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `tests/Feature/` — Existing test file structure dan conventions (RefreshDatabase, factories, etc.)
  - `database/factories/ServiceFactory.php` — Factory definition untuk Service model
  - `database/factories/QueueTicketFactory.php` — Factory definition untuk QueueTicket

  **External References**:
  - Gunakan `laravel-boost_search-docs` dengan queries: `["pest testing api", "feature test json"]`

  **WHY Each Reference Matters**:
  - `tests/Feature/`: Menentukan convention yang sudah ada — apakah pakai `$this->faker` vs `fake()`, apakah pakai `RefreshDatabase` vs `LazilyRefreshDatabase`
  - Factories: Harus verifikasi mereka berfungsi sebelum Wave 2 menulis API tests

  **Acceptance Criteria**:
  - [ ] Directory `tests/Feature/Api/` exists
  - [ ] `php artisan test --filter=ExampleApiTest --compact` → file can be loaded (even if test fails)
  - [ ] Factories dapat membuat Service dan QueueTicket: `Service::factory()->create()` → no exception

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Pest test infrastructure berfungsi
    Tool: Bash
    Preconditions: Laravel app configured
    Steps:
      1. Run `php artisan test --filter=ExampleApiTest --compact 2>&1 || true`
      2. Assert output contains "Tests:" (test ran, even if failed)
      3. Assert output does NOT contain "Error" or "Exception" (infrastructure works)
    Expected Result: Test file loads and runs (may fail assertion, but no infrastructure errors)
    Failure Indicators: PHP parse error, class not found, database connection error
    Evidence: .sisyphus/evidence/task-4-pest-setup.txt

  Scenario: Factories dapat membuat models
    Tool: Bash (artisan tinker)
    Preconditions: Database exists
    Steps:
      1. Run tinker: `App\Models\Service::factory()->create(); echo 'OK';`
      2. Assert output contains 'OK'
      3. Run tinker: `$s = App\Models\Service::factory()->create(); App\Models\QueueTicket::factory()->for($s)->create(); echo 'OK';`
      4. Assert output contains 'OK'
    Expected Result: Both factories create records without errors
    Failure Indicators: SQL errors, missing columns, factory definition errors
    Evidence: .sisyphus/evidence/task-4-factory-check.txt
  ```

  **Commit**: YES
  - Message: `test(api): verify test infrastructure and factories for API testing`
  - Files: `tests/Feature/Api/ExampleApiTest.php`
  - Pre-commit: `php artisan test --filter=ExampleApiTest --compact 2>&1 || true`

- [x] 5. GET /api/institution Endpoint (TDD: Test → Implementation)

  **What to do**:
  - **RED**: Buat `tests/Feature/Api/InstitutionTest.php`:
    ```php
    test('can get institution info', function () {
        $response = $this->getJson('/api/institution');
        $response->assertOk()
            ->assertJsonStructure(['name', 'address', 'phone', 'operating_hours']);
    });
    ```
  - **GREEN**: Buat `app/Http/Controllers/Api/PublicServiceController.php` dengan method `institution()`:
    - Return `config('institution')` sebagai JSON response
    - Tidak perlu Resource class — langsung `response()->json()`
  - **REFACTOR**: Pastikan response shape konsisten

  **Must NOT do**:
  - JANGAN hardcode institution data — baca dari `config('institution')`
  - JANGAN gunakan `env()` langsung

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Endpoint sangat sederhana — 1 test, 1 controller method
  - **Skills**: [`pest-testing`]
    - `pest-testing`: TDD flow dengan Pest 4
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Bukan Livewire component

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 6, 7, 8)
  - **Blocks**: Task 11 (homepage perlu institution data)
  - **Blocked By**: Tasks 1, 2, 4

  **References**:

  **Pattern References**:
  - `config/institution.php` — Structure config: returns `name` (string), `address` (string), `phone` (string), `operating_hours` (string), `logo_path` (string). Return langsung sebagai JSON tanpa Resource class.
  - `app/Http/Controllers/PublicQueueController.php:11-20` — Pattern bagaimana controller method index() bekerja

  **API/Type References**:
  - Expected response: `{ "name": "Pengadilan Agama", "address": "...", "phone": "...", "operating_hours": "Senin - Jumat, 08:00 - 16:00 WIB", "logo_path": "..." }`

  **WHY Each Reference Matters**:
  - `config/institution.php`: Ini satu-satunya source of truth untuk institution data — HARUS dibaca dari sini

  **Acceptance Criteria**:
  - [ ] `php artisan test --filter=InstitutionTest --compact` → PASS
  - [ ] `curl -s http://localhost:8000/api/institution | jq .name` → returns institution name string

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Institution endpoint returns valid JSON
    Tool: Bash (curl)
    Preconditions: Laravel app serving on localhost:8000
    Steps:
      1. Run `curl -s http://localhost:8000/api/institution`
      2. Parse JSON, assert key "name" exists and is non-empty string
      3. Assert key "operating_hours" exists and is string
    Expected Result: JSON with name and operating_hours keys
    Failure Indicators: 404, 500, missing keys, empty response
    Evidence: .sisyphus/evidence/task-5-institution-endpoint.txt

  Scenario: Institution endpoint menolak non-GET methods
    Tool: Bash (curl)
    Preconditions: Laravel app serving on localhost:8000
    Steps:
      1. Run `curl -s -o /dev/null -w "%{http_code}" -X POST http://localhost:8000/api/institution`
      2. Assert status code is 405
    Expected Result: Method Not Allowed for POST
    Failure Indicators: 200 or 500 response to POST
    Evidence: .sisyphus/evidence/task-5-institution-method-not-allowed.txt
  ```

  **Commit**: YES (group with Task 6)
  - Message: `feat(api): implement institution and service endpoints with tests`
  - Files: `tests/Feature/Api/InstitutionTest.php`, `app/Http/Controllers/Api/PublicServiceController.php`
  - Pre-commit: `php artisan test --filter=InstitutionTest --compact`

- [x] 6. GET /api/services + GET /api/services/{slug} Endpoints (TDD)

  **What to do**:
  - **RED**: Buat `tests/Feature/Api/ServiceTest.php`:
    ```php
    test('can list active services', function () {
        Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
        Service::factory()->create(['is_active' => false]); // should not appear
        $response = $this->getJson('/api/services');
        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'booking_enabled']]])
            ->assertJsonCount(1, 'data');
    });

    test('can get service by slug', function () {
        $service = Service::factory()->create(['slug' => 'konsultasi-hukum', 'is_active' => true]);
        $response = $this->getJson('/api/services/konsultasi-hukum');
        $response->assertOk()
            ->assertJsonPath('data.slug', 'konsultasi-hukum');
    });

    test('returns 404 for inactive service slug', function () {
        Service::factory()->create(['slug' => 'inactive-service', 'is_active' => false]);
        $response = $this->getJson('/api/services/inactive-service');
        $response->assertNotFound();
    });

    test('returns 404 for non-existent slug', function () {
        $response = $this->getJson('/api/services/does-not-exist');
        $response->assertNotFound();
    });
    ```
  - **GREEN**: Tambahkan methods ke `PublicServiceController`:
    - `index()`: Return `ServiceResource::collection()` — filter `is_active = true`, order by `sort_order`, `name`
    - `show(string $slug)`: Find by slug WHERE `is_active = true` → return `ServiceResource` atau 404
  - **REFACTOR**: Pastikan ServiceResource menghitung `remaining_quota` untuk tanggal hari ini

  **Must NOT do**:
  - JANGAN tampilkan service yang `is_active = false`
  - JANGAN include relasi users/officers dalam response

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Multiple test cases + controller methods + resource integration
  - **Skills**: [`pest-testing`]
    - `pest-testing`: TDD flow dengan multiple test scenarios
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Bukan Livewire

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 5, 7, 8)
  - **Blocks**: Tasks 11, 12
  - **Blocked By**: Tasks 1, 2, 4

  **References**:

  **Pattern References**:
  - `app/Http/Controllers/PublicQueueController.php:11-20` — Exact query pattern: `Service::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')`
  - `app/Http/Controllers/PublicQueueController.php:24-32` — Booking query pattern adds `->where('booking_enabled', true)`
  - `app/Http/Resources/ServiceResource.php` — Resource dari Task 2

  **API/Type References**:
  - `app/Models/Service.php` — fillable fields dan casts
  - `database/factories/ServiceFactory.php` — Factory untuk test data

  **WHY Each Reference Matters**:
  - `PublicQueueController.php:11-20`: Query pattern HARUS sama persis agar API dan web view menampilkan data yang konsisten
  - `ServiceFactory.php`: Tests butuh factory ini untuk create test data — perlu tahu states yang tersedia

  **Acceptance Criteria**:
  - [ ] `php artisan test --filter=ServiceTest --compact` → ALL 4 PASS
  - [ ] `curl -s http://localhost:8000/api/services | jq '.data | length'` → returns number > 0
  - [ ] `curl -s http://localhost:8000/api/services/[valid-slug] | jq '.data.slug'` → returns slug string

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Service list hanya tampilkan yang aktif
    Tool: Bash (curl)
    Preconditions: Database seeded dengan services (active dan inactive)
    Steps:
      1. Run `curl -s http://localhost:8000/api/services | jq '.data[] | .slug'`
      2. Assert all returned services have is_active=true (cross-check with DB)
      3. Assert response has 'data' wrapper array
    Expected Result: Only active services returned in data array
    Failure Indicators: Inactive services present, missing data wrapper
    Evidence: .sisyphus/evidence/task-6-services-list.txt

  Scenario: Service detail by slug returns correct data
    Tool: Bash (curl)
    Preconditions: Service with known slug exists
    Steps:
      1. Get first service slug: `SLUG=$(curl -s http://localhost:8000/api/services | jq -r '.data[0].slug')`
      2. Run `curl -s http://localhost:8000/api/services/$SLUG | jq '.data'`
      3. Assert response contains: name, slug, description, booking_enabled, daily_quota
    Expected Result: Single service object with all expected fields
    Failure Indicators: 404 for valid slug, missing fields
    Evidence: .sisyphus/evidence/task-6-service-detail.txt

  Scenario: Non-existent slug returns 404
    Tool: Bash (curl)
    Preconditions: None
    Steps:
      1. Run `curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/services/this-slug-does-not-exist-xyz`
      2. Assert status code is 404
    Expected Result: 404 Not Found
    Failure Indicators: 200 with empty data, 500 error
    Evidence: .sisyphus/evidence/task-6-service-not-found.txt
  ```

  **Commit**: YES (group with Task 5)
  - Message: `feat(api): implement institution and service endpoints with tests`
  - Files: `tests/Feature/Api/ServiceTest.php`, `app/Http/Controllers/Api/PublicServiceController.php`, `app/Http/Resources/ServiceResource.php`
  - Pre-commit: `php artisan test --filter=ServiceTest --compact`

- [x] 7. API Booking FormRequest with Business Rule Validation

  **What to do**:
  - Buat `app/Http/Requests/Api/StoreBookingRequest.php` — extend dari base FormRequest:
    ```php
    public function rules(): array
    {
        return [
            'service_id'         => ['required', 'integer', 'exists:services,id'],
            'service_date'       => ['required', 'date', 'after_or_equal:today', 'before_or_equal:+14 days', new \App\Rules\WeekdayOnly],
            'visitor_name'       => ['required', 'string', 'max:255'],
            'visitor_identifier' => ['nullable', 'string', 'max:64'],
            'visitor_phone'      => ['nullable', 'string', 'max:30'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }
    ```
  - Tambahkan `withValidator()` atau `after()` untuk business rule checks:
    1. Service harus `is_active = true` → 422 jika tidak
    2. Service harus `booking_enabled = true` → 422 jika tidak
    3. Jika `daily_quota` tidak null, cek `QueueTicket::count()` untuk service+date < quota → 422 jika penuh
    4. Buat custom rule `app/Rules/WeekdayOnly.php` via `php artisan make:rule WeekdayOnly` — tolak Sabtu (6) dan Minggu (0) menggunakan `Carbon::parse($value)->isWeekend()`
    5. Date rules: after_or_equal:today (same-day boleh), before_or_equal:+14 days, weekday only
  - Definisikan custom error messages dalam bahasa Indonesia

  **Must NOT do**:
  - JANGAN duplikasi logic dari `StorePublicQueueBookingRequest` — buat fresh dengan business rules
  - JANGAN hardcode quota numbers — baca dari `Service->daily_quota`

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Business logic validation cukup kompleks
  - **Skills**: [`pest-testing`]
    - `pest-testing`: Validation rules perlu di-test
  - **Skills Evaluated but Omitted**:
    - `developing-with-fortify`: Bukan auth-related

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 5, 6, 8)
  - **Blocks**: Task 8
  - **Blocked By**: None (FormRequest independent, tapi logically grouped with Wave 2)

  **References**:

  **Pattern References**:
  - `app/Http/Requests/StorePublicQueueBookingRequest.php` — Existing booking validation (shape-only, tanpa business rules)
  - `app/Http/Controllers/PublicQueueController.php:24-32` — Booking method yang filter `is_active` dan `booking_enabled`
  - `app/Models/Service.php` — `daily_quota` field dan cast

  **API/Type References**:
  - `app/Enums/QueueStatus.php` — Untuk counting tickets terhadap quota (semua non-cancelled)

  **WHY Each Reference Matters**:
  - `StorePublicQueueBookingRequest.php`: Referensi untuk field names dan basic rules — tapi API version HARUS lebih strict
  - `PublicQueueController.php:24-32`: Menunjukkan bahwa controller sudah filter booking_enabled — ini harus dipindahkan ke FormRequest level

  **Acceptance Criteria**:
  - [ ] File `app/Http/Requests/Api/StoreBookingRequest.php` exists
  - [ ] Rules include `after_or_equal:today` untuk service_date (same-day allowed)
  - [ ] Rules include `before_or_equal:+14 days` untuk batas maksimal 14 hari ke depan
  - [ ] Custom rule `WeekdayOnly` menolak hari Sabtu dan Minggu
  - [ ] Buat rule class `app/Rules/WeekdayOnly.php` via `php artisan make:rule WeekdayOnly`
  - [ ] Business rules validate: is_active, booking_enabled, daily_quota
  - [ ] `vendor/bin/pint --dirty --format agent` → no issues

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Validation rejects inactive service
    Tool: Bash (curl)
    Preconditions: Inactive service exists in DB (seeded or created via tinker)
    Steps:
      1. Create inactive service via tinker: `$s = App\Models\Service::factory()->create(['is_active' => false]); echo $s->id;`
      2. Run `curl -s -w "\n%{http_code}" -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -d '{"service_id":[ID],"service_date":"2026-03-10","visitor_name":"Test"}'`
      3. Assert HTTP status is 422
      4. Assert response contains error about service not being active
    Expected Result: 422 with validation error for inactive service
    Failure Indicators: 201 (ticket created for inactive service), 500
    Evidence: .sisyphus/evidence/task-7-reject-inactive.txt

  Scenario: Validation rejects past date
    Tool: Bash (curl)
    Preconditions: Active service exists
    Steps:
      1. Run `curl -s -w "\n%{http_code}" -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -d '{"service_id":1,"service_date":"2020-01-01","visitor_name":"Test"}'`
      2. Assert HTTP status is 422
      3. Assert error message references service_date
    Expected Result: 422 with date validation error
    Failure Indicators: 201 (booking created for past date)
    Evidence: .sisyphus/evidence/task-7-reject-past-date.txt

  Scenario: Validation rejects weekend date
    Tool: Bash (curl)
    Preconditions: Active service exists
    Steps:
      1. Find next Saturday date: `SATURDAY=$(date -d "next saturday" +%Y-%m-%d)`
      2. Run `curl -s -w "\n%{http_code}" -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"service_id\":1,\"service_date\":\"$SATURDAY\",\"visitor_name\":\"Test\"}"`
      3. Assert HTTP status is 422
      4. Assert error message references service_date (weekend not allowed)
    Expected Result: 422 with validation error — weekend dates rejected
    Failure Indicators: 201 (booking created for weekend)
    Evidence: .sisyphus/evidence/task-7-reject-weekend.txt

  Scenario: Validation rejects date more than 14 days ahead
    Tool: Bash (curl)
    Preconditions: Active service exists
    Steps:
      1. Calculate date 15 days ahead: `FUTURE=$(date -d "+15 days" +%Y-%m-%d)`
      2. Run `curl -s -w "\n%{http_code}" -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"service_id\":1,\"service_date\":\"$FUTURE\",\"visitor_name\":\"Test\"}"`
      3. Assert HTTP status is 422
      4. Assert error message references service_date
    Expected Result: 422 with validation error — date too far in future
    Failure Indicators: 201 (booking created for date >14 days)
    Evidence: .sisyphus/evidence/task-7-reject-future-date.txt
  ```

  **Commit**: YES
  - Message: `feat(api): add API booking form request with business rule validation`
  - Files: `app/Http/Requests/Api/StoreBookingRequest.php`
  - Pre-commit: `vendor/bin/pint --dirty --format agent`

- [x] 8. POST /api/queue/booking Endpoint (TDD)

  **What to do**:
  - **RED**: Buat `tests/Feature/Api/BookingTest.php`:
    ```php
    test('can create booking for active bookable service', function () {
        $service = Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
        $response = $this->postJson('/api/queue/booking', [
            'service_id' => $service->id,
            'service_date' => now()->addWeekday()->format('Y-m-d'),
            'visitor_name' => 'Jane Doe',
            'visitor_phone' => '08123456789',
        ]);
        $response->assertCreated()
            ->assertJsonStructure(['data' => ['ticket_number', 'service_date', 'visitor_name', 'status']]);
    });

    test('booking returns 422 for inactive service', ...);
    test('booking returns 422 for non-bookable service', ...);
    test('booking returns 422 for past date', ...);
    test('booking returns 422 for quota exceeded', ...);
    test('booking returns 422 for missing required fields', ...);
    test('booking sets status to booked (QueueStatus::Booked)', ...);
    test('booking sets channel to online_booking', ...);
    ```
  - **GREEN**: Buat `app/Http/Controllers/Api/PublicQueueController.php` dengan method `booking()`:
    - Inject `StoreBookingRequest` dan `CreateQueueTicket` action
    - Map validated data ke action payload dengan `channel = 'online_booking'`
    - Return `QueueTicketResource` dengan status 201
  - **REFACTOR**: Pastikan response shape konsisten

  **Must NOT do**:
  - JANGAN duplikasi ticket creation logic — gunakan `CreateQueueTicket` action
  - JANGAN hardcode channel — selalu `'online_booking'` untuk API bookings
  - JANGAN expose ticket `id` di response — hanya `ticket_number`

  **Recommended Agent Profile**:
  - **Category**: `deep`
    - Reason: Task paling kompleks — multiple test scenarios, business rules, action integration
  - **Skills**: [`pest-testing`]
    - `pest-testing`: TDD flow dengan banyak test scenarios
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Bukan Livewire

  **Parallelization**:
  - **Can Run In Parallel**: YES (setelah Task 7 selesai)
  - **Parallel Group**: Wave 2 (with Tasks 5, 6, 7 — tapi depends on 7)
  - **Blocks**: Task 12 (booking wizard perlu endpoint ini)
  - **Blocked By**: Tasks 1, 2, 4, 7

  **References**:

  **Pattern References**:
  - `app/Http/Controllers/PublicQueueController.php:34-49` — Exact storeBooking pattern — COPY this logic flow
  - `app/Actions/Queue/CreateQueueTicket.php` — Action class yang akan di-reuse, perhatikan payload shape
  - `app/Http/Requests/Api/StoreBookingRequest.php` — FormRequest dari Task 7

  **API/Type References**:
  - `app/Http/Resources/QueueTicketResource.php` — Resource untuk format response
  - `app/Enums/QueueStatus.php` — Status `Booked` untuk online booking

  **External References**:
  - Gunakan `laravel-boost_search-docs` dengan queries: `["form request api json", "resource response 201"]`

  **WHY Each Reference Matters**:
  - `PublicQueueController.php:34-49`: Ini EXACT pattern yang harus diikuti — same payload mapping, same action call, tapi return JSON instead of redirect
  - `CreateQueueTicket.php`: Payload shape harus match persis `@param array` yang didefinisikan di action

  **Acceptance Criteria**:
  - [ ] `php artisan test --filter=BookingTest --compact` → ALL PASS (8+ tests)
  - [ ] Created ticket has `channel = 'online_booking'` dan `status = 'booked'` (lowercase enum value)
  - [ ] Response status 201 dengan QueueTicketResource shape

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Happy path — berhasil booking
    Tool: Bash (curl)
    Preconditions: Active bookable service exists in DB
    Steps:
      1. Get active service: `SVC_ID=$(curl -s http://localhost:8000/api/services | jq '.data[0].id')`
      2. Run: `curl -s -w "\n%{http_code}" -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"service_id\":$SVC_ID,\"service_date\":\"$(date -d '+1 day' +%Y-%m-%d)\",\"visitor_name\":\"Jane Doe\",\"visitor_phone\":\"08123456789\"}"`
      3. Assert HTTP status is 201
      4. Assert response JSON has keys: ticket_number, service_date, visitor_name, status
      5. Assert status value is "booked" (lowercase enum value, NOT display label)
    Expected Result: 201 Created with ticket data, status=booked
    Failure Indicators: Non-201 status, missing ticket_number, wrong status
    Evidence: .sisyphus/evidence/task-8-booking-success.txt

  Scenario: Booking gagal — quota exceeded
    Tool: Bash (curl + tinker)
    Preconditions: Service with daily_quota=1 exists
    Steps:
      1. Create service with quota=1 via tinker: `$s = App\Models\Service::factory()->create(['is_active'=>true,'booking_enabled'=>true,'daily_quota'=>1]); echo $s->id;`
      2. Create 1 existing ticket for tomorrow via tinker
      3. Try to book: `curl -s -w "\n%{http_code}" -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"service_id\":[ID],\"service_date\":\"$(date -d '+1 day' +%Y-%m-%d)\",\"visitor_name\":\"Test\"}"`
      4. Assert HTTP status is 422
      5. Assert error message references quota
    Expected Result: 422 with quota exceeded error
    Failure Indicators: 201 (ticket created beyond quota)
    Evidence: .sisyphus/evidence/task-8-booking-quota-exceeded.txt

  Scenario: Rate limiting — booking endpoint throttled
    Tool: Bash (curl loop)
    Preconditions: Laravel app serving, throttle configured at 10/min for booking
    Steps:
      1. Send 12 rapid POST requests to /api/queue/booking (can be invalid payload)
      2. Assert at least one response has HTTP status 429
    Expected Result: 429 Too Many Requests after threshold
    Failure Indicators: All 12 requests return 422 or 201 (no throttle)
    Evidence: .sisyphus/evidence/task-8-booking-throttle.txt
  ```

  **Commit**: YES
  - Message: `feat(api): implement public queue booking endpoint with tests`
  - Files: `tests/Feature/Api/BookingTest.php`, `app/Http/Controllers/Api/PublicQueueController.php`
  - Pre-commit: `php artisan test --filter=BookingTest --compact`

- [x] 9. GET /api/queue/lookup Endpoint (TDD)

  **What to do**:
  - **RED**: Buat `tests/Feature/Api/LookupTest.php`:
    ```php
    test('can lookup ticket by ticket_number and service_date', function () {
        $ticket = QueueTicket::factory()->create([
            'ticket_number' => 'A0001',
            'service_date' => '2026-03-10',
            'status' => QueueStatus::Booked,
        ]);
        $response = $this->getJson('/api/queue/lookup?ticket_number=A0001&service_date=2026-03-10');
        $response->assertOk()
            ->assertJsonPath('data.ticket_number', 'A0001');
    });

    test('lookup returns 404 when ticket not found', function () {
        $response = $this->getJson('/api/queue/lookup?ticket_number=ZZZZ&service_date=2026-03-10');
        $response->assertNotFound();
    });

    test('lookup returns 422 when missing params', function () {
        $response = $this->getJson('/api/queue/lookup');
        $response->assertUnprocessable();
    });

    test('lookup returns queue position for waiting tickets', function () {
        // Create 3 tickets with Waiting status, verify position = 3 for last one
    });

    test('lookup returns zero position for booked tickets', function () {
        // Booked tickets have no queue position
    });
    ```
  - **GREEN**: Tambahkan method `lookup()` ke `Api/PublicQueueController`:
    - Buat `app/Http/Requests/Api/LookupTicketRequest.php` dengan rules: `ticket_number` required string, `service_date` required date
    - Query: `QueueTicket::with(['service', 'counter', 'queuePool'])->where('ticket_number', $tn)->whereDate('service_date', $date)->first()`
    - Jika null → return 404 JSON: `{ "message": "Tiket tidak ditemukan" }`
    - Jika found → return `QueueTicketResource` (includes queue_position calculation)
  - **REFACTOR**: Extract queue position calculation ke reusable method/trait

  **Must NOT do**:
  - JANGAN buat lookup fields optional — keduanya WAJIB untuk API (berbeda dari web version)
  - JANGAN expose ticket ID — identifier = ticket_number + service_date

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Multiple test scenarios + queue position logic
  - **Skills**: [`pest-testing`]
    - `pest-testing`: TDD flow
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Bukan Livewire

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 10, 11, 12)
  - **Blocks**: Task 13
  - **Blocked By**: Tasks 1, 2, 4

  **References**:

  **Pattern References**:
  - `app/Http/Controllers/PublicQueueController.php:51-76` — EXACT lookup logic to replicate for API
  - `app/Http/Requests/LookupQueueTicketRequest.php` — Existing validation (fields nullable di web, tapi WAJIB di API)

  **API/Type References**:
  - `app/Enums/QueueStatus.php` — `Waiting` status untuk queue position calculation
  - `app/Http/Resources/QueueTicketResource.php` — Response format

  **WHY Each Reference Matters**:
  - `PublicQueueController.php:51-76`: Queue position calculation logic ada di sini — HARUS di-copy persis (count tickets with lower sequence_number in same pool+date)
  - `LookupQueueTicketRequest.php`: API version berbeda — fields harus required, bukan nullable

  **Acceptance Criteria**:
  - [ ] `php artisan test --filter=LookupTest --compact` → ALL PASS (5 tests)
  - [ ] Lookup found → 200 dengan QueueTicketResource
  - [ ] Lookup not found → 404 dengan `{ "message": "Tiket tidak ditemukan" }`
  - [ ] Queue position dihitung untuk status Waiting, 0 untuk status lain

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Happy path — lookup ticket yang ada
    Tool: Bash (curl)
    Preconditions: Ticket created via booking endpoint
    Steps:
      1. Create booking first: `TICKET=$(curl -s -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -H "Accept: application/json" -d '{"service_id":1,"service_date":"2026-03-10","visitor_name":"Lookup Test"}' | jq -r '.data.ticket_number')`
      2. Lookup: `curl -s "http://localhost:8000/api/queue/lookup?ticket_number=$TICKET&service_date=2026-03-10" | jq .`
      3. Assert response has key 'data.ticket_number' matching $TICKET
      4. Assert response has key 'data.status'
    Expected Result: 200 with matching ticket data
    Failure Indicators: 404, wrong ticket returned, missing fields
    Evidence: .sisyphus/evidence/task-9-lookup-found.txt

  Scenario: Lookup ticket yang tidak ada
    Tool: Bash (curl)
    Preconditions: None
    Steps:
      1. Run `curl -s -w "\n%{http_code}" "http://localhost:8000/api/queue/lookup?ticket_number=ZZZZZZZ&service_date=2026-01-01"`
      2. Assert HTTP status is 404
      3. Assert response contains "message" key
    Expected Result: 404 with error message
    Failure Indicators: 200 with null data, 500
    Evidence: .sisyphus/evidence/task-9-lookup-not-found.txt

  Scenario: Lookup tanpa parameter → validation error
    Tool: Bash (curl)
    Preconditions: None
    Steps:
      1. Run `curl -s -w "\n%{http_code}" -H "Accept: application/json" "http://localhost:8000/api/queue/lookup"`
      2. Assert HTTP status is 422
      3. Assert response has 'errors' key with ticket_number and service_date
    Expected Result: 422 with validation errors for both fields
    Failure Indicators: 200 or 404 (no validation)
    Evidence: .sisyphus/evidence/task-9-lookup-missing-params.txt
  ```

  **Commit**: YES
  - Message: `feat(api): implement ticket lookup endpoint with tests`
  - Files: `tests/Feature/Api/LookupTest.php`, `app/Http/Requests/Api/LookupTicketRequest.php`, `app/Http/Controllers/Api/PublicQueueController.php`
  - Pre-commit: `php artisan test --filter=LookupTest --compact`

- [x] 10. GET /api/queue/ticket/{ticket_number} Endpoint (TDD)

  **What to do**:
  - **RED**: Buat `tests/Feature/Api/TicketDetailTest.php`:
    ```php
    test('can get ticket detail by ticket_number', function () {
        $ticket = QueueTicket::factory()->create([
            'ticket_number' => 'B0001',
            'service_date' => '2026-03-10',
            'status' => QueueStatus::Booked,
        ]);
        $response = $this->getJson('/api/queue/ticket/B0001?service_date=2026-03-10');
        $response->assertOk()
            ->assertJsonPath('data.ticket_number', 'B0001')
            ->assertJsonPath('data.status', 'booked');
    });

    test('ticket detail returns 404 for non-existent ticket', ...);
    test('ticket detail returns 404 when date mismatch', ...);
    test('ticket detail includes service relationship', ...);
    test('ticket detail includes queue position for waiting tickets', ...);
    ```
  - **GREEN**: Tambahkan method `show()` ke `Api/PublicQueueController`:
    - Accept `ticket_number` route parameter + `service_date` query parameter
    - Query sama seperti lookup tapi lewat route parameter
    - Return `QueueTicketResource` dengan relationships loaded
  - **REFACTOR**: Extract shared lookup logic antara `lookup()` dan `show()` ke private method

  **Must NOT do**:
  - JANGAN accept numeric ID — hanya ticket_number
  - JANGAN tampilkan ticket tanpa service_date verification (prevent enumeration)

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Test scenarios + shared logic extraction
  - **Skills**: [`pest-testing`]
    - `pest-testing`: TDD flow
  - **Skills Evaluated but Omitted**:
    - `livewire-development`: Bukan Livewire

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 9, 11, 12)
  - **Blocks**: Task 14
  - **Blocked By**: Tasks 1, 2, 4

  **References**:

  **Pattern References**:
  - `app/Http/Controllers/PublicQueueController.php:78-95` — Confirmation method pattern
  - Task 9's lookup method — Share query/position logic

  **API/Type References**:
  - `app/Http/Resources/QueueTicketResource.php` — Response format
  - `app/Models/QueueTicket.php` — Model relationships: service, counter, queuePool

  **WHY Each Reference Matters**:
  - `PublicQueueController.php:78-95`: Confirmation logic loads ticket with relationships dan computes queue position — API version harus identical behavior
  - Task 9: Lookup dan ticket detail share logic (query + position) — extract ke private method

  **Acceptance Criteria**:
  - [ ] `php artisan test --filter=TicketDetailTest --compact` → ALL PASS (5 tests)
  - [ ] Ticket found → 200 dengan full QueueTicketResource including service relationship
  - [ ] Ticket not found → 404
  - [ ] Requires service_date query param untuk prevent enumeration

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Happy path — get ticket detail
    Tool: Bash (curl)
    Preconditions: Ticket exists via previous booking
    Steps:
      1. Create booking: `RESULT=$(curl -s -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -H "Accept: application/json" -d '{"service_id":1,"service_date":"2026-03-10","visitor_name":"Detail Test"}')`
      2. Extract: `TN=$(echo $RESULT | jq -r '.data.ticket_number')`
      3. Get detail: `curl -s "http://localhost:8000/api/queue/ticket/$TN?service_date=2026-03-10" | jq .`
      4. Assert response has 'data.ticket_number', 'data.service.name', 'data.status'
    Expected Result: 200 with full ticket details including service relationship
    Failure Indicators: 404, missing service data, wrong ticket
    Evidence: .sisyphus/evidence/task-10-ticket-detail.txt

  Scenario: Ticket detail tanpa service_date → rejected
    Tool: Bash (curl)
    Preconditions: Ticket exists
    Steps:
      1. Run `curl -s -w "\n%{http_code}" -H "Accept: application/json" "http://localhost:8000/api/queue/ticket/A0001"`
      2. Assert HTTP status is 422 or 404
    Expected Result: Request rejected without service_date (prevent enumeration)
    Failure Indicators: 200 returning ticket data without date verification
    Evidence: .sisyphus/evidence/task-10-ticket-no-date.txt
  ```

  **Commit**: YES
  - Message: `feat(api): implement ticket detail endpoint with tests`
  - Files: `tests/Feature/Api/TicketDetailTest.php`, `app/Http/Controllers/Api/PublicQueueController.php`
  - Pre-commit: `php artisan test --filter=TicketDetailTest --compact`

- [x] 11. Next.js Homepage — Adapt Finris Template

  **What to do**:
  - Modifikasi `frontend-public/src/app/page.tsx` — ganti konten Finris default dengan:
    1. **Hero Section**: Heading "Sistem Antrian PTSP [Institution Name]", subheading dari institution info, CTA buttons "Booking Antrian" → `/antrian`, "Cek Status" → `/antrian/cek`
    2. **Services Section**: Grid/list layanan dari `GET /api/services` — tampilkan name, description, badge booking_enabled
    3. **Info Section**: Operating hours dari `GET /api/institution`, alamat, kontak
  - Fetch data di Server Component (Next.js App Router SSR):
    ```typescript
    // page.tsx as async server component
    const services = await getServices();
    const institution = await getInstitution();
    ```
  - Adapt Finris sections: Gunakan existing section components (Hero, ServiceArea, dll) sebagai base, modifikasi content
  - Pastikan responsive — ikuti Finris responsive patterns

  **Must NOT do**:
  - JANGAN install Tailwind — gunakan Finris custom CSS (`public/assets/css/style.css`)
  - JANGAN buat homepage CMS-driven — service list dari API, sisanya static
  - JANGAN migrasi semua Finris template sections — hanya yang relevan
  - JANGAN tambahkan SEO structured data / JSON-LD

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Adaptasi template UI, layout, responsive design
  - **Skills**: []
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: Finris TIDAK pakai Tailwind — custom CSS
    - `fluxui-development`: Bukan Livewire/Flux project

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 9, 10, 12)
  - **Blocks**: Task 15
  - **Blocked By**: Tasks 3, 5, 6

  **References**:

  **Pattern References**:
  - `frontend-public/src/app/page.tsx` — Current homepage structure (Finris home-one)
  - `frontend-public/src/sections/home-one/` — Hero, About, Service sections dari Finris
  - `frontend-public/src/components/context/ContextProvider.tsx` — Context wrapper pattern
  - `resources/views/welcome.blade.php` — Laravel homepage Blade view — referensi konten yang perlu di-replicate

  **API/Type References**:
  - `frontend-public/src/lib/api.ts` — `getServices()`, `getInstitution()` functions dari Task 3
  - `frontend-public/src/types/api.ts` — `Service`, `Institution` interfaces dari Task 3

  **External References**:
  - `frontend-public/public/assets/css/style.css` — CSS classes yang tersedia
  - Finris template structure: sections pattern, component naming

  **WHY Each Reference Matters**:
  - `welcome.blade.php`: Ini adalah konten yang perlu di-replicate di Next.js — struktur, teks, layout
  - `src/sections/home-one/`: Finris sections yang bisa di-adapt — jangan buat dari nol, modifikasi existing
  - `src/lib/api.ts`: Server-side data fetching functions — gunakan di async server component

  **Acceptance Criteria**:
  - [ ] `cd frontend-public && pnpm build` → exit code 0
  - [ ] Homepage menampilkan institution name
  - [ ] Homepage menampilkan daftar services dari API
  - [ ] Homepage memiliki link ke `/antrian` dan `/antrian/cek`
  - [ ] Mobile responsive

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Homepage loads dan menampilkan services
    Tool: Playwright (playwright skill)
    Preconditions: Laravel API running on localhost:8000, Next.js dev server on localhost:3000
    Steps:
      1. Navigate to http://localhost:3000/
      2. Wait for page load (networkidle)
      3. Assert page title contains institution name or "Antrian"
      4. Assert element containing "Booking Antrian" text exists (CTA button)
      5. Assert element containing "Cek Status" text exists (CTA button)
      6. Assert service names are displayed (at least 1 service card/item visible)
      7. Take screenshot
    Expected Result: Page loads with institution info, service list, and CTA buttons
    Failure Indicators: Blank page, "Loading..." stuck, API errors in console, no services shown
    Evidence: .sisyphus/evidence/task-11-homepage.png

  Scenario: Homepage mobile responsive
    Tool: Playwright (playwright skill)
    Preconditions: Same as above
    Steps:
      1. Set viewport to 375x812 (iPhone X)
      2. Navigate to http://localhost:3000/
      3. Assert page content visible tanpa horizontal scroll
      4. Assert CTA buttons visible
      5. Take screenshot
    Expected Result: Page renders correctly on mobile viewport
    Failure Indicators: Horizontal scroll, overlapping elements, hidden CTAs
    Evidence: .sisyphus/evidence/task-11-homepage-mobile.png
  ```

  **Commit**: YES
  - Message: `feat(frontend): implement homepage with service catalog`
  - Files: `frontend-public/src/app/page.tsx`, `frontend-public/src/sections/` (modified sections)
  - Pre-commit: `cd frontend-public && pnpm build`

- [x] 12. Next.js Booking Wizard — 3-Step Form

  **What to do**:
  - Buat `frontend-public/src/app/antrian/page.tsx` — Client Component ('use client') dengan 3-step wizard:
    1. **Step 1 — Pilih Layanan**: Grid/list services dari `GET /api/services` (hanya `booking_enabled = true`), pilih satu service
    2. **Step 2 — Isi Data**: Form fields: visitor_name (required), visitor_identifier (NIK, optional), visitor_phone (optional), service_date (date picker, min=today, max=14 days ahead, weekdays only), notes (optional)
    3. **Step 3 — Konfirmasi & Submit**: Ringkasan data yang diisi, tombol "Submit Booking"
  - State management: gunakan React `useState` untuk step navigation dan form data
  - On submit: call `createBooking()` dari API client → on success, redirect ke `/antrian/konfirmasi/[ticket_number]?date=[service_date]`
  - Error handling: tampilkan validation errors dari API (422), tampilkan error message untuk quota exceeded
  - Loading state: disable submit button saat request in-flight, tampilkan spinner
  - Buat sub-components jika perlu: `StepSelector.tsx`, `StepForm.tsx`, `StepConfirm.tsx`

  **Must NOT do**:
  - JANGAN install form library (react-hook-form, formik) — gunakan native React state
  - JANGAN install date picker library — gunakan native HTML date input
  - JANGAN tambahkan client-side validation yang berlebihan — server-side validation sudah cukup
  - JANGAN duplikasi business logic (quota check, dll) di client — trust server response

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Complex UI with multi-step form, state management, error handling
  - **Skills**: []
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: Custom CSS, bukan Tailwind
    - `livewire-development`: Next.js, bukan Livewire

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Tasks 9, 10, 11)
  - **Blocks**: Task 15
  - **Blocked By**: Tasks 3, 6, 8

  **References**:

  **Pattern References**:
  - `resources/views/pages/public/antrian/booking.blade.php` — Existing booking wizard Blade view — EXACT content/flow to replicate
  - `frontend-public/src/components/elements/` — Existing Finris form elements to use
  - `frontend-public/src/app/page.tsx` — Page structure pattern

  **API/Type References**:
  - `frontend-public/src/lib/api.ts` — `getServices()`, `createBooking()` dari Task 3
  - `frontend-public/src/types/api.ts` — `Service`, `BookingPayload`, `ApiError` interfaces

  **External References**:
  - `frontend-public/public/assets/css/style.css` — Form styling classes

  **WHY Each Reference Matters**:
  - `booking.blade.php`: Ini adalah flow yang EXACT harus di-replicate — 3 steps, same fields, same UX
  - `api.ts`: createBooking() function handles POST request — wizard harus call ini on final submit
  - Finris form elements: Reuse existing styled inputs/buttons dari template

  **Acceptance Criteria**:
  - [ ] `cd frontend-public && pnpm build` → exit code 0
  - [ ] 3 steps visible dan navigable (next/back buttons)
  - [ ] Step 1 shows bookable services from API
  - [ ] Step 3 submits booking dan redirects to confirmation on success
  - [ ] Validation errors dari API ditampilkan di form
  - [ ] Submit button disabled saat loading

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Full booking flow — happy path
    Tool: Playwright (playwright skill)
    Preconditions: Laravel API running, Next.js dev server running, bookable services exist
    Steps:
      1. Navigate to http://localhost:3000/antrian
      2. Wait for services to load
      3. Click on first service card/button (Step 1)
      4. Assert Step 2 form appears
      5. Fill "visitor_name" input with "Jane Doe"
      6. Fill "service_date" input with today's date (or next weekday if today is weekend)
      7. Click "Next" or "Lanjutkan" button
      8. Assert Step 3 confirmation shows "Jane Doe" and selected service name
      9. Click "Submit Booking" / "Kirim" button
      10. Wait for redirect
      11. Assert URL contains "/antrian/konfirmasi/"
      12. Take screenshot
    Expected Result: Complete 3-step booking flow ending at confirmation page
    Failure Indicators: Step transition fails, form submission error, no redirect
    Evidence: .sisyphus/evidence/task-12-booking-flow.png

  Scenario: Booking validation error displayed
    Tool: Playwright (playwright skill)
    Preconditions: Same as above
    Steps:
      1. Navigate to http://localhost:3000/antrian
      2. Select a service (Step 1)
      3. Leave visitor_name empty
      4. Try to proceed/submit
      5. Assert error message visible near visitor_name field
    Expected Result: Validation error displayed inline
    Failure Indicators: No error shown, form submits despite empty required field
    Evidence: .sisyphus/evidence/task-12-booking-validation-error.png

  Scenario: Booking wizard mobile responsive
    Tool: Playwright (playwright skill)
    Preconditions: Same as above
    Steps:
      1. Set viewport to 375x812
      2. Navigate to http://localhost:3000/antrian
      3. Assert step navigation visible
      4. Assert form fields usable (not cut off)
      5. Take screenshot
    Expected Result: Wizard renders properly on mobile
    Failure Indicators: Overlapping elements, unusable form on mobile
    Evidence: .sisyphus/evidence/task-12-booking-mobile.png
  ```

  **Commit**: YES
  - Message: `feat(frontend): implement booking wizard with 3-step flow`
  - Files: `frontend-public/src/app/antrian/page.tsx`, optional sub-components
  - Pre-commit: `cd frontend-public && pnpm build`

- [x] 13. Halaman Ticket Lookup (`/antrian/cek`) — Next.js

  **What to do**:
  - Buat halaman di `frontend-public/src/app/antrian/cek/page.tsx`
  - Implementasi form pencarian dengan 2 field: Nomor Antrian (text) dan Tanggal Layanan (date)
  - Gunakan `apiClient.lookupTicket()` dari `src/lib/api.ts` (Task 3)
  - **State management**: `idle` → `loading` → `found` / `not-found` / `error`
  - **Tampilan hasil** (jika tiket ditemukan):
    - Info dasar: ticket_number, visitor_name, service name, service_date
    - Status badge dengan warna sesuai enum (Booked=blue, Waiting=amber, Called=green, Completed=zinc, Cancelled=red, Skipped=orange)
    - Status-specific message:
      - `Waiting`: "Posisi antrian Anda: {position}" (amber background)
      - `Called`: "Silakan segera menuju {counter_name}" (green background)
      - `Completed`: "Layanan telah selesai" (zinc background)
      - `Booked`: "Tiket terdaftar. Silakan datang dan lakukan check-in di loket" (blue background)
      - `Cancelled`: "Tiket ini telah dibatalkan" (red background)
      - `Skipped`: "Tiket ini telah dilewati" (orange background)
  - **Tampilan not-found**: Card merah dengan heading "Tiket Tidak Ditemukan" dan tombol "Periksa Ulang"
  - Gunakan Finris CSS classes — referensi pattern dari halaman booking (Task 12)
  - Form menggunakan GET query params (bisa di-bookmark/share URL hasil pencarian)
  - Animasi transisi state menggunakan framer-motion

  **Must NOT do**:
  - Jangan gunakan Tailwind — gunakan custom CSS classes dari Finris template
  - Jangan tambahkan real-time polling/WebSocket
  - Jangan ekspos internal ticket ID — hanya `ticket_number` + `service_date`

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Halaman frontend dengan form interaktif dan state-dependent UI
  - **Skills**: []
    - Tidak ada skill khusus — halaman ini menggunakan custom CSS (bukan Tailwind/Flux)
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: Template Finris menggunakan custom CSS, bukan Tailwind
    - `livewire-development`: Next.js, bukan Livewire

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 4 (with Tasks 14, 15)
  - **Blocks**: Task 15 (smoke test)
  - **Blocked By**: Task 3 (API client), Task 9 (lookup API endpoint)

  **References**:

  **Pattern References** (existing code to follow):
  - `resources/views/pages/public/antrian/lookup.blade.php` — Source of truth untuk UI layout, field names, status-specific messaging, dan not-found state
  - `frontend-public/src/app/antrian/page.tsx` — Booking wizard page pattern (sister page, same directory level)

  **API/Type References** (contracts to implement against):
  - `frontend-public/src/types/api.ts:QueueTicket` — TypeScript interface for ticket data (dari Task 3) — digunakan sebagai response type untuk lookup
  - `frontend-public/src/lib/api.ts:lookupTicket()` — API client method to call (dari Task 3)

  **External References**:
  - Finris CSS: `frontend-public/public/assets/css/style.css` — Available CSS classes for forms and cards

  **WHY Each Reference Matters**:
  - `lookup.blade.php`: Exact field ordering, status color mapping, dan message text yang harus di-reproduce di Next.js
  - `api.ts`: Gunakan method yang sudah dibuat, jangan buat fetch manual
  - `style.css`: Pastikan class names konsisten dengan halaman lain

  **Acceptance Criteria**:

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: Successful ticket lookup with Booked status
    Tool: Playwright (playwright skill)
    Preconditions: Laravel running at localhost:8000, Next.js at localhost:3000, at least 1 booked ticket exists via API
    Steps:
      1. Navigate to http://localhost:3000/antrian/cek
      2. Assert page heading contains "Cek Status Antrian"
      3. Fill input[name="ticket_number"] with existing ticket number (e.g., "A-001")
      4. Fill input[name="service_date"] with valid date (e.g., "2026-03-10")
      5. Click submit button
      6. Wait for loading state to resolve (max 5s)
      7. Assert ticket detail card visible with ticket number "A-001"
      8. Assert status badge shows status_label "Terdaftar (Online)" with blue styling (raw status value is "booked")
      9. Assert message contains "Tiket terdaftar. Silakan datang dan lakukan check-in di loket"
      10. Take screenshot
    Expected Result: Ticket detail card displayed with correct data and "Terdaftar (Online)" status label
    Failure Indicators: Spinner stuck, no result card, wrong status color, missing message
    Evidence: .sisyphus/evidence/task-13-lookup-booked.png

  Scenario: Ticket not found
    Tool: Playwright (playwright skill)
    Preconditions: Laravel running at localhost:8000, Next.js at localhost:3000
    Steps:
      1. Navigate to http://localhost:3000/antrian/cek
      2. Fill input[name="ticket_number"] with "NONEXISTENT-999"
      3. Fill input[name="service_date"] with "2026-01-01"
      4. Click submit button
      5. Wait for loading state to resolve (max 5s)
      6. Assert not-found card visible with red styling
      7. Assert heading contains "Tiket Tidak Ditemukan"
      8. Assert "Periksa Ulang" button visible
      9. Click "Periksa Ulang" button
      10. Assert form is reset/empty
      11. Take screenshot
    Expected Result: Not-found card displayed with message and retry button
    Failure Indicators: Error page, generic error, no not-found card
    Evidence: .sisyphus/evidence/task-13-lookup-notfound.png

  Scenario: Lookup form validation
    Tool: Playwright (playwright skill)
    Preconditions: Next.js at localhost:3000
    Steps:
      1. Navigate to http://localhost:3000/antrian/cek
      2. Leave both fields empty
      3. Click submit button
      4. Assert form validation prevents submission (HTML5 required or custom validation)
      5. Fill only ticket_number, leave date empty
      6. Click submit — assert still blocked
      7. Take screenshot
    Expected Result: Form cannot be submitted with empty fields
    Failure Indicators: Form submits with empty fields, no validation feedback
    Evidence: .sisyphus/evidence/task-13-lookup-validation.png

  Scenario: Lookup mobile responsive
    Tool: Playwright (playwright skill)
    Preconditions: Next.js at localhost:3000
    Steps:
      1. Set viewport to 375x812
      2. Navigate to http://localhost:3000/antrian/cek
      3. Assert form fields visible and usable
      4. Assert page not horizontally scrollable
      5. Take screenshot
    Expected Result: Lookup page renders correctly on mobile viewport
    Failure Indicators: Overlapping elements, horizontal scroll, cut-off fields
    Evidence: .sisyphus/evidence/task-13-lookup-mobile.png
  ```

  **Commit**: YES
  - Message: `feat(frontend): implement ticket lookup page with status display`
  - Files: `frontend-public/src/app/antrian/cek/page.tsx`
  - Pre-commit: `cd frontend-public && pnpm build`

- [x] 14. Halaman Confirmation (`/antrian/konfirmasi/[ticket]`) — Next.js

  **What to do**:
  - Buat halaman di `frontend-public/src/app/antrian/konfirmasi/[ticket]/page.tsx`
  - Halaman ini menggunakan dynamic route — `[ticket]` = ticket_number
  - Gunakan `apiClient.getTicketDetail()` dari `src/lib/api.ts` (Task 3) — endpoint membutuhkan `ticket_number` + `service_date` sebagai query param
  - **PENTING**: Halaman ini butuh `service_date` juga — ambil dari query parameter (`?date=2026-03-10`)
  - URL pattern: `/antrian/konfirmasi/A-001?date=2026-03-10`
  - **Layout ticket card** (referensi dari `confirmation.blade.php`):
    - Header gradient (cyan) dengan ikon tiket dan status badge
    - Nomor tiket besar (prominent, center-aligned)
    - Grid 2 kolom: Layanan + Tanggal
    - Nama pengunjung
    - Posisi antrian (jika `queue_position > 0`) — amber card
    - Panduan/instruksi — cyan info card
    - Footer: tanggal dibuat + nama institusi
  - **Actions**: Tombol "Cetak Tiket" (window.print())
  - **Links**: "Cek Status Antrian" → `/antrian/cek`, "Kembali ke Beranda" → `/`
  - **Print CSS**: Hide action buttons dan links saat print (`@media print`)
  - **Error state**: Jika ticket tidak ditemukan, tampilkan error card dengan link ke lookup
  - **Loading state**: Skeleton/spinner saat fetching ticket data
  - Gunakan Finris CSS classes — cards, badges, typography

  **Must NOT do**:
  - Jangan gunakan Tailwind — gunakan custom CSS classes dari Finris template
  - Jangan expose internal database ID di URL — gunakan `ticket_number`
  - Jangan tambahkan edit/cancel functionality
  - Jangan gunakan SSR/ISR untuk fetch — client-side fetch karena data real-time

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Halaman frontend dengan layout kartu tiket yang detail dan print styling
  - **Skills**: []
    - Tidak ada skill khusus — custom CSS template
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: Template Finris menggunakan custom CSS, bukan Tailwind
    - `livewire-development`: Next.js, bukan Livewire

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 4 (with Tasks 13, 15)
  - **Blocks**: Task 15 (smoke test)
  - **Blocked By**: Task 3 (API client), Task 10 (ticket detail API endpoint)

  **References**:

  **Pattern References** (existing code to follow):
  - `resources/views/pages/public/antrian/confirmation.blade.php` — Source of truth untuk ticket card layout, gradient header, queue position display, print CSS, dan action links
  - `frontend-public/src/app/antrian/page.tsx` — Sister page pattern (same directory level)
  - `frontend-public/src/app/antrian/cek/page.tsx` — Lookup page (Task 13) — status badge color mapping pattern to reuse

  **API/Type References** (contracts to implement against):
  - `frontend-public/src/types/api.ts:QueueTicket` — TypeScript interface for ticket detail (dari Task 3) — includes service, status, queue_position, counter_name
  - `frontend-public/src/lib/api.ts:getTicketDetail()` — API client method (dari Task 3)
  - `config/institution.php` — Institution name used in footer — API menyediakan via `/api/institution`

  **External References**:
  - Finris CSS: `frontend-public/public/assets/css/style.css` — Card, badge, gradient classes

  **WHY Each Reference Matters**:
  - `confirmation.blade.php`: Exact layout reference — ticket card structure, gradient header, queue position box, print styling yang harus di-reproduce
  - `api.ts:getTicketDetail()`: Method yang sudah tersedia — panggil dengan `ticket_number` + `service_date`
  - Task 13 lookup page: Reuse status-to-color mapping logic, jangan duplikasi

  **Acceptance Criteria**:

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: View confirmation for booked ticket
    Tool: Playwright (playwright skill)
    Preconditions: Laravel running at localhost:8000, Next.js at localhost:3000, booked ticket "A-001" exists for date 2026-03-10
    Steps:
      1. Navigate to http://localhost:3000/antrian/konfirmasi/A-001?date=2026-03-10
      2. Wait for loading to complete (max 5s)
      3. Assert ticket number "A-001" displayed prominently (large font)
      4. Assert service name visible
      5. Assert service date shows "10 Mar 2026" or similar formatted date
      6. Assert visitor name visible
      7. Assert status badge shows status_label "Terdaftar (Online)" (raw status value is "booked", color: blue)
      8. Assert gradient header visible (cyan-ish color)
      9. Assert "Cetak Tiket" button visible
      10. Assert "Cek Status Antrian" link points to /antrian/cek
      11. Assert "Kembali ke Beranda" link points to /
      12. Assert institution name visible in footer area
      13. Take screenshot
    Expected Result: Full ticket card rendered with all data fields and correct layout
    Failure Indicators: Missing data fields, broken layout, wrong ticket number, no gradient header
    Evidence: .sisyphus/evidence/task-14-confirmation-booked.png

  Scenario: Confirmation page with queue position
    Tool: Playwright (playwright skill)
    Preconditions: Laravel running, ticket with Waiting status exists (queue_position > 0)
    Steps:
      1. Navigate to confirmation URL for a Waiting ticket
      2. Wait for loading to complete (max 5s)
      3. Assert queue position card visible (amber/orange background)
      4. Assert text contains "antrian ke-" followed by a number
      5. Take screenshot
    Expected Result: Queue position displayed in amber highlight card
    Failure Indicators: No queue position card, position shows 0 for Waiting ticket
    Evidence: .sisyphus/evidence/task-14-confirmation-position.png

  Scenario: Confirmation for nonexistent ticket
    Tool: Playwright (playwright skill)
    Preconditions: Laravel running at localhost:8000, Next.js at localhost:3000
    Steps:
      1. Navigate to http://localhost:3000/antrian/konfirmasi/FAKE-999?date=2026-01-01
      2. Wait for loading to complete (max 5s)
      3. Assert error state displayed (not a crash/500)
      4. Assert link to lookup page (/antrian/cek) visible
      5. Take screenshot
    Expected Result: Graceful error state with navigation options
    Failure Indicators: Unhandled exception, blank page, React error boundary
    Evidence: .sisyphus/evidence/task-14-confirmation-notfound.png

  Scenario: Print ticket (verify print CSS)
    Tool: Playwright (playwright skill)
    Preconditions: Confirmation page loaded with valid ticket
    Steps:
      1. Navigate to confirmation page for valid ticket
      2. Evaluate page for @media print styles: check that action buttons have display:none in print stylesheet
      3. Assert "Cetak Tiket" button has onclick handler
      4. Take screenshot in emulated print mode if possible
    Expected Result: Print-specific CSS hides action buttons, ticket card remains visible
    Failure Indicators: No print CSS rules, buttons visible in print
    Evidence: .sisyphus/evidence/task-14-confirmation-print.png

  Scenario: Confirmation mobile responsive
    Tool: Playwright (playwright skill)
    Preconditions: Next.js at localhost:3000
    Steps:
      1. Set viewport to 375x812
      2. Navigate to confirmation page for valid ticket
      3. Assert ticket card visible and not horizontally overflowing
      4. Assert all data fields readable
      5. Assert action buttons usable
      6. Take screenshot
    Expected Result: Ticket card renders correctly on mobile
    Failure Indicators: Horizontal scroll, overlapping text, cut-off card
    Evidence: .sisyphus/evidence/task-14-confirmation-mobile.png
  ```

  **Commit**: YES
  - Message: `feat(frontend): implement confirmation page with ticket card and print support`
  - Files: `frontend-public/src/app/antrian/konfirmasi/[ticket]/page.tsx`
  - Pre-commit: `cd frontend-public && pnpm build`

- [x] 15. Frontend Build Verification + API Integration Smoke Test

  **What to do**:
  - **Laravel side**:
    - Jalankan `php artisan test --compact` — semua test HARUS pass
    - Jalankan `vendor/bin/pint --dirty --format agent` — pastikan formatting clean
    - Verify semua 6 API endpoints bisa diakses via curl
  - **Next.js side**:
    - Jalankan `cd frontend-public && pnpm build` — harus exit code 0
    - Verify semua 4 route accessible (200 status) saat dev server running
  - **Integration smoke test** (end-to-end flow):
    1. Curl `/api/institution` → verify response shape
    2. Curl `/api/services` → verify returns active services
    3. Curl `/api/services/{slug}` → verify single service detail
    4. POST `/api/queue/booking` dengan data valid → verify 201 + ticket data
    5. Curl `/api/queue/lookup?ticket_number={no}&service_date={date}` → verify ticket found
    6. Curl `/api/queue/ticket/{ticket_number}?service_date={date}` → verify ticket detail
    7. Open Next.js homepage di browser → verify service cards loaded dari API
    8. Navigate ke booking → verify form renders
    9. Navigate ke lookup → verify form renders
    10. Navigate ke confirmation → verify ticket card renders
  - **CORS verification**: OPTIONS request dari localhost:3000 origin ke API → verify headers present

  **Must NOT do**:
  - Jangan fix bugs di task ini — hanya verifikasi dan report
  - Jangan tambahkan test baru — hanya jalankan yang sudah ada
  - Jangan modify kode apapun

  **Recommended Agent Profile**:
  - **Category**: `deep`
    - Reason: Task verifikasi yang membutuhkan pemahaman menyeluruh terhadap seluruh sistem dan kemampuan debugging jika ada masalah
  - **Skills**: [`playwright`]
    - `playwright`: Untuk verifikasi Next.js pages di browser — navigasi, screenshot, dan assertion DOM
  - **Skills Evaluated but Omitted**:
    - `pest-testing`: Hanya menjalankan test, tidak menulis — `php artisan test` cukup via bash

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Sequential (after Wave 4)
  - **Blocks**: Final Verification Wave
  - **Blocked By**: ALL Tasks 1-14

  **References**:

  **Pattern References**:
  - `.sisyphus/plans/nextjs-public-pages.md:Success Criteria` — Verification commands and expected outputs listed at bottom of this plan

  **API/Type References**:
  - `routes/api.php` — All registered API routes (dari Task 1)
  - `frontend-public/src/lib/api.ts` — API client configuration including base URL

  **WHY Each Reference Matters**:
  - Success Criteria section: Contains exact curl commands and expected outputs — follow those commands verbatim
  - `api.php`: Verify all 6 routes exist and respond
  - `api.ts`: Verify NEXT_PUBLIC_API_URL is correctly configured

  **Acceptance Criteria**:

  **QA Scenarios (MANDATORY):**

  ```
  Scenario: All Laravel tests pass
    Tool: Bash
    Preconditions: Laravel application configured with test database
    Steps:
      1. Run: php artisan test --compact
      2. Assert exit code 0
      3. Assert output shows 0 failures
      4. Capture output
    Expected Result: All tests PASS, 0 failures, 0 errors
    Failure Indicators: Any FAIL, error, or non-zero exit code
    Evidence: .sisyphus/evidence/task-15-laravel-tests.txt

  Scenario: Laravel code formatting clean
    Tool: Bash
    Preconditions: Laravel project root
    Steps:
      1. Run: vendor/bin/pint --dirty --format agent
      2. Assert no files needed formatting changes
    Expected Result: Clean output, no files modified
    Failure Indicators: Files reformatted (indicates code style violations)
    Evidence: .sisyphus/evidence/task-15-pint.txt

  Scenario: All 6 API endpoints respond correctly
    Tool: Bash (curl)
    Preconditions: Laravel running at localhost:8000 with seeded data
    Steps:
      1. curl -s http://localhost:8000/api/institution | jq . → assert "name" field exists
      2. curl -s http://localhost:8000/api/services | jq '.data | length' → assert > 0
      3. curl -s http://localhost:8000/api/services/{first_slug} | jq '.data.slug' → assert matches
      4. curl -s -o /dev/null -w "%{http_code}" -X POST http://localhost:8000/api/queue/booking -H "Content-Type: application/json" -d '{"service_id":1,"service_date":"2026-03-10","visitor_name":"Smoke Test"}' → assert 201
      5. curl -s "http://localhost:8000/api/queue/lookup?ticket_number={ticket_no}&service_date=2026-03-10" | jq '.data.ticket_number' → assert matches
      6. curl -s "http://localhost:8000/api/queue/ticket/{ticket_no}?service_date=2026-03-10" | jq '.data.ticket_number' → assert matches
    Expected Result: All 6 endpoints return correct status codes and response shapes
    Failure Indicators: 404, 500, missing fields, CORS errors
    Evidence: .sisyphus/evidence/task-15-api-smoke.txt

  Scenario: CORS headers present
    Tool: Bash (curl)
    Preconditions: Laravel running at localhost:8000
    Steps:
      1. curl -I -X OPTIONS http://localhost:8000/api/services -H "Origin: http://localhost:3000" -H "Access-Control-Request-Method: GET"
      2. Assert response contains Access-Control-Allow-Origin header
      3. Assert header value includes localhost:3000 or *
    Expected Result: CORS preflight returns correct headers
    Failure Indicators: No CORS headers, 405 Method Not Allowed
    Evidence: .sisyphus/evidence/task-15-cors.txt

  Scenario: Next.js production build succeeds
    Tool: Bash
    Preconditions: frontend-public/ directory exists with all pages implemented
    Steps:
      1. cd frontend-public && pnpm build
      2. Assert exit code 0
      3. Assert no TypeScript errors in output
      4. Assert all 4 routes listed in build output
    Expected Result: Build completes successfully with 0 errors
    Failure Indicators: Non-zero exit code, TypeScript errors, missing routes
    Evidence: .sisyphus/evidence/task-15-nextjs-build.txt

  Scenario: All 4 Next.js routes accessible
    Tool: Playwright (playwright skill)
    Preconditions: Next.js dev server running at localhost:3000, Laravel API at localhost:8000
    Steps:
      1. Navigate to http://localhost:3000/ → assert 200 and page content visible
      2. Navigate to http://localhost:3000/antrian → assert 200 and booking form visible
      3. Navigate to http://localhost:3000/antrian/cek → assert 200 and lookup form visible
      4. Navigate to http://localhost:3000/antrian/konfirmasi/A-001?date=2026-03-10 → assert 200 and ticket card visible (or graceful error if ticket doesn't exist)
      5. Take screenshot of each page
    Expected Result: All 4 routes return 200 and render meaningful content
    Failure Indicators: 404, 500, blank page, React error boundary
    Evidence: .sisyphus/evidence/task-15-routes-home.png, task-15-routes-booking.png, task-15-routes-lookup.png, task-15-routes-confirmation.png

  Scenario: End-to-end booking flow
    Tool: Playwright (playwright skill)
    Preconditions: Both servers running, seeded data available
    Steps:
      1. Go to http://localhost:3000/
      2. Click "Daftar Antrian" or booking link
      3. Assert redirected to /antrian
      4. Select a service, fill date, fill name
      5. Submit booking
      6. Assert redirected to confirmation page
      7. Note ticket number from confirmation
      8. Navigate to /antrian/cek
      9. Enter ticket number and date from step 7
      10. Submit lookup
      11. Assert ticket found with Booked status
      12. Take screenshots at each step
    Expected Result: Full booking → confirmation → lookup flow works end-to-end
    Failure Indicators: Any step fails, broken redirect, data mismatch
    Evidence: .sisyphus/evidence/task-15-e2e-booking.png, task-15-e2e-confirmation.png, task-15-e2e-lookup.png
  ```

  **Commit**: NO (verification only, no code changes)

---

## Final Verification Wave (MANDATORY — after ALL implementation tasks)

> 4 review agents run in PARALLEL. ALL must APPROVE. Rejection → fix → re-run.

- [x] F1. **Plan Compliance Audit** — `deep`
  Read the plan end-to-end. For each "Must Have": verify implementation exists (read file, curl endpoint, run command). For each "Must NOT Have": search codebase for forbidden patterns — reject with file:line if found. Check evidence files exist in `.sisyphus/evidence/`. Compare deliverables against plan.
  Output: `Must Have [N/N] | Must NOT Have [N/N] | Tasks [N/N] | VERDICT: APPROVE/REJECT`

- [x] F2. **Code Quality Review** — `unspecified-high`
  Run `vendor/bin/pint --dirty --format agent` + `php artisan test --compact`. Review all changed files for: `DB::` usage, `env()` outside config, empty catches, console.log in prod, commented-out code, unused imports. Check AI slop: excessive comments, over-abstraction, generic names (data/result/item/temp). Verify Next.js build: `cd frontend-public && pnpm build`.
  Output: `Pint [PASS/FAIL] | Tests [N pass/N fail] | Build [PASS/FAIL] | Files [N clean/N issues] | VERDICT`

- [x] F3. **Real Manual QA** — `unspecified-high` (+ `playwright` skill for frontend)
  Start from clean state. Execute EVERY QA scenario from EVERY task — follow exact steps, capture evidence. Test cross-task integration (booking flow end-to-end, lookup after booking). Test edge cases: empty state, invalid input, rapid actions. Save to `.sisyphus/evidence/final-qa/`.
  Output: `Scenarios [N/N pass] | Integration [N/N] | Edge Cases [N tested] | VERDICT`

- [x] F4. **Scope Fidelity Check** — `deep`
  For each task: read "What to do", read actual diff (git log/diff). Verify 1:1 — everything in spec was built (no missing), nothing beyond spec was built (no creep). Check "Must NOT do" compliance. Detect cross-task contamination: Task N touching Task M's files. Flag unaccounted changes.
  Output: `Tasks [N/N compliant] | Contamination [CLEAN/N issues] | Unaccounted [CLEAN/N files] | VERDICT`

---

## Commit Strategy

- **1**: `chore(api): register public api routes with CORS and throttle config` — bootstrap/app.php, routes/api.php
- **2**: `feat(api): add API resource classes for services and tickets` — app/Http/Resources/*.php
- **3**: `feat(frontend): add TypeScript types and API client module` — frontend-public/src/lib/*, frontend-public/src/types/*
- **4**: `test(api): verify factories and test infrastructure for API testing` — tests/Feature/Api/*
- **5**: `test(api): add institution endpoint test` + `feat(api): implement institution endpoint` — tests/Feature/Api/InstitutionTest.php, app/Http/Controllers/Api/*
- **6**: `test(api): add service endpoints tests` + `feat(api): implement service endpoints` — tests/Feature/Api/ServiceTest.php, app/Http/Controllers/Api/*
- **7**: `feat(api): add API booking form request with business rules` — app/Http/Requests/Api/StoreBookingRequest.php
- **8**: `test(api): add booking endpoint tests` + `feat(api): implement booking endpoint` — tests/Feature/Api/BookingTest.php, app/Http/Controllers/Api/*
- **9**: `test(api): add lookup endpoint tests` + `feat(api): implement lookup endpoint` — tests/Feature/Api/LookupTest.php
- **10**: `test(api): add ticket detail tests` + `feat(api): implement ticket detail endpoint` — tests/Feature/Api/TicketTest.php
- **11**: `feat(frontend): implement homepage with service catalog` — frontend-public/src/app/page.tsx, frontend-public/src/sections/*
- **12**: `feat(frontend): implement booking wizard with 3-step flow` — frontend-public/src/app/antrian/*
- **13**: `feat(frontend): implement ticket lookup page` — frontend-public/src/app/antrian/cek/*
- **14**: `feat(frontend): implement confirmation page` — frontend-public/src/app/antrian/konfirmasi/*
- **15**: `chore(verify): frontend build and API integration smoke test` — N/A

---

## Success Criteria

### Verification Commands
```bash
# Laravel API Tests
php artisan test --compact                    # Expected: ALL PASS, 0 failures
vendor/bin/pint --dirty --format agent        # Expected: no formatting issues

# API Smoke Tests
curl -s http://localhost:8000/api/institution | jq .         # Expected: { "name": "...", "operating_hours": "..." }
curl -s http://localhost:8000/api/services | jq '.data | length'  # Expected: > 0
curl -s http://localhost:8000/api/services/[slug] | jq .     # Expected: service detail JSON
curl -X POST http://localhost:8000/api/queue/booking \
  -H "Content-Type: application/json" \
  -d '{"service_id":1,"service_date":"2026-03-10","visitor_name":"Test"}' | jq .  # Expected: 201 with ticket data

# CORS Check
curl -I -X OPTIONS http://localhost:8000/api/services \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET"   # Expected: 200 with CORS headers

# Next.js Build
cd frontend-public && pnpm build              # Expected: exit code 0

# Next.js Routes (with dev server running)
curl -s -o /dev/null -w "%{http_code}" http://localhost:3000/           # Expected: 200
curl -s -o /dev/null -w "%{http_code}" http://localhost:3000/antrian    # Expected: 200
curl -s -o /dev/null -w "%{http_code}" http://localhost:3000/antrian/cek # Expected: 200
```

### Final Checklist
- [ ] All "Must Have" present
- [ ] All "Must NOT Have" absent
- [ ] All Pest tests pass
- [ ] Frontend builds without errors
- [ ] All 4 Next.js routes accessible
- [ ] CORS configured correctly
- [ ] Rate limiting active on booking endpoint
