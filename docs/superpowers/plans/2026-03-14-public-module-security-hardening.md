# Public Module Security Hardening — Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix all security findings from the public module audit — hardcoded passwords, PII exposure, missing rate limiting, inconsistent validation, and config weaknesses.

**Architecture:** Each task is an isolated security fix with its own test(s) and commit. Tasks are ordered by severity (P0 first). All fixes follow existing Laravel patterns in the codebase: Form Requests for validation, middleware aliases in `bootstrap/app.php`, Pest for tests.

**Tech Stack:** Laravel 12, Pest 4, PHP 8.3

---

## Chunk 1: P0 — Critical & High Severity Fixes

### Task 1: Separate Kiosk and TV Display Passwords + Hash Comparison

**Files:**
- Modify: `config/kiosk.php`
- Modify: `app/Http/Controllers/KioskController.php:17-35`
- Modify: `app/Http/Controllers/TvDisplayController.php:17-35`
- Test: `tests/Feature/Kiosk/KioskAuthTest.php`
- Create: `tests/Feature/TvDisplay/TvDisplayAuthLoginTest.php`

- [ ] **Step 1: Write failing tests for hashed password comparison**

In `tests/Feature/Kiosk/KioskAuthTest.php`, add a new test:

```php
it('logs in with hashed kiosk password', function () {
    config(['kiosk.kiosk_password' => bcrypt('test-kiosk-pass')]);

    $response = post(route('kiosk.authenticate'), [
        'password' => 'test-kiosk-pass',
    ]);

    $response->assertRedirect(route('kiosk.index'))
        ->assertSessionHas('kiosk_authenticated', true);
});
```

In `tests/Feature/TvDisplay/TvDisplayAuthLoginTest.php`, create:

```php
<?php

use function Pest\Laravel\post;

it('logs in with hashed tv-display password', function () {
    config(['kiosk.tv_display_password' => bcrypt('test-tv-pass')]);

    $response = post(route('tv-display.authenticate'), [
        'password' => 'test-tv-pass',
    ]);

    $response->assertRedirect(route('tv-display.index'))
        ->assertSessionHas('tv_display_authenticated', true);
});

it('rejects wrong tv-display password', function () {
    config(['kiosk.tv_display_password' => bcrypt('correct-pass')]);

    $response = post(route('tv-display.authenticate'), [
        'password' => 'wrong-pass',
    ]);

    $response->assertSessionHasErrors(['password']);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter="hashed kiosk password|hashed tv-display password|rejects wrong tv-display"`
Expected: FAIL — config keys don't exist yet, plaintext comparison still used.

- [ ] **Step 3: Update config to separate passwords**

In `config/kiosk.php`, replace entire file:

```php
<?php

return [
    'kiosk_password' => env('KIOSK_PASSWORD'),
    'tv_display_password' => env('TV_DISPLAY_PASSWORD'),
    'session_lifetime' => env('MODULE_SESSION_LIFETIME', 1440),
];
```

- [ ] **Step 4: Update KioskController to use Hash::check**

In `app/Http/Controllers/KioskController.php`, replace the `login` method:

```php
public function login(Request $request): RedirectResponse
{
    $request->validate([
        'password' => ['required', 'string'],
    ]);

    $hashedPassword = (string) config('kiosk.kiosk_password');

    if (! $hashedPassword || ! Hash::check($request->input('password'), $hashedPassword)) {
        return back()->withErrors([
            'password' => 'Error: Password yang dimasukkan salah.',
        ]);
    }

    session([
        ModuleSession::KioskAuthenticated->value => true,
        ModuleSession::KioskAuthenticatedAt->value => now()->timestamp,
    ]);

    return redirect()->route('kiosk.index');
}
```

Add `use Illuminate\Support\Facades\Hash;` to imports.

- [ ] **Step 5: Update TvDisplayController to use Hash::check with separate password**

In `app/Http/Controllers/TvDisplayController.php`, replace the `login` method:

