# Implementation Plan: UI/UX Overhaul

## Phase 1: Layout & Core Dashboard Overhaul [checkpoint: fc3f5cd]
- [x] Task: Update `resources/views/dashboard.blade.php` to utilize Flux UI statistic cards. 4d8f498
- [x] Task: Ensure Dashboard tests pass and visual layout is correct. 2a99ab5
- [x] Task: Conductor - User Manual Verification 'Phase 1: Layout & Core Dashboard Overhaul' (Protocol in workflow.md) fc3f5cd

## Phase 2: Public Booking & Lookup Views
- [~] Task: Refactor `booking.blade.php` to use `<flux:form>`, `<flux:input>`, and `<flux:button>`.
- [ ] Task: Refactor `lookup.blade.php` to use Flux UI form elements and result cards.
- [ ] Task: Ensure existing Pest tests for Public endpoints pass with the new layout structure.
- [ ] Task: Conductor - User Manual Verification 'Phase 2: Public Booking & Lookup Views' (Protocol in workflow.md)

## Phase 3: Frontdesk & Display Views
- [ ] Task: Refactor `frontdesk/antrian.blade.php` to use Flux headings, tables, and buttons for queue management.
- [ ] Task: Refactor `display/index.blade.php` to use high-contrast Flux typography and grid for screen displays.
- [ ] Task: Test both views to ensure no functionality is broken by UI changes.
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Frontdesk & Display Views' (Protocol in workflow.md)

## Phase 4: Report Views
- [ ] Task: Refactor `laporan/antrian/index.blade.php` to use `<flux:table>` and statistical summaries.
- [ ] Task: Test Laporan endpoint rendering.
- [ ] Task: Conductor - User Manual Verification 'Phase 4: Report Views' (Protocol in workflow.md)