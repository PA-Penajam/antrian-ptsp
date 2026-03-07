# PRD: Aplikasi Antrian PTSP Pengadilan Agama

## Ringkasan

Dokumen ini mendefinisikan kebutuhan produk untuk aplikasi antrian PTSP Pengadilan Agama berbasis web responsif dengan model layanan hibrida: antrean online, antrean hari yang sama dengan bantuan petugas, dan antrean datang langsung melalui kiosk yang dibantu petugas. Fokus MVP adalah membuat operasional PTSP lebih tertib, terukur, dan mudah dipakai oleh masyarakat dengan literasi digital rendah tanpa bergantung pada integrasi aplikasi luar instansi.

Produk ini bukan hanya alat ambil nomor antrean. MVP harus mencakup master layanan, pengelolaan loket, dashboard petugas, display antrean, check-in kedatangan, pelaporan operasional, dan kontrol tata kelola minimum agar selaras dengan prinsip SPBE internal.

## Latar Belakang

PTSP Pengadilan Agama membutuhkan layanan antrean yang bisa melayani dua kondisi sekaligus:

- masyarakat yang mampu mengambil antrean sendiri secara online;
- masyarakat yang datang langsung dan perlu dibantu petugas.

Kondisi operasional saat ini juga menuntut fleksibilitas di loket. Tiga loket umum melayani beberapa layanan bersama, sedangkan layanan pembayaran dan Posbakum ditangani secara khusus. Karena itu, desain produk harus sederhana di sisi publik, tetapi cukup kaya di sisi operasional dan pelaporan.

## Tujuan Produk

- Menyediakan sistem antrean PTSP yang menyatukan kanal online dan offline dalam satu sistem.
- Mengurangi antrean manual yang tidak tercatat dan sulit dipantau.
- Memudahkan petugas mengelola antrean, pemanggilan, dan status pelayanan.
- Menyediakan laporan layanan dan laporan loket yang akurat.
- Menjadi fondasi aplikasi layanan internal yang selaras SPBE tanpa integrasi lintas instansi pada fase MVP.

## Sasaran Keberhasilan

- Masyarakat dapat mengambil antrean melalui minimal dua kanal: online dan datang langsung.
- Seluruh tiket antrean tercatat dalam sistem dan memiliki status yang dapat dilacak.
- Petugas dapat memanggil tiket berdasarkan pool antrean yang menjadi kewenangannya.
- Pimpinan atau admin dapat melihat rekap per layanan, per loket, dan per petugas.
- Data layanan, log aktivitas, dan konfigurasi operasional tersimpan terpusat.

## Persona

### 1. Pencari Keadilan / Pengguna Layanan

Pengguna publik yang ingin mengambil antrean untuk layanan PTSP. Sebagian dapat mengisi form online, sebagian lain perlu bantuan petugas saat datang ke kantor.

### 2. Petugas Frontdesk

Petugas yang membantu masyarakat mengambil antrean, melakukan verifikasi kedatangan, dan mengarahkan pengguna ke layanan yang benar.

### 3. Petugas PTSP

Petugas loket yang memanggil nomor antrean, melayani pengguna, serta memperbarui status tiket selama proses pelayanan.

### 4. Admin

Pengelola sistem yang mengatur layanan, pool antrean, loket, akun petugas, jadwal operasional, kuota, dan konfigurasi lainnya.

### 5. Pimpinan / Monitoring

Pengguna internal yang membutuhkan pemantauan operasional PTSP melalui dashboard dan laporan.

## Cakupan MVP

### Layanan MVP

MVP mencakup lima layanan berikut:

- Pendaftaran
- Pembayaran
- Informasi/Pengaduan
- Pengambilan Produk Hukum
- Posbakum

Layanan lain dapat ditambahkan manual setelah MVP tanpa mengubah fondasi utama aplikasi.

### Kanal Masuk Antrean

MVP mendukung tiga jalur pembuatan tiket antrean:

- booking online untuk tanggal kunjungan;
- antrean hari yang sama dengan bantuan petugas;
- datang langsung melalui kiosk dengan bantuan petugas.

### Model Pool Antrean

MVP menggunakan tiga pool antrean:

- `Umum`
  Digunakan untuk layanan Pendaftaran, Informasi/Pengaduan, dan Pengambilan Produk Hukum.
- `Pembayaran`
  Digunakan khusus untuk layanan Pembayaran.
- `Posbakum`
  Digunakan khusus untuk layanan Posbakum.

Model ini dipilih agar operasional loket tetap sederhana, tetapi laporan per layanan tetap dapat dipisahkan.

### Model Loket Aktif

