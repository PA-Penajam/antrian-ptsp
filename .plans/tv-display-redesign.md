# Plan: Redesign TV Display — LG 32"

> **Status:** ✅ Completed (2026-03-08)
> **Dibuat:** 2026-03-08
> **Target:** Redesign halaman `/tv-display` dari dark theme monoton ke light theme profesional
> **Hardware:** LG 32 inch Smart TV (webOS, Chromium 94-108)

---

## Konteks

### Masalah Saat Ini
1. Dark theme (`bg-zinc-950`) memantulkan bayangan di ruangan terang (lampu neon kantor)
2. Font terlalu kecil untuk jarak pandang 2-5m (TV 32")
3. Split ratio 45/55 salah — video mendapat lebih banyak ruang dari antrian
4. Tabel riwayat tidak terbaca dari jauh (font `text-sm`)
5. Tidak ada branding institusi (logo, nama kantor)
6. Semua nomor dipanggil memiliki visual weight yang sama
7. YouTube menampilkan konten tidak relevan (dev tutorial)

### Parameter TV 32" LG
| Parameter | Nilai |
|---|---|
| Resolusi render | 1920x1080 atau 1366x768 |
| Jarak pandang optimal | 2-5 meter |
| Min font nomor utama | 100-120px |
| Min font sekunder | 28-36px |
| Overscan padding | 20-30px |
| Browser engine | Chromium 94-108 (webOS) |

### Kompatibilitas WebOS (Hasil Riset)
- **Aman:** CSS Grid, Flexbox, Custom Properties, ES6+, Alpine.js, Livewire JS, YouTube iframe
- **Hindari:** `backdrop-blur` (frame drop), gradient animasi (berat GPU), Vite HMR (tidak stabil)
- **Wajib:** Production build (`npm run build`), HTTPS, `wire:poll.keep-alive`

---

## Color System

| Role | Warna | Tailwind Class | Keterangan |
|---|---|---|---|
| Primary | Navy Blue | `blue-900` | Header, nomor utama |
| Accent | Amber | `amber-500` | Highlight nomor aktif dipanggil |
| Success | Emerald | `emerald-600` | Status indicator |
| Surface | White / Slate 50 | `white` / `slate-50` | Cards, background |
| Text Primary | Slate 900 | `slate-900` | Teks utama |
| Text Secondary | Slate 500 | `slate-500` | Teks pendukung |
| Header BG | Navy Blue | `blue-900` | Background header |
| Header Text | White | `white` | Teks header |

---

## Wireframe Layout

```
┌──────────────────────────────────────────────────────────┐
│ [LOGO] PENGADILAN AGAMA          08:35:22  Senin-Jumat.. │ ← Header navy blue
├────────────────────────────────┬─────────────────────────┤
│                                │                         │
│   SEDANG DIPANGGIL             │                         │
│  ┌──────────────────────┐      │                         │
│  │     SLX71-001        │      │      [VIDEO PLAYER]     │
│  │     LOKET 03         │ Hero │                         │
│  └──────────────────────┘      │                         │
│                                │                         │
│  ┌────────────┐ ┌────────────┐ │                         │
│  │ E2ESVC-005 │ │ SYX84-012  │ │                         │
│  │ Loket 01   │ │ Loket 02   │ │                         │
│  └────────────┘ └────────────┘ │                         │
│                                │                         │
│   RIWAYAT                      │                         │
│   PLH-011  Loket 04  08:30    │                         │
│   PLH-012  Loket 01  08:25    │                         │
│   PLH-013  Loket 03  08:20    │                         │
│        60%                     │          40%            │
└────────────────────────────────┴─────────────────────────┘
```

**Split ratio:** Queue 60% | Video 40%

---

## Tasks

### Task 1: Meta Tags TV-Optimized
- **File:** `resources/views/pages/tv-display/index.blade.php`
- **Status:** `[x]`
- **Perubahan:**
  - Ubah viewport meta: tambah `maximum-scale=1.0, user-scalable=no`
  - Tambah `<meta name="format-detection" content="telephone=no">`
  - Ubah body class: `bg-zinc-950 text-white` → `bg-slate-50 text-slate-900`
  - Tambah inline style: `cursor: none` pada body

### Task 2: Redesign Header — Branding Institusi
- **File:** `resources/views/livewire/tv-display.blade.php` (bagian header)
- **Status:** `[x]`
- **Perubahan:**
  - Header background: `bg-blue-900 text-white`
  - Tambah logo institusi: `config('institution.logo_path')` — tampilkan dengan `<img>` jika path ada, fallback ke icon/text
  - Tampilkan nama institusi: `config('institution.name')`
  - Running text/marquee: jam operasional dari `config('institution.operating_hours')`
  - Pertahankan jam real-time (sudah ada)
  - Layout header: `flex items-center justify-between`
  - Logo + nama di kiri, jam di kanan, info operasional di tengah/bawah sebagai ticker

### Task 3: Redesign Panel Nomor Dipanggil (Hero Area)
- **File:** `resources/views/livewire/tv-display.blade.php` (bagian currentCalls)
- **Status:** `[x]`
- **Perubahan:**
  - **Nomor terbaru** (index 0) → Hero card besar
    - Font nomor: `text-7xl` hingga `text-8xl` (100-120px)
    - Font loket: `text-3xl`
    - Card style: `bg-white rounded-2xl shadow-lg border-l-8 border-amber-500`
    - Animasi `pulse-gentle` CSS keyframe saat baru muncul
  - **Nomor lainnya** → Card lebih kecil di bawah hero
    - Font nomor: `text-4xl`
    - Font loket: `text-xl`
    - Card style: `bg-white rounded-xl shadow-sm`
    - Layout: grid 2 kolom untuk nomor non-hero
  - Hapus `bg-amber-500 text-zinc-950` → ganti white card + amber left border
  - Label "SEDANG DIPANGGIL": `text-blue-900 font-bold text-2xl uppercase tracking-wider`
  - State kosong: `bg-white rounded-2xl` dengan teks `text-slate-400`

### Task 4: Redesign Riwayat Panggilan — Simplified Cards
- **File:** `resources/views/livewire/tv-display.blade.php` (bagian recentCalls)
- **File (backend):** `app/Livewire/TvDisplay.php` — ubah limit `recentCalls()` dari 20 → 4
- **Status:** `[x]`
- **Perubahan:**
  - **Hapus `<table>`** — ganti dengan card list vertikal
  - Tampilkan hanya **4 item terakhir** (bukan 20)
  - Setiap card: layout horizontal (`flex items-center justify-between`)
    - Kiri: Nomor antrian (`text-xl font-bold font-mono text-slate-700`)
    - Tengah: Nama loket (`text-lg text-slate-500`)
    - Kanan: Waktu (`text-lg font-mono text-slate-400`)
  - Card style: `bg-white rounded-xl p-4 shadow-sm`
  - Opacity cascade: item 1 = `opacity-90`, item 2 = `opacity-75`, item 3 = `opacity-60`, item 4 = `opacity-45`
  - Label "RIWAYAT": `text-slate-500 font-semibold text-lg uppercase tracking-wider`
  - Item yang masih `Called` diberi aksen: `border-l-4 border-amber-400`

### Task 5: Redesign Panel Video — Proporsi 60/40
- **File:** `resources/views/livewire/tv-display.blade.php` (bagian video)
- **Status:** `[x]`
- **Perubahan:**
  - Queue panel: `w-[60%]` (dari `w-1/2 xl:w-[45%]`)
  - Video panel: `w-[40%]` (dari `w-1/2 xl:w-[55%]`)
  - Border antar panel: `border-slate-200` (dari `border-zinc-800`)
  - Video container background: `bg-slate-100` (dari `bg-black`)
  - Tambah rounded corners pada video: `rounded-xl overflow-hidden m-4`
  - Pertahankan semua logic video player & YouTube fallback (tidak berubah)

### Task 6: Light Theme Root & TV-Specific CSS
- **File:** `resources/views/livewire/tv-display.blade.php` (root div)
- **Status:** `[x]`
- **Perubahan:**
  - Root div class: `bg-slate-50 text-slate-900` (dari `bg-zinc-950 text-white`)
  - Tambah safe-area padding: `p-5` pada root (overscan protection ~20px)
  - Ubah `wire:poll.5s` → `wire:poll.5s.keep-alive` (cegah throttling webOS)
  - Connection indicator: hapus `backdrop-blur-sm`, ganti `bg-red-900/90` → `bg-red-500 text-white`
  - Hapus semua referensi warna zinc-800/900/950 dari UI
  - Hidden logout button: sesuaikan warna ke light theme

### Task 7: Animasi & Polish
- **File:** `resources/views/livewire/tv-display.blade.php` atau inline `<style>`
- **Status:** `[x]`
- **Perubahan:**
  - CSS `@keyframes pulse-gentle` untuk hero card:
    ```css
    @keyframes pulse-gentle {
      0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
      50% { box-shadow: 0 0 0 12px rgba(245, 158, 11, 0); }
    }
    ```
  - Alpine `x-transition` pada card nomor antrian (sudah ada Alpine, manfaatkan)
  - Running text (CSS marquee) untuk informasi layanan di header
    ```css
    @keyframes marquee {
      0% { transform: translateX(100%); }
      100% { transform: translateX(-100%); }
    }
    ```
  - Transisi halus saat data berubah via Livewire morphing

---

## File yang Dimodifikasi

| File | Task | Jenis Perubahan |
|---|---|---|
| `resources/views/pages/tv-display/index.blade.php` | 1, 6 | Meta tags, body class |
| `resources/views/livewire/tv-display.blade.php` | 2, 3, 4, 5, 6, 7 | Full UI redesign |
| `app/Livewire/TvDisplay.php` | 4 | Ubah limit recentCalls: 20 → 4 |

**Total file dimodifikasi:** 3
**File baru:** 0
**Dependency baru:** 0

---

## Batasan Teknis (HARUS Dipatuhi)

1. **DILARANG** menggunakan `backdrop-blur` — frame drop di webOS
2. **DILARANG** menggunakan gradient animasi — berat GPU TV
3. **DILARANG** menggunakan `box-shadow` kompleks berlebihan
4. **WAJIB** production build (`npm run build`) — Vite HMR tidak stabil di TV
5. **WAJIB** HTTPS untuk fitur modern Chromium TV
6. **WAJIB** `wire:poll.keep-alive` agar polling tidak di-throttle webOS
7. Font tetap `Instrument Sans` (sudah bundled via Vite, .woff2)
8. Desain berbasis resolusi **1920x1080** (browser TV merender di 1080p lalu upscale)
9. Hindari manipulasi DOM berlebihan di Alpine (memory leak risk pada RAM terbatas TV)

---

## Verifikasi Setelah Implementasi

- [x] `vendor/bin/pint --dirty --format agent` — format PHP
- [x] `npm run build` — pastikan build sukses
- [x] LSP diagnostics clean pada 3 file yang dimodifikasi
- [x] Visual check di browser desktop (1920x1080 viewport)
- [ ] Test di TV LG 32" (jika memungkinkan)
- [x] `wire:poll.keep-alive` berjalan (cek Network tab: request setiap 5 detik)
- [x] Video player tetap berfungsi (local + YouTube fallback)
- [x] Logo/branding tampil jika `INSTITUTION_LOGO_PATH` diset di `.env`
