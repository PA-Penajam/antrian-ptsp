# Implementation Plan: Kiosk UI/UX Improvement

**Tanggal**: 8 Maret 2026
**Module**: Kiosk (`/kiosk`)
**Status**: Draft — Menunggu Approval

---

## Ringkasan

Plan ini mencakup perbaikan UI/UX modul Kiosk berdasarkan hasil review yang telah dilakukan. Perbaikan diprioritaskan berdasarkan severity dan impact terhadap pengalaman pengguna di perangkat kiosk (touchscreen, mode portrait).

---

## Daftar Task

### P0 — BLOCKER (Harus diperbaiki sebelum task lain)

#### Task 1: Fix Bug `<head>` Kosong pada Halaman Booking

**Problem:**
- Halaman `/kiosk` (setelah login) memiliki `<head>` kosong — tidak ada CSS, JS, maupun Livewire
- `window.Livewire` adalah `undefined` → semua `wire:click` tidak berfungsi
- Wizard 4 langkah **sepenuhnya mati** (tidak bisa diklik, tidak ada styling)

**Root Cause:**
- `KioskBooking.php` menggunakan `#[Layout('layouts.kiosk')]` (Livewire layout attribute)
- `layouts/kiosk.blade.php` memiliki `@props(['title' => null])` di baris pertama
- `@props` adalah directive Blade Component, **bukan** untuk Livewire layout rendering
- Livewire `#[Layout()]` mengharapkan plain Blade file dengan `{{ $slot }}` — tanpa `@props`
- Bukti: `layouts/public.blade.php` (tanpa `@props`) berhasil dipakai oleh `QueueDisplay.php`
- Dampak tambahan: `layouts/tv-display.blade.php` juga punya `@props` dan kemungkinan bug yang sama pada `TvDisplay.php`

**Solusi:**
- Hapus `@props(['title' => null])` dari `resources/views/layouts/kiosk.blade.php`
- Ganti referensi `$title` dengan `$title ?? null` (seperti pola di docs Livewire)
- Lakukan hal yang sama untuk `resources/views/layouts/tv-display.blade.php`
- Pastikan component wrapper `components/layouts/kiosk.blade.php` tetap pakai `@props` (karena ini memang Blade component dan dipakai oleh `login.blade.php` via `<x-layouts.kiosk>`)

**File yang diubah:**
- `resources/views/layouts/kiosk.blade.php` — hapus `@props`, ganti `$title` → `$title ?? null`
- `resources/views/layouts/tv-display.blade.php` — hapus `@props`, ganti `$title` → `$title ?? null`

**Kriteria selesai:**
- [ ] Halaman `/kiosk` (setelah login) me-render CSS dan JS dengan benar
- [ ] `window.Livewire` terdefinisi di browser console
- [ ] Klik kartu layanan berhasil navigasi ke Step 2
- [ ] Halaman login `/kiosk/login` tetap berfungsi (tidak terdampak)
- [ ] `php artisan test --compact --filter=Kiosk` — semua test pass
- [ ] TV Display (`/tv`) juga berfungsi dengan benar (jika ada)

---

### P1 — PENTING (UX kiosk yang signifikan)

#### Task 2: Tambah Toggle Show/Hide Password di Halaman Login

**Problem:**
- Input password tidak memiliki toggle visibility
- Pada perangkat kiosk (touchscreen tanpa keyboard fisik), user tidak bisa memverifikasi input
- Ini critical UX issue untuk kiosk karena typo sangat umum terjadi pada virtual keyboard

**Solusi:**
- Gunakan `flux:input` dengan atribut `type="password"` dan tambahkan eye icon toggle
- Atau gunakan `flux:input` dengan `viewable` attribute jika Flux UI Pro mendukungnya (perlu cek docs)
- Toggle harus berukuran besar (touch-friendly, minimal 48x48px)

**File yang diubah:**
- `resources/views/pages/kiosk/login.blade.php` — modifikasi field password

**Kriteria selesai:**
- [ ] Ada tombol eye icon di dalam field password
- [ ] Klik toggle mengubah type antara `password` dan `text`
- [ ] Toggle berukuran touch-friendly (≥48px)
- [ ] Halaman login tetap berfungsi normal (submit form, error display)

---

#### Task 3: Optimasi Layout Portrait untuk Login

