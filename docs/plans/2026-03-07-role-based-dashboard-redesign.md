# Role-Based Dashboard Redesign Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Membangun dashboard berbasis role untuk `petugas`, `monitor/pimpinan`, dan `admin` agar dashboard menjadi pusat kerja operasional, monitoring kinerja, dan observabilitas aplikasi.

**Architecture:** Tetap gunakan satu route `dashboard`, tetapi render komponen dashboard yang berbeda berdasarkan role user. Fondasi backend dimulai dari dispatch antrean yang atomic dan eligibility layanan per user, lalu dilanjutkan dengan audit trail, metrik agregat, dan UI Flux/Livewire yang spesifik per role.

**Tech Stack:** Laravel 12, Livewire 4, Flux UI Pro 2.x, Tailwind CSS 4, Pest 4, MySQL.

## Ruang Lingkup
- Dashboard `petugas` sebagai workstation operasional
- Dashboard `monitor/pimpinan` sebagai layar kinerja per petugas dan layanan
- Dashboard `admin` sebagai health panel aplikasi dan pusat kontrol
- Dispatch otomatis FIFO berbasis layanan yang diizinkan per user
- Audit aktivitas petugas, daftar skip layanan, dan agregasi statistik role-based

## Prinsip Produk
- `Petugas` tidak memilih tiket manual; sistem meng-claim tiket eligible tertua secara atomic.
- `Skip` tidak membatalkan tiket; tiket pindah ke daftar skip dan dapat dipanggil ulang.
- Setiap aksi layanan harus tercatat per user untuk tracing jumlah pihak yang dilayani.
- `Monitor` melihat distribusi layanan per petugas dan per layanan, bukan tombol operasional.
- `Admin` melihat health aplikasi, aktivitas publik, kegagalan antrean, dan shortcut manajemen.

## Task 1: Fondasi Role-Aware Dashboard Shell

**Files:**
- Modify: `routes/web.php`
- Modify: `resources/views/dashboard.blade.php`
- Create: `resources/views/components/dashboard/petugas-dashboard.blade.php`
- Create: `resources/views/components/dashboard/monitor-dashboard.blade.php`
- Create: `resources/views/components/dashboard/admin-dashboard.blade.php`
- Test: `tests/Feature/DashboardTest.php`
- Test: `tests/Feature/Navigation/PtspNavigationTest.php`

**Step 1: Write failing tests for role-based dashboard shell**

Tambahkan assertion bahwa:
- user `petugas` melihat modul panggilan;
- user `monitor` melihat ringkasan monitoring;
- user `admin` melihat health aplikasi dan shortcut manajemen.

**Step 2: Run targeted tests to verify they fail**

Run: `php artisan test --compact tests/Feature/DashboardTest.php tests/Feature/Navigation/PtspNavigationTest.php`

Expected: FAIL karena dashboard masih generik untuk semua role.

**Step 3: Implement minimal role-aware dashboard renderer**

- Pertahankan route `dashboard`, tetapi kirimkan role aktif ke view.
- Di `resources/views/dashboard.blade.php`, render partial/komponen berbeda sesuai role.
- Jangan pindah ke route terpisah.

**Step 4: Run targeted tests to verify they pass**

Run: `php artisan test --compact tests/Feature/DashboardTest.php tests/Feature/Navigation/PtspNavigationTest.php`

**Step 5: Commit**

```bash
git add routes/web.php resources/views/dashboard.blade.php resources/views/components/dashboard tests/Feature/DashboardTest.php tests/Feature/Navigation/PtspNavigationTest.php
git commit -m "feat: add role-aware dashboard shell"
```

## Task 2: Eligibility Layanan Per User dan Dispatch Atomic

**Files:**
- Modify: `app/Models/User.php`
- Modify: `app/Models/Service.php`
- Modify: `app/Actions/Queue/CallNextTicket.php`
- Modify: `app/Http/Controllers/OfficerQueueController.php`
- Create: `database/migrations/2026_03_07_000001_create_service_user_table.php`
- Create: `database/factories/UserFactory.php` (state jika perlu)
- Test: `tests/Feature/Officer/CounterQueueWorkflowTest.php`
- Test: `tests/Feature/Auth/PtspAuthorizationTest.php`

