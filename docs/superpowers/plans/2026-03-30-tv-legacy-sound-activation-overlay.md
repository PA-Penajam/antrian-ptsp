# TV-Legacy Sound Activation Overlay — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix audio yang tidak keluar di LG Smart TV webOS dengan menambahkan sound activation overlay yang meminta user gesture sebelum memutar audio.

**Architecture:** Menambahkan overlay HTML + CSS inline di blade view, lalu memodifikasi fungsi JavaScript `unlockAudioIfNeeded()` agar unlock langsung pada elemen media yang sebenarnya (`audioPlayer` dan `tvPlayer`). Semua perubahan di 1 file.

**Tech Stack:** HTML, CSS (inline di Blade), vanilla JavaScript, jQuery (sudah ada)

---

### Task 1: Tambah overlay HTML dan CSS

**Files:**
- Modify: `resources/views/pages/tv-display/legacy.blade.php:6-14` (tambah CSS overlay di block `@push('styles')`)
- Modify: `resources/views/pages/tv-display/legacy.blade.php:347` (tambah overlay HTML sebelum `</div>` penutup dan `<audio>`)

- [ ] **Step 1: Tambah CSS untuk overlay di dalam block `@push('styles') <style>`**

Sisipkan setelah baris `body { ... }` block (setelah line 13 `}`):

```css
    /* === Sound Activation Overlay === */
    .sound-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.88);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: opacity 0.4s ease;
    }

    .sound-overlay.fade-out {
        opacity: 0;
        pointer-events: none;
    }

    .sound-overlay-icon {
        font-size: 5rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
        animation: pulse-icon 2s ease-in-out infinite;
    }

    .sound-overlay-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
    }

    .sound-overlay-subtitle {
        font-size: 1.3rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.6);
    }

    @keyframes pulse-icon {
        0%, 100% { transform: scale(1); opacity: 0.7; }
        50% { transform: scale(1.12); opacity: 1; }
    }
```

- [ ] **Step 2: Tambah overlay HTML**

Sisipkan tepat sebelum baris `<audio id="ttsAudio" style="display:none;"></audio>` (line 349):

```html
{{-- Overlay aktivasi suara — diperlukan untuk memenuhi Autoplay Policy browser --}}
<div id="soundOverlay" class="sound-overlay">
    <i class="ki-duotone ki-speaker sound-overlay-icon">
        <span class="path1"></span>
        <span class="path2"></span>
    </i>
    <div class="sound-overlay-title">Tekan Tombol Apa Saja</div>
    <div class="sound-overlay-subtitle">untuk Mengaktifkan Suara</div>
</div>
```

- [ ] **Step 3: Verifikasi visual — buka halaman tv-legacy di browser**

Buka URL tv-legacy (via `get-absolute-url`). Pastikan:
- Overlay muncul fullscreen dengan background gelap
- Ikon speaker terlihat dengan animasi pulse
- Teks jelas terbaca
- Konten di belakang overlay tersembunyi

---

### Task 2: Refactor fungsi `unlockAudioIfNeeded` menjadi `activateSound`

**Files:**
- Modify: `resources/views/pages/tv-display/legacy.blade.php:404-405` (ganti event listener registration)
- Modify: `resources/views/pages/tv-display/legacy.blade.php:437-441` (ganti fungsi `unlockAudioIfNeeded`)

- [ ] **Step 1: Ganti registrasi event listener di `$(document).ready`**

Ganti baris 404-405:

```javascript
        document.addEventListener('click', unlockAudioIfNeeded, { once: true });
        document.addEventListener('keydown', unlockAudioIfNeeded, { once: true });
```

Menjadi:

```javascript
        document.addEventListener('click', activateSound);
        document.addEventListener('keydown', activateSound);
```

Catatan: Tidak pakai `{ once: true }` karena cleanup manual dilakukan di dalam `activateSound`.

- [ ] **Step 2: Ganti fungsi `unlockAudioIfNeeded` dengan `activateSound`**

Ganti baris 437-441:

```javascript
    function unlockAudioIfNeeded() {
        var unlockAudio = new Audio('data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA');
        unlockAudio.volume = 0;
        unlockAudio.play().catch(function () {});
    }
```

Menjadi:

```javascript
    var soundActivated = false;

    function activateSound() {
        if (soundActivated) { return; }
        soundActivated = true;

        // Hapus event listeners
        document.removeEventListener('click', activateSound);
        document.removeEventListener('keydown', activateSound);

        // Unmute video player
        tvPlayer.muted = false;

        // Unlock audio element yang sebenarnya dengan silent clip
        audioPlayer.src = 'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA';
        audioPlayer.volume = 0;
        var unlockPromise = audioPlayer.play();
        if (unlockPromise && typeof unlockPromise.catch === 'function') {
            unlockPromise
                .then(function () {
                    audioPlayer.pause();
                    audioPlayer.removeAttribute('src');
                    audioPlayer.volume = 1;
                })
                .catch(function () {
                    audioPlayer.removeAttribute('src');
                    audioPlayer.volume = 1;
                });
        }

        // Fade-out dan hapus overlay
        var overlay = document.getElementById('soundOverlay');
        if (overlay) {
            overlay.classList.add('fade-out');
            setTimeout(function () {
                overlay.parentNode.removeChild(overlay);
            }, 400);
        }
    }
```

- [ ] **Step 3: Verifikasi — buka halaman, tekan tombol keyboard/klik**

Pastikan:
- Overlay hilang dengan animasi fade-out setelah tekan tombol
- Tidak ada error di console browser
- Variabel `soundActivated` mencegah eksekusi ganda

---

### Task 3: Tambah console.warn di `speakWithBrowserTts` saat speechSynthesis tidak tersedia

**Files:**
- Modify: `resources/views/pages/tv-display/legacy.blade.php:673-676` (tambah warning log)

- [ ] **Step 1: Tambah console.warn**

Ganti baris 673-676:

```javascript
        if (!('speechSynthesis' in window)) {
            tvPlayer.volume = 1;
            return;
        }
```

Menjadi:

```javascript
        if (!('speechSynthesis' in window)) {
            console.warn('TV Display: speechSynthesis tidak tersedia di browser ini. Fallback TTS dilewati.');
            tvPlayer.volume = 1;
            return;
        }
```

- [ ] **Step 2: Verifikasi — buka console browser**

Jika `speechSynthesis` tidak tersedia (misalnya di webOS), pesan warning harus muncul di console. Ini membantu debugging di lapangan.

---

### Task 4: Verifikasi end-to-end audio flow

**Files:**
- Tidak ada perubahan file — hanya verifikasi manual

- [ ] **Step 1: Test skenario — overlay muncul dan hilang**

1. Buka halaman tv-legacy
2. Pastikan overlay muncul
3. Tekan tombol apa saja (klik / Enter / arrow key)
4. Pastikan overlay hilang dengan fade-out

- [ ] **Step 2: Test skenario — video bersuara**

1. Setelah overlay hilang, pastikan video promosi bersuara (jika video punya audio track)
2. Verifikasi `tvPlayer.muted` bernilai `false` di console: ketik `document.getElementById('tvPlayer').muted`

- [ ] **Step 3: Test skenario — TTS announcement bersuara**

1. Panggil nomor antrian dari loket
2. Pastikan suara announcement "Nomor antrian... Silakan menuju..." terdengar
3. Pastikan volume video turun selama announcement dan naik kembali setelah selesai

- [ ] **Step 4: Test skenario — tanpa interaksi (overlay masih tampil)**

1. Buka halaman tv-legacy, JANGAN tekan tombol apapun
2. Panggil nomor antrian dari loket
3. Pastikan announcement TIDAK berbunyi (audio diblokir karena belum ada user gesture)
4. Ini adalah perilaku yang diharapkan — overlay menunjukkan bahwa user perlu menekan tombol

- [ ] **Step 5: Commit semua perubahan**

```bash
git add resources/views/pages/tv-display/legacy.blade.php
git commit -m "fix(tv-legacy): tambah sound activation overlay untuk webOS autoplay policy

Menambahkan overlay interaksi yang meminta user menekan tombol untuk
mengaktifkan suara. Memperbaiki:
- Video muted yang tidak pernah di-unmute
- Audio element unlock pada objek throwaway, bukan elemen yang sebenarnya
- Tidak ada warning saat speechSynthesis tidak tersedia"
```