**Problem:**
- Layout 2 kolom (`grid-cols-[minmax(0,1.15fr)_minmax(24rem,30rem)]`) tetap side-by-side di mode portrait (1080×1920)
- Panel informasi kiri terlalu banyak teks dan mengambil ruang yang seharusnya untuk form
- Pada kiosk portrait, user harus scroll atau area form terlalu sempit

**Solusi:**
- Sembunyikan panel informasi kiri di viewport portrait/sempit (`hidden lg:flex` sudah ada, tapi breakpoint mungkin kurang pas untuk 1080px width)
- Pastikan form login tampil center di full-width saat panel kiri tersembunyi
- Pertimbangkan untuk menambahkan ringkasan singkat (1-2 baris) di atas form sebagai pengganti panel kiri

**File yang diubah:**
- `resources/views/pages/kiosk/login.blade.php` — sesuaikan breakpoint dan layout

**Kriteria selesai:**
- [ ] Di viewport 1080px wide (portrait kiosk), form login tampil center full-width
- [ ] Panel informasi tersembunyi di portrait
- [ ] Di viewport ≥1280px (landscape/desktop), layout 2 kolom tetap tampil
- [ ] Tidak ada horizontal scroll pada viewport kiosk

---

#### Task 4: Perbaiki Kontras Teks (WCAG AA Compliance)

**Problem:**
- Beberapa teks menggunakan `text-zinc-400`, `text-zinc-500`, `text-zinc-600` di background gelap
- Kemungkinan gagal WCAG AA minimum contrast ratio (4.5:1 untuk normal text, 3:1 untuk large text)
- Pada layar kiosk dengan pencahayaan ambient beragam, teks bisa sulit dibaca

**Solusi:**
- Audit semua warna teks di halaman kiosk (login + booking wizard)
- Naikkan brightness teks sekunder: `text-zinc-500` → `text-zinc-400` atau `text-zinc-300`
- Khusus label dan informasi penting: minimal `text-zinc-300`
- Placeholder text bisa tetap lebih subtle tapi minimal `text-zinc-500`

**File yang diubah:**
- `resources/views/pages/kiosk/login.blade.php`
- `resources/views/livewire/kiosk-booking.blade.php`

**Kriteria selesai:**
- [ ] Semua teks informatif memenuhi WCAG AA contrast ratio
- [ ] Label form minimal `text-zinc-300`
- [ ] Teks deskripsi minimal `text-zinc-400`
- [ ] Visual tetap konsisten dan tidak "washed out"

---

### P2 — IMPROVEMENT (UX enhancement)

#### Task 5: Sederhanakan Panel Informasi Login

**Problem:**
- Panel kiri memiliki 3 card info (Fokus, Sentuh, Aman) + heading + paragraph
- Terlalu verbose untuk flow login yang seharusnya cepat
- Informasi ini lebih relevan untuk admin, bukan untuk operator yang login setiap hari

**Solusi:**
- Kurangi content panel kiri menjadi: logo/icon institusi + nama institusi + 1 baris tagline
- Hapus 3 card info atau jadikan lebih compact (single row badges)
- Fokuskan visual pada branding, bukan penjelasan fitur

**File yang diubah:**
- `resources/views/pages/kiosk/login.blade.php`

**Kriteria selesai:**
- [ ] Panel kiri menampilkan branding yang clean dan minimal
- [ ] Tidak ada wall of text
- [ ] Visual tetap premium/polished

---

#### Task 6: Implementasi Barcode di Ticket (Step 4)

**Problem:**
- Step 4 (Tiket Berhasil Dibuat) menampilkan placeholder barcode: `<div class="h-16 w-48 rounded bg-zinc-700"></div>`
- Barcode belum diimplementasi — user tidak bisa scan tiket

**Solusi:**
- Generate barcode/QR code dari `ticket_number` menggunakan library server-side atau client-side
- Opsi 1: Gunakan package PHP seperti `picqer/php-barcode-generator` (server-side)
- Opsi 2: Gunakan JavaScript library untuk generate di client
- Tampilkan barcode Code128 atau QR code yang scannable
- **Catatan**: Ini memerlukan approval untuk install dependency baru

**File yang diubah:**
- `resources/views/livewire/kiosk-booking.blade.php` — ganti placeholder
- Mungkin `app/Livewire/KioskBooking.php` — generate barcode data
- `composer.json` — jika install package baru (perlu approval)