**Step 1: Write failing tests for service eligibility and atomic claim**

Tambahkan skenario:
- petugas umum hanya mengambil tiket dari layanan yang diizinkan;
- layanan khusus tidak bisa diambil user yang tidak diizinkan;
- dua petugas tidak bisa meng-claim tiket yang sama;
- tiket tertua yang eligible selalu dipilih lebih dulu.

**Step 2: Run targeted tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Officer/CounterQueueWorkflowTest.php`

**Step 3: Implement minimal eligibility mapping**

- Tambahkan pivot `service_user` atau nama serupa untuk daftar layanan yang diizinkan per user.
- Tambahkan relationship Eloquent di `User` dan `Service`.
- Ikuti pola Eloquent, jangan pakai query mentah.

**Step 4: Implement atomic next-ticket claim**

- Update `CallNextTicket` agar memilih tiket eligible tertua dengan locking/transaction.
- Simpan `called_by` atau metadata actor yang diperlukan untuk tracing.
- Pastikan tiket yang sudah di-claim tidak muncul lagi untuk petugas lain.

**Step 5: Run targeted tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Officer/CounterQueueWorkflowTest.php`

**Step 6: Commit**

```bash
git add app/Models/User.php app/Models/Service.php app/Actions/Queue/CallNextTicket.php app/Http/Controllers/OfficerQueueController.php database/migrations tests/Feature/Officer/CounterQueueWorkflowTest.php
git commit -m "feat: add service eligibility and atomic queue dispatch"
```

## Task 3: Audit Trail dan Statistik Petugas

**Files:**
- Modify: `app/Actions/Queue/CallNextTicket.php`
- Modify: `app/Actions/Queue/RecallTicket.php`
- Modify: `app/Actions/Queue/SkipTicket.php`
- Modify: `app/Actions/Queue/CompleteTicket.php`
- Modify: `app/Actions/Queue/CancelTicket.php`
- Modify: `app/Models/QueueActivity.php`
- Modify: `app/Support/Reports/QueueReportBuilder.php`
- Create: `app/Support/Dashboard/PetugasStats.php`
- Test: `tests/Feature/Audit/QueueAuditLogTest.php`
- Test: `tests/Feature/Officer/CounterQueueWorkflowTest.php`
- Test: `tests/Unit/Reports/QueueReportBuilderTest.php`

**Step 1: Write failing tests for per-user service trace**

Tambahkan assertion bahwa sistem bisa menghitung:
- jumlah pihak yang dilayani user hari ini;
- jumlah skip, recall, complete per user;
- distribusi layanan yang dikerjakan petugas.

**Step 2: Run targeted tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Audit/QueueAuditLogTest.php tests/Unit/Reports/QueueReportBuilderTest.php`

**Step 3: Implement audit enrichment**

- Pastikan setiap aksi antrean menyimpan actor, status asal, status tujuan, layanan, dan timestamp penting.
- Gunakan enum/status yang sudah ada, jangan hard-code string baru tanpa alasan.

**Step 4: Implement stats service for petugas dashboard**

- Buat service support khusus untuk merakit statistik harian petugas.
- Pisahkan concern query agregat dari Blade/Livewire component.

**Step 5: Run targeted tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Audit/QueueAuditLogTest.php tests/Feature/Officer/CounterQueueWorkflowTest.php tests/Unit/Reports/QueueReportBuilderTest.php`

**Step 6: Commit**

```bash
git add app/Actions/Queue app/Models/QueueActivity.php app/Support/Dashboard app/Support/Reports/QueueReportBuilder.php tests/Feature/Audit/QueueAuditLogTest.php tests/Feature/Officer/CounterQueueWorkflowTest.php tests/Unit/Reports/QueueReportBuilderTest.php
git commit -m "feat: add queue audit metrics for role dashboards"
```

