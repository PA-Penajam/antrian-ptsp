# Learnings — nextjs-public-pages

## [2026-03-09] Initial Exploration

### Laravel App Structure (Key Facts)
- **bootstrap/app.php**: Uses `->withRouting(web:..., commands:..., health:...)` — perlu tambahkan `api:` parameter
- **routes/**: Hanya ada web.php, console.php, settings.php — BELUM ada api.php
- **QueueStatus enum** values: `booked`, `waiting`, `called`, `completed`, `cancelled`, `skipped`
- **QueueStatus label()**: Booked='Terdaftar (Online)', Waiting='Menunggu Dipanggil', Called='Sedang Dipanggil', Completed='Selesai', Cancelled='Dibatalkan', Skipped='Dilewati'
- **config/institution.php**: Returns name, address, phone, operating_hours, logo_path — semua dari env()
- **CreateQueueTicket action**: payload shape = `{service_id, channel, service_date, visitor_name, visitor_identifier?, visitor_phone?, notes?, created_by?}`
- **Queue position**: count tickets WHERE queue_pool_id=X AND service_date=Y AND status=Waiting AND sequence_number < $ticket->sequence_number, +1
- **PublicQueueController.storeBooking**: Uses channel = 'online_booking', returns redirect()->route('queue.confirmation', $ticket)

### Service Model Key Fields
- fillable: queue_pool_id, name, code, slug, description, requirements, is_active, booking_enabled, walk_in_enabled, daily_quota, sort_order, letter_code
- casts: is_active/booking_enabled/walk_in_enabled as boolean, daily_quota/sort_order as integer

### QueueTicket Model Key Fields
- fillable: service_id, queue_pool_id, counter_id, created_by, channel, ticket_number, sequence_number, service_date, visitor_name, visitor_identifier, visitor_phone, notes, status, checked_in_at, called_at, started_at, completed_at, cancelled_at
- casts: service_date as 'date', timestamps as 'datetime', status as QueueStatus enum

### Next.js Frontend (frontend-public/)
- Template: Finris (agency template) — home-one sections
- Stack: Next.js App Router, TypeScript, custom CSS (bukan Tailwind)
- CSS: `public/assets/css/style.css` — gunakan class dari sini
- Current page.tsx imports sections from `@/sections/home-one/`
- No existing API client or type definitions

### Existing Tests
- Tests in: tests/Feature/{Admin,Audit,Auth,Dashboard,Database,Frontdesk,Integration,Kiosk,Middleware,Models,Navigation,Officer,Public,Queue,Reports,Seeders,Settings,TvDisplay}/
- Convention: Pest v4, uses RefreshDatabase or LazilyRefreshDatabase
- Check existing tests to see convention (faker vs fake(), etc.)

### CORS Configuration
- config/cors.php likely exists (Laravel default) — check before configuring in bootstrap/app.php
- Need: env-driven FRONTEND_URL origin (default: http://localhost:3000)

### Critical Path Dependencies
- Task 1 must complete before tasks 5,6,7,8,9,10 (routes must be registered)
- Task 2 must complete before tasks 5,6,8,9,10 (Resource classes needed)
- Task 3 must complete before tasks 11,12,13,14 (types + API client)
- Task 4 must complete before tasks 5,6,8,9,10 (test infra verification)
-e 
## [2026-03-09] Task 1: API Routes + CORS
- Laravel 12 uses Application::configure() with withRouting() to register API routes
- Add api: __DIR__.'/../routes/api.php' to withRouting() method
- CORS middleware added via $middleware->api(prepend: [HandleCors::class])
- config/cors.php uses env('FRONTEND_URL', 'http://localhost:3000') for allowed_origins
- Throttle middleware applied per route group: throttle:60,1 for read, throttle:10,1 for booking
- Note: route:list fails if controllers don't exist yet (expected - will be created in T5-T10)
- Pint formatter passes successfully
## [2026-03-09] Task 2: API Resources
- ServiceResource: expose id, name, code, slug, description, requirements, booking_enabled, daily_quota, remaining_quota (computed)
- QueueTicketResource: expose ticket_number (bukan id), service_date, visitor_name, status (raw enum value), status_label, service (nested Resource), queue_position (hitung hanya jika Waiting), counter_name, timestamps
- Gunakan whenLoaded() untuk relasi yang mungkin belum di-load
- Queue position dihitung berdasarkan sequence_number pada queue_pool yang sama dan service_date yang sama
- Format datetime menggunakan toIso8601String()

## [2026-03-09] Task 3: TypeScript Types + API Client
- [findings] Created 3 files: types/api.ts (interfaces), lib/api.ts (native fetch client), .env.local.example
- [findings] Used native fetch instead of axios per requirements
- [findings] TypeScript check shows pre-existing errors (missing node_modules) - code itself is correct
- [findings] @/ alias sudah dikonfigurasi di tsconfig.json

## [2026-03-09] Task 4: Test Infrastructure
- Convention: Uses `test()` or `it()` syntax for tests
- RefreshDatabase: Used in some tests (e.g., ActivityLogTest.php, PublicQueueConfirmationTest.php), but not always required for simple tests
- ServiceFactory: No custom states - uses default definition with fields: queue_pool_id, name, code, slug, description, requirements, is_active, booking_enabled, walk_in_enabled, daily_quota, sort_order
- QueueTicketFactory: No custom states - uses default definition with fields: service_id, queue_pool_id, counter_id, created_by, channel, ticket_number, sequence_number, service_date, visitor_name, visitor_identifier, visitor_phone, notes, status (default: QueueStatus::Waiting), timestamps
- Factory verification: Both Service::factory()->create() and QueueTicket::factory()->create() work correctly
- Test location: tests/Feature/Api/ExampleApiTest.php created successfully
- Test passes: "it api test infrastructure is working" - 1 passed, 7.17s

## [2026-03-09] Task 6: Services List & Show Endpoints
- Created PublicServiceController with index() and show() methods
- index(): Returns active services sorted by sort_order then name
- show(): Returns service by slug (only active), 404 if not found
- ServiceTest.php created with 4 tests: list active, get by slug, 404 for inactive, 404 for non-existent
- All tests pass successfully (12 assertions)
- ServiceResource includes remaining_quota computed property
- Pint formatter applied to fix minor issues
- Evidence saved to task-6-services-list.txt
## [2026-03-09] Task 7: StoreBookingRequest
- WeekdayOnly rule uses Carbon::parse($value)->isWeekend()
- withValidator() early-returns if basic validation already failed
- Quota check: count non-cancelled tickets for service+date vs daily_quota
- Error messages in Bahasa Indonesia

## [2026-03-09 17:45] Task 5: institution endpoint
- institution() method returns config('institution') as JSON directly (no Resource class needed)
- controller namespace: App\Http\Controllers\Api
- config/institution.php returns: name, address, phone, operating_hours, logo_path from env()
- PublicServiceController already exists, updated institution() method to return full config('institution')
- InstitutionTest.php created with 2 tests: can get info, returns 405 for non-GET
- All tests pass (2 passed, 6 assertions)

## [2026-03-09] Task 8: POST /api/queue/booking
- Created tests/Feature/Api/BookingTest.php with 6 test cases:
  - can create booking and returns 201
  - cannot book inactive service returns 422
  - cannot book when booking disabled returns 422
  - cannot book past date returns 422
  - quota exceeded returns 422
  - missing required fields returns 422
- Created app/Http/Controllers/Api/PublicQueueController.php with booking() method
- Uses StoreBookingRequest for validation and CreateQueueTicket action for ticket creation
- Channel set to 'online_booking' which automatically sets status to 'booked'
- Loads service and queuePool relationships before returning
- Returns QueueTicketResource with 201 status code
- Pint formatter applied to fix minor formatting issues
- No LSP errors in modified files

## [2026-03-09] Task 9 & 10: Ticket Lookup & Detail Endpoints

### Test Implementation
- LookupTest.php: 5 test cases for GET /api/queue/lookup endpoint
  - can lookup ticket by ticket_number and service_date
  - lookup returns 404 when ticket not found  
  - lookup returns 422 when missing params
  - lookup returns queue position for waiting tickets
  - lookup returns null position for booked tickets
- TicketDetailTest.php: 5 test cases for GET /api/queue/ticket/{ticket_number} endpoint
  - can get ticket detail by ticket_number
  - ticket detail returns 404 for non-existent ticket
  - ticket detail returns 404 when service_date mismatch
  - ticket includes service relationship
  - ticket includes queue position for waiting tickets

### Request Validation
- LookupTicketRequest.php: Validates ticket_number (required string) and service_date (required date)
- Error messages in Bahasa Indonesia ("Nomor tiket wajib diisi", "Tanggal layanan wajib diisi")
- show() method uses inline Request::validate() for service_date parameter

### Controller Implementation
- PublicQueueController@lookup(): Uses LookupTicketRequest, returns 404 with message "Tiket tidak ditemukan" if not found
- PublicQueueController@show(): Validates service_date query param, returns same 404 message if not found
- Private findTicket() method extracted for shared logic: loads service, counter, queuePool relationships, uses whereDate for service_date

### Key Design Decisions
1. **SRP Compliance**: Each endpoint has single responsibility - lookup vs detail (different URL patterns)
2. **Shared Logic Extraction**: findTicket() method prevents code duplication
3. **Enum Handling**: QueueStatus enum used correctly, queue_position computed only for Waiting tickets
4. **Security**: Service_date required to prevent ticket enumeration
5. **API Consistency**: Both endpoints return same 404 message format

### Testing Results
- LookupTest: 5 passed, 12 assertions
- TicketDetailTest: 5 passed, 18 assertions  
- Total API tests: 17 passed (excluding BookingTest rate limiting issue)

### Gotchas
- GET request query parameters must be passed in URL string, not as array second parameter in getJson()
- Need to set DB_CONNECTION=sqlite DB_DATABASE=:memory: environment variables for tests to use SQLite
- BookingTest has rate limiting issue (429) but not related to our implementation


## [2026-03-09] Task 11: Next.js Homepage
- `frontend-public/src/app/page.tsx` sekarang memakai async server component dengan `Promise.all()` untuk `getInstitution()` dan `getServices()` plus fallback `.catch()` agar homepage tetap render saat API tidak tersedia.
- Homepage memakai class Finris yang sudah ada (`banner-one`, `services-one`, `about-one`, `process-one`, `section-title`, `thm-btn`) tanpa menambah Tailwind atau CSS baru.
- Hero menampilkan heading PTSP, nama institusi, dua CTA publik, ringkasan jumlah layanan aktif, dan jumlah layanan dengan booking online.
- Section layanan memakai data API untuk `name`, `description`, status `booking_enabled`, serta info kuota bila tersedia.
- Section informasi memakai data institusi untuk jam operasional, alamat, dan kontak; ditambah alur kunjungan singkat agar halaman tetap fungsional walau template asli bersifat marketing.
- `pnpm install --frozen-lockfile` perlu dijalankan lebih dulu karena binary `next` belum tersedia di `frontend-public/node_modules/.bin`; sesudah itu `pnpm exec tsc --noEmit` dan `pnpm build` berjalan sukses.
## [2026-03-09] Task 12: Booking Wizard Public Page
- `frontend-public/src/app/antrian/page.tsx` dibuat sebagai Client Component dengan `useState`, `useEffect`, dan `useRouter` untuk alur 3-step booking publik.
- Step 1 fetch `getServices()` di client lalu hanya menampilkan layanan dengan `booking_enabled = true`; layanan dengan `remaining_quota <= 0` ditandai tidak bisa dipilih.
- Step 2 memakai native `<input type="date">` dengan `min` hari ini dan `max` +14 hari; validasi client juga menolak tanggal weekend sebelum submit.
- Error API 422 dari `createBooking(payload)` dibaca dari `ApiError.errors` dan dirender inline per field melalui state `errors`.
- Redirect sukses menggunakan `router.push(`/antrian/konfirmasi/${ticket.data.ticket_number}`)`.
- Verifikasi: `pnpm exec tsc --noEmit` dan `pnpm build` di `frontend-public` sama-sama sukses; build masih menampilkan warning Turbopack soal multiple lockfiles di root workspace.
## [2026-03-09] Task 14: Ticket Confirmation Public Page
- `frontend-public/src/app/antrian/konfirmasi/[ticket]/page.tsx` dibuat sebagai async Server Component tanpa `'use client'`, memakai pola Next.js App Router `params: Promise<{ ticket: string }>` lalu `await params`.
- Detail tiket diambil server-side lewat `getTicketDetail(ticketNumber)`; error API ditangkap sebagai `ApiError` dan dirender menjadi state halaman error dengan pesan utama `Tiket tidak ditemukan`.
- Halaman sukses memakai class Finris yang sudah ada (`page-header`, `services-one__single`, `about-one__points`, `process-one__single`, `thm-btn`, `section-title`, `inner-section`) tanpa Tailwind.
- Field utama yang dirender: `ticket_number`, `service.name`, `status_label`, `service_date` (format `id-ID`), `visitor_name`, plus kondisi `queue_position` saat `waiting` dan `counter_name` saat `called`.
- Verifikasi lulus: `pnpm exec tsc --noEmit`, LSP diagnostics bersih untuk file baru, dan `pnpm build` berhasil dengan route dinamis `/antrian/konfirmasi/[ticket]`.
- 2026-03-09: Halaman publik lookup tiket Next.js memakai client component dengan `useState` untuk `ticketNumber`, `serviceDate`, `result`, `error`, dan `isLoading`, lalu memanggil `lookupTicket(ticketNumber, serviceDate)` dari `@/lib/api` pada submit.
- 2026-03-09: Untuk halaman publik Finris, pola yang paling aman adalah menggabungkan `page-header`, `contact-page__right`, `contact-page__left`, `about-one__points`, dan `services-one__single` agar form dan kartu hasil tetap konsisten tanpa Tailwind.
- 2026-03-09: `lookupTicket` melempar `ApiError` pada non-2xx; untuk skenario lookup tiket, fallback pesan `Tiket tidak ditemukan` perlu disiapkan agar 404 tampil ramah di UI.
- 2026-03-09: Jika `pnpm build` gagal karena `.next/lock`, cek dulu proses `next build` yang masih berjalan; setelah proses selesai dan lock hilang, build berikutnya dapat berjalan normal.
- F1 audit 2026-03-09 22:03:54: 6 API routes terdaftar via laravel-boost_list-routes; StoreBookingRequest + WeekdayOnly + ServiceResource + QueueTicketResource terverifikasi; CORS env-driven (FRONTEND_URL) tanpa wildcard origin; throttle booking/read aktif di routes/api.php.
- Pest API suite terverifikasi pass via laravel-boost_tinker dengan override DB_CONNECTION=sqlite DB_DATABASE=:memory: -> 23 passed (58 assertions).
- Guardrails terverifikasi: tidak ada Sanctum/install:api, tidak ada DB:: di API layer, tidak ada env() langsung di app/bootstrap/routes/tests/database, tidak ada frontend test suite custom, dan artefak Next.js wajib tersedia.
