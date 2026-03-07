# Public UI/UX Overhaul — Sistem Antrian PTSP Pengadilan Agama

## TL;DR

> **Quick Summary**: Redesign total seluruh halaman publik (landing, booking, lookup, display, layout) agar profesional, intuitif untuk pengguna dengan literasi digital rendah, dan selaras dengan PRD. Termasuk TTS audio untuk pemanggilan antrian dengan caching.
> 
> **Deliverables**:
> - Dedicated public layout (always light mode, branding institusi)
> - Landing page dengan katalog layanan, jam operasional, panduan persyaratan
> - Booking flow dengan step indicator, info layanan, konfirmasi + print
> - Enhanced lookup dengan status Indonesia dan posisi antrian
> - Real-time display page dengan Livewire polling + TTS audio
> - Branding configurable via .env
> - Updated public test suite
> 
> **Estimated Effort**: Large
> **Parallel Execution**: YES — 4 waves
> **Critical Path**: Task 1 (env config) → Task 2 (public layout) → Task 4-8 (pages) → Task 9 (display) → Task 10 (TTS) → Final Verification

---

## Context

### Original Request
User meminta review dan overhaul UI/UX halaman publik karena "dari segi UI/UX kurang menarik dan tidak membuat saya sebagai user paham apa yang harus dilakukan." Scope: Full Overhaul termasuk layout terpisah, branding instansi, dan semua gap PRD.

### Interview Summary
**Key Discussions**:
- **Scope**: Full overhaul — redesign total semua halaman publik
- **Branding**: "Pengadilan Agama [Umum]" — generik, configurable via .env
- **Theme**: Light mode default — halaman publik selalu light, no dark mode
- **TTS**: Text-to-Speech dengan caching — nomor yang sudah dipanggil tidak regenerate audio. Harus 100% free.

**Research Findings**:
- **TTS**: Web Speech API (SpeechSynthesis) sebagai primary — zero dependency, browser-native, supports Indonesian. Cache via IndexedDB/memory map. Fallback: visual-only jika voice Indonesian tidak tersedia.
- **Current state**: Semua halaman publik masih menggunakan template default "Laravel Starter Kit", tidak ada branding institusi, tidak ada informasi layanan, booking form minimal, lookup high-friction, display statis tanpa polling/audio.
- **Database ready**: Service model sudah punya `description`, `requirements`, `daily_quota`, `booking_enabled`, `walk_in_enabled` — hanya belum ditampilkan ke publik.
- **Existing tests**: `tests/Feature/Public/PublicQueueBookingPageTest.php`, `tests/Feature/Public/PublicQueueLookupPageTest.php`, `tests/Feature/Public/QueueDisplayTest.php` — harus di-update, bukan diabaikan.

### Metis Review
**Identified Gaps** (addressed):
- **Status `booked` belum di-plan**: Ditambahkan ke status map publik (booked, waiting, called, completed, cancelled)
- **`ticket_number` tidak globally unique**: [DECISION NEEDED: Lookup strategy]
- **`daily_quota` belum enforced**: Default informational only — tampilkan sisa kuota tanpa hard limit
- **Branding contract belum jelas**: Default: `INSTITUTION_NAME`, `INSTITUTION_ADDRESS`, `INSTITUTION_PHONE`, `OPERATING_HOURS` di .env
- **Confirmation page belum ada route**: Default: redirect ke GET `/antrian/konfirmasi/{ticket_id}`
- **Recall audio behavior**: Default: recall DOES replay audio
- **Auth/error pages**: Excluded — hanya 5 halaman publik
- **Existing tests harus di-update**: Ditambahkan ke setiap task relevan
- **`dark:` classes masih ada di views**: Harus dihapus dari semua view publik
- **PII di display**: Display TIDAK boleh menampilkan nama/telepon pengunjung

---

## Work Objectives

### Core Objective
Redesign total seluruh halaman publik Sistem Antrian PTSP agar profesional, intuitif, dan selaras dengan PRD — dengan fokus pada pengguna dengan literasi digital rendah di lingkungan Pengadilan Agama.

### Concrete Deliverables
- `resources/views/layouts/public.blade.php` — dedicated public layout (full rewrite)
- `resources/views/welcome.blade.php` — landing page with service catalog
- `resources/views/pages/public/antrian/booking.blade.php` — enhanced booking form
- `resources/views/pages/public/antrian/confirmation.blade.php` — NEW confirmation page
- `resources/views/pages/public/antrian/lookup.blade.php` — enhanced lookup
- `resources/views/pages/display/index.blade.php` — real-time display with TTS
- `app/Livewire/QueueDisplay.php` — NEW Livewire component for display
- `.env` additions: `INSTITUTION_NAME`, `INSTITUTION_ADDRESS`, `INSTITUTION_PHONE`, `OPERATING_HOURS`
- `config/institution.php` — NEW config file for institution settings
- Updated test files in `tests/Feature/Public/`

### Definition of Done
- [ ] `php artisan test --compact tests/Feature/Public` → ALL PASS
- [ ] Semua halaman publik menggunakan dedicated public layout
- [ ] Zero `dark:` classes di view publik
- [ ] Branding institusi tampil di semua halaman publik
- [ ] Landing page menampilkan katalog layanan dan panduan
- [ ] Booking flow: pilih layanan → isi form → konfirmasi + print
- [ ] Lookup menampilkan status Indonesia dan posisi antrian
- [ ] Display auto-refresh dan TTS audio berfungsi di Chrome