## Task 4: Petugas Dashboard sebagai Workstation

**Files:**
- Create: `resources/views/components/dashboard/petugas-dashboard.blade.php`
- Create: `app/Livewire/Dashboard/PetugasDashboard.php`
- Modify: `app/Http/Controllers/OfficerQueueController.php`
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Modify: `resources/views/layouts/app/header.blade.php`
- Test: `tests/Feature/Officer/CounterQueueWorkflowTest.php`
- Test: `tests/Feature/Dashboard/PtspDashboardTest.php`

**Step 1: Write failing tests for workstation dashboard**

Tambahkan assertion bahwa dashboard petugas menampilkan:
- tiket aktif;
- tombol `Panggil Berikutnya`, `Proses Layanan`, `Panggil Ulang`, `Lewati`, `Selesai`;
- daftar skip layanan;
- statistik “jumlah pihak yang dilayani hari ini”.

**Step 2: Run targeted tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Dashboard/PtspDashboardTest.php tests/Feature/Officer/CounterQueueWorkflowTest.php`

**Step 3: Implement Livewire workstation component**

- Tampilkan satu panel tiket aktif sebagai fokus utama.
- Tampilkan daftar skip layanan yang dapat di-recall.
- Gunakan Flux card, button, badge, separator, callout.
- Pastikan tombol disable/loading state aman untuk aksi berulang.

**Step 4: Implement navigation clarity**

- Ubah label menu agar petugas melihat entry yang sesuai konteks kerja.
- Jangan arahkan petugas ke halaman admin generik.

**Step 5: Run targeted tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Dashboard/PtspDashboardTest.php tests/Feature/Officer/CounterQueueWorkflowTest.php`

**Step 6: Commit**

```bash
git add app/Livewire/Dashboard/PetugasDashboard.php resources/views/components/dashboard/petugas-dashboard.blade.php resources/views/layouts/app/sidebar.blade.php resources/views/layouts/app/header.blade.php tests/Feature/Dashboard/PtspDashboardTest.php tests/Feature/Officer/CounterQueueWorkflowTest.php
git commit -m "feat: turn petugas dashboard into service workstation"
```

## Task 5: Monitor Dashboard untuk Pimpinan

**Files:**
- Create: `resources/views/components/dashboard/monitor-dashboard.blade.php`
- Create: `app/Livewire/Dashboard/MonitorDashboard.php`
- Create: `app/Support/Dashboard/MonitorStats.php`
- Modify: `app/Http/Controllers/Report/QueueReportController.php`
- Test: `tests/Feature/Reports/QueueReportPageTest.php`
- Test: `tests/Feature/Dashboard/PtspDashboardTest.php`

**Step 1: Write failing tests for monitor analytics**

Tambahkan assertion bahwa monitor melihat:
- jumlah pihak yang dilayani per petugas;
- jumlah per layanan;
- backlog/waiting by service;
- metrik throughput hari ini.

**Step 2: Run targeted tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Reports/QueueReportPageTest.php tests/Feature/Dashboard/PtspDashboardTest.php`

**Step 3: Implement aggregation service**

- Buat query agregat per petugas dan per layanan.
- Siapkan data matrix/table untuk tampilan monitor.
- Hindari N+1; gunakan eager load atau agregasi terpisah yang jelas.

**Step 4: Implement monitor dashboard UI**

- Gunakan kartu KPI untuk total utama.
- Gunakan table/matrix Flux untuk petugas x layanan.
- Tambahkan empty state jika belum ada aktivitas hari itu.

**Step 5: Run targeted tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Reports/QueueReportPageTest.php tests/Feature/Dashboard/PtspDashboardTest.php`

**Step 6: Commit**

```bash
git add app/Livewire/Dashboard/MonitorDashboard.php app/Support/Dashboard/MonitorStats.php resources/views/components/dashboard/monitor-dashboard.blade.php app/Http/Controllers/Report/QueueReportController.php tests/Feature/Reports/QueueReportPageTest.php tests/Feature/Dashboard/PtspDashboardTest.php
git commit -m "feat: add monitor dashboard for service throughput"
```

