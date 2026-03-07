# Redesign Dashboard & Navigasi Admin - PTSP

## TL;DR

> **Redesign navigasi global admin dan visual dashboard** untuk memberikan pengalaman pengguna yang lebih komprehensif. Sidebar akan diperbarui dengan grup menu yang lengkap, dan dashboard akan dirombak dengan kartu statistik berwarna, chart tren, grid quick actions, dan live counter.
> 
> **Deliverables**:
> - Sidebar & Header Navigation dengan grup "Master Data" dan "Manajemen Pengguna"
> - Kartu statistik 5 KPI dengan warna semantic (hijau/merah/biru/amber)
> - Chart tren 7 hari (bar chart: total vs completed)
> - Donut chart distribusi tiket per layanan
> - Grid 6 quick action cards dengan ikon Flux
> - Live counter dengan `wire:poll.visible`
> - Notifikasi panel dengan recent events
> 
> **Estimated Effort**: Medium-High (4-5 hours implementasi)
> **Parallel Execution**: YES - 4 waves
> **Critical Path**: Task 1 → Task 3 → Task 5 → Task 7 → Task 9

---

## Context

### Original Request
Redesign dashboard admin agar lebih "hidup" tapi tetap cocok dengan Flux UI dan gaya project PTSP ini.
**Update**: Lakukan audit keseluruhan halaman admin karena di sidebar hanya ada 2 menu saja (Dashboard & Admin). Jika memulai dari dashboard, experience terasa kurang karena menu admin lainnya tersembunyi.

### User Decisions (Confirmed)
1. **Navigasi Global**: Perluas sidebar dan header untuk menampilkan semua menu admin (Layanan, Loket, Users, Roles, Izin Layanan) dalam grup yang logis.
2. **Gaya Kartu**: Kartu Berwarna Full (background soft + border warna)
3. **Chart**: Tren 7 Hari + Distribusi Layanan
4. **Quick Actions**: Grid Action Cards dengan Ikon di Dashboard
5. **Fitur Tambahan**: Live Counter + Notifikasi Real-time

### Research Findings
- **Sidebar Issue**: Saat ini menu admin hanya 1 link (`/admin/layanan`). Menu lain hanya bisa diakses via shortcut di dashboard.
- **Flux components tersedia**: `flux:sidebar.group`, `flux:sidebar.item`, `flux:card`, `flux:chart`, `flux:badge`, `flux:button`, `flux:dropdown`, `flux:callout`, `flux:skeleton`
- **Pattern terbaik**: `⚡dashboard-stats.blade.php` - kartu berwarna + chart
- **Flux gap**: Tidak ada pie/donut chart native → Solusi: Custom SVG donut atau flux:chart dengan styling khusus
- **Live updates**: `wire:poll.visible` (bukan WebSocket, belum ada infra Echo)

### Metis Review - Guardrails Identified

**[GUARDRAIL] Data Definition**:
- "Tren 7 hari" basis: tiket **dibuat** per hari
- "Distribusi Layanan" basis: tiket **completed** per layanan (hari ini)
- KPI definitions locked (lihat Acceptance Criteria)

**[GUARDRAIL] Scope Boundaries**:
- Named route refactor → OUT OF SCOPE (hardcoded URL tetap)
- WebSocket real-time → OUT OF SCOPE (gunakan polling)
- Cross-page notification persistence → OUT OF SCOPE (dashboard-only)

**[GUARDRAIL] Flux Limitations**:
- Pie/donut tidak native → Fallback: Custom SVG donut ring sederhana
- Notification center tidak ada → Solusi: Komposisi `flux:dropdown` + `flux:card`

**[EDGE CASES] Handled**:
- Zero state: Chart 7 hari tetap render semua tanggal dengan value 0
- Empty donut: Tampilkan "Belum ada data" callout
- Many services: Top 5 + "Lainnya" bucket
- No counter/user: Fallback label "Sistem"

**[OLD WIDGETS FATE]**:
- `Ringkasan Failure Operasional` (existing card) → **REMOVE** (digantikan oleh KPI cards yang lebih komprehensif)
- `Aktivitas Pengguna Layanan` (existing card) → **REMOVE** (data masuk ke notification panel & trend chart)
- `Shortcut Manajemen` (existing button group) → **REPLACE** dengan Grid Quick Actions (Task 9)

---

## Work Objectives

### Core Objective
Memperbaiki UX navigasi admin secara global dengan mengekspos semua menu di sidebar, serta mentransformasi visual dashboard admin dari flat/utilitarian menjadi vibrant/analytics-focused.

### Concrete Deliverables
- `resources/views/layouts/app/sidebar.blade.php` (updated navigation)
- `resources/views/layouts/app/header.blade.php` (updated mobile navigation)
- `resources/views/components/dashboard/⚡admin-dashboard.blade.php` (redesigned)
- Enhanced `app/Support/Dashboard/AdminStats.php` (tambah method untuk trend & distribution)
- `resources/views/components/dashboard/stat-card.blade.php` (reusable component)

