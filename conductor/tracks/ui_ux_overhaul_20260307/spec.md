# Specification: UI/UX Overhaul

## Objective
Convert all barebone HTML views for the Antrian PTSP application into fully styled views utilizing the Flux UI Pro component library. This ensures a consistent, modern, and highly accessible user experience.

## Scope
- Refactor `resources/views/dashboard.blade.php` to use appropriate Flux components (cards, stats, etc.).
- Refactor `resources/views/pages/public/antrian/booking.blade.php` using Flux forms, inputs, and buttons.
- Refactor `resources/views/pages/public/antrian/lookup.blade.php` using Flux forms, inputs, and tables/cards for results.
- Refactor `resources/views/pages/frontdesk/antrian.blade.php` to use Flux layouts, notifications, and typography.
- Refactor `resources/views/pages/display/index.blade.php` to use Flux grid, typography, and visual cues for calling queues.
- Refactor `resources/views/pages/laporan/antrian/index.blade.php` to use Flux tables and statistic components.

## Technical Constraints
- Must use strictly Flux UI Pro components where applicable.
- Avoid raw Tailwind classes unless absolutely necessary for layout adjustments not covered by Flux.
- All views must extend the main `x-layouts::app` or `x-layouts::auth` depending on context.