## Task 6: Admin Dashboard untuk Health Aplikasi

**Files:**
- Create: `resources/views/components/dashboard/admin-dashboard.blade.php`
- Create: `app/Livewire/Dashboard/AdminDashboard.php`
- Create: `app/Support/Dashboard/AdminStats.php`
- Modify: `app/Models/User.php`
- Modify: `app/Models/QueueTicket.php`
- Test: `tests/Feature/Dashboard/PtspDashboardTest.php`

**Step 1: Write failing tests for admin health widgets**

Tambahkan assertion bahwa admin melihat:
- booking berhasil/gagal hari ini;
- jumlah tiket dibuat/batal/selesai;
- shortcut ke layanan, loket, user, role, dan izin layanan;
- ringkasan error atau failure antrean.

**Step 2: Run targeted tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Dashboard/PtspDashboardTest.php`

**Step 3: Implement admin stats service**

- Sediakan data observabilitas minimum dari database yang sudah ada.
- Jika log error belum terstruktur di DB, tampilkan ringkasan failure operasional yang memang tersedia sekarang.
- Jangan over-engineer event monitoring di tahap pertama.

**Step 4: Implement admin dashboard UI**

- Baris atas untuk KPI health.
- Panel tengah untuk aktivitas pengguna layanan dan failure antrean.
- Panel bawah untuk quick actions ke modul manajemen.

**Step 5: Run targeted tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Dashboard/PtspDashboardTest.php`

**Step 6: Commit**

```bash
git add app/Livewire/Dashboard/AdminDashboard.php app/Support/Dashboard/AdminStats.php resources/views/components/dashboard/admin-dashboard.blade.php tests/Feature/Dashboard/PtspDashboardTest.php
git commit -m "feat: add admin health dashboard"
```

## Task 7: UI Manajemen Layanan dan Loket

**Files:**
- Modify: `app/Http/Controllers/Admin/ServiceManagementController.php`
- Modify: `app/Http/Controllers/Admin/CounterManagementController.php`
- Create: `resources/views/pages/admin/layanan/index.blade.php`
- Create: `resources/views/pages/admin/loket/index.blade.php`
- Optional Create: `app/Livewire/Admin/ServicesManager.php`
- Optional Create: `app/Livewire/Admin/CountersManager.php`
- Test: `tests/Feature/Admin/ManageServicesTest.php`
- Test: `tests/Feature/Admin/ManageCountersTest.php`

**Step 1: Write failing tests for real admin pages**

Tambahkan assertion bahwa halaman admin:
- merender layout aplikasi, bukan plain text;
- menampilkan tabel data;
- punya form create/update yang usable.

**Step 2: Run targeted tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Admin/ManageServicesTest.php tests/Feature/Admin/ManageCountersTest.php`

**Step 3: Replace text responses with views**

- Ubah controller index dari `response()` menjadi `view()`.
- Gunakan Flux table, modal, button, field, toggle, badge.

**Step 4: Run targeted tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Admin/ManageServicesTest.php tests/Feature/Admin/ManageCountersTest.php`

**Step 5: Commit**

```bash
git add app/Http/Controllers/Admin/ServiceManagementController.php app/Http/Controllers/Admin/CounterManagementController.php resources/views/pages/admin tests/Feature/Admin/ManageServicesTest.php tests/Feature/Admin/ManageCountersTest.php
git commit -m "feat: add admin management pages for services and counters"
```

## Task 8: UI Manajemen User, Role, dan Izin Layanan

**Files:**
- Create: `app/Http/Controllers/Admin/UserManagementController.php`
- Create: `app/Http/Requests/StoreUserRequest.php`
- Create: `app/Http/Requests/UpdateUserRequest.php`
- Create: `resources/views/pages/admin/users/index.blade.php`
- Create: `resources/views/pages/admin/roles/index.blade.php` atau gabungkan sesuai desain akhir
- Modify: `routes/web.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/Admin/ManageUsersTest.php`
- Test: `tests/Feature/Auth/PtspAuthorizationTest.php`