MVP menggunakan lima loket aktif:

- tiga loket umum;
- satu loket pembayaran;
- satu loket Posbakum.

Walaupun antrean umum dibagi ke tiga loket, tiket tetap menyimpan layanan yang dipilih pemohon agar laporan layanan tetap akurat.

## Kebutuhan Fungsional

### 1. Master Layanan

Admin harus dapat mengelola data layanan yang mencakup:

- nama layanan;
- kode layanan;
- deskripsi singkat;
- persyaratan;
- pool antrean;
- status aktif/nonaktif;
- jam layanan;
- kuota harian bila diperlukan.

### 2. Master Loket

Admin harus dapat mengelola data loket yang mencakup:

- nama atau nomor loket;
- jenis loket;
- pool antrean yang dilayani;
- status aktif/nonaktif.

### 3. Manajemen Pengguna dan Peran

Sistem harus mendukung peran berikut:

- admin;
- frontdesk;
- petugas loket;
- monitoring/pimpinan.

Setiap peran hanya dapat mengakses fitur yang sesuai kewenangannya.

### 4. Pengambilan Antrean Online

Pengguna publik dapat membuat antrean tanpa akun melalui form sederhana. Data minimum yang direkam:

- nama pemohon;
- identitas dasar atau nomor identitas;
- nomor HP;
- layanan yang dipilih;
- tanggal kunjungan;
- catatan singkat bila diperlukan.

Setelah berhasil, sistem menampilkan bukti antrean yang bisa digunakan saat datang ke kantor.

### 5. Pengambilan Antrean Assisted / Walk-In

Petugas frontdesk harus dapat:

- membuat tiket untuk pemohon yang datang langsung;
- membantu pemohon menggunakan kiosk;
- memilih layanan sesuai kebutuhan pemohon;
- memastikan tiket masuk ke pool antrean yang benar.

### 6. Check-In Kedatangan

Saat pemohon datang ke kantor, petugas atau kiosk-assisted flow harus dapat mengaktifkan tiket agar siap dipanggil. Tujuannya adalah menghindari no-show langsung memenuhi antrean panggil.

### 7. Dashboard Petugas Loket

Petugas harus dapat:

- melihat antrean yang menunggu pada pool yang menjadi kewenangannya;
- memanggil nomor berikutnya;
- memanggil ulang;
- melewati tiket;
- membatalkan tiket;
- menyelesaikan tiket;
- melihat tiket yang sedang aktif di loketnya.

### 8. Display Antrean

Sistem harus menyediakan layar display untuk area tunggu yang menampilkan minimal:

- nomor yang sedang dipanggil;
- loket tujuan;
- daftar panggilan terakhir;
- identitas pool atau layanan bila diperlukan.

### 9. Pelaporan

Sistem harus menyediakan laporan operasional minimal:

- jumlah tiket per layanan;
- jumlah tiket per loket;
- jumlah tiket per petugas;
- status tiket per hari;
- jumlah tiket selesai, batal, dilewati, atau tidak hadir.

Laporan harus tetap akurat walaupun beberapa layanan berada dalam pool antrean umum yang sama.

### 10. Audit Log

Sistem harus mencatat aktivitas penting seperti:

- pembuatan tiket;
- sumber tiket: online, assisted, atau kiosk;
- check-in;
- pemanggilan;
- panggil ulang;
- lewati;
- batal;
- selesai;
- perubahan master layanan;
- perubahan konfigurasi loket;
- override manual oleh petugas atau admin.

## Kebutuhan Non-Fungsional

- Antarmuka harus responsif dan dapat digunakan di desktop maupun ponsel.
- Form publik harus sederhana dan tidak membutuhkan akun.
- Istilah dan alur harus mudah dipahami oleh pengguna dengan literasi digital rendah.
- Data aplikasi harus tersimpan terpusat dalam satu basis data instansi.
- Akses petugas harus memakai autentikasi dan pembatasan berdasarkan peran.
- Sistem harus mendukung backup dan retensi data operasional.
- Nomor antrean harus konsisten dan mudah dibaca oleh publik.

## Aturan Produk Penting

### Penomoran

MVP tidak menggunakan nomor global lintas semua layanan. Nomor antrean harus mengikuti pool antrean agar mudah dipahami dan mudah dilaporkan.

Contoh bentuk penomoran:

- pool umum: nomor umum berurutan;
- pool pembayaran: nomor khusus pembayaran berurutan;
- pool Posbakum: nomor khusus Posbakum berurutan.

Format final kode tiket dapat diputuskan saat desain teknis, tetapi tidak boleh mengorbankan keterbacaan publik dan akurasi pelaporan.

