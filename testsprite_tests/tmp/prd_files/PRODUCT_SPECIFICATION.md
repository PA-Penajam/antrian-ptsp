# Product Specification Document (PSD)
## Sistem Manajemen Antrian PTSP (Pelayanan Terpadu Satu Pintu)

---

## 1. Executive Summary

### 1.1 Deskripsi Produk
**Antrian PTSP** adalah sistem manajemen antrian berbasis web yang dirancang untuk Pelayanan Terpadu Satu Pintu (PTSP) Pengadilan Agama. Sistem ini mengintegrasikan proses antrian online, offline, dan semi-digital melalui kiosk yang dibantu petugas.

### 1.2 Value Proposition
| Aspek | Deskripsi |
|-------|-----------|
| **Efisiensi** | Digitalisasi antrian mengurangi penumpukan fisik dan waktu tunggu |
| **Transparansi** | Seluruh tiket tercatat dengan status yang dapat dilacak real-time |
| **Fleksibilitas** | Mendukung multi-kanal: online, frontdesk, dan kiosk-assisted |
| **Akuntabilitas** | Audit log lengkap dan laporan operasional terperinci |
| **Aksesibilitas** | Antarmuka responsif untuk semua perangkat dan literasi digital |

### 1.3 Target Pengguna
1. **Pengunjung/Publik** - Masyarakat umum yang mengambil antrian
2. **Petugas Frontdesk** - Membantu pengguna dan verifikasi kedatangan
3. **Petugas Loket** - Melayani dan memanggil antrian
4. **Admin** - Mengelola konfigurasi sistem
5. **Manajemen/Pimpinan** - Monitoring dan pelaporan

---

## 2. Tech Stack & Arsitektur

### 2.1 Backend Stack
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Bahasa | PHP | 8.4.18 |
| Framework | Laravel | 12.53.0 |
| Database | MySQL | 8.x |
| Authentication | Laravel Fortify | 1.35.0 |

### 2.2 Frontend Stack
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Framework | Livewire | 4.2.1 |
| UI Library | Flux UI Pro | 2.13.0 |
| CSS Framework | Tailwind CSS | 4.2.1 |
| Build Tool | Vite | 7.x |

### 2.3 Testing & Quality
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Testing Framework | Pest PHP | 4.4.1 |
| Code Formatter | Laravel Pint | 1.27.1 |
| PHPUnit | PHPUnit | 12.5.12 |

### 2.4 Arsitektur Database

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   queue_pools   │────▶│    services     │◀────│  service_user   │
│  (Pool Antrian) │     │   (Layanan)     │     │ (Mapping User)  │
└─────────────────┘     └─────────────────┘     └─────────────────┘
         │                       │
         │              ┌────────┴────────┐
         │              │                 │
         │        ┌─────▼─────┐     ┌────▼────┐
         │        │  counters │     │  users  │
         │        │  (Loket)  │     │(Petugas)│
         │        └─────┬─────┘     └────┬────┘
         │              │                │
         │              │    ┌───────────┘
         │              │    │
         │      ┌───────▼────▼──────────┐
         │      │    counter_sessions   │
         │      │   (Sesi Petugas)      │
         │      └───────────────────────┘
         │
┌────────▼────────┐     ┌─────────────────┐     ┌─────────────────┐
│  queue_tickets  │────▶│ queue_activities│     │      users      │
│ (Tiket Antrian) │     │   (Audit Log)   │     │  (Pembuat)      │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

---

## 3. Domain Model & Entitas

### 3.1 Queue Pool (Pool Antrian)
Pool antrian adalah kategori utama untuk mengelompokkan layanan dan loket.

| Atribut | Tipe | Deskripsi |
|---------|------|-----------|
| `id` | BigInt (PK) | Identifier unik |
| `name` | String | Nama pool (Umum, Pembayaran, Posbakum) |
| `code` | String (Unique) | Kode identifikasi pool |
| `description` | Text | Deskripsi pool |
| `is_active` | Boolean | Status aktif/nonaktif |