```php
public function login(Request $request): RedirectResponse
{
    $request->validate([
        'password' => ['required', 'string'],
    ]);

    $hashedPassword = (string) config('kiosk.tv_display_password');

    if (! $hashedPassword || ! Hash::check($request->input('password'), $hashedPassword)) {
        return back()->withErrors([
            'password' => 'Password salah.',
        ]);
    }

    session([
        ModuleSession::TvDisplayAuthenticated->value => true,
        ModuleSession::TvDisplayAuthenticatedAt->value => now()->timestamp,
    ]);

    return redirect()->route('tv-display.index');
}
```

Add `use Illuminate\Support\Facades\Hash;` to imports.

- [ ] **Step 6: Fix existing KioskAuthTest to use new config key**

Update the existing test `it('logs in with correct password')` in `tests/Feature/Kiosk/KioskAuthTest.php`:

```php
it('logs in with correct password', function () {
    config(['kiosk.kiosk_password' => bcrypt('test-pass')]);

    $response = post(route('kiosk.authenticate'), [
        'password' => 'test-pass',
    ]);

    $response->assertRedirect(route('kiosk.index'))
        ->assertSessionHas('kiosk_authenticated', true);

    expect(session()->has('kiosk_authenticated_at'))->toBeTrue();
});
```

Also update `it('rejects wrong kiosk password')`:

```php
it('rejects wrong kiosk password', function () {
    config(['kiosk.kiosk_password' => bcrypt('correct-pass')]);

    $response = from(route('kiosk.login'))->post(route('kiosk.authenticate'), [
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('kiosk.login'))
        ->assertSessionHasErrors(['password']);

    expect(session()->has('kiosk_authenticated'))->toBeFalse();
});
```

- [ ] **Step 7: Run all auth tests to verify they pass**

Run: `php artisan test --compact --filter="KioskAuth|TvDisplayAuthLogin"`
Expected: ALL PASS

- [ ] **Step 8: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add config/kiosk.php app/Http/Controllers/KioskController.php app/Http/Controllers/TvDisplayController.php tests/Feature/Kiosk/KioskAuthTest.php tests/Feature/TvDisplay/TvDisplayAuthLoginTest.php
git commit -m "security: separate kiosk/tv-display passwords and use Hash::check

Removes hardcoded default password. Each module now has its own
env variable (KIOSK_PASSWORD, TV_DISPLAY_PASSWORD) and passwords
are compared using bcrypt Hash::check instead of plaintext.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 2: Add Rate Limiting to Kiosk & TV Display Login

**Files:**
- Modify: `routes/web.php:85-100`
- Create: `tests/Feature/Security/LoginRateLimitingTest.php`

- [ ] **Step 1: Write failing test for login rate limiting**

Create `tests/Feature/Security/LoginRateLimitingTest.php`:

```php
<?php

use function Pest\Laravel\post;

it('rate limits kiosk login after 5 attempts', function () {
    config(['kiosk.kiosk_password' => bcrypt('correct')]);

    for ($i = 0; $i < 5; $i++) {
        post(route('kiosk.authenticate'), ['password' => 'wrong']);
    }

    $response = post(route('kiosk.authenticate'), ['password' => 'wrong']);

    $response->assertStatus(429);
});

it('rate limits tv-display login after 5 attempts', function () {
    config(['kiosk.tv_display_password' => bcrypt('correct')]);

    for ($i = 0; $i < 5; $i++) {
        post(route('tv-display.authenticate'), ['password' => 'wrong']);
    }

    $response = post(route('tv-display.authenticate'), ['password' => 'wrong']);

    $response->assertStatus(429);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter="rate limits"`
Expected: FAIL — no throttle middleware yet, status 302 instead of 429.

- [ ] **Step 3: Add throttle middleware to login routes**

In `routes/web.php`, wrap the login POST routes with throttle. Replace lines 85-100:

```php
// Kiosk routes (no auth - uses own password system)
Route::get('/kiosk/login', [KioskController::class, 'showLogin'])->name('kiosk.login');
Route::post('/kiosk/login', [KioskController::class, 'login'])->name('kiosk.authenticate')->middleware('throttle:5,1');
Route::post('/kiosk/logout', [KioskController::class, 'logout'])->name('kiosk.logout');
Route::middleware('module.password:kiosk')->group(function () {
    Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
});

// TV Display routes (no auth - uses own password system)
Route::get('/tv-display/login', [TvDisplayController::class, 'showLogin'])->name('tv-display.login');
Route::post('/tv-display/login', [TvDisplayController::class, 'login'])->name('tv-display.authenticate')->middleware('throttle:5,1');
Route::post('/tv-display/logout', [TvDisplayController::class, 'logout'])->name('tv-display.logout');
Route::middleware('module.password:tv-display')->group(function () {
    Route::get('/tv-display', [TvDisplayController::class, 'index'])->name('tv-display.index');
    Route::get('/tv-display/tts/announcement', [TvDisplayTtsController::class, 'announcement'])->name('tv-display.tts.announcement');
    Route::get('/tv-display/tts/audio/{cacheKey}', [TvDisplayTtsController::class, 'audio'])->name('tv-display.tts.audio');
});
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter="rate limits"`
Expected: PASS

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/web.php tests/Feature/Security/LoginRateLimitingTest.php
git commit -m "security: add rate limiting to kiosk and tv-display login routes

Limits login attempts to 5 per minute per IP to prevent brute force
attacks on shared module passwords.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 3: Remove Ticket Enumeration Endpoint & Create Public-Safe Resource

**Files:**
- Modify: `routes/api.php:13`
- Modify: `app/Http/Controllers/Api/PublicQueueController.php:65-77`
- Create: `app/Http/Resources/PublicQueueTicketResource.php`
- Modify: `app/Http/Resources/QueueTicketResource.php` (remove PII for unauthenticated)
- Create: `tests/Feature/Security/TicketEnumerationTest.php`

- [ ] **Step 1: Write failing test that ticket/{ticketNumber} is removed**

Create `tests/Feature/Security/TicketEnumerationTest.php`:

```php
<?php

use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;

it('does not expose ticket by ticket number without service_date', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create(['is_active' => true]);
    $ticket = QueueTicket::factory()->for($service)->create();

    $response = $this->getJson("/api/queue/ticket/{$ticket->ticket_number}");

    $response->assertNotFound();
});

it('does not expose visitor PII in public ticket lookup', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create(['is_active' => true]);
    $ticket = QueueTicket::factory()->for($service)->create([
        'visitor_name' => 'Nama Rahasia',
        'visitor_identifier' => '1234567890123456',
        'visitor_phone' => '081234567890',
    ]);

    $response = $this->getJson('/api/queue/lookup?' . http_build_query([
        'ticket_number' => $ticket->ticket_number,
        'service_date' => $ticket->service_date->format('Y-m-d'),
    ]));

    $response->assertOk()
        ->assertJsonMissing(['visitor_identifier' => '1234567890123456'])
        ->assertJsonMissing(['visitor_phone' => '081234567890']);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter="TicketEnumeration"`
Expected: FAIL — route still exists, PII still exposed.

- [ ] **Step 3: Create PublicQueueTicketResource without PII**

Run: `php artisan make:class App/Http/Resources/PublicQueueTicketResource --no-interaction`

