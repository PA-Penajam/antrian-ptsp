# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary audiences, equally important:

- **Pengunjung / masyarakat:** take a queue number (online, kiosk, or with frontdesk help), check status, and arrive prepared with the right documents for a service at this court's PTSP.
- **Petugas operasional:** frontdesk (issue/check-in tickets) and officer at a loket (call, recall, skip, complete, cancel) so the desk runs without friction during operating hours.

Other confirmed audiences:

- **Admin:** configure layanan, loket, queue pools, users/roles, and wilayah.
- **Monitor / pimpinan:** operational reports, audit trail, and monthly service reports.

This deployment is **one Pengadilan Agama** (institution name, address, phone, hours, and logo are configurable). It is not a multi-tenant product for many courts.

## Product Purpose

**Antrian PTSP** is a web queue-management system for Pelayanan Terpadu Satu Pintu at a Pengadilan Agama. It digitizes taking a number, waiting, being called, and recording service so the waiting room is less crowded and every ticket is trackable.

Success means both sides work: the public can self-serve without confusion, and staff can run check-in and loket calling in a clear, auditable flow during the day.

## Positioning

One ticket record across **online booking, walk-in/frontdesk, and kiosk**, with real-time TV display and TTS announcement, thermal ticket print on kiosk, and a full activity log. A generic clinic-style queue board, or an online form with no desk/kiosk/TV loop, could not truthfully claim this court-floor operating model.

## Operating Context

- Physical PTSP hall: waiting area, frontdesk, numbered loket, hallway TV, and a kiosk (including older devices that need the legacy HTML modes).
- Typical layanan pools: Umum (pendaftaran, informasi/pengaduan, pengambilan produk hukum), Pembayaran, Posbakum.
- Ticket lifecycle in the product: booked → waiting → called → completed, plus cancelled and skipped.
- Staff log in with Fortify (roles: admin, frontdesk, officer, monitor). Kiosk and TV Display use separate module passwords, not the same staff session.
- Reports (queue, audit, laporan bulanan PDF) are part of how pimpinan evaluates service.
- Operating hours default: Senin–Jumat, 08:00–16:00 WIB (configurable).

## Capabilities and Constraints

Shipped capabilities:

- Public: home, pilih layanan, booking, cek antrian, signed confirmation.
- Frontdesk: create tickets and check-in.
- Officer workstation: call next, recall, skip, complete, cancel at an assigned loket.
- Kiosk booking with thermal printer; **kiosk-legacy** for older hardware (plain HTML, no Livewire/Alpine).
- TV Display with TTS announcements; **tv-legacy** for older displays.
- Real-time updates (Laravel Reverb / Echo).
- Admin: layanan, loket, queue pools, users, wilayah.
- Monitor: laporan antrian, audit trail, laporan bulanan (PDF/Excel).
- Public JSON API for institution, services, booking, and lookup.

Constraints:

- Indonesian locale (`id`) for public and staff copy.
- Must keep the three intake channels and both modern + legacy kiosk/TV surfaces.
- Staff UI is Livewire + Flux UI Pro; do not replace the stack without an explicit product decision.
- Institution identity comes from config (`INSTITUTION_NAME`, address, phone, hours, logo path), not hardcoded court branding in templates.
- A separate `frontend-public/` app was moved out of this repo; this Laravel app still serves the public Blade surfaces listed above.

Planned, not shipped (do not treat as current product): WhatsApp/SMS queue notifications; Survey IKM at end of service.

Undecided: exact court name beyond the configurable default “Pengadilan Agama”; whether a named WCAG level is required.

## Brand Commitments

- Product name: **Antrian PTSP**.
- Voice: Indonesian, casual and polite (`kasual & ramah`), still respectful; microcopy must be easy for the public and must avoid technical jargon.
- Institution branding is data: name/logo/address/phone/hours from config. Do not invent a court identity or claims not in config/content.
- No fabricated testimonials, press, or performance numbers.

## Evidence on Hand

- Product definition: `conductor/product.md`, `conductor/product-guidelines.md`, `docs/PRODUCT_SPECIFICATION.md`.
- Running surfaces: public pages, kiosk, TV, officer, frontdesk, admin, reports (see `routes/web.php`).
- Configurable institution fields: `config/institution.php` (env: `INSTITUTION_*`, `OPERATING_HOURS`). Logo path may be empty.
- Favicon/app icon: `public/favicon.svg`, `public/apple-touch-icon.png`. Layout logos: `resources/views/components/app-logo.blade.php` and `app-logo-icon.blade.php`.
- Future tracks (not evidence of shipped features): `conductor/tracks/feature_notifikasi_pesan_20260307`, `conductor/tracks/feature_survey_ikm_20260307`.

Do not fabricate visitor quotes, wait-time benchmarks, or a specific court seal if `INSTITUTION_LOGO_PATH` is empty.

## Product Principles

1. **Public and desk succeed together.** Self-serve must be obvious; loket and frontdesk must stay fast and unambiguous at peak hours.
2. **One ticket, three doors.** Online, kiosk, and walk-in share the same lifecycle and status language.
3. **The hall is part of the product.** TV, TTS, printed tickets, and legacy device modes are operational, not extras.
4. **Speak like PTSP, not like software.** Indonesian, polite, short; no jargon for masyarakat.
5. **Configurable court, not a fake brand.** Name, logo, and address come from this institution’s config; never invent official claims.

## Accessibility & Inclusion

Public users include mixed ages and digital literacy; copy and UI must stay readable, high-contrast, and simple. Formal WCAG target is **undecided** (product guidelines ask for strong contrast and large-enough type, not a numbered standard).