**Pool yang Terdefinisi:**
1. **Umum** - Untuk Pendaftaran, Informasi/Pengaduan, Pengambilan Produk Hukum
2. **Pembayaran** - Khusus layanan pembayaran
3. **Posbakum** - Khusus layanan Posbakum

### 3.2 Service (Layanan)
Layanan adalah jenis pelayanan yang tersedia di PTSP.

| Atribut | Tipe | Deskripsi |
|---------|------|-----------|
| `id` | BigInt (PK) | Identifier unik |
| `queue_pool_id` | BigInt (FK) | Pool yang dimiliki |
| `name` | String | Nama layanan |
| `code` | String (Unique) | Kode layanan |
| `letter_code` | Char(1) (Unique) | Kode huruf (A, B, C, dll) |
| `slug` | String (Unique) | URL-friendly identifier |
| `description` | Text | Deskripsi layanan |
| `requirements` | Text | Persyaratan layanan |
| `is_active` | Boolean | Status aktif |
| `booking_enabled` | Boolean | Izin booking online |
| `walk_in_enabled` | Boolean | Izin walk-in |
| `daily_quota` | Integer | Kuota harian (opsional) |
| `sort_order` | SmallInt | Urutan tampilan |

**Layanan MVP:**
1. Pendaftaran
2. Pembayaran
3. Informasi/Pengaduan
4. Pengambilan Produk Hukum
5. Posbakum

### 3.3 Counter (Loket)
Loket adalah tempat petugas melayani antrian.

| Atribut | Tipe | Deskripsi |
|---------|------|-----------|
| `id` | BigInt (PK) | Identifier unik |
| `queue_pool_id` | BigInt (FK) | Pool yang dilayani |
| `name` | String | Nama/nomor loket |
| `code` | String (Unique) | Kode loket |
| `is_active` | Boolean | Status aktif |
| `sort_order` | SmallInt | Urutan tampilan |

**Konfigurasi Loket MVP:**
- 3 Loket Umum
- 1 Loket Pembayaran
- 1 Loket Posbakum

### 3.4 Queue Ticket (Tiket Antrian)
Tiket antrian adalah entitas utama yang merepresentasikan satu permintaan layanan.

| Atribut | Tipe | Deskripsi |
|---------|------|-----------|
| `id` | BigInt (PK) | Identifier unik |
| `service_id` | BigInt (FK) | Layanan yang dipilih |
| `queue_pool_id` | BigInt (FK) | Pool antrian |
| `counter_id` | BigInt (FK, nullable) | Loket yang melayani |
| `created_by` | BigInt (FK, nullable) | User pembuat tiket |
| `channel` | Enum | Sumber: `online`, `walk_in`, `kiosk` |
| `ticket_number` | String | Nomor tiket (contoh: A-001) |
| `sequence_number` | Integer | Nomor urut harian per pool |
| `service_date` | Date | Tanggal layanan |
| `visitor_name` | String | Nama pengunjung |
| `visitor_identifier` | String | NIK/Identitas pengunjung |
| `visitor_phone` | String | Nomor HP |
| `notes` | Text | Catatan |
| `status` | Enum | Status tiket |

**Status Tiket:**
| Status | Deskripsi |
|--------|-----------|
| `pending` | Tiket dibuat, menunggu check-in |
| `checked_in` | Sudah check-in, siap dipanggil |
| `called` | Sedang dipanggil |
| `in_service` | Sedang dilayani |
| `completed` | Selesai dilayani |
| `skipped` | Dilewati |
| `cancelled` | Dibatalkan |

**Timestamp Tracking:**
- `checked_in_at` - Waktu check-in
- `called_at` - Waktu dipanggil
- `started_at` - Waktu mulai layanan
- `completed_at` - Waktu selesai
- `cancelled_at` - Waktu dibatalkan

### 3.5 Queue Activity (Audit Log)
Mencatat seluruh aktivitas pada tiket antrian.

| Atribut | Tipe | Deskripsi |
|---------|------|-----------|
| `id` | BigInt (PK) | Identifier unik |
| `queue_ticket_id` | BigInt (FK) | Tiket terkait |
| `user_id` | BigInt (FK, nullable) | User yang melakukan aksi |
| `counter_id` | BigInt (FK, nullable) | Loket terkait |
| `action` | String | Jenis aksi |
| `meta` | JSON | Metadata tambahan |

