# Antrian PTSP - Project Overview

## Purpose
Sistem antrian digital untuk **Pelayanan Terpadu Satu Pintu (PTSP)** — a government one-stop service queue management system. Handles public queue booking, frontdesk check-in, officer counter operations, admin management, and reporting.

## Tech Stack
- **Backend**: Laravel 12 (v12.53.0), PHP 8.4.18
- **Frontend**: Livewire 4 + Flux UI Pro v2.13 + Tailwind CSS v4 + Alpine.js
- **Auth**: Laravel Fortify v1 (headless auth with 2FA support)
- **Database**: MySQL (db_antrian_ptsp)
- **Testing**: Pest v4 + pestphp/pest-plugin-laravel v4
- **Bundler**: Vite v7 with @tailwindcss/vite plugin
- **Code Style**: Laravel Pint (preset: laravel)
- **Dev Dependency**: Laravel Sail, Laravel Pail, Boost MCP

## Core Domain Models
- **QueuePool** — Pool/group of queues (e.g. by department)
- **Service** — Available services within a pool (with code, quota, booking/walk-in toggles)
- **QueueTicket** — Individual queue ticket (status: waiting→called→completed/cancelled/skipped)
- **Counter** — Service counter (linked to pool)
- **CounterSession** — Officer session at a counter (open/close tracking)
- **QueueActivity** — Audit log for all queue actions
- **User** — With roles: admin, frontdesk, officer, monitor

## User Roles (App\Enums\UserRole)
- `admin` — Manages services and counters
- `frontdesk` — Creates tickets, checks in visitors
- `officer` — Operates counter (call, skip, complete, cancel, recall)
- `monitor` — Views reports

## Routes Structure
- `/antrian` — Public booking
- `/antrian/cek` — Public ticket lookup
- `/display` — Display board (current/recent calls)
- `/dashboard` — Auth dashboard
- `/frontdesk/antrian` — Frontdesk operations (role: frontdesk)
- `/petugas/loket/{counter}` — Officer counter panel (role: officer)
- `/laporan/antrian` — Reports (role: monitor)
- `/admin/layanan` — Service management (role: admin)
- `/admin/loket` — Counter management (role: admin)

## Architecture Patterns
- **Action Classes** in `app/Actions/Queue/` for business logic (CallNextTicket, CancelTicket, CompleteTicket, etc.)
- **Form Request** classes in `app/Http/Requests/` for validation
- **Concerns** (traits) in `app/Concerns/` for shared validation rules
- **Support** classes in `app/Support/` for helpers (e.g. QueueReportBuilder)
- **Livewire** pages use Volt with ⚡ prefix (single-file components)
- **Blade views** follow `pages/{section}/{page}` structure
- **Flux UI Pro** components used extensively (`<flux:*>`)

## Database
- MySQL engine
- 14 tables total (including Laravel defaults: cache, sessions, jobs, migrations, password_reset_tokens, failed_jobs)
- Domain tables: queue_pools, services, counters, counter_sessions, queue_tickets, queue_activities
- All models have factories in `database/factories/`
- Seeders: DatabaseSeeder, QueueMvpSeeder
