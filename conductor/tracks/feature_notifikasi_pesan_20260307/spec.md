# Specification: Integrasi WhatsApp/SMS untuk Notifikasi Antrian

## Objective
Mengintegrasikan layanan pengiriman pesan (WhatsApp/SMS) untuk memberikan notifikasi otomatis kepada pengunjung mengenai status antrian mereka.

## Scope
- Mendaftarkan dan mengonfigurasi provider pihak ketiga untuk WhatsApp/SMS API.
- Membuat service/job di Laravel untuk mengirim notifikasi secara asinkron (queue).
- Menambahkan preferensi nomor telepon dan persetujuan pengiriman pesan pada halaman registrasi antrian/booking.
- Mengirimkan pesan ketika tiket berhasil dibuat, serta reminder saat antrian pengunjung sudah mendekati giliran (misal: 3 antrian sebelumnya).

## Technical Constraints
- Pekerjaan pengiriman pesan harus dilakukan melalui Laravel Queue (redis/database) agar tidak memblokir response time sistem utama.
- Menyediakan fallback log jika pesan gagal terkirim.
- Memastikan format pesan template dapat dikonfigurasi melalui database atau file config.