**Jenis Aksi:**
- `created` - Pembuatan tiket
- `checked_in` - Check-in
- `called` - Pemanggilan
- `recalled` - Panggil ulang
- `started` - Mulai layanan
- `completed` - Selesai layanan
- `skipped` - Dilewati
- `cancelled` - Dibatalkan

### 3.6 Counter Session (Sesi Loket)
Mencatat sesi petugas di loket.

| Atribut | Tipe | Deskripsi |
|---------|------|-----------|
| `id` | BigInt (PK) | Identifier unik |
| `counter_id` | BigInt (FK) | Loket |
| `user_id` | BigInt (FK) | Petugas |
| `opened_at` | Timestamp | Waktu buka sesi |
| `closed_at` | Timestamp (nullable) | Waktu tutup sesi |
| `status` | Enum | `open`, `closed` |

### 3.7 User & Roles (Pengguna & Peran)

**Struktur User:**
| Atribut | Tipe | Deskripsi |
|---------|------|-----------|
| `id` | BigInt (PK) | Identifier unik |
| `name` | String | Nama lengkap |
| `email` | String (Unique) | Email login |
| `role` | Enum | Peran pengguna |
| `password` | String | Password terenkripsi |

**Peran (UserRole):**
| Peran | Deskripsi | Akses |
|-------|-----------|-------|
| `admin` | Administrator sistem | Full access |
| `frontdesk` | Petugas frontdesk | Booking, check-in |
| `officer` | Petugas loket | Panggil, layani antrian |
| `monitor` | Monitoring/Pimpinan | Laporan, dashboard |

---

## 4. Alur Bisnis (Business Flow)

### 4.1 Flowchart Utama

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         ALUR ANTARIAN PTSP                               │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│   ONLINE     │      │  WALK-IN     │      │    KIOSK     │
│  (Booking)   │      │  (Frontdesk) │      │  (Assisted)  │
└──────┬───────┘      └──────┬───────┘      └──────┬───────┘
       │                     │                     │
       │                     │                     │
       ▼                     ▼                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                    CREATED (Pending)                             │
│              Tiket dibuat, menunggu check-in                     │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          │ Check-In
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                   CHECKED_IN                                     │
│            Tiket aktif, masuk antrian panggil                    │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          │ Petugas memanggil
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                    CALLED                                        │
│              Tiket dipanggil ke loket                            │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          │ Petugas mulai layanan
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                  IN_SERVICE                                      │
│             Sedang dilayani di loket                             │
└─────────────────────────┬───────────────────────────────────────┘
                          │
              ┌───────────┼───────────┐
              │           │           │
              ▼           ▼           ▼
       ┌──────────┐ ┌──────────┐ ┌──────────┐
       │ COMPLETED│ │  SKIPPED │ │ CANCELLED│
       │  (Selesai)│ │(Dilewati)│ │(Dibatalkan)
       └──────────┘ └──────────┘ └──────────┘
```

### 4.2 Alur Booking Online

```
Pengunjung ──▶ Pilih Layanan ──▶ Pilih Tanggal ──▶ Isi Data
                                                           │
                                                           ▼
                                ┌──────────────────────────────────────┐
                                │  Konfirmasi & Bukti Tiket            │
                                │  • Nomor tiket                       │
                                │  • QR Code (opsional)                │
                                │  • Tanggal layanan                   │
                                └──────────────────────────────────────┘
                                                           │
                                                           ▼
                                Datang ke PTSP ──▶ Check-In ──▶ Antri
```

### 4.3 Alur Frontdesk (Walk-In)

```
Pengunjung Datang ──▶ Petugas Frontdesk ──▶ Pilih Layanan
                                                          │
                                                          ▼
                               Isi Data Pengunjung ──▶ Buat Tiket
                                                          │
                                                          ▼
                               Otomatis CHECKED_IN ──▶ Siap Dipanggil