### Definition of Done
- [ ] Sidebar menampilkan grup "Master Data" dan "Manajemen Pengguna" dengan sub-menu lengkap
- [ ] Semua 5 KPI cards berwarna dengan ikon Flux
- [ ] Chart tren 7 hari render data real dari database
- [ ] Donut chart distribusi layanan render top 5 layanan
- [ ] Grid 6 quick action cards dengan ikon dan hover effects
- [ ] Live counter refresh otomatis setiap 30 detik (`wire:poll.visible`)
- [ ] Notifikasi panel menampilkan 10 recent events terakhir
- [ ] Dark mode support sempurna
- [ ] Zero states handled gracefully
- [ ] All acceptance criteria pass

### Must Have
- [ ] Expandable sidebar groups untuk Admin
- [ ] Kartu statistik berwarna dengan ikon (5 KPI)
- [ ] Bar chart tren 7 hari
- [ ] Donut chart distribusi layanan
- [ ] Grid quick actions (6 cards)
- [ ] Live polling 30s
- [ ] Notifikasi panel

### Must NOT Have (Guardrails)
- [ ] WebSocket/Socket.io integration
- [ ] Named route refactor
- [ ] Cross-page notification state
- [ ] User preference settings untuk dashboard
- [ ] Export PDF functionality

---

## Verification Strategy

### Test Decision
- **Infrastructure exists**: YES (Pest v4)
- **Automated tests**: Tests-after (tidak TDD karena ini redesign visual)
- **Framework**: Pest 4
- **Agent-Executed QA**: Playwright untuk visual verification

### QA Policy
Setiap task memiliki QA scenarios yang dieksekusi oleh agent:
- **Visual verification**: Screenshot perubahan
- **Functional**: Assert data render correctly
- **Responsive**: Test di mobile viewport
- **Dark mode**: Toggle dan verify colors

---

## Execution Strategy

```
Wave 1 (Global Navigation - Independent):
├── Task 1: Update Sidebar Navigation (Desktop) [INDEPENDENT]
└── Task 2: Update Header Navigation (Mobile) [INDEPENDENT]

Wave 2 (Dashboard Foundation - Data Layer):
├── Task 3: Extend AdminStats dengan trend & distribution methods [BISA PARALLEL]
├── Task 4: Create reusable stat-card component [BISA PARALLEL]
└── Task 5: Redesign KPI cards section (5 cards berwarna) [DEPENDS: 3, 4]

Wave 3 (Dashboard Visualization - Charts):
├── Task 6: Implement bar chart tren 7 hari [DEPENDS: 3, 5]
├── Task 7: Implement donut chart distribusi layanan [DEPENDS: 3, 5]
└── Task 8: Add zero states & loading skeletons [DEPENDS: 6, 7]

Wave 4 (Dashboard UX Enhancement - Interactive):
├── Task 9: Create grid quick action cards (6 cards) [DEPENDS: 5]
├── Task 10: Implement live polling (wire:poll.visible) [DEPENDS: 5]
├── Task 11: Create notification panel component [INDEPENDENT]
└── Task 12: Final polish & dark mode verification [DEPENDS: ALL]

Wave FINAL (Verification):
├── Task F1: Visual regression test (Playwright screenshots)
├── Task F2: Responsive test (mobile & tablet)
└── Task F3: Feature test untuk data accuracy

Critical Path: Task 1 → Task 3 → Task 5 → Task 7 → Task 9 → F1-F3
```

### Dependency Matrix

| Task | Depends On | Blocks | Same File? |
|------|------------|--------|------------|
| 1 | - | - | NO (sidebar.blade.php) |
| 2 | - | - | NO (header.blade.php) |
| 3 | - | 5, 6, 7 | NO (AdminStats.php) |
| 4 | - | 5 | NO (stat-card.blade.php) |
| 5 | 3, 4 | 6, 7, 9, 10 | YES (⚡admin-dashboard.blade.php) |
| 6 | 3, 5 | 8 | YES |
| 7 | 3, 5 | 8 | YES |
| 8 | 6, 7 | - | YES |
| 9 | 5 | - | YES |
| 10 | 5 | - | YES |
| 11 | - | - | NO (notification-panel.blade.php) |
| 12 | All above | F1-F3 | YES |
| F1-F3 | All above | - | - |

---

## TODOs