### Must Have
- Dedicated public layout terpisah dari admin (always light mode)
- Branding institusi configurable via .env
- Katalog layanan dengan deskripsi dan persyaratan di landing page
- Step indicator di booking flow
- Halaman konfirmasi booking dengan opsi print
- Status label Bahasa Indonesia (Menunggu Dipanggil, Sedang Dipanggil, Selesai, Dibatalkan, Terdaftar)
- Posisi antrian di lookup
- Auto-refresh display via Livewire polling
- TTS audio pemanggilan dengan caching (Web Speech API)
- Semua form menggunakan Flux UI Pro components
- Updated test coverage untuk semua halaman publik

### Must NOT Have (Guardrails)
- ❌ Dark mode / theme toggle di halaman publik
- ❌ Perubahan format nomor tiket (tetap `POOL-XXXX` per hari)
- ❌ WhatsApp/SMS notification system
- ❌ Feedback/rating system
- ❌ QR code atau PDF ticket generation
- ❌ Server-side TTS dependency (eSpeak-NG, Coqui, dll.)
- ❌ Perubahan area admin/frontdesk/officer/auth
- ❌ SPA/Inertia/dependency frontend baru
- ❌ PII (nama, telepon) di display page
- ❌ Hard enforcement `daily_quota` (informational only)
- ❌ Scheduling engine / kalender libur
- ❌ Analytics atau kiosk mode
- ❌ Perubahan domain model/migration (kecuali lookup strategy jika disetujui)

---

## Verification Strategy

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.

### Test Decision
- **Infrastructure exists**: YES (Pest 4)
- **Automated tests**: YES (Tests-after) — update existing + add new
- **Framework**: Pest 4 via `php artisan test --compact`
- **Run command**: `php artisan test --compact tests/Feature/Public`

### QA Policy
Every task MUST include agent-executed QA scenarios.
Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

- **Frontend/UI**: Use Playwright (playwright skill) — Navigate, interact, assert DOM, screenshot
- **Backend**: Use Bash (curl/artisan test) — Send requests, assert status + response
- **Livewire**: Use Pest Livewire testing + Playwright for visual verification

### Status Contract (Public-facing labels)
| DB Value | Label Indonesia | Tampil di Lookup | Tampil di Display |
|----------|----------------|-----------------|-------------------|
| `booked` | Terdaftar (Online) | ✅ | ❌ |
| `waiting` | Menunggu Dipanggil | ✅ | ❌ |
| `called` | Sedang Dipanggil | ✅ | ✅ (current) |
| `completed` | Selesai | ✅ | ✅ (recent) |
| `cancelled` | Dibatalkan | ✅ | ❌ |

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Foundation — start immediately, all independent):
├── Task 1: Environment config + institution settings [quick]
├── Task 2: Dedicated public layout [visual-engineering]
├── Task 3: Status helper + Indonesian labels [quick]

Wave 2 (Page redesigns — after Wave 1, MAX PARALLEL):
├── Task 4: Landing page overhaul (depends: 1, 2) [visual-engineering]
├── Task 5: Booking form enhancement (depends: 2, 3) [visual-engineering]
├── Task 6: Confirmation page — NEW (depends: 2, 3) [coding]
├── Task 7: Lookup page enhancement (depends: 2, 3) [coding]
├── Task 8: PublicQueueController updates (depends: 3, 6) [coding]

Wave 3 (Real-time display + TTS — after Wave 2):
├── Task 9: Livewire QueueDisplay component (depends: 2, 3) [deep]
├── Task 10: TTS audio system with caching (depends: 9) [deep]

Wave 4 (Testing + cleanup — after Wave 3):
├── Task 11: Update existing public tests + add new (depends: 4-10) [coding]
├── Task 12: Cleanup dark: classes + Pint formatting (depends: 4-10) [quick]

Wave FINAL (Independent review, 4 parallel):
├── Task F1: Plan compliance audit (oracle)
├── Task F2: Code quality review (unspecified-high)
├── Task F3: Real manual QA with Playwright (unspecified-high)
├── Task F4: Scope fidelity check (deep)