```

### 4.4 Alur Petugas Loket

```
Petugas Login ──▶ Pilih/Buka Loket ──▶ Dashboard Loket
                                                    │
                                                    ▼
                              ┌──────────────────────────────────────┐
                              │         DASHBOARD PETUGAS            │
                              │                                      │
                              │  ┌─────────┐  ┌─────────────────────┐│
                              │  │Waiting  │  │   Current Ticket    ││
                              │  │List     │  │   (Active Service)  ││
                              │  └────┬────┘  └─────────────────────┘│
                              │       │                              │
                              └───────┼──────────────────────────────┘
                                      │ Call Next
                                      ▼
                              ┌──────────────────────────────────────┐
                              │      ACTION BUTTONS                  │
                              │  [Recall] [Skip] [Complete] [Cancel] │
                              └──────────────────────────────────────┘
```

---

## 5. Fitur & Fungsionalitas

### 5.1 Modul Publik (Public Access)

#### 5.1.1 Halaman Utama (`/`)
- Landing page informatif PTSP
- Akses cepat ke booking antrian
- Informasi layanan yang tersedia

#### 5.1.2 Booking Online (`/antrian`)
**Fitur:**
- Form pemilihan layanan (dropdown/searchable)
- Pemilihan tanggal kunjungan (date picker)
- Input data pengunjung:
  - Nama lengkap (required)
  - Nomor identitas (NIK/KTP/similar)
  - Nomor HP (untuk notifikasi)
  - Catatan tambahan (opsional)
- Validasi kuota harian
- Konfirmasi booking

**Validasi:**
- Tanggal tidak boleh di masa lalu
- Kuota harian belum penuh
- Nomor HP valid (format Indonesia)

#### 5.1.3 Cek Status Antrian (`/antrian/cek`)
**Fitur:**
- Pencarian tiket berdasarkan:
  - Nomor tiket
  - Nomor HP
  - NIK
- Tampilan status tiket real-time
- Estimasi waktu tunggu (jika memungkinkan)

#### 5.1.4 Konfirmasi Tiket (`/antrian/konfirmasi/{ticket}`)
- Detail lengkap tiket
- QR Code untuk verifikasi
- Instruksi kedatangan
- Tombol download/cetak bukti

#### 5.1.5 Display Antrian (`/display`)
**Fitur:**
- Tampilan nomor yang sedang dipanggil (besar, terbaca)
- Daftar panggilan terakhir (5-10 terakhir)
- Informasi loket tujuan
- Rotasi informasi/iklan PTSP
- Auto-refresh real-time

### 5.2 Modul Frontdesk

#### 5.2.1 Dashboard Frontdesk (`/frontdesk/antrian`)
**Fitur:**
- Form pembuatan tiket cepat
- Pencarian tiket existing
- Daftar tiket hari ini dengan filter status
- Tombol check-in untuk tiket online

**Aksi:**
- `POST /frontdesk/antrian` - Buat tiket walk-in
- `POST /frontdesk/antrian/check-in` - Check-in tiket online

#### 5.2.2 Validasi & Check-In
- Verifikasi identitas pengunjung
- Scan/cek QR Code tiket
- Update status tiket ke `checked_in`
- Pencetakan tiket fisik (opsional)

### 5.3 Modul Petugas Loket

#### 5.3.1 Dashboard Petugas (`/petugas/loket/{counter}`)
**Komponen:**
- Info loket aktif
- Antrian menunggu (queue list)
- Tiket aktif saat ini (jika sedang melayani)
- Statistik harian petugas

**Aksi yang Tersedia:**

| Aksi | Endpoint | Deskripsi |
|------|----------|-----------|
| Call Next | `POST /petugas/loket/{counter}/call-next` | Panggil tiket berikutnya dari pool |
| Recall | `POST /petugas/loket/{counter}/recall` | Panggil ulang tiket yang sama |
| Skip | `POST /petugas/loket/{counter}/skip` | Lewati tiket, kembalikan ke antrian |
| Complete | `POST /petugas/loket/{counter}/complete` | Tandai tiket selesai |
| Cancel | `POST /petugas/loket/{counter}/cancel` | Batalkan tiket |

#### 5.3.2 Manajemen Sesi Loket
- Buka sesi loket saat mulai shift
- Tutup sesi loket saat akhir shift
- Riwayat sesi per petugas

### 5.4 Modul Admin

#### 5.4.1 Manajemen Layanan (`/admin/layanan`)
**Fitur CRUD:**
- Tambah layanan baru
- Edit detail layanan
- Aktif/nonaktifkan layanan
- Atur kuota harian
- Konfigurasi pool antrian

#### 5.4.2 Manajemen Loket (`/admin/loket`)
**Fitur CRUD:**
- Tambah loket baru
- Edit nama/kode loket
- Assign ke pool antrian
- Aktif/nonaktifkan loket
- Atur urutan tampilan

#### 5.4.3 Manajemen Pengguna (`/admin/users`)
**Fitur:**
- CRUD user petugas
- Assign peran (role)
- Reset password
- Assign layanan yang dapat ditangani (service_user)
- Nonaktifkan akun

### 5.5 Modul Monitoring & Laporan

#### 5.5.1 Dashboard Monitoring (`/dashboard`)
**Role-Based Dashboard:**

**Admin Dashboard:**
- Statistik harian total
- Jumlah tiket per layanan
- Jumlah tiket per loket
- Performa petugas
- Grafik tren harian

**Monitor Dashboard:**
- Overview operasional real-time
- Status seluruh loket
- Antrian menunggu per pool
- Rekap mingguan/bulanan

**Petugas Dashboard:**
- Statistik pribadi
- Riwayat layanan hari ini
- Rata-rata waktu layanan

#### 5.5.2 Laporan Antrian (`/laporan/antrian`)
**Jenis Laporan:**
- Laporan per Layanan
- Laporan per Loket
- Laporan per Petugas
- Laporan Status Tiket

**Filter:**
- Rentang tanggal
- Pool antrian
- Layanan spesifik
- Loket spesifik
- Petugas spesifik

**Format Export:**
- PDF (untuk presentasi)
- Excel/CSV (untuk analisis)

### 5.6 Modul Kiosk & TV Display

#### 5.6.1 Kiosk Self-Service (`/kiosk`)
**Akses:** Protected dengan module password

**Fitur:**
- Interface touch-friendly
- Pemilihan layanan dengan ikon besar
- Input data sederhana
- Print tiket otomatis
- Bantuan petugas (assisted mode)

#### 5.6.2 TV Display (`/tv-display`)
**Akses:** Protected dengan module password

**Fitur:**
- Tampilan fullscreen untuk TV
- Layout optimal untuk jarak jauh
- Animasi pemanggilan
- Suara panggilan (text-to-speech)
- Informasi cuaca/jam

---

## 6. UI/UX Design System

### 6.1 Design Principles

| Prinsip | Implementasi |
|---------|--------------|
| **Konsistensi** | Menggunakan Flux UI Pro di seluruh aplikasi |
| **Aksesibilitas** | Kontras warna tinggi, teks besar, touch target minimal 44px |
| **Minimalis** | Whitespace lega, fokus pada aksi utama |
| **Ramah** | Bahasa kasual dan tidak kaku |
| **Responsif** | Mobile-first, optimal di semua ukuran layar |

### 6.2 Tone & Voice

**Gaya Bahasa:**
- ✅ "Silakan pilih layanan yang Anda butuhkan"
- ✅ "Nomor antrian Anda sudah siap"
- ❌ "Silahkan" (ejaan tidak baku)
- ❌ "Mohon maaf atas ketidaknyamanannya" (terlalu formal)

**Microcopy:**
- Error messages yang informatif: "Nomor HP harus dimulai dengan 08"
- Success messages yang hangat: "Berhasil! Tiket Anda sudah tersimpan"
- Instructions yang jelas: "Tekan tombol ini untuk memanggil antrian berikutnya"

### 6.3 Color Palette (Flux UI Default)

| Token | Penggunaan |
|-------|------------|
| `zinc` | Warna netral, teks, border |
| `indigo` | Primary color, tombol utama, link |
| `emerald` | Success states, status selesai |
| `amber` | Warning states, status menunggu |
| `rose` | Error states, status batal |
| `sky` | Info states, status dipanggil |

### 6.4 Component Standards

**Button Hierarchy:**
```html
<!-- Primary Action -->
<flux:button variant="primary">Panggil Berikutnya</flux:button>

