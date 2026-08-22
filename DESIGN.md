---
name: Antrian PTSP
description: Sistem antrian dan manajemen layanan PTSP modern Pengadilan Agama
colors:
  court-cyan: "#0e7490"
  court-cyan-light: "#0891b2"
  oasis-emerald: "#0f766e"
  oasis-emerald-light: "#10b981"
  civic-blue: "#2563eb"
  amber-glow: "#f59e0b"
  coral-crimson: "#ef4444"
  deep-slate: "#0f172a"
  slate-muted: "#64748b"
  soft-mist: "#f8fcfd"
  pure-white: "#ffffff"
  zinc-surface: "#18181b"
  zinc-dark: "#09090b"
typography:
  display:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(2.5rem, 6vw, 4.5rem)"
    fontWeight: 800
    lineHeight: 1
    letterSpacing: "0.02em"
  headline:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2rem"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "-0.01em"
  title:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "normal"
  body:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Instrument Sans, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "0.18em"
rounded:
  sm: "8px"
  md: "12px"
  lg: "16px"
  xl: "24px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  xxl: "48px"
components:
  button-primary:
    backgroundColor: "{colors.court-cyan}"
    textColor: "{colors.pure-white}"
    rounded: "{rounded.lg}"
    padding: "14px 28px"
  button-primary-hover:
    backgroundColor: "{colors.court-cyan-light}"
  button-touch:
    backgroundColor: "{colors.court-cyan}"
    textColor: "{colors.pure-white}"
    rounded: "{rounded.lg}"
    padding: "18px 36px"
    height: "64px"
  card-public:
    backgroundColor: "{colors.pure-white}"
    rounded: "{rounded.xl}"
    padding: "28px"
  input-field:
    backgroundColor: "{colors.pure-white}"
    textColor: "{colors.deep-slate}"
    rounded: "{rounded.lg}"
    padding: "14px 20px"
---

# Design System: Antrian PTSP

## Overview

**Creative North Star: "The Digital Balai"**

Antrian PTSP mengadopsi suasana pelayanan peradilan yang hangat, terbuka, berwibawa, dan menenangkan. Antarmuka dirancang layaknya balai musyawarah modern: ruang yang rapi dan lapang, informasi yang langsung terbaca tanpa prasangka literasi digital, serta alur pelayanan yang memberikan ketenangan dan kepastian bagi setiap pengunjung masyarakat dan petugas di loket.

Sistem visual memadukan keteduhan warna laut dan oasis (*Court Cyan* & *Oasis Emerald*) dengan latar terang yang higienis (*Soft Mist*). Kedalaman antarmuka dibangun secara halus melalui pelapisan permukaan (*layered surfaces*), gradasi latar sejuk, serta radius kurva yang ramah (*rounded-2xl* dan *rounded-3xl*), menghadirkan kesan sentuhan yang humanis dan meyakinkan di layar publik, terminal kiosk, TV display ruang tunggu, hingga workstation internal.

**Key Characteristics:**
- **Jernih & Menenangkan:** Mengeliminasi kecemasan menunggu antrian melalui tata letak lapang, warna sejuk, dan status antrian yang transparan.
- **Ramah Sentuh & Inklusif:** Target interaksi berukuran besar dengan batas radius lembut untuk kenyamanan layar sentuh kiosk dan kemudahan membaca bagi berbagai rentang usia.
- **Hierarki Operasional Presisi:** Pemisahan tegas antara nomor tiket, nomor loket, nama layanan, dan tombol tindakan primer agar petugas dan pengunjung selalu sinkron.

## Colors

Palet *Pelayanan Sejuk & Berwibawa* memancarkan integritas civic yang teduh, dipadukan dengan aksen status operasional yang tegas.