**Kriteria selesai:**
- [ ] Barcode/QR code muncul di tiket
- [ ] Barcode berisi `ticket_number` yang scannable
- [ ] Visual barcode proportional dan readable

---

#### Task 7: Perbaiki Posisi dan Visibility Tombol Logout

**Problem:**
- Tombol "Keluar" ada di kanan bawah dengan style `variant="ghost"` — sangat subtle
- Pada kiosk, operator/admin mungkin kesulitan menemukan tombol logout
- Risiko: session kiosk tetap terbuka tanpa sengaja

**Solusi:**
- Pindahkan tombol logout ke posisi yang lebih prominent (kiri bawah atau top bar)
- Tambahkan visual yang lebih jelas (border, background color)
- Pertimbangkan menambah label konfirmasi sebelum logout

**File yang diubah:**
- `resources/views/livewire/kiosk-booking.blade.php`

**Kriteria selesai:**
- [ ] Tombol logout mudah ditemukan secara visual
- [ ] Posisi konsisten di semua step
- [ ] Ada konfirmasi sebelum logout (opsional, tergantung keputusan)

---

### P3 — NICE TO HAVE

#### Task 8: Aksesibilitas — Font Size Control

**Problem:**
- Tidak ada opsi untuk memperbesar ukuran teks
- Pengunjung lansia atau yang berkebutuhan khusus mungkin kesulitan membaca

**Solusi:**
- Tambahkan toggle font size (Normal / Besar) di halaman booking
- Gunakan CSS custom property atau class toggle untuk scale teks
- Simpan preferensi di session (bukan localStorage, karena kiosk shared device)

**Kriteria selesai:**
- [ ] Ada toggle ukuran teks di halaman kiosk
- [ ] Minimal 2 size: Normal dan Besar
- [ ] Preferensi tidak persist setelah session/reset wizard

---

## Urutan Eksekusi

```
Task 1 (P0) ─── BLOCKER, harus selesai dulu
    │
    ├── Task 2 (P1) ── bisa parallel
    ├── Task 3 (P1) ── bisa parallel
    └── Task 4 (P1) ── bisa parallel
            │
            ├── Task 5 (P2) ── setelah P1 selesai
            ├── Task 6 (P2) ── butuh approval dependency
            └── Task 7 (P2) ── setelah P1 selesai
                    │
                    └── Task 8 (P3) ── terakhir/opsional
```

---

## Estimasi Effort

| Task | Effort | Dependency |
|------|--------|------------|
| Task 1 — Fix `<head>` kosong | ~15 menit | Tidak ada |
| Task 2 — Toggle password | ~15 menit | Task 1 |
| Task 3 — Layout portrait | ~30 menit | Task 1 |
| Task 4 — Kontras WCAG | ~20 menit | Task 1 |
| Task 5 — Sederhanakan panel | ~20 menit | Task 3, 4 |
| Task 6 — Barcode | ~45 menit | Task 1, approval dependency |
| Task 7 — Tombol logout | ~15 menit | Task 1 |
| Task 8 — Font size control | ~30 menit | Task 7 |
| **Total** | **~3.5 jam** | |

---

## Catatan Penting

1. **Task 1 adalah BLOCKER absolut** — tanpa fix ini, wizard booking tidak bisa digunakan sama sekali
2. **Task 6 memerlukan approval** untuk install package barcode baru
3. **TV Display (`layouts/tv-display.blade.php`) juga terdampak bug yang sama** — akan di-fix bersamaan di Task 1
4. Semua perubahan UI mengikuti **design system yang sudah ada** (Flux UI Pro, Tailwind CSS v4, dark glassmorphism theme)
5. Constraint: **Tidak ada dark/light toggle di kiosk** (sesuai aturan project)
6. Constraint: **Kiosk booking harus menggunakan `channel='walk_in_kiosk'`** — tidak diubah

---

## Acceptance Criteria (Keseluruhan)

- [ ] Halaman booking `/kiosk` berfungsi penuh (CSS, JS, Livewire loaded)
- [ ] Wizard 4 langkah bisa dilalui dari awal hingga tiket tercetak
- [ ] Login page memiliki toggle show/hide password
- [ ] Layout responsive untuk portrait (1080×1920) dan landscape
- [ ] Semua teks memenuhi WCAG AA contrast ratio
- [ ] Test suite kiosk tetap pass: `php artisan test --compact --filter=Kiosk`
- [ ] `vendor/bin/pint --dirty --format agent` — no formatting issues