<!-- Secondary Action -->
<flux:button>Batal</flux:button>

<!-- Destructive Action -->
<flux:button variant="danger">Hapus</flux:button>
```

**Form Standards:**
```html
<flux:field>
    <flux:label>Nama Lengkap</flux:label>
    <flux:input wire:model="visitor_name" placeholder="Masukkan nama lengkap" />
    <flux:error name="visitor_name" />
</flux:field>
```

---

## 7. API & Endpoint Summary

### 7.1 Public Routes

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/` | Halaman utama |
| GET | `/antrian` | Form booking |
| POST | `/antrian` | Submit booking |
| GET | `/antrian/cek` | Cek status |
| GET | `/antrian/konfirmasi/{ticket}` | Konfirmasi tiket |
| GET | `/display` | Display antrian public |

### 7.2 Authenticated Routes

| Method | Endpoint | Deskripsi | Role |
|--------|----------|-----------|------|
| GET | `/dashboard` | Dashboard user | All |
| GET | `/frontdesk/antrian` | Dashboard frontdesk | frontdesk |
| POST | `/frontdesk/antrian` | Buat tiket | frontdesk |
| POST | `/frontdesk/antrian/check-in` | Check-in | frontdesk |
| GET | `/petugas/loket/{counter}` | Dashboard loket | officer |
| POST | `/petugas/loket/{counter}/call-next` | Panggil | officer |
| POST | `/petugas/loket/{counter}/recall` | Recall | officer |
| POST | `/petugas/loket/{counter}/skip` | Skip | officer |
| POST | `/petugas/loket/{counter}/complete` | Complete | officer |
| POST | `/petugas/loket/{counter}/cancel` | Cancel | officer |
| GET | `/laporan/antrian` | Laporan | monitor |
| GET | `/admin/layanan` | Manajemen layanan | admin |
| GET | `/admin/loket` | Manajemen loket | admin |
| GET | `/admin/users` | Manajemen user | admin |