- [ ] 1. Update Sidebar Navigation (Desktop)

  **What to do**:
  - Edit `resources/views/layouts/app/sidebar.blade.php`
  - Ganti single menu "Admin" dengan dua `flux:sidebar.group` yang `expandable`:
    1. **Master Data**: Layanan (`/admin/layanan`), Loket (`/admin/loket`)
    2. **Manajemen Pengguna**: Users (`/admin/users`), Roles (`/admin/roles`), Izin Layanan (`/admin/izin-layanan`)
  - Set `:expanded` logic berdasarkan `request()->is(...)` agar grup terbuka saat berada di halaman terkait.

  **Must NOT do**:
  - Jangan ubah menu untuk role lain (Frontdesk, Monitor, dll).

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: `fluxui-development`

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1
  - **Blocks**: None
  - **Blocked By**: None

  **References**:
  - `resources/views/layouts/app/sidebar.blade.php:48-52` - Current Admin menu
  - Flux docs: `flux:sidebar.group` dengan `expandable`

  **Acceptance Criteria**:
  - [ ] Sidebar memiliki grup "Master Data" dan "Manajemen Pengguna" untuk Admin
  - [ ] Semua 5 menu admin tersedia di sidebar
  - [ ] Grup terbuka otomatis jika user berada di salah satu halamannya

  **QA Scenarios**:
  ```
  Scenario: Admin sidebar shows all menus in groups
    Tool: Playwright
    Preconditions: Login sebagai admin
    Steps:
      1. Navigate to /dashboard
      2. Verify: Sidebar memiliki grup "Master Data" dan "Manajemen Pengguna"
      3. Click "Master Data" untuk expand
      4. Verify: Menu "Layanan" dan "Loket" terlihat
      5. Click "Layanan"
      6. Verify: Navigate ke /admin/layanan dan grup "Master Data" tetap terbuka
    Expected Result: Navigasi sidebar lengkap dan berfungsi
    Evidence: .sisyphus/evidence/task-1-sidebar.png
  ```

  **Commit**: YES
  - Message: `feat(nav): expand admin sidebar navigation with groups`
  - Files: `resources/views/layouts/app/sidebar.blade.php`

- [ ] 2. Update Header Navigation (Mobile)

  **What to do**:
  - Edit `resources/views/layouts/app/header.blade.php`
  - Terapkan perubahan yang sama persis dengan Task 1 pada bagian Mobile Menu (`<flux:sidebar>` di dalam header.blade.php).
  - Untuk top navbar desktop (`<flux:navbar>`), biarkan tetap "Admin" yang mengarah ke `/admin/layanan` (karena navbar horizontal kurang cocok untuk banyak dropdown, dan sidebar adalah navigasi utama).

  **Must NOT do**:
  - Jangan merusak layout navbar desktop.

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: `fluxui-development`

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1
  - **Blocks**: None
  - **Blocked By**: None

  **References**:
  - `resources/views/layouts/app/header.blade.php:102-106` - Current mobile Admin menu

  **Acceptance Criteria**:
  - [ ] Mobile sidebar memiliki grup "Master Data" dan "Manajemen Pengguna"
  - [ ] Top navbar desktop tetap rapi

  **QA Scenarios**:
  ```
  Scenario: Mobile sidebar shows all admin menus
    Tool: Playwright
    Preconditions: Login sebagai admin, viewport mobile (375px)
    Steps:
      1. Navigate to /dashboard
      2. Click hamburger menu untuk buka sidebar
      3. Verify: Grup "Master Data" dan "Manajemen Pengguna" terlihat
      4. Click "Manajemen Pengguna"
      5. Verify: Menu Users, Roles, Izin Layanan terlihat
    Expected Result: Mobile navigasi lengkap
    Evidence: .sisyphus/evidence/task-2-mobile-nav.png
  ```

  **Commit**: YES
  - Message: `feat(nav): expand admin mobile navigation`
  - Files: `resources/views/layouts/app/header.blade.php`

- [ ] 3. Extend AdminStats dengan Trend & Distribution Methods

  **What to do**:
  - Tambah method `getTrendData()` untuk 7 hari terakhir (created tickets per day)
  - Tambah method `getServiceDistribution()` untuk completed tickets per layanan (hari ini)
  - Optimize query dengan single aggregated query per method
  - Handle zero data gracefully (return array dengan 7 hari, value 0)

  **Must NOT do**:
  - Jangan ubah method `build()` existing (backward compatibility)
  - Jangan tambahkan caching dulu (out of scope)
  - Jangan query N+1

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `pest-testing`

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2
  - **Blocks**: Task 5, 6, 7
  - **Blocked By**: None

  **References**:
  - `app/Support/Dashboard/AdminStats.php` - Existing class structure
  - `app/Models/QueueTicket.php` - Model untuk query

  **Acceptance Criteria**:
  - [ ] Method `getTrendData()` return array 7 hari dengan format `[['date' => '01 Mar', 'total' => 10, 'completed' => 5], ...]`
  - [ ] Method `getServiceDistribution()` return array **max 6 items** (top 5 layanan + 1 bucket "Lainnya")
  - [ ] Query tidak N+1

  **QA Scenarios**:
  ```
  Scenario: Trend data returns 7 days with correct format
    Tool: Bash (php artisan tinker)
    Preconditions: Database memiliki data tickets 7 hari terakhir
    Steps:
      1. Run: `AdminStats::getTrendData()`
      2. Verify: Array memiliki exactly 7 items
    Expected Result: Array dengan 7 items, format sesuai spec
    Evidence: .sisyphus/evidence/task-3-trend-data.json
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add trend and distribution methods to AdminStats`
  - Files: `app/Support/Dashboard/AdminStats.php`
  - Pre-commit: `php artisan test --filter=AdminStatsTest`

