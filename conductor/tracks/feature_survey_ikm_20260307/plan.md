# Implementation Plan: Sistem Survey Indeks Kepuasan Masyarakat (IKM)

## Phase 1: Database & Model Setup
- [ ] Task: Buat migration dan model `Survey` dan `SurveyResponse` yang berelasi dengan tabel `QueueTicket` dan `Service`/`Counter`.
- [ ] Task: Buat endpoint backend dan logic untuk memverifikasi apakah sebuah tiket valid dan belum pernah mengisi survey.
- [ ] Task: Conductor - User Manual Verification 'Phase 1: Database & Model Setup' (Protocol in workflow.md)

## Phase 2: Halaman Publik Survey (UI/UX)
- [ ] Task: Buat Livewire component untuk halaman pengisian IKM menggunakan form dan rating dari Flux UI Pro.
- [ ] Task: Buat routing publik untuk mengakses halaman IKM menggunakan hash token atau nomor tiket.
- [ ] Task: Integrasikan link survey ke halaman "Lookup Antrian" (setelah status tiket menjadi "completed").
- [ ] Task: Conductor - User Manual Verification 'Phase 2: Halaman Publik Survey (UI/UX)' (Protocol in workflow.md)

## Phase 3: Dashboard Laporan IKM
- [ ] Task: Buat Livewire component di sisi Laporan/Admin untuk menampilkan agregasi/rata-rata rating kepuasan per layanan/loket.
- [ ] Task: Tambahkan chart atau tabel (menggunakan Flux UI) untuk menampilkan distribusi kepuasan.
- [ ] Task: Pastikan semua test (Feature/Unit) berjalan dengan baik.
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Dashboard Laporan IKM' (Protocol in workflow.md)