### 7.3 Module Routes (Password Protected)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/kiosk` | Kiosk interface |
| GET | `/kiosk/login` | Login kiosk |
| GET | `/tv-display` | TV display |
| GET | `/tv-display/login` | Login TV display |

---

## 8. Security & Authentication

### 8.1 Authentication Methods

1. **Laravel Fortify** - Untuk user petugas (admin, frontdesk, officer, monitor)
2. **Module Password** - Untuk kiosk dan TV display (shared password)

### 8.2 Role-Based Access Control (RBAC)

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    Admin    │────▶│   Monitor   │     │  Frontdesk  │
│   (Full)    │     │  (Report)   │     │  (Booking)  │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                                │
                                         ┌──────┴──────┐
                                         │   Officer   │
                                         │  (Counter)  │
                                         └─────────────┘
```

### 8.3 Security Measures
- Password hashing dengan bcrypt
- CSRF protection pada semua form
- Rate limiting pada endpoint publik
- SQL injection protection via Eloquent ORM
- XSS protection via Blade escaping

---

## 9. Audit & Logging

### 9.1 Audit Trail
Semua aktivitas penting dicatat di `queue_activities`:

- Siapa yang melakukan aksi (user_id)
- Kapan dilakukan (created_at)
- Apa yang dilakukan (action)
- Di loket mana (counter_id)
- Metadata tambahan (meta - JSON)

### 9.2 Logging Levels
- **INFO** - Aktivitas normal (create ticket, call next)
- **WARNING** - Aktivitas yang perlu perhatian (skip, cancel)
- **ERROR** - Kegagalan sistem

---

## 10. Performance & Scalability

### 10.1 Database Indexing
- Index pada `queue_tickets.status` untuk query cepat
- Index pada `queue_tickets.service_date` untuk filter harian
- Index pada `queue_activities.action` untuk laporan
- Composite index pada `(queue_pool_id, service_date, status)`

### 10.2 Caching Strategy
- Cache daftar layanan aktif
- Cache daftar loket aktif
- Cache statistik dashboard (refresh setiap 5 menit)

### 10.3 Optimization
- Lazy loading pada relasi Eloquent
- Pagination pada daftar tiket
- Selective field loading

---

## 11. Testing Strategy

### 11.1 Unit Testing (Pest PHP)
- Model relationships
- Action classes
- Form request validation
- Policy/authorization

### 11.2 Feature Testing
- End-to-end user flows
- API endpoint testing
- Authentication flows
- CRUD operations

### 11.3 Browser Testing
- Livewire component interaction
- Display refresh mechanism
- Kiosk touch interactions

---

## 12. Deployment & DevOps

### 12.1 Environment Requirements
- PHP 8.4+
- MySQL 8.0+ / MariaDB 10.6+
- Node.js 20+ (build)
- Composer 2.x

### 12.2 Build Process
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Prepare application
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

### 12.3 Available Commands
```bash
# Development
composer run dev          # Jalankan server + queue + vite
npm run dev               # Vite dev server

