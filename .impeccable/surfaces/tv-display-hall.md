---
slug: tv-display-hall
primary_target: resources/views/livewire/tv-display.blade.php
related_targets:
  - resources/views/pages/tv-display/index.blade.php
  - resources/views/pages/tv-display/legacy.blade.php
  - resources/views/pages/tv-display/login.blade.php
  - app/Http/Controllers/TvDisplayController.php
  - app/Http/Controllers/TvDisplayTtsController.php
mode: Experience
---

# Surface Brief: TV Display Hall & TTS Announcement (Modern & Legacy Track)

## 1. Job and Audience
- **Audience:** Pengunjung dan pencari keadilan yang duduk di ruang tunggu PTSP (jarak pandang 3 s/d 15 meter).
- **Context:** Menunggu nomor antrian dipanggil sambil memperhatikan layar monitor atau mendengarkan pengumuman audio.
- **Mode:** **Experience & Announce** (Visual keterbacaan tinggi jarak jauh & audio jernih).

## 2. Dual-Track Architecture & Outcome
- **✨ Modern TV (`/tv-display`):**
  - Livewire 4 + Alpine.js + Reverb WebSockets + ResponsiveVoice / Web Audio API.
  - Dual-Pane Layout (Hero Caller Panel 60% + Counter Grid/Video 40%), smooth radar pulse glow saat pemanggilan, running text marquee halus.
- **⚡ Legacy TV (`/tv-display/legacy`):**
  - Pure Blade HTML + Vanilla JS Polling (`/tv-legacy/api/state`) + Server-streamed Audio (`/tv-display/tts/*`).
  - Solid High-Contrast Monolithic Layout (Latar `#0f172a`, teks putih tebal, border solid 2px, zero continuous animations, zero blur).
  - Autoplay sound overlay activator ramah remote TV.

## 3. Selected Direction
- **Modern:** *The Digital Balai Glass & Ambient Pulse*.
- **Legacy:** *High-Contrast Solid Industrial Flat*.