Critical Path: Task 1 → Task 2 → Task 5 → Task 8 → Task 9 → Task 10 → Task 11 → F1-F4
Parallel Speedup: ~60% faster than sequential
Max Concurrent: 5 (Wave 2)
```

### Dependency Matrix

| Task | Depends On | Blocks | Wave |
|------|-----------|--------|------|
| 1 | — | 4, 5, 6, 7, 8 | 1 |
| 2 | — | 4, 5, 6, 7, 8, 9 | 1 |
| 3 | — | 4, 5, 6, 7, 8, 9 | 1 |
| 4 | 1, 2 | 11 | 2 |
| 5 | 2, 3 | 8, 11 | 2 |
| 6 | 2, 3 | 8, 11 | 2 |
| 7 | 2, 3 | 11 | 2 |
| 8 | 3, 5, 6 | 9, 11 | 2 |
| 9 | 2, 3, 8 | 10, 11 | 3 |
| 10 | 9 | 11 | 3 |
| 11 | 4-10 | F1-F4 | 4 |
| 12 | 4-10 | F1-F4 | 4 |
| F1-F4 | 11, 12 | — | FINAL |

### Agent Dispatch Summary

- **Wave 1**: **3 tasks** — T1 → `quick`, T2 → `visual-engineering`, T3 → `quick`
- **Wave 2**: **5 tasks** — T4 → `visual-engineering`, T5 → `visual-engineering`, T6 → `coding`, T7 → `coding`, T8 → `coding`
- **Wave 3**: **2 tasks** — T9 → `deep`, T10 → `deep`
- **Wave 4**: **2 tasks** — T11 → `coding`, T12 → `quick`
- **FINAL**: **4 tasks** — F1 → `oracle`, F2 → `unspecified-high`, F3 → `unspecified-high`, F4 → `deep`

---

## TODOs

### Wave 1 — Foundation (Start Immediately, All Independent)

- [ ] 1. Environment Config + Institution Settings

  **What to do**:
  - Buat file `config/institution.php` yang membaca dari .env:
    - `INSTITUTION_NAME` (default: "Pengadilan Agama")
    - `INSTITUTION_ADDRESS` (default: "")
    - `INSTITUTION_PHONE` (default: "")
    - `OPERATING_HOURS` (default: "Senin - Jumat, 08:00 - 16:00 WIB")
    - `INSTITUTION_LOGO_PATH` (default: null — gunakan logo bawaan jika null)
  - Tambahkan variabel baru ke `.env.example`
  - Update `APP_NAME` di `.env.example` dari `Laravel` ke `Sistem Antrian PTSP`

  **Must NOT do**:
  - Jangan ubah APP_NAME di .env yang sedang aktif (hanya .env.example)
  - Jangan buat migration atau model baru

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Single config file creation + .env update, straightforward task
  - **Skills**: [`livewire-development`]
    - `livewire-development`: Config akan dipakai di Livewire components nanti, perlu paham convention
  - **Skills Evaluated but Omitted**:
    - `tailwindcss-development`: No styling involved

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 2, 3)
  - **Blocks**: Tasks 4, 5, 6, 7, 8
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `config/app.php` — Convention untuk config file di project ini
  - `.env.example:1-65` — Existing env vars, lihat format dan naming convention

  **Acceptance Criteria**:
  - [ ] File `config/institution.php` exists dan return array dengan keys: name, address, phone, operating_hours, logo_path
  - [ ] `.env.example` berisi semua variabel INSTITUTION_*
  - [ ] `php artisan tinker --execute="echo config('institution.name')"` → output "Pengadilan Agama"

  **QA Scenarios:**

  ```
  Scenario: Config values accessible from app
    Tool: Bash (php artisan tinker)
    Preconditions: Fresh app state, no custom .env INSTITUTION_* values
    Steps:
      1. Run: php artisan tinker --execute="echo config('institution.name')"
      2. Assert output contains "Pengadilan Agama" (default value)
      3. Run: php artisan tinker --execute="echo config('institution.operating_hours')"
      4. Assert output contains "Senin - Jumat"
    Expected Result: All config keys return sensible defaults
    Failure Indicators: "null" output or PHP error
    Evidence: .sisyphus/evidence/task-1-config-defaults.txt

  Scenario: .env.example contains all institution vars
    Tool: Bash (grep)
    Steps:
      1. Run: grep 'INSTITUTION_' .env.example
      2. Assert output contains INSTITUTION_NAME, INSTITUTION_ADDRESS, INSTITUTION_PHONE, OPERATING_HOURS
    Expected Result: All 4+ institution env vars present
    Failure Indicators: Missing any INSTITUTION_* variable
    Evidence: .sisyphus/evidence/task-1-env-example.txt
  ```

  **Commit**: YES
  - Message: `feat(config): add institution branding configuration`
  - Files: `config/institution.php`, `.env.example`
  - Pre-commit: `php artisan config:clear`

- [ ] 2. Dedicated Public Layout (Full Rewrite)

  **What to do**:
  - Rewrite `resources/views/layouts/public.blade.php` sebagai layout mandiri:
    - HTML5 doctype lengkap dengan `<html lang="id">`
    - Meta tags SEO dasar (title, description)
    - Tailwind CSS 4 via @vite
    - **Always light mode** — TIDAK ada `dark:` classes, tidak ada class `dark` di `<html>`
    - Header: logo institusi + `config('institution.name')` + tagline "Sistem Antrian PTSP"
    - Navigation publik: link ke Beranda (/), Ambil Antrian (/antrian), Cek Antrian (/antrian/cek)
    - Footer: alamat, telepon, jam operasional dari `config('institution.*')`
    - `{{ $slot }}` untuk konten halaman
  - Gunakan Flux UI Pro components (flux:navbar, flux:navlist, dll.)
  - Responsive: mobile-first, terlihat baik di HP dan desktop
  - Warna: palette profesional pemerintah — biru tua/navy sebagai primary
  - Typography: clean, readable, ukuran besar untuk aksesibilitas

  **Must NOT do**:
  - Jangan include admin navigation, user menu, atau dark mode toggle
  - Jangan pakai `dark:` classes sama sekali
  - Jangan import JavaScript selain @vite dan Alpine.js (sudah dari Livewire)

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Full layout design — HTML structure, Flux UI, Tailwind styling, responsive
  - **Skills**: [`fluxui-development`, `tailwindcss-development`, `livewire-development`]
    - `fluxui-development`: Layout pakai flux:navbar, flux:navlist, flux:heading
    - `tailwindcss-development`: Heavy styling — responsive layout, government palette
    - `livewire-development`: Layout harus compatible dengan Livewire (Alpine, wire:navigate)

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 3)
  - **Blocks**: Tasks 4, 5, 6, 7, 8, 9
  - **Blocked By**: None (can start immediately)

  **References**:

  **Pattern References**:
  - `resources/views/layouts/app.blade.php` — Admin layout structure, lihat @vite, Alpine, Livewire integration
  - `resources/views/layouts/public.blade.php:1-3` — Current 3-line wrapper (will be rewritten)
  - `resources/views/layouts/app/header.blade.php` — Admin header, jangan copy tapi lihat structure

  **API/Type References**:
  - `config/institution.php` (Task 1) — Keys: name, address, phone, operating_hours, logo_path

  **Acceptance Criteria**:
  - [ ] `resources/views/layouts/public.blade.php` > 50 lines
  - [ ] File mengandung `config('institution.name')`
  - [ ] File mengandung `<html lang="id">`
  - [ ] `grep -c 'dark:' resources/views/layouts/public.blade.php` → 0
  - [ ] Navigation links: `/`, `/antrian`, `/antrian/cek` ada di layout
  - [ ] Footer menampilkan jam operasional

  **QA Scenarios:**

  ```
  Scenario: Public layout renders with branding
    Tool: Playwright (playwright skill)
    Preconditions: App running, Task 1 completed
    Steps:
      1. Navigate to http://localhost:8000/
      2. Assert page title contains institution name or "Sistem Antrian PTSP"
      3. Assert selector 'header' exists and contains institution name text
      4. Assert selector 'nav' contains links with href="/", href="/antrian", href="/antrian/cek"
      5. Assert selector 'footer' exists and contains operating hours text
      6. Take screenshot
    Expected Result: Full branding visible, navigation functional, footer with info
    Failure Indicators: "Laravel Starter Kit" text visible, missing nav, no footer
    Evidence: .sisyphus/evidence/task-2-public-layout.png

  Scenario: No dark mode classes in public layout
    Tool: Bash (grep)
    Steps:
      1. Run: grep -c 'dark:' resources/views/layouts/public.blade.php
      2. Assert output is 0
    Expected Result: Zero dark mode references
    Failure Indicators: Any count > 0
    Evidence: .sisyphus/evidence/task-2-no-dark-mode.txt
  ```

  **Commit**: YES
  - Message: `feat(layout): create dedicated public layout with institution branding`
  - Files: `resources/views/layouts/public.blade.php`
  - Pre-commit: `vendor/bin/pint --dirty --format agent`

- [ ] 3. Status Enum + Indonesian Labels

  **What to do**:
  - Buat Enum `app/Enums/QueueStatus.php` dengan cases: Booked, Waiting, Called, Completed, Cancelled
  - Tambahkan method `label(): string` — return label Indonesia:
    - Booked → "Terdaftar (Online)", Waiting → "Menunggu Dipanggil", Called → "Sedang Dipanggil", Completed → "Selesai", Cancelled → "Dibatalkan"
  - Tambahkan method `color(): string` — return Tailwind badge classes:
    - Booked → `text-blue-600 bg-blue-50`, Called → `text-green-600 bg-green-50`, dst.
  - Cast `status` di `QueueTicket` model ke `QueueStatus` enum
  - Update `database/factories/QueueTicketFactory.php` jika pakai raw string

  **Must NOT do**:
  - Jangan ubah migration (status tetap string di DB)
  - Jangan ubah logic set status di admin/frontdesk actions

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Enum creation + model cast, well-defined task
  - **Skills**: [`livewire-development`]

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2)
  - **Blocks**: Tasks 4, 5, 6, 7, 8, 9
  - **Blocked By**: None

  **References**:

  **Pattern References**:
  - `app/Enums/UserRole.php` — Existing enum pattern, copy structure exactly
  - `app/Models/QueueTicket.php:36-47` — Current casts() method, add status cast here

  **API/Type References**:
  - `app/Models/QueueTicket.php:28` — `'status'` in fillable
  - DB column `queue_tickets.status` — string: booked, waiting, called, completed, cancelled

  **Test References**:
  - `database/factories/QueueTicketFactory.php` — May use raw strings, needs enum update

  **Acceptance Criteria**:
  - [ ] `app/Enums/QueueStatus.php` exists with 5 cases
  - [ ] `QueueStatus::Called->label()` returns "Sedang Dipanggil"
  - [ ] `QueueTicket` model casts `status` to `QueueStatus`
  - [ ] `php artisan test --compact tests/Feature/Public` → ALL PASS

  **QA Scenarios:**

  ```
  Scenario: Enum labels return correct Indonesian text
    Tool: Bash (php artisan tinker)
    Steps:
      1. Run: php artisan tinker --execute="echo App\Enums\QueueStatus::Called->label()"
      2. Assert output is "Sedang Dipanggil"
      3. Run: php artisan tinker --execute="echo App\Enums\QueueStatus::Booked->label()"
      4. Assert output is "Terdaftar (Online)"
    Expected Result: All 5 enum cases return correct Indonesian labels
    Failure Indicators: English labels, PHP error, class not found
    Evidence: .sisyphus/evidence/task-3-enum-labels.txt

  Scenario: Existing tests still pass after enum cast
    Tool: Bash (php artisan test)
    Steps:
      1. Run: php artisan test --compact tests/Feature/Public
      2. Assert exit code 0
    Expected Result: ALL existing public tests pass
    Failure Indicators: Any test failure
    Evidence: .sisyphus/evidence/task-3-existing-tests.txt
  ```

  **Commit**: YES
  - Message: `feat(enum): add QueueStatus enum with Indonesian labels`
  - Files: `app/Enums/QueueStatus.php`, `app/Models/QueueTicket.php`, `database/factories/QueueTicketFactory.php`
  - Pre-commit: `php artisan test --compact tests/Feature/Public && vendor/bin/pint --dirty --format agent`

---

### Wave 2 — Page Redesigns (After Wave 1, MAX PARALLEL)


- [ ] 4. Landing Page Overhaul

  **What to do**:
  - Redesign `resources/views/welcome.blade.php` sebagai landing page PTSP:
    - Hero section: judul "Sistem Antrian PTSP" + deskripsi singkat layanan
    - **Katalog Layanan**: tampilkan semua Service dengan `booking_enabled=true` atau `walk_in_enabled=true`
      - Setiap layanan: nama, deskripsi, persyaratan, estimasi waktu (jika ada)
      - Badge: "Online" jika booking_enabled, "Walk-in" jika walk_in_enabled
    - **Info Sisa Kuota Hari Ini**: per layanan, tampilkan `daily_quota - jumlah_tiket_hari_ini` (informational)
    - **Panduan Pengunjung**: langkah-langkah sederhana (1. Pilih layanan, 2. Isi form, 3. Tunggu panggilan)
    - **Jam Operasional**: dari `config('institution.operating_hours')`
    - **CTA Buttons**: "Ambil Nomor Antrian" (→ /antrian), "Cek Status Antrian" (→ /antrian/cek)
    - **Link Display**: "Lihat Papan Antrian" (→ /display)
  - Gunakan Flux UI Pro components: flux:card, flux:button, flux:badge, flux:heading
  - Data layanan di-pass dari controller atau via Livewire component
  - Layout: `<x-layouts.public>`

  **Must NOT do**:
  - Jangan tampilkan data pribadi pengunjung
  - Jangan hard-code nama layanan (ambil dari DB)
  - Jangan pakai `dark:` classes

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Full page design — hero, cards, grid layout, responsive, government aesthetic
  - **Skills**: [`fluxui-development`, `tailwindcss-development`, `livewire-development`]
    - `fluxui-development`: Cards, buttons, badges, headings
    - `tailwindcss-development`: Grid layout, responsive design, spacing, typography
    - `livewire-development`: May need Livewire for dynamic quota display

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 5, 6, 7)
  - **Blocks**: Task 11
  - **Blocked By**: Tasks 1, 2

  **References**:

  **Pattern References**:
  - `resources/views/welcome.blade.php` — Current landing (will be rewritten)
  - `app/Http/Controllers/PublicQueueController.php` — Controller yang serve halaman publik

  **API/Type References**:
  - `app/Models/Service.php` — Fields: name, code, description, requirements, daily_quota, booking_enabled, walk_in_enabled
  - `app/Models/QueuePool.php` — Relationship: Service belongsTo QueuePool
  - `config/institution.php` (Task 1) — operating_hours, name

  **Acceptance Criteria**:
  - [ ] Landing page menampilkan katalog layanan dari DB (bukan hard-coded)
  - [ ] Setiap layanan card menampilkan: nama, deskripsi, persyaratan
  - [ ] Info sisa kuota hari ini tampil per layanan (informational)
  - [ ] CTA buttons menuju /antrian dan /antrian/cek
  - [ ] Jam operasional tampil dari config
  - [ ] Zero `dark:` classes

  **QA Scenarios:**

  ```
  Scenario: Landing page shows service catalog from DB
    Tool: Playwright (playwright skill)
    Preconditions: App running, seeder data loaded (services A-E)
    Steps:
      1. Navigate to http://localhost:8000/
      2. Assert page contains heading with "Sistem Antrian" or institution name
      3. Assert at least 3 service cards visible (CSS: [data-service] or .service-card)
      4. Assert first service card contains service name text (e.g., "Pendaftaran")
      5. Assert CTA button with href="/antrian" exists
      6. Assert CTA button with href="/antrian/cek" exists
      7. Assert operating hours text visible in page
      8. Take screenshot
    Expected Result: Full landing page with services, CTAs, and operating info
    Failure Indicators: Empty page, no service cards, missing CTAs
    Evidence: .sisyphus/evidence/task-4-landing-page.png

  Scenario: Landing shows daily quota info
    Tool: Playwright (playwright skill)
    Preconditions: Service with daily_quota=50, 3 tickets today
    Steps:
      1. Navigate to http://localhost:8000/
      2. Find service card with quota info
      3. Assert text contains quota information (e.g., "Sisa kuota: 47")
    Expected Result: Quota displayed as remaining count
    Failure Indicators: No quota info, shows raw daily_quota without subtracting
    Evidence: .sisyphus/evidence/task-4-landing-quota.png
  ```

  **Commit**: YES
  - Message: `feat(landing): redesign public landing page with service catalog`
  - Files: `resources/views/welcome.blade.php`, possibly new Livewire component
  - Pre-commit: `vendor/bin/pint --dirty --format agent`

- [ ] 5. Booking Form Enhancement

  **What to do**:
  - Redesign `resources/views/pages/public/antrian/booking.blade.php`:
    - **Step indicator** di atas form: 1. Pilih Layanan → 2. Isi Data → 3. Konfirmasi
    - **Step 1 - Pilih Layanan**:
      - Tampilkan layanan sebagai cards (bukan dropdown) dengan: nama, deskripsi singkat, sisa kuota hari ini
      - Hanya tampilkan layanan dengan `booking_enabled=true`
      - Badge "Kuota Habis" jika sisa kuota = 0 (disabled, tidak bisa dipilih)
    - **Step 2 - Isi Data**:
      - Nama pengunjung (required)
      - Nomor telepon (optional)
      - Catatan/keperluan (optional)
      - Tampilkan persyaratan layanan yang dipilih (dari `Service.requirements`)
    - **Step 3 - Konfirmasi**: review data sebelum submit (client-side, belum kirim ke server)
    - Submit POST ke `/antrian` lalu redirect ke confirmation page
  - Multi-step bisa pakai Alpine.js state atau Livewire wizard
  - Gunakan Flux UI Pro: flux:input, flux:textarea, flux:button, flux:card, flux:radio.group
  - Layout: `<x-layouts.public>`

  **Must NOT do**:
  - Jangan enforce daily_quota sebagai hard limit (informational only di step 1)
  - Jangan tambah field baru yang tidak ada di QueueTicket model
  - Jangan pakai `dark:` classes

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
    - Reason: Multi-step form with cards, step indicators, conditional display, responsive
  - **Skills**: [`fluxui-development`, `tailwindcss-development`, `livewire-development`]
    - `fluxui-development`: Form inputs, radio groups, cards, buttons
    - `tailwindcss-development`: Step indicator styling, card grid, form layout
    - `livewire-development`: May use Livewire for dynamic step navigation + service selection

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 4, 6, 7)
  - **Blocks**: Task 8, 11
  - **Blocked By**: Tasks 2, 3

  **References**:

  **Pattern References**:
  - `resources/views/pages/public/antrian/booking.blade.php` — Current booking form (will be enhanced)
  - `app/Http/Requests/StorePublicQueueBookingRequest.php` — Existing validation rules

  **API/Type References**:
  - `app/Models/Service.php` — Fields: name, description, requirements, daily_quota, booking_enabled
  - `app/Http/Controllers/PublicQueueController.php:booking()` — Current data passed to view
  - `app/Http/Controllers/PublicQueueController.php:storeBooking()` — POST handler

  **Acceptance Criteria**:
  - [ ] Step indicator visible (3 steps)
  - [ ] Step 1 shows services as cards with descriptions and quota info
  - [ ] Only booking_enabled=true services shown
  - [ ] Step 2 shows requirements for selected service
  - [ ] Step 3 shows review before submit
  - [ ] Form submits successfully and redirects

  **QA Scenarios:**

  ```
  Scenario: Complete booking flow (happy path)
    Tool: Playwright (playwright skill)
    Preconditions: App running, Service A (Pendaftaran) has booking_enabled=true, daily_quota=50
    Steps:
      1. Navigate to http://localhost:8000/antrian
      2. Assert step indicator shows 3 steps, step 1 active
      3. Assert service cards visible, click on "Pendaftaran" card
      4. Assert step 2 active, requirements for Pendaftaran visible
      5. Fill input[name=visitor_name] with "Test User"
      6. Assert step 3 shows review: service name + visitor name
      7. Click submit button
      8. Assert redirect to confirmation page (URL contains /antrian/konfirmasi)
      9. Take screenshot at each step
    Expected Result: 3-step flow completes, redirects to confirmation
    Failure Indicators: Missing steps, form error, no redirect
    Evidence: .sisyphus/evidence/task-5-booking-flow.png

  Scenario: Booking with quota habis shows disabled
    Tool: Playwright (playwright skill)
    Preconditions: Service with daily_quota=1, 1 ticket already today
    Steps:
      1. Navigate to http://localhost:8000/antrian
      2. Find service card with exhausted quota
      3. Assert card shows "Kuota Habis" badge or is visually disabled
      4. Assert card is not clickable / selecting it shows warning
    Expected Result: Full quota service visually disabled
    Failure Indicators: User can select full-quota service
    Evidence: .sisyphus/evidence/task-5-booking-quota-full.png
  ```

  **Commit**: YES (groups with Task 6)
  - Message: `feat(booking): enhance booking form with multi-step flow`
  - Files: `resources/views/pages/public/antrian/booking.blade.php`
  - Pre-commit: `vendor/bin/pint --dirty --format agent`

- [ ] 6. Confirmation Page (NEW)

  **What to do**:
  - Buat route GET `/antrian/konfirmasi/{queueTicket}` di `routes/web.php`
  - Buat view `resources/views/pages/public/antrian/confirmation.blade.php`:
    - Tampilkan: nomor tiket (besar, prominent), layanan, tanggal, nama pengunjung
    - Status: "Terdaftar (Online)" dengan badge warna
    - **Panduan selanjutnya**: "Silakan datang ke kantor pada jam operasional. Tunjukkan nomor tiket ini kepada petugas."
    - **Print button**: `window.print()` dengan @media print CSS untuk layout tiket bersih
    - **Link actions**: "Cek Status" (→ /antrian/cek), "Kembali ke Beranda" (→ /)
    - Posisi antrian: "Anda antrian ke-X dari Y hari ini"
  - Layout: `<x-layouts.public>`
  - Route harus accessible tanpa auth tapi hanya untuk tiket milik sendiri (via signed URL atau session flash)

  **Must NOT do**:
  - Jangan tampilkan data pengunjung lain
  - Jangan buat PDF atau QR code
  - Jangan pakai `dark:` classes

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: New route + view + controller method, some business logic for queue position
  - **Skills**: [`fluxui-development`, `tailwindcss-development`, `livewire-development`]

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 4, 5, 7)
  - **Blocks**: Task 8, 11
  - **Blocked By**: Tasks 2, 3

  **References**:

  **Pattern References**:
  - `routes/web.php:14-22` — Existing public route group
  - `app/Http/Controllers/PublicQueueController.php` — Add confirmation() method here

  **API/Type References**:
  - `app/Models/QueueTicket.php` — All fields needed for display
  - `app/Enums/QueueStatus.php` (Task 3) — label() and color() for status badge

  **Acceptance Criteria**:
  - [ ] Route GET `/antrian/konfirmasi/{queueTicket}` exists
  - [ ] Page displays ticket number prominently
  - [ ] Page shows service name, date, visitor name
  - [ ] Status badge with Indonesian label
  - [ ] Print button functional
  - [ ] Queue position displayed

  **QA Scenarios:**

  ```
  Scenario: Confirmation page shows ticket details
    Tool: Playwright (playwright skill)
    Preconditions: Ticket created via booking flow
    Steps:
      1. Navigate to http://localhost:8000/antrian/konfirmasi/{ticket_id}
      2. Assert ticket number displayed in large text
      3. Assert service name visible
      4. Assert status badge shows "Terdaftar (Online)"
      5. Assert "Silakan datang" guidance text visible
      6. Assert print button exists
      7. Assert link to /antrian/cek exists
      8. Take screenshot
    Expected Result: Complete confirmation with all ticket info
    Failure Indicators: Missing ticket data, wrong status label
    Evidence: .sisyphus/evidence/task-6-confirmation.png

  Scenario: Invalid ticket ID returns 404
    Tool: Bash (curl)
    Steps:
      1. Run: curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/antrian/konfirmasi/99999
      2. Assert output is 404
    Expected Result: 404 for non-existent ticket
    Failure Indicators: 500 error or 200 with empty page
    Evidence: .sisyphus/evidence/task-6-confirmation-404.txt
  ```

  **Commit**: YES (groups with Task 5)
  - Message: `feat(confirmation): add ticket confirmation page with print`
  - Files: `routes/web.php`, `resources/views/pages/public/antrian/confirmation.blade.php`, `app/Http/Controllers/PublicQueueController.php`
  - Pre-commit: `vendor/bin/pint --dirty --format agent`

- [ ] 7. Lookup Page Enhancement

  **What to do**:
  - Redesign `resources/views/pages/public/antrian/lookup.blade.php`:
    - **Search form**: [DECISION NEEDED: Lookup strategy — see plan summary]
      - Option A: Ticket number + date (current, high friction)
      - Option B: Ticket number only, show latest matching ticket
      - Option C: Add unique booking_code to QueueTicket (requires migration)
    - **Result display**:
      - Nomor tiket (prominent)
      - Layanan yang dipilih
      - Status dengan label Indonesia + color badge (dari QueueStatus enum)
      - **Posisi antrian**: "Anda antrian ke-X" jika status = waiting (hitung tiket waiting dengan sequence_number lebih kecil di pool yang sama untuk service_date yang sama)
      - **Estimasi waktu**: "Estimasi ~Y menit" (rata-rata waktu layanan * posisi)
      - Loket yang memanggil (jika status = called)
      - Waktu selesai (jika status = completed)
    - **Empty/not found state**: pesan ramah "Tiket tidak ditemukan. Pastikan nomor tiket benar."
  - Gunakan Flux UI Pro: flux:input, flux:button, flux:card, flux:badge
  - Layout: `<x-layouts.public>`

  **Must NOT do**:
  - Jangan tampilkan data pengunjung lain
  - Jangan pakai `dark:` classes
  - Jangan ubah format ticket_number

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Business logic for queue position calculation + lookup query + view
  - **Skills**: [`fluxui-development`, `tailwindcss-development`, `livewire-development`]

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 4, 5, 6)
  - **Blocks**: Task 11
  - **Blocked By**: Tasks 2, 3

  **References**:

  **Pattern References**:
  - `resources/views/pages/public/antrian/lookup.blade.php` — Current lookup (will be redesigned)
  - `app/Http/Controllers/PublicQueueController.php:lookup()` — Current lookup logic

  **API/Type References**:
  - `app/Models/QueueTicket.php` — sequence_number for position calculation
  - `app/Enums/QueueStatus.php` (Task 3) — label(), color()
  - `app/Actions/Queue/GenerateTicketNumber.php` — ticket_number format: POOL-XXXX

  **Acceptance Criteria**:
  - [ ] Search form accepts ticket number
  - [ ] Result shows status with Indonesian label and color badge
  - [ ] Queue position shown for waiting status
  - [ ] Counter shown for called status
  - [ ] Not-found state shows friendly message

  **QA Scenarios:**

  ```
  Scenario: Lookup shows ticket with Indonesian status
    Tool: Playwright (playwright skill)
    Preconditions: Ticket UMUM-0001 exists with status=waiting
    Steps:
      1. Navigate to http://localhost:8000/antrian/cek
      2. Fill search input with "UMUM-0001"
      3. Click search button
      4. Assert result shows "UMUM-0001" ticket number
      5. Assert status badge shows "Menunggu Dipanggil" (not "waiting")
      6. Assert queue position text visible (e.g., "Antrian ke-1")
      7. Take screenshot
    Expected Result: Ticket found with Indonesian labels
    Failure Indicators: English status, missing position, 500 error
    Evidence: .sisyphus/evidence/task-7-lookup-found.png

  Scenario: Lookup with non-existent ticket shows friendly message
    Tool: Playwright (playwright skill)
    Steps:
      1. Navigate to http://localhost:8000/antrian/cek
      2. Fill search input with "XXXXX-9999"
      3. Click search button
      4. Assert page shows friendly not-found message (not error page)
      5. Assert message contains "tidak ditemukan" text
    Expected Result: Friendly not-found state
    Failure Indicators: 500 error, blank page, English error
    Evidence: .sisyphus/evidence/task-7-lookup-not-found.png
  ```

  **Commit**: YES
  - Message: `feat(lookup): enhance ticket lookup with Indonesian status and queue position`
  - Files: `resources/views/pages/public/antrian/lookup.blade.php`, `app/Http/Controllers/PublicQueueController.php`
  - Pre-commit: `vendor/bin/pint --dirty --format agent`

- [ ] 8. PublicQueueController Updates

  **What to do**:
  - Update `app/Http/Controllers/PublicQueueController.php`:
    - `index()`: Pass Service collection (with booking_enabled/walk_in_enabled + sisa kuota hari ini) ke welcome view
    - `booking()`: Pass services with booking_enabled=true + sisa kuota + requirements ke booking view
    - `storeBooking()`: After store, redirect ke `/antrian/konfirmasi/{ticket}` instead of back()
    - `confirmation()`: NEW — show ticket detail + queue position
    - `lookup()`: Update query logic sesuai keputusan lookup strategy + hitung queue position + eager load relations
  - Pastikan semua query menggunakan eager loading (prevent N+1)
  - Tambahkan method private `calculateQueuePosition(QueueTicket $ticket): int` — hitung tiket waiting dengan sequence_number lebih kecil di pool + tanggal yang sama

  **Must NOT do**:
  - Jangan ubah logic create ticket (itu di Actions)
  - Jangan sentuh admin/frontdesk endpoints
  - Jangan expose PII ke display

  **Recommended Agent Profile**:
  - **Category**: `coding`
    - Reason: Controller business logic, query optimization, routing
  - **Skills**: [`livewire-development`]

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends on Task 5 & 6 route decisions)
  - **Parallel Group**: Wave 2 (late — after Tasks 5, 6 finalize routes)
  - **Blocks**: Task 9, 11
  - **Blocked By**: Tasks 3, 5, 6

  **References**:

  **Pattern References**:
  - `app/Http/Controllers/PublicQueueController.php` — Current controller (will be updated)
  - `app/Actions/Queue/CreateQueueTicket.php` — Ticket creation flow, status set to 'booked'
  - `app/Actions/Queue/GenerateTicketNumber.php` — How ticket numbers are generated

  **API/Type References**:
  - `app/Models/Service.php` — Eager load: queuePool, tickets (for quota calc)
  - `app/Models/QueueTicket.php` — Relationships: service, queuePool, counter
  - `app/Enums/QueueStatus.php` (Task 3) — For status filtering in position calc

  **Acceptance Criteria**:
  - [ ] `index()` passes services with quota info to view
  - [ ] `booking()` passes only booking_enabled services
  - [ ] `storeBooking()` redirects to confirmation page
  - [ ] `confirmation()` returns ticket detail with position
  - [ ] `lookup()` returns ticket with Indonesian status + position
  - [ ] No N+1 queries (check with debugbar or query count)

  **QA Scenarios:**

  ```
  Scenario: storeBooking redirects to confirmation
    Tool: Bash (curl)
    Preconditions: Valid service exists
    Steps:
      1. Run: curl -s -D - -X POST http://localhost:8000/antrian -d "service_id=1&visitor_name=Test"
      2. Assert HTTP status 302
      3. Assert Location header contains "/antrian/konfirmasi/"
    Expected Result: POST redirects to confirmation page
    Failure Indicators: 200 with form re-render, 500 error, redirect to /antrian
    Evidence: .sisyphus/evidence/task-8-store-redirect.txt

  Scenario: index passes services with quota to view
    Tool: Bash (php artisan test)
    Steps:
      1. Run: php artisan test --compact --filter=PublicQueueBookingPageTest
      2. Assert all tests pass
    Expected Result: Existing booking tests still pass
    Failure Indicators: Test failures
    Evidence: .sisyphus/evidence/task-8-controller-tests.txt
  ```

  **Commit**: YES
  - Message: `refactor(controller): update PublicQueueController for enhanced flows`
  - Files: `app/Http/Controllers/PublicQueueController.php`, `routes/web.php`
  - Pre-commit: `php artisan test --compact tests/Feature/Public && vendor/bin/pint --dirty --format agent`

---

### Wave 3 — Real-time Display + TTS (After Wave 2)

## Final Verification Wave

- [ ] F1. **Plan Compliance Audit** — `oracle`
  Read the plan end-to-end. For each "Must Have": verify implementation exists (read file, curl endpoint, run command). For each "Must NOT Have": search codebase for forbidden patterns — reject with file:line if found. Check evidence files exist in `.sisyphus/evidence/`. Compare deliverables against plan.
  Output: `Must Have [N/N] | Must NOT Have [N/N] | Tasks [N/N] | VERDICT: APPROVE/REJECT`

- [ ] F2. **Code Quality Review** — `unspecified-high`
  Run `vendor/bin/pint --test --format agent` + `php artisan test --compact tests/Feature/Public`. Review all changed files for: empty catches, console.log in prod, commented-out code, unused imports. Check AI slop: excessive comments, over-abstraction, generic names. Verify zero `dark:` classes in public views.
  Output: `Pint [PASS/FAIL] | Tests [N pass/N fail] | Files [N clean/N issues] | VERDICT`

- [ ] F3. **Real Manual QA** — `unspecified-high` + `playwright` skill
  Start from clean state. Navigate all 5 public pages. Execute EVERY QA scenario from EVERY task. Test cross-page flow: landing → booking → confirmation → lookup. Test display page TTS. Capture screenshots. Save to `.sisyphus/evidence/final-qa/`.
  Output: `Scenarios [N/N pass] | Integration [N/N] | Edge Cases [N tested] | VERDICT`

- [ ] F4. **Scope Fidelity Check** — `deep`
  For each task: read "What to do", read actual diff. Verify 1:1 — everything in spec was built, nothing beyond spec. Check "Must NOT do" compliance. Detect cross-task contamination. Flag unaccounted changes.
  Output: `Tasks [N/N compliant] | Contamination [CLEAN/N issues] | Unaccounted [CLEAN/N files] | VERDICT`

---

## Commit Strategy

- **Wave 1**: `feat(config): add institution branding configuration` — config/institution.php, .env.example
- **Wave 1**: `feat(layout): create dedicated public layout` — resources/views/layouts/public.blade.php
- **Wave 1**: `feat(helper): add queue status labels and helpers` — app/Helpers/, config/
- **Wave 2**: `feat(landing): redesign public landing page with service catalog` — resources/views/welcome.blade.php
- **Wave 2**: `feat(booking): enhance booking flow with step indicators and service info` — booking.blade.php, confirmation.blade.php
- **Wave 2**: `feat(lookup): improve ticket lookup with Indonesian status labels` — lookup.blade.php
- **Wave 2**: `refactor(controller): update PublicQueueController for new flows` — PublicQueueController.php
- **Wave 3**: `feat(display): add real-time Livewire queue display with TTS` — QueueDisplay.php, display/index.blade.php
- **Wave 4**: `test(public): update and expand public page test suite` — tests/Feature/Public/
- **Wave 4**: `style(public): remove dark mode classes and run Pint` — multiple files

---

## Success Criteria

### Verification Commands
```bash
php artisan test --compact tests/Feature/Public  # Expected: ALL PASS
vendor/bin/pint --test --format agent             # Expected: No fixable issues
```

### Final Checklist
- [ ] All "Must Have" present and verified
- [ ] All "Must NOT Have" absent (searched and confirmed)
- [ ] All public tests pass
- [ ] Pint formatting clean
- [ ] Evidence files exist for all QA scenarios
- [ ] All 4 Final Verification agents APPROVE