- [ ] 4. Create Reusable StatCard Component

  **What to do**:
  - Buat component Blade reusable: `resources/views/components/dashboard/stat-card.blade.php`
  - Props: `value`, `label`, `icon`, `color` (blue|green|red|amber|zinc), `trend` (optional)
  - Styling mengikuti pattern `⚡dashboard-stats`:
    - Background: `bg-{color}-50 dark:bg-{color}-900/20`
    - Border: `border-{color}-200 dark:border-{color}-800`
    - Text: `text-{color}-700 dark:text-{color}-300`
  - Support ikon Flux

  **Must NOT do**:
  - Jangan hardcode warna (gunakan prop)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `fluxui-development`, `tailwindcss-development`

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2
  - **Blocks**: Task 5
  - **Blocked By**: None

  **References**:
  - `resources/views/components/⚡dashboard-stats.blade.php:44-68` - Kartu berwarna pattern

  **Acceptance Criteria**:
  - [ ] Component menerima props: value, label, icon, color
  - [ ] Styling sesuai spec warna

  **QA Scenarios**:
  ```
  Scenario: Stat card renders with correct color scheme
    Tool: Playwright
    Preconditions: Component used dengan color='green'
    Steps:
      1. Navigate to test page dengan <x-dashboard.stat-card color="green" ... />
      2. Screenshot element
    Expected Result: Warna sesuai spec, dark mode compatible
    Evidence: .sisyphus/evidence/task-4-stat-card-colors.png
  ```

  **Commit**: YES
  - Message: `feat(ui): create reusable stat-card component`
  - Files: `resources/views/components/dashboard/stat-card.blade.php`
- [ ] 5. Redesign KPI Cards Section (5 Cards Berwarna)

  **What to do**:
  - Redesign section pertama di `⚡admin-dashboard.blade.php`
  - Gunakan `<x-dashboard.stat-card>` untuk 5 KPI:
    1. **Booking Berhasil** (green) - icon: `check-circle`
    2. **Booking Gagal** (red) - icon: `x-circle`
    3. **Tiket Dibuat** (blue) - icon: `ticket`
    4. **Tiket Batal** (amber) - icon: `minus-circle`
    5. **Tiket Selesai** (green) - icon: `check-badge`
  - Layout: `grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5`
  - Update Livewire component untuk load semua KPI data

  **Must NOT do**:
  - Jangan hapus data existing, hanya restyle
  - Jangan tambah KPI baru (scope locked)

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: `fluxui-development`, `livewire-development`, `tailwindcss-development`

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends Task 3 & 4)
  - **Parallel Group**: Wave 2 (sequential)
  - **Blocks**: Task 6, 7, 9, 10
  - **Blocked By**: Task 3, Task 4

  **References**:
  - `resources/views/components/dashboard/⚡admin-dashboard.blade.php:40-58` - Current KPI section
  - `app/Support/Dashboard/AdminStats.php` - Data source

  **Acceptance Criteria**:
  - [ ] 5 kartu KPI berwarna sesuai mapping
  - [ ] Setiap kartu punya ikon Flux
  - [ ] Layout responsive (5 kolom di xl, 3 di lg, 2 di sm, 1 di mobile)

  **QA Scenarios**:
  ```
  Scenario: All 5 KPI cards render with correct colors and icons
    Tool: Playwright
    Preconditions: Login sebagai admin, ada data tickets
    Steps:
      1. Navigate to /dashboard
      2. Screenshot KPI section
      3. Verify: 5 cards visible dengan label benar
      4. Verify: Warna sesuai mapping (green, red, blue, amber, green)
    Expected Result: 5 colored cards dengan icons, responsive, dark mode OK
    Evidence: .sisyphus/evidence/task-5-kpi-cards.png
  ```

  **Commit**: YES
  - Message: `feat(dashboard): redesign KPI cards with colors and icons`
  - Files: `resources/views/components/dashboard/⚡admin-dashboard.blade.php`