# Testing
php artisan test          # Jalankan test suite
vendor/bin/pint           # Code formatting

# Production
npm run build             # Build assets
php artisan config:cache  # Cache config
php artisan route:cache   # Cache routes
```

---

## 13. Future Roadmap

### 13.1 Phase 1 (Post-MVP)
- [ ] Notifikasi WhatsApp/SMS untuk pengingat dan panggilan
- [ ] Survey Kepuasan Masyarakat (IKM) terintegrasi
- [ ] Dashboard pimpinan yang lebih kaya
- [ ] Export laporan yang lebih lengkap (PDF, Excel)
- [ ] PWA (Progressive Web App) untuk mobile experience

### 13.2 Phase 2 (Enhancement)
- [ ] Notifikasi pengingat kedatangan ( reminder )
- [ ] Check-in mandiri (self-service)
- [ ] Histori kunjungan pengguna
- [ ] Pre-screening dokumen sebelum kedatangan
- [ ] Analitik SLA dan beban loket

### 13.3 Phase 3 (Integration)
- [ ] Integrasi dengan aplikasi internal instansi
- [ ] Single Sign-On (SSO)
- [ ] API untuk integrasi eksternal
- [ ] Mobile app native (Android/iOS)

---

## 14. Appendices

### 14.1 Glossary

| Istilah | Definisi |
|---------|----------|
| **PTSP** | Pelayanan Terpadu Satu Pintu |
| **Pool** | Kelompok antrian untuk kategori layanan tertentu |
| **Loket** | Tempat petugas melayani antrian |
| **Tiket** | Nomor antrian untuk satu permintaan layanan |
| **Check-in** | Proses konfirmasi kedatangan pengunjung |
| **Walk-in** | Pengunjung yang datang langsung tanpa booking |
| **Kiosk** | Terminal self-service untuk pengambilan antrian |
| **IKM** | Indeks Kepuasan Masyarakat |
| **SPBE** | Sistem Pemerintahan Berbasis Elektronik |

### 14.2 Related Documents
- `docs/plans/2026-03-06-ptsp-queue-prd.md` - Product Requirements Document
- `docs/plans/2026-03-06-ptsp-queue-implementation-plan.md` - Implementation Plan
- `docs/plans/2026-03-06-ui-ux-flux-ptsp.md` - UI/UX Guidelines
- `conductor/product.md` - Product Definition
- `conductor/tech-stack.md` - Technical Stack
- `conductor/product-guidelines.md` - Product Guidelines

### 14.3 Reference Links
- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Flux UI Documentation](https://fluxui.dev)
- [Tailwind CSS Documentation](https://tailwindcss.com)
- [Pest PHP Documentation](https://pestphp.com)

---

**Document Version:** 1.0  
**Last Updated:** 8 Maret 2026  
**Author:** Development Team  
**Status:** Active Development
