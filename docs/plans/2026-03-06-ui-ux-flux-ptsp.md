# UI/UX PTSP Berbasis Flux UI Pro Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Menerapkan UI/UX production-ready untuk seluruh alur PTSP (public, frontdesk, officer, admin, monitor) dengan Flux UI Pro yang konsisten, responsif, dan usable.

**Architecture:** Pendekatan bertahap dimulai dari fondasi layout + design tokens, lalu migrasi halaman prioritas tinggi ke komponen Flux (`field`, `input`, `select`, `button`, `table`, `badge`, `card`, `modal`, `toast`). Alur submit memakai PRG pattern (redirect + flash state), dan area dinamis memakai Livewire/Flux loading states.

**Tech Stack:** Laravel 12, Livewire 4, Flux UI Pro 2.x, Tailwind CSS 4, Pest 4.

## Ruang Lingkup
- Public: `/`, `/antrian`, `/antrian/cek`, `/display`
- Internal: `/dashboard`, `/frontdesk/antrian`, `/petugas/loket/{counter}`, `/admin/layanan`, `/admin/loket`, `/laporan/antrian`
- Shared UI system: layout, komponen status, empty/loading/error state, navigasi

## Task 1: Fondasi UI System PTSP

**Files:**
- Modify: `resources/css/app.css`
- Modify: `resources/views/partials/head.blade.php`
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Create: `resources/views/components/ptsp/page-header.blade.php`
- Create: `resources/views/components/ptsp/status-badge.blade.php`
- Test: `tests/Feature/Navigation/PtspNavigationTest.php`

1. Definisikan token visual PTSP (warna status antrean, radius, spacing, elevasi ringan) di `app.css` tanpa breaking Flux defaults.
2. Rapikan metadata head (title pattern per halaman, branding PTSP, favicon konsisten).
3. Bersihkan item navigasi starter kit (repo/docs) dan ganti dengan menu domain PTSP per role.
4. Buat komponen reusable `page-header` dan `status-badge` agar konsisten lintas halaman.
5. Tambah/ubah test navigasi role-based agar menu sesuai hak akses.

## Task 2: Rebuild Homepage dan Public Entry Flow

**Files:**
- Modify: `resources/views/welcome.blade.php`
- Modify: `resources/views/pages/public/antrian/booking.blade.php`
- Modify: `resources/views/pages/public/antrian/lookup.blade.php`
- Modify: `app/Http/Controllers/PublicQueueController.php`
- Test: `tests/Feature/Public/PublicQueueBookingPageTest.php`
- Test: `tests/Feature/Public/PublicQueueLookupPageTest.php`

1. Ubah homepage dari konten starter ke landing PTSP (CTA: ambil antrean, cek tiket, display).
2. Refactor form booking dengan Flux fields (`flux:field`, `flux:label`, `flux:input`, `flux:select`, `flux:textarea`, `flux:error`).
3. Tambahkan state sukses/error/empty yang jelas menggunakan `flux:callout` + `status-badge`.
4. Terapkan PRG pattern di `storeBooking` (redirect ke GET + flash tiket), hindari submit ganda saat refresh.
5. Rebuild lookup dengan layout card + hasil pencarian yang mudah dipindai.
6. Update test fitur untuk validasi elemen UI penting, flash state, dan redirect behavior.

## Task 3: Halaman Display Antrean (Monitor Publik)

**Files:**
- Modify: `resources/views/pages/display/index.blade.php`
- Modify: `routes/web.php`
- Optional Create: `app/Livewire/Display/QueueBoard.php`
- Test: `tests/Feature/Public/QueueDisplayTest.php`

1. Ubah UI display menjadi board dua panel: “Sedang Dipanggil” dan “Riwayat Panggilan”.
2. Gunakan `flux:card`, `flux:badge`, `flux:separator`, tipografi besar untuk nomor tiket.
3. Tambahkan auto-refresh terukur (Livewire polling atau interval reload ringan) agar data up-to-date.
4. Tambahkan empty state yang informatif saat belum ada panggilan.
5. Pastikan mode TV (kontras tinggi, jarak baca jauh, minim noise visual).

## Task 4: Frontdesk Operasional End-to-End

**Files:**
- Modify: `resources/views/pages/frontdesk/antrian.blade.php`
- Modify: `app/Http/Controllers/FrontdeskQueueController.php`
- Optional Create: `app/Livewire/Frontdesk/QueueDesk.php`
- Test: `tests/Feature/Frontdesk/AssistedQueueEntryTest.php`
- Test: `tests/Feature/Frontdesk/QueueCheckInTest.php`

1. Bangun form pembuatan tiket manual dengan validasi inline Flux.
2. Tambahkan modul check-in tiket (search tiket + action button) dalam satu layar.
3. Tambahkan feedback aksi (success/warning/error) dan loading states (`wire:loading` / data-loading).
4. Terapkan PRG atau Livewire action state untuk mencegah double action.
5. Update test untuk skenario happy path + invalid input + race basic (aksi berulang).

## Task 5: Officer Workspace (Panggil/Recall/Skip/Selesai/Batal)

**Files:**
- Modify: `app/Http/Controllers/OfficerQueueController.php`
- Create/Modify: `resources/views/pages/officer/loket/show.blade.php`
- Optional Create: `app/Livewire/Officer/CounterQueuePanel.php`
- Test: `tests/Feature/Officer/CounterQueueWorkflowTest.php`

1. Ganti response teks menjadi dashboard loket operasional.
2. Tampilkan antrean aktif + tiket berikutnya dalam komponen card/table Flux.
3. Sediakan action buttons yang aman (confirm modal untuk cancel/skip).
4. Gunakan badge status konsisten dan jejak aksi singkat untuk petugas.
5. Pastikan authorisasi pool tetap terjaga dan tervalidasi oleh test workflow.

