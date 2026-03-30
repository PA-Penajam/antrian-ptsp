# TV-Legacy Sound Activation Overlay

**Tanggal:** 2026-03-30
**Status:** Draft
**Scope:** Fix audio tidak keluar di LG Smart TV webOS

---

## Konteks Masalah

Halaman tv-legacy (`/tv-legacy`) tidak mengeluarkan suara sama sekali saat dijalankan di browser bawaan LG Smart TV (webOS 22 / Chromium 94+). Ini mencakup:

1. **Video promosi** — elemen `<video>` memiliki atribut `muted` yang tidak pernah di-unmute secara programatik. `tvPlayer.volume = 1` tidak berpengaruh selama `muted=true`.
2. **TTS Announcement** — `audioPlayer.play()` diblokir oleh Autoplay Policy karena tidak ada user gesture sebelumnya.
3. **Speech Synthesis Fallback** — `window.speechSynthesis` tidak tersedia di browser webOS, sehingga fallback juga gagal tanpa suara.
4. **Audio Unlock tidak efektif** — `unlockAudioIfNeeded()` membuat objek `Audio` baru yang langsung dibuang, bukan melakukan unlock pada elemen `<audio id="ttsAudio">` yang sebenarnya dipakai.

## Perangkat Target

- LG Smart TV dengan webOS 22 (v7.5.7-25)
- Browser bawaan webOS (Chromium 94+ engine)
- Kontrol via remote TV (OK button, arrow keys)

## Solusi

### Pendekatan: Overlay Interaksi Sederhana

Tampilkan overlay fullscreen saat halaman dimuat. Saat user menekan tombol apa saja di remote TV, overlay dihapus dan semua audio di-unlock.

### File yang Diubah

Hanya **1 file**: `resources/views/pages/tv-display/legacy.blade.php`

Tidak ada file baru, controller baru, atau route baru.

### Overlay UI

- **Posisi:** Fullscreen overlay, `position: fixed`, z-index tinggi (di atas semua konten)
- **Background:** `rgba(0,0,0,0.85)` — gelap agar pesan jelas terlihat
- **Konten:**
  - Ikon speaker muted (menggunakan icon set yang sudah ada di project)
  - Teks utama: "Tekan Tombol Apa Saja"
  - Teks sub: "untuk Mengaktifkan Suara"
  - Teks petunjuk: "Tekan OK di Remote TV"
- **Animasi:** Pulse ringan pada ikon agar menarik perhatian
- **Font size:** Besar, mudah dibaca dari jarak 2-3 meter (konteks TV)

### Logika Audio Unlock

Pada event `click` atau `keydown` pertama setelah halaman dimuat:

1. **Unmute video:** `tvPlayer.muted = false`
2. **Unlock audio element:** Play silent WAV base64 melalui `audioPlayer` (elemen `<audio id="ttsAudio">` yang sebenarnya), bukan objek `new Audio()` yang dibuang
3. **Hapus overlay:** Fade-out lalu remove dari DOM
4. **Cleanup:** Hapus event listener yang sudah tidak diperlukan

### Perubahan pada Fungsi Existing

| Fungsi | Sebelum | Sesudah |
|--------|---------|---------|
| `unlockAudioIfNeeded()` | Membuat `new Audio()` throwaway dan play silent clip | Langsung play silent clip melalui `audioPlayer`, set `tvPlayer.muted = false`, hapus overlay |
| Event listener `click`/`keydown` (line 404-405) | Memanggil `unlockAudioIfNeeded` dengan `{ once: true }` | Digabung ke fungsi baru `activateSound()` yang handle overlay + unlock |
| `speakWithBrowserTts()` | Return diam jika `speechSynthesis` tidak ada | Tambah `console.warn` agar tidak gagal diam-diam, tetap return tanpa error |

### Yang TIDAK Berubah

- Alur TTS (MiniMax API → browser fallback) tetap sama
- Polling state tetap 5 detik
- Video playlist logic tetap sama
- Backend (controller, service, routes) tidak berubah
- Layout dan styling konten di belakang overlay tetap sama

### Perilaku Sesi

| Situasi | Perlu tekan tombol? |
|---------|-------------------|
| Halaman baru dimuat | Ya, sekali |
| TV sleep lalu bangun (halaman masih di memory) | Tidak |
| TV restart / mati-nyala | Ya, sekali |
| Halaman di-refresh | Ya, sekali |

Ini adalah keterbatasan Autoplay Policy browser yang tidak bisa di-bypass.

## Acceptance Criteria

1. Overlay muncul saat halaman dimuat
2. Overlay hilang setelah user menekan tombol apa saja (remote OK, arrow, atau klik)
3. Video promosi bersuara setelah overlay dihilangkan
4. TTS announcement bersuara setelah overlay dihilangkan
5. Tidak ada regresi pada tampilan antrian, polling, atau video playlist
6. Kompatibel dengan browser bawaan LG webOS 22