Then replace the file content:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicQueueTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => encrypt($this->id),
            'ticket_number' => $this->ticket_number,
            'service_date' => $this->service_date?->format('Y-m-d'),
            'visitor_name' => $this->maskedVisitorName(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'service' => $this->whenLoaded('service', fn () => new ServiceResource($this->service)),
            'queue_position' => $this->resource->getQueuePosition(),
            'counter_name' => $this->whenLoaded('counter', fn () => $this->counter?->name),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'called_at' => $this->called_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }

    private function maskedVisitorName(): string
    {
        $name = (string) $this->visitor_name;
        if (mb_strlen($name) <= 3) {
            return str_repeat('*', mb_strlen($name));
        }

        return mb_substr($name, 0, 2) . str_repeat('*', mb_strlen($name) - 2);
    }
}
```

- [ ] **Step 4: Remove `show` route and update `lookup`/`showById` to use PublicQueueTicketResource**

In `routes/api.php`, remove line 13 (`queue/ticket/{ticketNumber}` route):

```php
Route::middleware('throttle:60,1')->group(function () {
    Route::get('institution', [PublicServiceController::class, 'institution'])->name('api.institution');
    Route::get('services', [PublicServiceController::class, 'index'])->name('api.services.index');
    Route::get('services/{slug}', [PublicServiceController::class, 'show'])->name('api.services.show');
    Route::get('queue/lookup', [PublicQueueController::class, 'lookup'])->name('api.queue.lookup');
    Route::get('queue/ticket-by-id/{encryptedId}', [PublicQueueController::class, 'showById'])->name('api.queue.showById');
});
```

In `app/Http/Controllers/Api/PublicQueueController.php`:
- Remove the `show` method entirely
- Update `lookup` and `showById` to use `PublicQueueTicketResource`
- Update `booking` to keep using `QueueTicketResource` (creator sees their own data)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Actions\Queue\CreateQueueTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LookupTicketRequest;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Http\Resources\PublicQueueTicketResource;
use App\Http\Resources\QueueTicketResource;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;

class PublicQueueController extends Controller
{
    public function booking(StoreBookingRequest $request, CreateQueueTicket $action): JsonResponse
    {
        $validated = $request->validated();

        $ticket = $action->handle([
            'service_id' => (int) $validated['service_id'],
            'channel' => 'online_booking',
            'service_date' => $validated['service_date'],
            'visitor_name' => $validated['visitor_name'],
            'visitor_identifier' => $validated['visitor_identifier'] ?? null,
            'visitor_phone' => $validated['visitor_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => null,
        ]);

        $ticket->load(['service', 'queuePool']);

        return QueueTicketResource::make($ticket)->response()->setStatusCode(201);
    }

    public function lookup(LookupTicketRequest $request): JsonResponse
    {
        $ticket = $this->findTicket($request->ticket_number, $request->service_date);

        if (! $ticket) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        return PublicQueueTicketResource::make($ticket)->response();
    }

    public function showById(string $encryptedId): JsonResponse
    {
        try {
            $id = decrypt($encryptedId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        $ticket = QueueTicket::query()
            ->with(['service', 'counter', 'queuePool'])
            ->find($id);

        if (! $ticket) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        return PublicQueueTicketResource::make($ticket)->response();
    }

    private function findTicket(string $ticketNumber, string $serviceDate): ?QueueTicket
    {
        return QueueTicket::query()
            ->with(['service', 'counter', 'queuePool'])
            ->where('ticket_number', $ticketNumber)
            ->whereDate('service_date', $serviceDate)
            ->first();
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --compact --filter="TicketEnumeration"`
Expected: PASS

- [ ] **Step 6: Run full test suite to check for regressions**

Run: `php artisan test --compact`
Expected: ALL PASS (any tests referencing the removed `api.queue.show` route will need updating)

- [ ] **Step 7: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/api.php app/Http/Controllers/Api/PublicQueueController.php app/Http/Resources/PublicQueueTicketResource.php tests/Feature/Security/TicketEnumerationTest.php
git commit -m "security: remove ticket enumeration endpoint and mask PII in public API

Removes GET /api/queue/ticket/{ticketNumber} which allowed unauthenticated
enumeration of visitor data. Creates PublicQueueTicketResource that masks
visitor names and omits sensitive fields (identifier, phone).

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Chunk 2: P1 — High/Medium Severity Fixes

### Task 4: Add Rate Limiting to Public Web Routes

**Files:**
- Modify: `routes/web.php:17-21`
- Create: `tests/Feature/Security/PublicWebRateLimitingTest.php`

- [ ] **Step 1: Write failing test for web booking rate limit**

Create `tests/Feature/Security/PublicWebRateLimitingTest.php`:

```php
<?php

use App\Models\QueuePool;
use App\Models\Service;

it('rate limits public booking submissions', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create([
        'is_active' => true,
        'booking_enabled' => true,
    ]);

    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

    for ($i = 0; $i < 10; $i++) {
        $this->post('/antrian', [
            'service_id' => $service->id,
            'service_date' => now()->addDay()->toDateString(),
            'visitor_name' => "Visitor {$i}",
            'visitor_identifier' => "ID{$i}",
            'visitor_phone' => "08123456789{$i}",
        ]);
    }

    $response = $this->post('/antrian', [
        'service_id' => $service->id,
        'service_date' => now()->addDay()->toDateString(),
        'visitor_name' => 'One More',
        'visitor_identifier' => 'IDMORE',
        'visitor_phone' => '08199999999',
    ]);

    $response->assertStatus(429);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter="rate limits public booking"`
