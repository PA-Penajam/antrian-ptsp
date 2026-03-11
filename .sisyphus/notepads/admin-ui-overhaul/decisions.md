# Architectural Decisions

## 2026-03-08 Session: ses_336972af3ffeNRQxCYruthTCWe

### Wave Execution Strategy
- Wave 1: All 5 tasks parallel (T1, T2, T3, T4, T5)
- Wave 2: T6 + T7 parallel; T8 after T7; T9 after T7 (parallel with T8)
- Wave 3: T10 + T11 + T12 parallel; T13 after T12
- Wave 4: T14 starts after Wave 1; T15 after T14; T16 after T15
- Wave 5: T17 starts after Wave 1 (parallel with T14); T18 after T17; T19 after T18
- Wave 6: T20 + T21 parallel (after all previous)

### Must NOT Change
- Public routes: /, /antrian, /display
- Database schema (except config/kiosk.php)
- Queue engine / nomor antrian logic
- Role/permission model/enum

### Key Conventions
- Route naming: admin.layanan.index, admin.layanan.store, etc.
- Kiosk session key: kiosk_authenticated
- TV Display session key: tv_display_authenticated  
- Shared password from config('kiosk.password')
- Admin-only dark/light toggle (NOT on kiosk or TV display)

## 2026-03-08 - Task 16 Kiosk UX Polish
- Keep all changes inside `resources/views/livewire/kiosk-booking.blade.php` to preserve the existing KioskBooking PHP logic and validation flow.
- Apply loading spinners to both primary submit actions (`submitData`, `confirmBooking`) for consistent feedback across the wizard.
- Preserve the existing empty-state card shell and update its copy to the required phrase instead of replacing it with a brand new layout.