- [ ] 6. Implement Bar Chart Tren 7 Hari

  **What to do**:
  - Tambah section baru setelah KPI cards: "Tren 7 Hari Terakhir"
  - Gunakan `flux:chart` component (pattern dari `⚡dashboard-stats`)
  - Chart type: bar chart dengan 2 series:
    - Total Tiket (blue-200)
    - Selesai (green-500)
  - Data source: `AdminStats::getTrendData()`
  - Tambah legend dan tooltip

  **Must NOT do**:
  - Jangan tambah series ketiga (keep it simple)
  - Jangan custom chart library (gunakan Flux)

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: `fluxui-development`, `livewire-development`

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 7)
  - **Parallel Group**: Wave 3
  - **Blocks**: Task 8
  - **Blocked By**: Task 3, Task 5

  **References**:
  - `resources/views/components/⚡dashboard-stats.blade.php:69-95` - Chart pattern
  - Flux docs: `flux:chart`, `flux:chart.bar`, `flux:chart.legend`

  **Acceptance Criteria**:
  - [ ] Bar chart render 7 hari data
  - [ ] 2 series: total (blue) dan completed (green)
  - [ ] Legend visible di bawah chart

  **QA Scenarios**:
  ```
  Scenario: Bar chart renders 7 days trend correctly
    Tool: Playwright
    Preconditions: Login sebagai admin, Task 3 data available
    Steps:
      1. Navigate to /dashboard
      2. Scroll to "Tren 7 Hari" section
      3. Screenshot chart area
      4. Verify: 7 bars render untuk setiap hari
    Expected Result: Chart renders dengan data correct, legend visible, tooltip works
    Evidence: .sisyphus/evidence/task-6-trend-chart.png
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add 7-day trend bar chart`
  - Files: `resources/views/components/dashboard/⚡admin-dashboard.blade.php`

- [ ] 7. Implement Donut Chart Distribusi Layanan

  **What to do**:
  - Tambah section: "Distribusi per Layanan"
  - Buat custom SVG donut ring (karena Flux tidak punya pie/donut native)
  - Alternatif: Gunakan `flux:chart` dengan creative styling atau horizontal bar
  - Data: Top 5 layanan dengan completed tickets + bucket "Lainnya"
  - Warna: Palette Flux (blue, green, amber, red, zinc)
  - Tambah legend dengan persentase

  **Must NOT do**:
  - Jangan import library chart external (Chart.js, D3, dll)
  - Jangan over-engineer SVG (keep it simple)

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: `fluxui-development`, `tailwindcss-development`

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 6)
  - **Parallel Group**: Wave 3
  - **Blocks**: Task 8
  - **Blocked By**: Task 3, Task 5

  **References**:
  - SVG donut pattern: Simple SVG circle dengan stroke-dasharray
  - Flux docs: `flux:card` untuk container

  **Acceptance Criteria**:
  - [ ] Donut chart render dengan data top 5 layanan
  - [ ] Bucket "Lainnya" untuk sisa layanan
  - [ ] Legend menampilkan nama + persentase

  **QA Scenarios**:
  ```
  Scenario: Donut chart renders service distribution
    Tool: Playwright
    Preconditions: Login sebagai admin, ada completed tickets per layanan
    Steps:
      1. Navigate to /dashboard
      2. Screenshot "Distribusi per Layanan" section
      3. Verify: Donut chart visible dengan segments
    Expected Result: Donut renders correctly dengan legend
    Evidence: .sisyphus/evidence/task-7-donut-chart.png
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add service distribution donut chart`
  - Files: `resources/views/components/dashboard/⚡admin-dashboard.blade.php`

- [ ] 8. Add Zero States & Loading Skeletons

  **What to do**:
  - Tambah `flux:skeleton` untuk loading state KPI cards
  - Tambah `flux:callout` dengan icon untuk zero state:
    - "Belum ada data tren untuk 7 hari terakhir"
    - "Belum ada distribusi layanan hari ini"
  - Handle edge case: no services, no tickets
  - Conditional rendering: `wire:loading` dan `@if(empty($data))`

  **Must NOT do**:
  - Jangan tampilkan chart kosong (gunakan zero state)
  - Jangan spinner loading yang blocking

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `fluxui-development`

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends Task 6, 7)
  - **Parallel Group**: Wave 3
  - **Blocks**: None
  - **Blocked By**: Task 6, Task 7

  **References**:
  - `resources/views/components/dashboard/⚡admin-dashboard.blade.php:63-66` - Zero state pattern existing
  - Flux docs: `flux:skeleton`, `flux:callout`

  **Acceptance Criteria**:
  - [ ] Skeleton render saat data loading
  - [ ] Zero state callout muncul saat data kosong

  **QA Scenarios**:
  ```
  Scenario: Zero state displays when no data
    Tool: Playwright
    Preconditions: Database kosong (fresh state)
    Steps:
      1. Navigate to /dashboard
      2. Verify: Skeletons muncul sementara loading
      3. Verify: Zero state callouts muncul setelah load
    Expected Result: Graceful zero state, tidak error
    Evidence: .sisyphus/evidence/task-8-zero-state.png
  ```

  **Commit**: NO (group dengan Task 12)
