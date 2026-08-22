---
slug: reports-and-monitoring
primary_target: resources/views/livewire/reports/laporan-bulanan.blade.php
related_targets:
  - resources/views/pages/laporan/antrian/index.blade.php
  - resources/views/pages/laporan/audit/index.blade.php
  - resources/views/pdf/laporan-bulanan.blade.php
  - app/Http/Controllers/Report/QueueReportController.php
  - app/Http/Controllers/Report/AuditTrailController.php
mode: Read
---

# Surface Brief: Executive Reports, Audit Trail & Monthly Exports

## 1. Job and Audience
- **Visitor / Audience:** Pimpinan Pengadilan (Ketua, Panitera, Sekretaris) dan supervisor pengawas PTSP.
- **Context:** Memantau tren waktu tunggu, kepatuhan SOP, performa loket, dan menyusun laporan berkala resmi ke instansi pembina (Badilag / PTA).
- **Mode:** **Read & Analyze (Data Density, Statistical Synthesis & Export Ready)**

## 2. Outcome and Proof
- **Laporan Antrian:** Filter rentang tanggal fleksibel, metrik ringkasan waktu tunggu rata-rata (*AWT*), waktu pelayanan rata-rata (*AST*), dan rasio penyelesaian.
- **Audit Trail:** Log kronologis tindakan operasional staf dengan badge status terstandarisasi.
- **Laporan Bulanan:** Rekapitulasi per layanan/loket dengan satu klik ekspor PDF resmi siap cetak kop surat & Excel.

## 3. Selected Direction
- **Visual Authority:** *The Digital Balai Data Surface* (Flux UI Pro Table, Kartu Metrik Agregat, PDF Print Formatting).
