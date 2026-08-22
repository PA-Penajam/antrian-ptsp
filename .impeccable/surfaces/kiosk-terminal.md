---
slug: kiosk-terminal
primary_target: resources/views/livewire/kiosk-booking.blade.php
related_targets:
  - resources/views/pages/kiosk/index.blade.php
  - resources/views/pages/kiosk/legacy.blade.php
  - resources/views/pages/kiosk/login.blade.php
  - app/Http/Controllers/KioskController.php
mode: Operate
---

# Surface Brief: Kiosk Touch Station (Modern & Legacy Track)

## 1. Job and Audience
- **Visitor / Audience:** Pengunjung walk-in di lobi PTSP dari berbagai latar belakang usia dan tingkat literasi digital.
- **Context & Mindset:** Berada langsung di depan mesin antrian mandiri, ingin mengambil tiket secepat mungkin tanpa kebingungan.
- **Mode:** **Operate (Zero Friction, Instant Touch)**

## 2. Dual-Track Architecture & Outcome
- **✨ Modern Kiosk (`/kiosk`):**
  - Livewire 4 + Alpine.js + Tailwind CSS v4 + Thermal Printer Bridge.
  - Kartu sentuh beranimasi halus, On-Screen Virtual Numpad responsif, progress visual, dan integrasi modal reprint.
- **⚡ Legacy Kiosk (`/kiosk-legacy`):**
  - Pure Blade Server-rendered HTML + Bootstrap 5 / Plain CSS + Vanilla JS.
  - Low-overhead Solid Flat Industrial Design (Zero Blur, Zero Heavy Drop Shadows, Zero Continuous Animations).
  - Target tombol ekstra besar (**72px - 88px**) untuk layar sentuh resistif jadul.
  - Fail-safe direct POST form submission ke `/kiosk-legacy/print`.

## 3. Selected Direction
- **Modern:** *The Digital Balai Glass & Ambient Lift*.
- **Legacy:** *High-Contrast Solid Industrial Flat*.
- **Shared Operational Features:**
  - Auto-reset timer 30 detik kembali ke beranda.
  - Cetak ulang tiket booking online via NIK / No HP.
  - Indikator status printer thermal aktif/offline.