- [ ] 9. Create Grid Quick Action Cards (5 Cards)

  **What to do**:
  - Replace existing "Shortcut Manajemen" section
  - Buat grid 5 cards dengan ikon besar:
    1. **Layanan** - icon: `cog-6-tooth`, color: blue, url: `/admin/layanan`
    2. **Loket** - icon: `computer-desktop`, color: green, url: `/admin/loket`
    3. **Users** - icon: `users`, color: amber, url: `/admin/users`
    4. **Roles** - icon: `shield-check`, color: purple, url: `/admin/roles`
    5. **Izin Layanan** - icon: `key`, color: red, url: `/admin/izin-layanan`
  - Card styling: hover effect, shadow, transition
  - Layout: `grid gap-4 sm:grid-cols-2 lg:grid-cols-3` (5 cards = 2 baris: 3+2)

  **Must NOT do**:
  - Jangan ubah URL (hardcoded tetap)
  - Jangan tambah action baru di luar yang ada

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: `fluxui-development`, `tailwindcss-development`

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 4
  - **Blocks**: None
  - **Blocked By**: Task 5

  **References**:
  - `resources/views/components/dashboard/⚡admin-dashboard.blade.php:91-99` - Existing shortcut section
  - Pattern grid cards: `resources/views/pages/public/antrian/booking.blade.php:213` (service cards)

  **Acceptance Criteria**:
  - [ ] 5 action cards dalam grid (3+2 layout)
  - [ ] Setiap card punya ikon Flux, label, hover effect
  - [ ] Warna card sesuai mapping
  - [ ] Click navigate ke URL yang benar
  - [ ] Responsive: 3 kolom di lg, 2 di sm, 1 di mobile

  **QA Scenarios**:
  ```
  Scenario: Quick action cards grid renders correctly
    Tool: Playwright
    Preconditions: Login sebagai admin
    Steps:
      1. Navigate to /dashboard
      2. Scroll ke "Akses Cepat" section
      3. Screenshot grid
      4. Verify: 5 cards visible dalam grid
      5. Verify: Setiap card punya ikon dan label
      6. Hover pada card dan verify effect
      7. Click card "Layanan" dan verify navigate ke /admin/layanan
    Expected Result: Grid renders, hover effects work, navigation correct
    Evidence: .sisyphus/evidence/task-9-quick-actions.png
  ```

  **Commit**: YES
  - Message: `feat(dashboard): redesign quick actions as grid cards with icons`
  - Files: `resources/views/components/dashboard/⚡admin-dashboard.blade.php`

- [ ] 10. Implement Live Polling (wire:poll.visible)

  **What to do**:
  - Tambah `wire:poll.30s.visible="refreshStats"` di root div component
  - Refresh method: `refreshStats()` yang re-call `AdminStats::build()`
  - Update Livewire property `$stats` dan `$trendData` secara reactive
  - Indikator: Tambah small "Live" badge dengan pulse dot

  **Must NOT do**:
  - Jangan gunakan WebSocket/Socket.io
  - Jangan polling terlalu frequent (< 30s)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `livewire-development`

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 4
  - **Blocks**: None
  - **Blocked By**: Task 5

  **References**:
  - Livewire docs: `wire:poll.visible` directive

  **Acceptance Criteria**:
  - [ ] `wire:poll.30s.visible="refreshStats"` ditambahkan
  - [ ] Data refresh setiap 30 detik (saat tab visible)
  - [ ] "Live" badge dengan pulse dot indicator
  - [ ] Tidak ada page reload

  **QA Scenarios**:
  ```
  Scenario: Live polling updates data every 30s
    Tool: Playwright
    Preconditions: Login sebagai admin, 2 browser tabs
    Steps:
      1. Open dashboard di Tab 1
      2. Create new ticket di Tab 2 (backend)
      3. Wait 30s di Tab 1
      4. Verify: KPI numbers update tanpa refresh
      5. Verify: "Live" badge visible dengan pulse animation
    Expected Result: Data updates otomatis setelah 30s
    Evidence: .sisyphus/evidence/task-10-live-polling.gif
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add live polling with wire:poll.visible`
  - Files: `resources/views/components/dashboard/⚡admin-dashboard.blade.php`