## Task 6: Admin UI (Layanan & Loket)

**Files:**
- Modify: `app/Http/Controllers/Admin/ServiceManagementController.php`
- Modify: `app/Http/Controllers/Admin/CounterManagementController.php`
- Create: `resources/views/pages/admin/layanan/index.blade.php`
- Create: `resources/views/pages/admin/loket/index.blade.php`
- Optional Create: `app/Livewire/Admin/ServicesManager.php`
- Optional Create: `app/Livewire/Admin/CountersManager.php`
- Test: `tests/Feature/Admin/ManageServicesTest.php`
- Test: `tests/Feature/Admin/ManageCountersTest.php`

1. Ganti output plain text jadi halaman manajemen tabel + form modal.
2. Gunakan `flux:table` untuk listing dan `flux:modal` untuk create/edit.
3. Tambahkan filter dasar (aktif/nonaktif, pool) untuk efisiensi kerja admin.
4. Standarkan validasi error messages dan feedback aksi.
5. Update test CRUD utama dan authorization.

## Task 7: Laporan Monitor UI

**Files:**
- Modify: `resources/views/pages/laporan/antrian/index.blade.php`
- Modify: `app/Http/Controllers/Report/QueueReportController.php`
- Test: `tests/Feature/Reports/QueueReportPageTest.php`

1. Ubah laporan dari paragraf polos ke ringkasan metrik + tabel grup.
2. Tambahkan filter tanggal yang jelas dan CTA ekspor (jika endpoint tersedia).
3. Gunakan komponen visual Flux (`card`, `table`, `badge`) untuk readability.
4. Update test render data per kategori dan empty state.

## Task 8: Accessibility, Responsiveness, dan Konsistensi

**Files:**
- Modify: semua view yang terdampak di atas
- Test: tambah assertion pada test feature terkait

1. Pastikan label-input terhubung, fokus keyboard terlihat, dan hierarchy heading benar.
2. Validasi breakpoint mobile/tablet/desktop untuk halaman public + internal.
3. Samakan bahasa mikrocopy UI (Indonesia formal, ringkas, operasional).
4. Audit kontras warna status antrean untuk kondisi terang/gelap.

## Task 9: Verifikasi dan Stabilization

**Files:**
- Modify: file yang gagal lint/test

1. Jalankan format: `vendor/bin/pint --dirty --format agent`.
2. Jalankan test minimum per area yang diubah dengan `php artisan test --compact <file-test>`.
3. Jalankan smoke test route utama role-based.
4. Final pass UI manual untuk 6 persona: publik, frontdesk, officer, monitor, admin, pengguna non-login.

## Urutan Eksekusi Prioritas
1. Task 1 (Fondasi)
2. Task 2 (Public booking/lookup)
3. Task 4 (Frontdesk)
4. Task 5 (Officer)
5. Task 6 (Admin)
6. Task 3 (Display)
7. Task 7 (Laporan)
8. Task 8 (A11y/Responsive)
9. Task 9 (Verifikasi)

## Definisi Selesai (DoD)
- Tidak ada lagi halaman operasional yang plain text/prototype HTML.
- Semua alur utama memakai komponen Flux UI Pro dan konsisten visualnya.
- Submit actions aman dari double submit (PRG atau Livewire state guard).
- Test feature terkait area yang diubah lulus.
- Role-based UX jelas: user hanya melihat aksi yang relevan.

## Milestone Mingguan + Estimasi Effort

### Minggu 1: Fondasi + Public Core
- Task 1 `Fondasi UI System PTSP` - **2.0 hari**
- Task 2 `Homepage + Booking + Lookup` - **3.0 hari**
- Total minggu: **5.0 hari**

Deliverable:
- Design token PTSP aktif
- Navigasi internal bersih dari starter links
- `/`, `/antrian`, `/antrian/cek` sudah Flux UI Pro + PRG pattern

### Minggu 2: Display + Frontdesk
- Task 3 `Display Antrean` - **1.5 hari**
- Task 4 `Frontdesk Operasional` - **3.0 hari**
- Total minggu: **4.5 hari**

Deliverable:
- `/display` siap mode monitor (auto-refresh + empty state)
- `/frontdesk/antrian` siap create tiket + check-in dengan feedback/loading state

### Minggu 3: Officer + Admin
- Task 5 `Officer Workspace` - **2.5 hari**
- Task 6 `Admin UI Layanan & Loket` - **3.5 hari**
- Total minggu: **6.0 hari**

Deliverable:
- `/petugas/loket/{counter}` jadi workspace operasional lengkap
- `/admin/layanan` dan `/admin/loket` jadi UI manajemen berbasis Flux table/modal

### Minggu 4: Laporan + Quality Pass
- Task 7 `Laporan Monitor UI` - **1.5 hari**
- Task 8 `Accessibility + Responsiveness + Konsistensi` - **2.0 hari**
- Task 9 `Verifikasi + Stabilization` - **1.5 hari**
- Total minggu: **5.0 hari**

Deliverable:
- Halaman laporan usable dan konsisten visual
- Audit aksesibilitas + responsive selesai
- Pint + test suite area terdampak lulus

### Ringkasan Estimasi Per Task
1. Task 1 - **2.0 hari**
2. Task 2 - **3.0 hari**
3. Task 3 - **1.5 hari**
4. Task 4 - **3.0 hari**
5. Task 5 - **2.5 hari**
6. Task 6 - **3.5 hari**
7. Task 7 - **1.5 hari**
8. Task 8 - **2.0 hari**
9. Task 9 - **1.5 hari**

Total estimasi: **20.5 hari kerja** (sekitar **4 minggu** untuk 1 engineer full-time, dengan buffer ringan di Minggu 4).
