# Implementation Plan: Integrasi WhatsApp/SMS untuk Notifikasi Antrian

## Phase 1: Persiapan Infrastructure & Provider
- [ ] Task: Tentukan dan setup provider API WhatsApp/SMS.
- [ ] Task: Buat wrapper class atau Service di Laravel untuk interaksi dengan provider API tersebut.
- [ ] Task: Buat Laravel Job (`SendQueueNotificationJob`) untuk mengeksekusi pengiriman pesan di background.
- [ ] Task: Conductor - User Manual Verification 'Phase 1: Persiapan Infrastructure & Provider' (Protocol in workflow.md)

## Phase 2: Perubahan Database & UI Booking
- [ ] Task: Tambahkan field preferensi notifikasi pada tabel tiket jika diperlukan (apakah pengunjung ingin dikabari via WA).
- [ ] Task: Update halaman `booking.blade.php` untuk menampilkan input nomor WhatsApp aktif dan checkbox konfirmasi.
- [ ] Task: Validasi input nomor telepon dengan format internasional standar pada Form Request.
- [ ] Task: Conductor - User Manual Verification 'Phase 2: Perubahan Database & UI Booking' (Protocol in workflow.md)

## Phase 3: Integrasi Event & Listener
- [ ] Task: Buat event `TicketCreated` dan `TicketApproaching` (misal saat sisa 3 antrean).
- [ ] Task: Buat listener yang mendengarkan event tersebut lalu men-dispatch `SendQueueNotificationJob`.
- [ ] Task: Modifikasi sistem frontdesk/pemanggilan untuk trigger `TicketApproaching` kepada tiket-tiket berikutnya.
- [ ] Task: Tulis dan pastikan semua Feature/Unit tests untuk proses antrian dengan notifikasi berjalan mulus.
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Integrasi Event & Listener' (Protocol in workflow.md)