Expected: FAIL — no throttle on web routes.

- [ ] **Step 3: Add throttle middleware to public web routes**

In `routes/web.php`, replace lines 17-21:

```php
Route::get('/', [PublicQueueController::class, 'index'])->name('home');
Route::get('/antrian', [PublicQueueController::class, 'booking']);
Route::post('/antrian', [PublicQueueController::class, 'storeBooking'])->middleware('throttle:10,1');
Route::get('/antrian/cek', [PublicQueueController::class, 'lookup'])->middleware('throttle:30,1');
Route::get('/antrian/konfirmasi/{ticket}', [PublicQueueController::class, 'confirmation'])->name('queue.confirmation')->middleware('signed');
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter="rate limits public booking"`
Expected: PASS

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/web.php tests/Feature/Security/PublicWebRateLimitingTest.php
git commit -m "security: add rate limiting to public web booking and lookup routes

POST /antrian limited to 10/min, GET /antrian/cek limited to 30/min
to prevent spam ticket creation and brute force ticket lookups.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 5: Fix Inconsistent Validation on Web Booking Request

**Files:**
- Modify: `app/Http/Requests/StorePublicQueueBookingRequest.php`
- Create: `tests/Feature/Security/BookingValidationTest.php`

- [ ] **Step 1: Write failing tests for missing validation rules**

Create `tests/Feature/Security/BookingValidationTest.php`:

```php
<?php

use App\Models\QueuePool;
use App\Models\Service;

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $this->service = Service::factory()->for($pool)->create([
        'is_active' => true,
        'booking_enabled' => true,
    ]);
});

it('rejects booking with past service_date', function () {
    $response = $this->post('/antrian', [
        'service_id' => $this->service->id,
        'service_date' => now()->subDay()->toDateString(),
        'visitor_name' => 'Test',
        'visitor_identifier' => 'ID123',
        'visitor_phone' => '08123456789',
    ]);

    $response->assertSessionHasErrors(['service_date']);
});

it('rejects booking with service_date more than 14 days ahead', function () {
    $response = $this->post('/antrian', [
        'service_id' => $this->service->id,
        'service_date' => now()->addDays(15)->toDateString(),
        'visitor_name' => 'Test',
        'visitor_identifier' => 'ID123',
        'visitor_phone' => '08123456789',
    ]);

    $response->assertSessionHasErrors(['service_date']);
});

it('rejects booking with notes longer than 1000 characters', function () {
    $response = $this->post('/antrian', [
        'service_id' => $this->service->id,
        'service_date' => now()->addDay()->toDateString(),
        'visitor_name' => 'Test',
        'visitor_identifier' => 'ID123',
        'visitor_phone' => '08123456789',
        'notes' => str_repeat('x', 1001),
    ]);

    $response->assertSessionHasErrors(['notes']);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter="BookingValidation"`
Expected: FAIL — no date range or notes max validation on web request.

- [ ] **Step 3: Update StorePublicQueueBookingRequest validation rules**

In `app/Http/Requests/StorePublicQueueBookingRequest.php`, replace the `rules` method:

```php
public function rules(): array
{
    return [
        'service_id' => ['required', 'integer', 'exists:services,id'],
        'service_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:+14 days', new \App\Rules\WeekdayOnly],
        'visitor_name' => ['required', 'string', 'max:255'],
        'visitor_identifier' => ['required', 'string', 'max:64'],
        'visitor_phone' => ['required', 'string', 'max:30'],
        'notes' => ['nullable', 'string', 'max:1000'],
    ];
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter="BookingValidation"`
Expected: PASS

- [ ] **Step 5: Run existing booking tests to check regressions**

Run: `php artisan test --compact --filter="PublicQueueBooking"`
Expected: PASS

