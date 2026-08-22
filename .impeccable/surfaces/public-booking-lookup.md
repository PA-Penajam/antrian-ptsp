---
slug: public-booking-lookup
primary_target: resources/views/pages/public/antrian/booking.blade.php
related_targets:
  - resources/views/pages/public/antrian/lookup.blade.php
  - resources/views/pages/public/antrian/confirmation.blade.php
  - app/Http/Controllers/PublicQueueController.php
mode: Operate
---

# Surface Brief: Public Booking, Status Lookup & Confirmation

## 1. Job and Audience
- **Visitor / Audience:** Masyarakat pencari keadilan dan pemohon layanan PTSP (Pendaftaran Perkara, Konsultasi Posbakum, Pengambilan Akta/Salinan Putusan, Informasi/Pengaduan).
- **Context & Mindset:** Ingin kepastian jadwal, menghindari antre fisik berjam-jam, serta memastikan dokumen yang dibawa sudah lengkap.
- **Mode:** **Operate & Reassure** (Pemanduan alur pendaftaran berjenjang dan kepastian status tiket).

## 2. Outcome and Proof
- **Primary Actions:**
  1. *Booking Wizard (3 Langkah):* Memilih layanan aktif, mengecek sisa kuota hari ini, mengisi NIK/Nama/No HP/Wilayah, dan konfirmasi syarat berkas.
  2. *Status Lookup:* Mencari posisi nomor antrian secara real-time dengan status loket saat ini dan instruksi kedatangan.
  3. *Signed Confirmation / E-Ticket:* Menampilkan tanda terima e-tiket resmi bertanda tangan digital dengan QR Code, nomor besar, dan tombol cetak/unduh.
- **Product Truth:** Data kuota dinamis, verifikasi hari operasional (Senin-Jumat), dan identitas instansi dari `config/institution.php`.

## 3. Selected Direction
- **Visual Authority:** *The Digital Balai* (Instrument Sans, Court Cyan `#0e7490`, Oasis Emerald `#0f766e`, Soft Mist `#f8fcfd`).
- **Layout & Structure:**
  - Container terpusat (`max-w-4xl` hingga `max-w-5xl`).
  - Stepper wizard visual 3 langkah yang bersih.
  - Kartu tiket berdesain *Boarding Pass* dengan gerigi perforasi halus dan kontras tajam.

## 4. Scope and Boundaries
- **Fidelity:** Production-ready Blade + Flux UI Pro + Alpine.js + Tailwind CSS v4.
- **Untouched:** Endpoint controller, skema database tiket, dan signed URL validation.
- **Anti-goals:** Tidak menggunakan formulir panjang satu halaman yang melelahkan; gunakan stepper terpandu.

## 5. States and Ranges
- *Kuota Penuh:* Kartu layanan non-aktif (*disabled state*) dengan badge "Kuota Hari Ini Habis".
- *Hari Libur / Luar Jam Operasional:* Penanda kalender disabled.
- *Status Tiket:* Menunggu (Amber), Dipanggil (Emerald/Cyan), Selesai (Emerald), Batal/Terlewat (Red/Slate).

## 6. Constraints
- Bahasa Indonesia yang ramah dan sopan (`kasual & ramah`).
- Target sentuh minimal 48px pada tombol CTA ponsel.