- [ ] 11. Create Notification Panel Component

  **What to do**:
  - Buat component: `resources/views/components/dashboard/notification-panel.blade.php`
  - Gunakan `flux:dropdown` sebagai container
  - Isi: List 10 recent events dari `queue_activities` table
  - Fields: timestamp (relative), action, ticket number, user/counter
  - Badge: Unread count (jika ada events baru sejak last view)
  - Trigger: Bell icon di header atau di dashboard card

  **Must NOT do**:
  - Jangan simpan read state persistently (localStorage/session only)
  - Jangan notifikasi cross-page (dashboard only)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `fluxui-development`, `livewire-development`

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 4
  - **Blocks**: None
  - **Blocked By**: None

  **References**:
  - `resources/views/layouts/app/sidebar.blade.php:70` - Dropdown pattern
  - `app/Models/QueueActivity.php` - Model untuk query

  **Acceptance Criteria**:
  - [ ] Dropdown trigger dengan bell icon dan unread badge
  - [ ] List 10 recent events dengan format jelas
  - [ ] Relative timestamps ("2 menit yang lalu")
  - [ ] Dark mode support

  **QA Scenarios**:
  ```
  Scenario: Notification panel displays recent events
    Tool: Playwright
    Preconditions: Login sebagai admin, ada activities di DB
    Steps:
      1. Navigate to /dashboard
      2. Click bell icon
      3. Verify: Dropdown muncul dengan list events
      4. Verify: Setiap item punya timestamp, action, ticket
      5. Verify: Unread badge count correct
      6. Close dropdown dan reopen, verify badge cleared
    Expected Result: Panel works, events display correctly
    Evidence: .sisyphus/evidence/task-11-notification-panel.png
  ```

  **Commit**: YES
  - Message: `feat(dashboard): add notification panel with recent events`
  - Files: `resources/views/components/dashboard/notification-panel.blade.php`

- [ ] 12. Final Polish & Dark Mode Verification

  **What to do**:
  - Review semua section untuk konsistensi visual
  - Verifikasi dark mode untuk semua komponen baru
  - Pastikan spacing konsisten (`space-y-6`, `gap-4`)
  - Check accessibility: contrast ratio, focus states
  - Hapus kode lama yang tidak terpakai
  - Run `vendor/bin/pint` untuk formatting

  **Must NOT do**:
  - Jangan tambah fitur baru (scope locked)
  - Jangan refactor unrelated code

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `tailwindcss-development`, `pest-testing`

  **Parallelization**:
  - **Can Run In Parallel**: NO (final task)
  - **Parallel Group**: Wave 4
  - **Blocks**: F1-F3
  - **Blocked By**: All above

  **References**:
  - `resources/css/app.css` - Theme tokens

  **Acceptance Criteria**:
  - [ ] Semua section konsisten styling
  - [ ] Dark mode verified (screenshot)
  - [ ] Pint formatting pass
  - [ ] Tidak ada console errors
  - [ ] Smooth animations/transitions

  **QA Scenarios**:
  ```
  Scenario: Dark mode works correctly for all components
    Tool: Playwright
    Preconditions: Login sebagai admin
    Steps:
      1. Navigate to /dashboard (light mode)
      2. Screenshot full page
      3. Toggle dark mode
      4. Screenshot full page
      5. Verify: Semua komponen adapt warna dark
      6. Verify: Tidak ada elemen yang "hardcoded" light
    Expected Result: Dark mode sempurna, semua komponen adapt
    Evidence: .sisyphus/evidence/task-12-dark-mode.png
  ```

  **Commit**: YES
  - Message: `style(dashboard): final polish and dark mode verification`
  - Files: `resources/views/components/dashboard/⚡admin-dashboard.blade.php`, `resources/views/components/dashboard/stat-card.blade.php`
  - Pre-commit: `vendor/bin/pint --dirty`

---

## Final Verification Wave

> 3 review agents run in PARALLEL. ALL must APPROVE. Rejection → fix → re-run.

- [ ] F1. **Visual Regression Test** — `ui-designer` (manual verification)
  
  **CATATAN**: Playwright tidak tersedia di project ini. Gunakan manual verification:
  - Buka `/dashboard` di browser
  - Capture screenshots (light & dark mode)
  - Verify visual consistency
  
  Checklist:
  - [ ] Full dashboard page (light mode)
  - [ ] Full dashboard page (dark mode)
  - [ ] KPI cards section
  - [ ] Charts section
  - [ ] Quick actions grid
  - [ ] Sidebar navigation (expanded & collapsed)
  
  Output: `Visual [PASS/FAIL] | Screenshots [N captured] | VERDICT`
  Evidence: `.sisyphus/evidence/final-visual-*.png`

- [ ] F2. **Responsive Design Test** — `ui-designer` (manual verification)
  
  **CATATAN**: Playwright tidak tersedia. Gunakan browser DevTools:
  - Mobile (375px): Verify scrollable, cards stack correctly, mobile sidebar works
  - Tablet (768px): Verify 2-column grids work
  - Desktop (1440px): Verify full layout, sidebar groups work
  - Large desktop (1920px): Verify no excessive whitespace
  
  Check touch targets (min 44px), font sizes readable.
  
  Output: `Responsive [PASS/FAIL] | Viewports [N/N pass] | VERDICT`