- [ ] **Step 6: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Requests/StorePublicQueueBookingRequest.php tests/Feature/Security/BookingValidationTest.php
git commit -m "security: align web booking validation with API (date range + notes max)

Adds after_or_equal:today, before_or_equal:+14 days, WeekdayOnly rule
for service_date and max:1000 for notes field, matching the API request.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Chunk 3: P2 & P3 — Medium/Low Severity Fixes

### Task 6: Fix CheckModulePassword Middleware to Check Auth Flag

**Files:**
- Modify: `app/Http/Middleware/CheckModulePassword.php:17-29`
- Test: `tests/Feature/Kiosk/KioskAuthTest.php` (existing test already covers redirect behavior)

- [ ] **Step 1: Write failing test for middleware auth flag check**

Add to `tests/Feature/Kiosk/KioskAuthTest.php`:

```php
it('blocks access when timestamp exists but authenticated flag is false', function () {
    $response = withSession([
        'kiosk_authenticated' => false,
        'kiosk_authenticated_at' => now()->timestamp,
    ])->get('/kiosk');

    $response->assertRedirect('/kiosk/login');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter="blocks access when timestamp exists"`
Expected: FAIL — middleware only checks timestamp, not auth flag.

- [ ] **Step 3: Update middleware to check both keys**

In `app/Http/Middleware/CheckModulePassword.php`, replace the `handle` method:

```php
public function handle(Request $request, Closure $next, string $module): Response
{
    $sessionKey = self::resolveSessionKey($module);
    $timestampKey = self::resolveTimestampKey($module);

    $isAuthenticated = session($sessionKey, false);
    $authenticatedAt = session($timestampKey);
    $sessionLifetimeSeconds = config('kiosk.session_lifetime', 1440) * 60;

    if (! $isAuthenticated || ! $authenticatedAt || (now()->timestamp - $authenticatedAt) >= $sessionLifetimeSeconds) {
        session()->forget([$sessionKey, $timestampKey]);

        return redirect()->to('/' . $module . '/login');
    }

    return $next($request);
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter="KioskAuth"`
Expected: ALL PASS

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Middleware/CheckModulePassword.php tests/Feature/Kiosk/KioskAuthTest.php
git commit -m "security: check auth flag in CheckModulePassword middleware

Previously only checked timestamp for expiry. Now also verifies the
authenticated boolean flag, and clears stale session on rejection.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 7: Limit api/institution Response Fields

**Files:**
- Modify: `app/Http/Controllers/Api/PublicServiceController.php:13-16`

- [ ] **Step 1: Update institution endpoint to return only public fields**

In `app/Http/Controllers/Api/PublicServiceController.php`, replace the `institution` method:

```php
public function institution(): JsonResponse
{
    $config = (array) config('institution', []);

    return response()->json(collect($config)->only([
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'website',
    ])->all());
}
```

Note: check `config/institution.php` to see which keys exist. Only expose public-facing fields.

- [ ] **Step 2: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/Api/PublicServiceController.php
git commit -m "security: limit api/institution to public fields only

Prevents accidental exposure of internal config values by explicitly
allowlisting fields returned from config('institution').

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 8: Document Security Configuration (.env.example)

**Files:**
- Modify: `.env.example`

- [ ] **Step 1: Add security-related env vars to .env.example**

Add these entries (or update existing ones) in `.env.example`:

```dotenv
# Module Passwords (use: php -r "echo password_hash('your-password', PASSWORD_BCRYPT);" to generate)
KIOSK_PASSWORD=
TV_DISPLAY_PASSWORD=
MODULE_SESSION_LIFETIME=1440

# Session Security (set true for production)
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
```

- [ ] **Step 2: Commit**

```bash
git add .env.example
git commit -m "docs: add security env vars to .env.example

Documents KIOSK_PASSWORD, TV_DISPLAY_PASSWORD, SESSION_ENCRYPT,
and SESSION_SECURE_COOKIE with generation instructions.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Final Verification

- [ ] Run full test suite: `php artisan test --compact`
- [ ] Run Pint: `vendor/bin/pint --dirty --format agent`
- [ ] Verify no regressions on existing functionality
