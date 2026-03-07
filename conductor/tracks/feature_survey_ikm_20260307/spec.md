# Specification: Sistem Survey Indeks Kepuasan Masyarakat (IKM)

## Objective
Menyediakan fitur bagi pengguna layanan untuk mengisi survey kepuasan setelah pelayanan selesai, sebagai bahan pelaporan dan peningkatan mutu layanan.

## Scope
- Membuat tabel untuk menyimpan pertanyaan survey dan jawaban/rating dari pengunjung.
- Membuat halaman publik bagi pengunjung untuk mengisi rating kepuasan menggunakan URL unik/token yang terkait dengan tiket antrian yang telah `completed`.
- Menambahkan integrasi agar pengunjung mendapatkan link pengisian survey (bisa via QR Code di loket, tombol di halaman tracking, atau via WhatsApp/SMS).
- Membuat halaman dashboard bagi Admin/Manajemen untuk melihat hasil rekapitulasi rating kepuasan per loket/layanan.

## Technical Constraints
- Setiap tiket hanya dapat mengisi survey satu kali.
- Form pengisian harus sangat sederhana (misalnya bintang 1-5 dan kolom saran opsional).
- Menjamin UI/UX menggunakan komponen Flux UI Pro.