**Step 1: Write failing tests for user/role management**

Tambahkan test bahwa admin dapat:
- melihat daftar user;
- mengubah role user;
- mengatur layanan yang diizinkan per petugas;
- user non-admin tetap dilarang mengakses.

**Step 2: Run targeted tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Admin/ManageUsersTest.php tests/Feature/Auth/PtspAuthorizationTest.php`

**Step 3: Implement minimal CRUD and permission assignment**

- Gunakan Form Request untuk validasi.
- Simpan role dan relasi layanan per user.
- Utamakan alur edit role dan izin layanan; hindari fitur tambahan yang belum diminta.

**Step 4: Run targeted tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Admin/ManageUsersTest.php tests/Feature/Auth/PtspAuthorizationTest.php`

**Step 5: Commit**

```bash
git add app/Http/Controllers/Admin/UserManagementController.php app/Http/Requests/StoreUserRequest.php app/Http/Requests/UpdateUserRequest.php app/Models/User.php resources/views/pages/admin routes/web.php tests/Feature/Admin/ManageUsersTest.php tests/Feature/Auth/PtspAuthorizationTest.php
git commit -m "feat: add admin user role and service permission management"
```

## Task 9: Polishing, Accessibility, dan Verifikasi Akhir

**Files:**
- Modify: file yang terdampak dari task sebelumnya
- Test: semua file test yang berubah

**Step 1: Run formatter**

Run: `vendor/bin/pint --dirty --format agent`

Expected: `pass`

**Step 2: Run focused regression suite**

Run:

```bash
php artisan test --compact \
  tests/Feature/DashboardTest.php \
  tests/Feature/Dashboard/PtspDashboardTest.php \
  tests/Feature/Officer/CounterQueueWorkflowTest.php \
  tests/Feature/Audit/QueueAuditLogTest.php \
  tests/Feature/Reports/QueueReportPageTest.php \
  tests/Feature/Admin/ManageServicesTest.php \
  tests/Feature/Admin/ManageCountersTest.php \
  tests/Feature/Admin/ManageUsersTest.php \
  tests/Feature/Navigation/PtspNavigationTest.php \
  tests/Feature/Auth/PtspAuthorizationTest.php \
  tests/Unit/Reports/QueueReportBuilderTest.php
```

Expected: PASS

**Step 3: Run full suite**

Run: `php artisan test --compact`

Expected: PASS

**Step 4: Manual smoke check**

Verifikasi manual minimal:
- login sebagai petugas;
- klik `Panggil Berikutnya` tanpa konflik;
- skip tiket lalu recall dari daftar skip;
- login sebagai monitor dan cek matrix kinerja;
- login sebagai admin dan buka seluruh shortcut manajemen.

## Urutan Eksekusi Prioritas
1. Task 1: Role-aware dashboard shell
2. Task 2: Eligibility layanan per user + dispatch atomic
3. Task 3: Audit trail dan statistik petugas
4. Task 4: Petugas dashboard workstation
5. Task 5: Monitor dashboard
6. Task 6: Admin dashboard
7. Task 7: UI manajemen layanan dan loket
8. Task 8: UI manajemen user, role, dan izin layanan
9. Task 9: Polishing dan verifikasi akhir

## Definisi Selesai
- Dashboard berbeda nyata per role dan relevan dengan tugas pengguna.
- `Panggil Berikutnya` bersifat FIFO, atomic, dan bebas konflik antar petugas.
- Terdapat tracing jumlah pihak yang dilayani per petugas.
- Pimpinan dapat melihat distribusi layanan per petugas dan per layanan.
- Admin memiliki health overview aplikasi dan shortcut ke modul manajemen.
- Halaman admin tidak lagi berupa respons plain text.
- Seluruh test terkait area yang berubah lulus.
