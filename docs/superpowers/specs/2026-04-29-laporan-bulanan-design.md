# Desain Modul Laporan Bulanan Pendaftar Layanan

## 1. Arsitektur & Komponen

### 1.1 Livewire Component
**File:** `app/Livewire/Reports/LaporanBulanan.php`

Komponen Livewire 4 yang menangani:
- Filter bulan (1-12) dan tahun via Flux UI dropdown
- Preview data statistik dan tabel di browser
- Trigger download Excel dan PDF

**State:**
- `public int $bulan` — default bulan berjalan
- `public int $tahun` — default tahun berjalan

**Computed properties:**
- `ringkasan()` — total, completed, waiting, cancelled
- `perLayanan()` — jumlah per layanan (total, completed, cancelled)
- `perHari()` — jumlah per hari dalam bulan terpilih
- `perChannel()` — jumlah per channel (online_booking, walk_in_kiosk, assisted_same_day)

**Actions:**
- `downloadExcel()` — generate dan stream Excel
- `downloadPdf()` — generate dan stream PDF

### 1.2 Blade View
**File:** `resources/views/livewire/reports/laporan-bulanan.blade.php`

Layout:
1. Filter bar: dropdown bulan + tahun + tombol export Excel/PDF
2. Stat cards (4 kolom): Total, Dilayani, Menunggu, Dibatalkan
3. Tabel: Rekap Per Layanan (nama layanan, total, selesai, batal)
4. Tabel: Detail Per Hari (tanggal, total, per channel)
5. Tabel: Distribusi Channel (channel, jumlah, persentase)

### 1.3 Excel Export
**File:** `app/Exports/LaporanBulananExport.php`

Menggunakan `maatwebsite/excel` dengan multiple sheets:
- **Sheet "Ringkasan"** — statistik header + tabel per layanan
- **Sheet "Per Hari"** — detail harian dengan kolom tanggal, total, online, kiosk, assisted
- **Sheet "Per Channel"** — distribusi channel

### 1.4 PDF Export
**File:** `resources/views/pdf/laporan-bulanan.blade.php`

Template Blade untuk `barryvdh/laravel-dompdf`:
- Kop surat dari `config/institution.php`
- Judul laporan
- Tabel ringkasan per layanan
- Tabel detail per hari
- Tempat, tanggal, tanda tangan

### 1.5 Routes
**Edit:** `routes/web.php`

Tambahkan di dalam middleware group `Admin + Monitor`:
```php
Route::get('/laporan/bulanan', App\Livewire\Reports\LaporanBulanan::class)->name('laporan.bulanan');
```

Role yang diizinkan: Admin, Monitor (ditambahkan ke middleware group baru atau merge dengan existing Monitor group).

## 2. Data Flow

### 2.1 Sumber Data
Tabel `queue_tickets` dengan join ke `services`, difilter berdasarkan `service_date` dalam rentang bulan terpilih.

### 2.2 Query
| Data | Query |
|---|---|
| Ringkasan statistik | `COUNT(*) GROUP BY status` |
| Per layanan | `JOIN services GROUP BY service_id` |
| Per hari | `GROUP BY DATE(service_date)` untuk seluruh bulan |
| Per channel | `GROUP BY channel` |

### 2.3 Flow Download
```
User klik Download Excel
  → LaporanBulanan::downloadExcel()
  → LaporanBulananExport (Maatwebsite\Excel)
  → Response::streamDownload()

User klik Download PDF
  → LaporanBulanan::downloadPdf()
  → Generate HTML via Blade template
  → DomPDF render
  → Response::streamDownload()
```

## 3. Package Baru
- `maatwebsite/excel` — export Excel
- `barryvdh/laravel-dompdf` — export PDF

## 4. Error Handling
- Data kosong → pesan "Tidak ada data pendaftar untuk bulan ini", export tetap bisa dengan catatan
- Query gagal → flux:toast error
- Validasi input: bulan (1-12), tahun (2020 - tahun depan)

## 5. Testing

| Test | Tipe | Deskripsi |
|---|---|---|
| Admin bisa akses halaman laporan | Feature | GET /laporan/bulanan → 200 |
| Monitor bisa akses halaman laporan | Feature | GET /laporan/bulanan → 200 |
| Frontdesk ditolak akses laporan | Feature | GET /laporan/bulanan → 403 |
| Export Excel menghasilkan file valid | Feature | Content-Type: application/vnd.openxmlformats... |
| Export PDF menghasilkan file valid | Feature | Content-Type: application/pdf |
| Filter bulan mengubah data | Feature | Pilih bulan berbeda, assert tampilan berubah |
| Data statistik akurat | Unit | Bandingkan hasil query dengan data DB |

## 6. Daftar File Lengkap

| # | File | Aksi |
|---|---|---|
| 1 | `app/Livewire/Reports/LaporanBulanan.php` | Buat baru |
| 2 | `resources/views/livewire/reports/laporan-bulanan.blade.php` | Buat baru |
| 3 | `app/Exports/LaporanBulananExport.php` | Buat baru |
| 4 | `app/Exports/Sheets/PerLayananSheet.php` | Buat baru |
| 5 | `app/Exports/Sheets/PerHariSheet.php` | Buat baru |
| 6 | `app/Exports/Sheets/PerChannelSheet.php` | Buat baru |
| 7 | `resources/views/pdf/laporan-bulanan.blade.php` | Buat baru |
| 8 | `routes/web.php` | Edit |
| 9 | `tests/Feature/LaporanBulananTest.php` | Buat baru |
| 10 | `composer.json` | Edit (tambah package) |