- [ ] F3. **Feature Test - Data Accuracy** — `pest-testing`
  
  Run tests:
  - `DashboardTest`: Verify Livewire component renders
  - `DashboardTest`: Verify polling mechanism
  
  Command: `php artisan test --compact --filter=Dashboard`
  
  Output: `Tests [N pass/N fail] | Coverage [%] | VERDICT`

---

## Commit Strategy

### Commit Sequence

1. **Task 1**: `feat(nav): expand admin sidebar navigation with groups`
2. **Task 2**: `feat(nav): expand admin mobile navigation`
3. **Task 3**: `feat(dashboard): add trend and distribution methods to AdminStats`
4. **Task 4**: `feat(ui): create reusable stat-card component`
5. **Task 5**: `feat(dashboard): redesign KPI cards with colors and icons`
6. **Task 6**: `feat(dashboard): add 7-day trend bar chart`
7. **Task 7**: `feat(dashboard): add service distribution donut chart`
8. **Task 9**: `feat(dashboard): redesign quick actions as grid cards with icons`
9. **Task 10**: `feat(dashboard): add live polling with wire:poll.visible`
10. **Task 11**: `feat(dashboard): add notification panel with recent events`
11. **Task 12**: `style(dashboard): final polish and dark mode verification`

### Final Commit (after F1-F3 pass)
```
feat(dashboard): complete admin dashboard and navigation redesign

- Expand sidebar and mobile navigation with Master Data and User Management groups
- Add colored KPI cards with Flux icons
- Add 7-day trend bar chart
- Add service distribution donut chart
- Add grid quick action cards
- Add live polling (30s interval)
- Add notification panel with recent events
- Full dark mode support
- Responsive design
```

---

## Success Criteria

### Verification Commands

```bash
# 1. Run tests
php artisan test --compact --filter=Dashboard

# 2. Check formatting
vendor/bin/pint --dirty

# 3. Build assets (if needed)
npm run build

# 4. Verify no errors
php artisan route:clear
php artisan view:clear
```

### Final Checklist

- [ ] Sidebar navigation shows all admin menus in groups
- [ ] Mobile navigation shows all admin menus in groups
- [ ] All 5 KPI cards display with correct colors and icons
- [ ] Bar chart shows 7-day trend with 2 series
- [ ] Donut chart shows top 5 services distribution
- [ ] Grid quick actions (6 cards) work correctly
- [ ] Live polling updates every 30s
- [ ] Notification panel shows recent events
- [ ] Dark mode works perfectly
- [ ] Mobile responsive (tested on 375px, 768px, 1440px)
- [ ] All tests pass
- [ ] Code formatted with Pint
- [ ] No console errors
- [ ] Zero states handled gracefully

### Definition of "Done"

Dashboard admin redesign selesai ketika:
1. Semua TODO tasks (1-12) COMPLETED
2. Final Verification Wave (F1-F3) ALL APPROVED
3. Success Criteria checklist ALL CHECKED
4. User (atau Product Owner) sign-off

---

## Auto-Resolved Items (Metis Review)

### Fixed Silently
- **[Gap] Data Definition**: Locked - "Tren 7 hari" = created tickets, "Distribusi" = completed per service
- **[Gap] Flux Limitation**: Decided - Custom SVG donut (not external library)
- **[Gap] Live Updates**: Decided - `wire:poll.visible` 30s (not WebSocket)
- **[Gap] Color Mapping**: Decided - Green (success), Red (fail), Blue (neutral), Amber (warning)
- **[Gap] Top Navbar Desktop**: Karena layout aplikasi menggunakan sidebar sebagai navigasi utama di desktop, top navbar (`header.blade.php`) tidak perlu dirombak menjadi dropdown kompleks, cukup fokus pada perbaikan sidebar dan mobile menu.
- **[Gap] Route Naming**: Tetap menggunakan hardcoded URL (misal: `/admin/layanan`) untuk navigasi agar pengerjaan lebih cepat dan fokus pada UI.

### Defaults Applied
- **Polling Interval**: 30 seconds (balanced performance vs freshness)
- **Top N Services**: 5 services + "Lainnya" bucket
- **Recent Events**: 10 items in notification panel
- **Zero State**: `flux:callout` dengan icon info

---

## Risk Mitigation

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Donut chart too complex | Medium | Medium | Fallback ke horizontal bar jika SVG bermasalah |
| Performance issue dengan polling | Low | High | `wire:poll.visible` hanya poll saat tab active |
| Dark mode inconsistency | Low | Medium | Test dengan screenshots, verify semua tokens |
| Mobile layout breakage | Medium | Medium | Test di 375px early, adjust grid cols |
| Data query slow | Low | High | Optimize di Task 1, gunakan single query |
