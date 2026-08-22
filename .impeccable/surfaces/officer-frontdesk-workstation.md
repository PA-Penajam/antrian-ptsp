---
slug: officer-frontdesk-workstation
primary_target: resources/views/pages/officer/counter.blade.php
related_targets:
  - resources/views/components/dashboard/petugas-dashboard.blade.php
  - resources/views/pages/frontdesk/antrian.blade.php
  - app/Http/Controllers/OfficerQueueController.php
  - app/Http/Controllers/FrontdeskQueueController.php
mode: Operate
---

# Surface Brief: Officer Workstation & Frontdesk Intake

## 1. Job and Audience
- **Visitor / Audience:** Petugas loket pelayanan PTSP dan petugas resepsionis/frontdesk.
- **Context:** Bekerja intensif melayani puluhan pemohon setiap hari, membutuhkan kecepatan respon tanpa hambatan klik yang berulang.
- **Mode:** **Operate (High-Density Cockpit & Fast Intake)**

## 2. Outcome and Proof
- **Workstation Loket:** Memanggil antrian berikutnya (`Space` / `F2`), memanggil ulang (`F1`), menyelesaikan (`F4` / `Enter`), dan melewati (`F3`) dengan umpan balik instan dan stopwatch live durasi layanan.
- **Frontdesk Antrian:** Scan barcode/input nomor booking untuk check-in instan serta formulir cepat intake walk-in untuk pemohon prioritas/lansia.

## 3. Selected Direction
- **Visual Authority:** *The Digital Balai Staf Theme* (Zinc Surface Dark `#18181b` / High-Contrast Light Mode, Flux UI Pro Components, Live Pulse Beacon).
- **Layout & Structure:**
  - Workstation: Hero Calling Card dengan timer layanan + Hotkey action panel + Real-time queue pool drawer.
  - Frontdesk: Split-screen panel check-in & walk-in ticket issuer.
