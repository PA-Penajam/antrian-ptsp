---
slug: resources-views-welcome-blade-php
primary_target: resources/views/welcome.blade.php
related_targets:
  - app/Http/Controllers/PublicQueueController.php
  - routes/web.php
mode: Persuade
---

# Surface Brief: Public Landing Page (Beranda PTSP)

## 1. Job and Audience
- **Visitor / Audience:** Masyarakat umum dan pencari keadilan yang membutuhkan layanan di PTSP Pengadilan Agama (pendaftaran perkara, konsultasi, pengambilan produk hukum, posbakum, informasi).
- **Context & Mindset:** Sering kali merasa terburu-buru, cemas akan antrian panjang di kantor, atau belum paham syarat dokumen yang harus dibawa.
- **Need:** Membutuhkan akses instan untuk mengambil tiket antrian online, mengecek status antrian hari ini, dan memastikan seluruh dokumen persyaratan sudah lengkap sebelum berangkat ke kantor pengadilan.
- **Mode:** **Persuade & Guide** — Menyambut pengunjung dengan tenang, mengarahkan ke tindakan utama tanpa hambatan, dan memberikan kepastian prosedur.

## 2. Outcome and Proof
- **Primary Actions:**
  1. Tombol CTA Utama: **Ambil Nomor Antrian** (mengarah ke `/antrian`).
  2. Tombol CTA Sekunder: **Cek Status Antrian** (mengarah ke `/antrian/cek`).
  3. Cek Persyaratan Berkas per Layanan (interaktif langsung di katalog).
- **Success Criteria:** Pengunjung langsung memahami cara mendaftar, mengetahui situasi antrian hari ini di PTSP, dan datang dengan berkas lengkap.
- **Product Truth & Real Evidence:** Data layanan, status loket, kuota harian, jam operasional, serta nama/logo institusi bersumber dinamis dari database dan `config/institution.php`.

## 3. Selected Direction
- **Visual Authority:** *The Digital Balai* ([DESIGN.md](file:///Volumes/Dev/Projects/antrian-ptsp/DESIGN.md)) — Aksen *Court Cyan* & *Oasis Emerald*, kartu putih bergradasi sejuk dengan bayangan ambient lembut, tipografi *Instrument Sans* yang jernih.
- **Structural Sequence:**
  1. **Hero Balai:** Header lapang dengan identitas instansi, heading persuasif, tombol aksi primer ganda, dan kartu info jam operasional.
  2. **Live Antrian Ringkas (New Feature Widget):** Mini-dashboard visual yang menampilkan total tiket hari ini & status loket yang sedang memanggil antrian.
  3. **Katalog Layanan Interaktif:** Grid kartu layanan modern dengan badge kuota harian, status kanal (Booking Online / Walk-in), accordion/modal persyaratan berkas instan, dan tombol langsung "Pilih Layanan".
  4. **Panduan 3 Langkah Pengunjung:** Alur visual sederhana (1. Pilih Layanan, 2. Lengkapi Syarat, 3. Tunjukkan Tiket di PTSP).
  5. **Quick Access Petugas:** Tautan halus di bagian bawah untuk login staf/petugas tanpa mengganggu fokus publik.

## 4. Scope and Boundaries
- **Targets:**
  - `resources/views/welcome.blade.php` (Redesign antarmuka landing page)
  - `app/Http/Controllers/PublicQueueController.php` (Menyediakan query ringkas status antrian hari ini jika dibutuhkan widget live)
- **Fidelity:** Production-ready Blade template + Flux UI Pro + Tailwind CSS v4 + Alpine.js.
- **Untouched:** Layout publik `resources/views/layouts/public.blade.php`, halaman alur booking `/antrian`, lookup `/antrian/cek`, dan autentikasi staf tetap utuh.
- **Anti-goals:** Tidak menyematkan jargon hukum teknis yang membingungkan, tidak menggunakan animasi berlebih yang memperlambat loading di ponsel berspesifikasi rendah.

## 5. States and Ranges
- **Daftar Layanan:** Menampilkan layanan aktif; menyediakan empty state yang ramah dan bersahabat jika belum ada layanan terdaftar.
- **Live Antrian Widget:**
  - *Jam Layanan Aktif:* Menampilkan nomor tiket terkini yang sedang dipanggil per layanan/loket.
  - *Di Luar Jam Operasional / Belum Ada Antrian:* Menampilkan status "Belum ada antrian aktif hari ini" dengan panduan jadwal pelayanan.
- **Kuota Layanan:** Menampilkan sisa/total kuota harian secara dinamis.

## 6. Interaction and Layout
- **Mobile First & Responsive:** Tata letak grid 1 kolom di ponsel, 2 kolom di tablet, dan 3 kolom di desktop.
- **Instant Requirement Preview:** Tombol "Cek Persyaratan" pada setiap kartu layanan membuka rincian berkas secara instan menggunakan Alpine.js (`x-collapse` / dialog ringan) tanpa reload halaman.
- **Touch-Friendly Target:** Semua tombol CTA memiliki tinggi minimal 48px dan padding sentuh yang nyaman di layar sentuh.

## 7. Constraints and Open Decisions
- **Stack:** Laravel 12, Blade, Tailwind CSS v4, Flux UI Pro, Alpine.js.
- **Language & Tone:** Bahasa Indonesia yang ramah, sopan, dan jelas (*kasual & ramah*).
- **Branding:** Dinamis dari konfigurasi `config('institution.*')`.