### Hubungan Layanan, Pool, dan Loket

Setiap tiket harus menyimpan tiga dimensi data:

- layanan yang dipilih pemohon;
- pool antrean tempat tiket masuk;
- loket yang akhirnya melayani tiket.

Dengan model ini, sistem dapat tetap sederhana secara operasional dan tetap kuat secara pelaporan.

## Alur Pengguna Utama

### 1. Booking Online

Pengguna memilih layanan, memilih tanggal, mengisi data dasar, lalu menerima bukti antrean.

### 2. Datang Langsung

Pengguna datang ke kantor, dibantu petugas untuk memilih layanan, lalu sistem membuat tiket antrean dari dashboard frontdesk atau kiosk-assisted flow.

### 3. Check-In

Tiket diaktifkan saat pengguna hadir agar masuk ke antrean yang dapat dipanggil.

### 4. Pemanggilan Loket

Petugas memanggil tiket berikutnya sesuai pool yang dilayani, lalu memperbarui status tiket hingga selesai.

### 5. Monitoring dan Laporan

Admin atau pimpinan melihat performa harian berdasarkan layanan, loket, petugas, dan status tiket.

## Kepatuhan SPBE Internal

MVP ini ditujukan agar selaras dengan prinsip SPBE tanpa integrasi aplikasi luar instansi pada fase awal. Implikasi produknya:

- aplikasi ditetapkan sebagai layanan digital resmi internal instansi;
- data layanan, data antrean, dan log aktivitas tersimpan terpusat;
- layanan memiliki metadata standar pelayanan yang jelas;
- akses dibatasi dengan RBAC;
- aktivitas penting tercatat dalam audit trail;
- tersedia fondasi untuk backup, retensi, dan kontrol perubahan.

Pendekatan ini sejalan dengan kebutuhan tata kelola layanan elektronik instansi tanpa mewajibkan integrasi lintas aplikasi pada tahap MVP.

## Di Luar Cakupan MVP

- integrasi dengan aplikasi luar instansi;
- mobile app native Android/iOS;
- akun publik dan histori pengguna;
- notifikasi pihak ketiga sebagai dependensi utama;
- analitik lanjutan;
- upload dokumen publik sebelum kedatangan;
- survei kepuasan masyarakat terintegrasi penuh.

## Risiko dan Catatan

- Literasi digital masyarakat yang rendah menuntut frontdesk dan kiosk-assisted flow yang benar-benar sederhana.
- Jika penomoran terlalu rumit, pengguna akan sulit memahami antrean.
- Jika layanan umum dibagi terlalu detail sejak awal, operasional loket bisa menjadi lebih lambat.
- Jika laporan hanya didasarkan pada pool antrean, data layanan akan bias. Karena itu tiket harus menyimpan layanan asli yang dipilih.

## Roadmap Pasca-MVP

Bagian ini wajib menjadi catatan pengembangan setelah MVP selesai agar arah produk tetap jelas.

### Tahap Lanjutan 1

- menambah layanan PTSP lain di luar lima layanan MVP;
- menambah dashboard pimpinan yang lebih kaya;
- menambah survei kepuasan masyarakat setelah layanan selesai;
- menambah ekspor laporan yang lebih lengkap.

### Tahap Lanjutan 2

- menambahkan PWA agar aplikasi lebih nyaman dipakai seperti aplikasi ringan di perangkat petugas dan publik;
- menambahkan notifikasi pengingat kedatangan;
- menambahkan check-in mandiri yang lebih matang bila kesiapan pengguna meningkat;
- menambahkan fitur histori kunjungan pengguna.

### Tahap Lanjutan 3

- pre-screening atau unggah dokumen awal sebelum kedatangan;
- analitik SLA dan beban loket;
- integrasi dengan aplikasi internal lain jika nanti dibutuhkan instansi;
- integrasi keluar instansi hanya bila ada kebutuhan kebijakan dan kesiapan tata kelola.

## Referensi Riset

- Perpres Nomor 97 Tahun 2014 tentang Penyelenggaraan Pelayanan Terpadu Satu Pintu
- Perpres Nomor 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik
- Perpres Nomor 132 Tahun 2022 tentang Arsitektur SPBE Nasional
- PermenPANRB tentang Standar Pelayanan
- PermenPANRB Nomor 14 Tahun 2017 tentang Pedoman Penyusunan Survei Kepuasan Masyarakat
- PermenPANRB Nomor 5 Tahun 2020 tentang Pedoman Manajemen Risiko SPBE
- Peraturan BSSN Nomor 4 Tahun 2021 tentang Pedoman Manajemen Keamanan Informasi SPBE

