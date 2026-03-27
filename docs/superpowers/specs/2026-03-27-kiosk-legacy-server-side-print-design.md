# Desain: Server-Side Print Proxy untuk Kiosk Legacy

**Tanggal:** 2026-03-27
**Scope:** Hanya `kiosk-legacy` — Livewire kiosk tidak terpengaruh
**Masalah:** Mixed content blocking — halaman HTTPS tidak bisa konek ke HTTP printer dari browser Android 5

---

## Latar Belakang

Kiosk legacy diakses via `https://antrian-new.pa-penajam.go.id/kiosk-legacy`. Browser pada Android 5 memblokir koneksi HTTP ke printer Epson (`http://192.168.10.27:8008`) karena dianggap mixed content. PC bisa print karena mengizinkan mixed content secara manual.

Solusi: pindahkan logika cetak ke server Laravel. Server konek ke printer server-side (tidak ada mixed content), kiosk browser hanya menerima respons sukses/gagal.

---

## Arsitektur

```
Kiosk Browser (Android/PC)
    │
    │  POST /kiosk-legacy/print
    ▼
KioskController::printLegacy()
    │
    ├─► CreateQueueTicket        — buat tiket di DB (selalu berhasil)
    │
    ├─► PrintTicketToEposPrinter — HTTP POST ke printer via Guzzle
    │       └─ http://192.168.10.27:8008/cgi-bin/epos/service.cgi
    │
    └─► JSON: { success, ticket, printed, print_error? }
```

**Prinsip utama:**
- Tiket selalu dibuat meskipun printer gagal
- Print failure tidak membatalkan ticket creation
- Browser tidak butuh Epson ePOS SDK lagi untuk kiosk-legacy

---

## Komponen

### 1. `app/Actions/Queue/PrintTicketToEposPrinter.php` (baru)

- **Input:** `QueueTicket` (dengan relasi `service` sudah di-load)
- **Output:** `bool` — `true` jika printer cetak, `false` jika gagal
- Bangun XML SOAP ePOS-Print payload
- HTTP POST via Guzzle ke `http://{ip}:{port}/cgi-bin/epos/service.cgi?devid={device_id}&timeout=10000`
- Semua exception ditangkap secara graceful (tidak melempar ke luar)
- Log warning jika gagal

### 2. `KioskController::printLegacy()` (dimodifikasi)

- Inject `PrintTicketToEposPrinter`
- Load relasi `service` pada ticket setelah dibuat
- Panggil print action, tangkap hasilnya
- Response JSON: `{ success: true, ticket: {...}, printed: bool }`

### 3. `resources/views/pages/kiosk/legacy.blade.php` (dimodifikasi)

- Hapus semua JavaScript printer: `initPrinter`, `doPrintTicket`, `printTicket`, `flushPrinterCallbacks`, `showPrinterWarning`, variabel `printerInitCallbacks`, `eposPrinter`, dll.
- AJAX success handler: cek `res.printed`:
  - `true` → lanjut normal
  - `false` → tampilkan SweetAlert warning "Tiket berhasil dibuat, printer tidak merespons"
- Hapus data-attributes printer dari `kioskLegacyConfig`

### 4. `resources/views/layouts/legacy.blade.php` (dimodifikasi)

- Hapus conditional loading Epson ePOS SDK (`epos-2.27.0.js`)
- SDK tidak lagi dibutuhkan oleh halaman manapun yang pakai layout legacy

---

## Format XML ePOS-Print

```xml
<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <epos-print xmlns="http://www.epson-pos.com/schemas/2011/03/epos-print">
      <text align="center" width="1" height="1" bold="true">{nama_institusi}&#10;</text>
      <text align="center">Sistem Pelayanan Terpadu Satu Pintu&#10;</text>
      <text>------------------------------------------&#10;</text>
      <feed line="1"/>
      <text align="center" width="3" height="3" bold="true">{ticket_number}&#10;</text>
      <feed line="1"/>
      <text>------------------------------------------&#10;</text>
      <text align="left">Layanan : {service_name}&#10;</text>
      <text>Nama    : {visitor_name}&#10;</text>
      <text>Tanggal : {service_date}&#10;</text>
      <feed line="3"/>
      <cut type="feed"/>
    </epos-print>
  </s:Body>
</s:Envelope>
```

**HTTP Headers:**
```
Content-Type: text/xml; charset=utf-8
SOAPAction: ""
```

---

## Error Handling

| Kondisi | Behaviour |
|---------|-----------|
| Printer unreachable (timeout/refused) | `printed: false`, log warning, tiket tetap dibuat |
| HTTP response bukan 2xx | `printed: false`, log warning |
| Response XML menunjukkan error | `printed: false`, log warning |
| Printer mati/tidak konek | `printed: false`, kiosk tampilkan alert |
| Ticket creation gagal | Exception, tidak sampai ke print action |

---

## Testing

### Unit Test: `PrintTicketToEposPrinter`
- Mock Guzzle HTTP client
- Verifikasi XML payload yang dikirim mengandung data tiket yang benar
- Test return `true` saat printer OK
- Test return `false` saat Guzzle throw exception
- Test return `false` saat HTTP response error

### Feature Test: `KioskController::printLegacy`
- Verifikasi response JSON menyertakan field `printed: true/false`
- Verifikasi tiket tetap dibuat meski print gagal
- Mock `PrintTicketToEposPrinter` agar tidak konek ke printer sungguhan

---

## Konfigurasi

Menggunakan config yang sudah ada di `config/services.php`:
```php
'thermal_printer' => [
    'enabled' => env('THERMAL_PRINTER_ENABLED', false),
    'ip'      => env('THERMAL_PRINTER_IP', '192.168.1.100'),
    'port'    => env('THERMAL_PRINTER_PORT', 8008),
    'device_id' => env('THERMAL_PRINTER_DEVICE_ID', 'local_printer'),
]
```

Jika `THERMAL_PRINTER_ENABLED=false`, `PrintTicketToEposPrinter` langsung return `false` tanpa konek.