### Primary
- **Court Cyan** (#0e7490): Identitas utama civic; digunakan untuk header navigasi, aksen brand, dan tombol tindakan utama.
- **Court Cyan Light** (#0891b2): State hover, active highlight, dan aksen gradasi dinamis.

### Secondary
- **Oasis Emerald** (#0f766e): Representasi ketenangan dan kepastian; digunakan pada informasi jam layanan, badge penyelesaian, dan elemen institusi.
- **Oasis Emerald Light** (#10b981): Indikator status sukses, panggilan aktif, dan tiket siap diproses.

### Accent & Feedback
- **Civic Blue** (#2563eb): Aksen alur walk-in, transisi tindakan, dan elemen panduan interaktif.
- **Amber Glow** (#f59e0b): Status menunggu (*waiting*), badge penting, penanda perhatian, dan animasi getar gentle pada TV display.
- **Coral Crimson** (#ef4444): Status batal (*cancelled*), tiket terlewat (*skipped*), dan pesan validasi error.

### Neutral
- **Deep Slate** (#0f172a): Warna tipografi utama pada mode terang; menghadirkan kontras teks tajam dan keterbacaan prima.
- **Slate Muted** (#64748b): Tipografi sekunder, label deskriptif, dan garis tepi lembut.
- **Soft Mist** (#f8fcfd): Latar belakang utama kanvas publik dengan gradasi halus menuju putih bersih.
- **Pure White** (#ffffff): Latar kartu, container input, dan permukaan elevated.
- **Zinc Surface** (#18181b): Permukaan panel dan sidebar pada internal mode gelap (*dark theme*).
- **Zinc Dark** (#09090b): Latar kanvas utama mode gelap.

### Named Rules
**The Balai Contrast Rule.** Nomor antrian, status loket, dan instruksi publik wajib memiliki rasio kontras tajam (minimal 4.5:1 untuk teks biasa dan 3:1 untuk nomor besar/display) tanpa gradasi redup yang mengaburkan informasi.

**The Palette Restraint Rule.** Warna aksen status (*Amber* & *Coral*) hanya boleh dipakai untuk status tiket atau peringatan sistem, tidak boleh digunakan sebagai warna latar hiasan umum.

## Typography

**Display Font:** Instrument Sans (fallback: ui-sans-serif, system-ui, sans-serif)  
**Body Font:** Instrument Sans (fallback: ui-sans-serif, system-ui, sans-serif)  
**Label / Monospace Font:** Instrument Sans (dengan `tracking-[0.18em]` dan `uppercase` untuk label kategori)

**Character:** Tipografi humanis modern dengan keterbacaan kristal pada skala raksasa (TV Display & Kiosk) maupun densitas tinggi (Workstation Loket & Tabel Laporan).

### Hierarchy
- **Display** (800 font-black, clamp(2.5rem, 6vw, 4.5rem), line-height: 1): Nomor tiket utama pada TV Display, Kiosk hasil cari tiket, dan nomor loket raksasa.
- **Headline** (700 bold, 2rem / 32px, line-height: 1.2): Judul halaman publik, nama modul, dan kartu layanan utama.
- **Title** (600 semibold, 1.25rem / 20px, line-height: 1.3): Nama loket, label kelompok layanan, dan judul modal dialog.
- **Body** (400 regular & 500 medium, 1rem / 16px, line-height: 1.5): Teks penjelasan, instruksi formulir, dan tabel data staf.
- **Label** (600 semibold, 0.75rem / 12px, line-height: 1.2, uppercase, tracking: 0.18em): Kategori layanan, status badge, label jam operasional, dan heading menu.

### Named Rules
**The Big Ticket Rule.** Nomor tiket yang dipanggil atau dicetak harus selalu menggunakan bobot `font-black` dan menjadi elemen visual paling dominan pada layar bersangkutan.

## Layout

Tata letak Antrian PTSP terbagi menjadi empat pola spesifik sesuai konteks pengguna:
1. **Public Self-Service:** Container terpusat (`max-w-6xl` hingga `max-w-7xl`), header bertingkat dengan glassmorphism (`bg-white/95 backdrop-blur-md`), widget sinkronisasi live pantauan antrian hari ini, dan grid kartu layanan 1-3 kolom yang adaptif.
2. **Kiosk Touch Station:** Layar penuh terpusat (`max-w-5xl`), padding vertikal luas, tombol berorientasi sentuh satu sentuhan (tinggi 64px), dan layout alur wizard tanpa scroll berlebih.
3. **TV Display Hall:** Grid 2 kolom berimbang (`h-screen overflow-hidden`) — panel pemanggilan loket aktif di sisi kiri (60%) dan video edukasi / antrian antrean berjalan di sisi kanan (40%), dilengkapi running text marquee di bagian bawah.
4. **Internal Workstation / Admin:** Sidebar tetap collapsible, area kerja berkepadatan efisien dengan tabel flux dan widget monitoring status loket.

## Elevation & Depth

Sistem mengutamakan **Soft Layered & Ambient Depth** — kedalaman dibangun dari tumpukan permukaan putih dengan border tipis cyan/slate dan bayangan ambient sejuk, bukan bayangan hitam tajam.

### Shadow Vocabulary
- **Ambient Header:** `box-shadow: 0 18px 50px -32px rgba(14, 116, 144, 0.35)` — Menahan header mengapung lembut di atas kanvas publik.
- **Hero Elevated:** `box-shadow: 0 24px 60px -36px rgba(14, 116, 144, 0.35)` — Memberikan kedalaman pada container utama Balai publik.
- **Card Public:** `box-shadow: 0 14px 34px -24px rgba(15, 23, 42, 0.14)` (hover: `0 24px 48px -24px rgba(14, 116, 144, 0.22)`) — Elevasi kartu katalog layanan publik.
- **Card Elevated (Admin):** `box-shadow: 0 24px 60px -48px rgba(15, 23, 42, 0.18)` — Memberi elevasi pada kartu statistik dan modul utama dashboard staf.
- **Kiosk Active Card:** `box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)` — Penanda fokus jelas pada terminal cetak tiket.
- **TV Pulse Glow:** `box-shadow: 0 0 0 12px rgba(245, 158, 11, 0)` — Efek animasi radar gentle saat nomor tiket sedang dipanggil.

### Named Rules
**The Clean Surface Rule.** Seluruh kartu elevasi di atas background gradasi wajib memiliki background semi-transparan `bg-white/95` atau `bg-white` solid dengan border `border-cyan-100` atau `border-slate-200` untuk menjaga batas kontur.

## Shapes

- **Form Language:** Kurva membulat luas yang bersahabat dan ramah sentuhan.
- **Radius Scale:**
  - `rounded-2xl` (16px): Standar default untuk tombol, input text, dialog kecil, widget live, dan kartu statistik.
  - `rounded-3xl` (24px): Kartu layanan publik, hero container, modul Kiosk, panel menu dropdown, dan footer section.
  - `rounded-xl` (12px): Sub-elemen internal, list item, dan badge ikon.
  - `rounded-full` (9999px): Badge status, live beacon sync, navbar pill, dan tombol toggle ikon bulat.

## Components

### Buttons
- **Shape:** `rounded-2xl` (16px) dengan transisi halus (`transition duration-200`).
- **Primary:** Latar gradien Court Cyan (`from-cyan-700 via-cyan-600 to-teal-700`), teks putih, shadow lembut `shadow-cyan-700/25`.
- **Kiosk Touch Button:** Tinggi minimal 64px (`h-16`), font tebal berukuran besar (`text-xl font-bold`), padding ekstra `px-8`.
- **Subtle / Ghost:** Latar `bg-white` atau `bg-cyan-50` dengan teks `text-cyan-950` dan border `border-cyan-200`.

### Live Queue Monitor Widget
- **Sync Beacon:** Badge live sync emerald dengan dot animasi ping pulsing (`animate-ping`).
- **Stat Cards Trio:** 3 kartu mini gradien (Total Tiket Cyan, Menunggu Amber, Selesai Emerald) dengan hover lift (`-translate-y-1`).
- **Calling Card:** Container border 2px emerald dengan badge loket tebal dan nomor antrian font-black.
- **Quick Lookup:** Input nomor tiket instan dengan loading state responsif dan kartu panduan posisi antrian.

### Cards & Containers
- **Corner Style:** `rounded-2xl` hingga `rounded-3xl`.
- **Background:** `bg-white` atau `bg-white/95 backdrop-blur` dengan border `border-cyan-200/80` (publik) atau `border-zinc-200 dark:border-zinc-700` (internal).
- **Service Cards:** Kartu katalog interaktif dengan filter pill, badge kuota, accordion persyaratan instan (`x-collapse`), dan CTA langsung.
- **Stat Cards (Dashboard):** Gradasi pastel lembut per kategori status (`.admin-stat-total`, `.admin-stat-success`, `.admin-stat-warning`, `.admin-stat-danger`).

### Inputs & Form Fields
- **Style:** Border tegas 2px (`border-slate-200` atau `border-cyan-200`), latar putih, sudut `rounded-2xl`, tinggi minimal 48px (standar) atau 64px (kiosk).
- **Focus:** Ring fokus kontras cyan `ring-2 ring-cyan-500 ring-offset-2`.
- **Error:** Border `border-red-500` dengan pesan teks merah di bawah field.

### Badges & Chips
- **Style:** `rounded-full`, padding `px-3 py-1`, teks berukuran `text-xs font-semibold tracking-[0.16em] uppercase`.
- **Variants:** Emerald (Sukses / Jam Layanan), Amber (Menunggu), Red (Batal), Cyan (Layanan Aktif), Blue (Walk-in).

### Navigation & Header
- **Public Header:** Header melayang dengan logo instansi adaptif (border rounded-2xl), judul instansi dengan tracking lebar, dan navbar pill di tengah.
- **Staff Sidebar:** Sidebar vertikal collapsible dengan grouping bertingkat, ikon Flux, dan switch mode terang/gelap.

## Do's and Don'ts

### Do:
- **Do** gunakan nama dan logo instansi yang bersumber dinamis dari `config('institution.*')` tanpa hardcode identitas pengadilan di template.
- **Do** berikan target sentuh minimal 48px (ideal 64px) untuk semua tombol yang dapat diakses di Kiosk publik.
- **Do** sertakan visual feedback instan (indikator loading, spinner, atau bunyi TTS) ketika tombol panggil antrian ditekan.
- **Do** jaga kontras teks nomor antrian dan nomor loket tetap tajam di segala sudut pandang TV Display.
- **Do** sediakan layout responsif yang tetap rapi dari layar ponsel kecil hingga monitor TV 4K.

### Don't:
- **Don't** menggunakan warna merah atau kuning sebagai latar dekorasi umum karena dikhususkan untuk status penting (*batal* / *menunggu*).
- **Don't** menjejalkan teks jargon hukum atau istilah teknis sistem ke layar publik.
- **Don't** menggunakan font display dengan dekorasi rumit atau tipis yang sulit dibaca dari jarak jauh di ruang tunggu.
- **Don't** menghilangkan mode kontras tinggi dan fallback HTML sederhana (*legacy mode*) demi menjaga kompatibilitas hardware kiosk/TV lama.
