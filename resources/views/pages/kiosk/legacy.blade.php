@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════
       ANDROID 5 (CHROMIUM 37-53) HIGH-PERFORMANCE SOLID STYLES
       - Zero modern CSS nesting, zero oklch/color-mix
       - Zero backdrop-filter blurs
       - Hardware-accelerated solid flat design
       - 300ms touch delay removal & instant tactile feedback
       ═══════════════════════════════════════════════════════════ */

    html, body {
        background-color: #0b132b;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        touch-action: manipulation;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        user-select: none;
    }

    input, textarea, select {
        -webkit-user-select: auto;
        user-select: auto;
    }

    .kiosk-root {
        background-color: #0f172a;
        background-image: radial-gradient(circle at 50% 0%, #1e293b 0%, #0f172a 100%);
        min-height: 100vh;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .kiosk-overlay {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* === HEADER === */
    .kiosk-header {
        background: #0b1120;
        border-bottom: 2px solid #1e293b;
        padding: 18px 32px;
    }

    .kiosk-clock {
        font-variant-numeric: tabular-nums;
        letter-spacing: -1px;
        font-weight: 800;
        color: #38bdf8;
    }

    /* === SERVICE CARDS (SOLID INDUSTRIAL PALETTE) === */
    .service-card {
        border: none !important;
        border-radius: 24px !important;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        overflow: hidden;
        min-height: 240px;
        position: relative;
        color: #ffffff !important;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 28px 20px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
    }

    .service-card:active {
        transform: scale(0.96) !important;
        filter: brightness(0.9);
    }

    /* High contrast solid brand colors */
    .svc-purple  { background-color: #7c3aed !important; }
    .svc-rose    { background-color: #e11d48 !important; }
    .svc-emerald { background-color: #059669 !important; }
    .svc-amber   { background-color: #d97706 !important; }
    .svc-cyan    { background-color: #0891b2 !important; }
    .svc-blue    { background-color: #2563eb !important; }

    .service-card h3 {
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1.45rem !important;
        line-height: 1.25 !important;
        margin-top: 12px;
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }

    .service-action-badge {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 1px;
        padding: 8px 18px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* === GRID SYSTEM FOR SERVICES === */
    .kiosk-service-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 22px;
        width: 100%;
        max-width: 1320px;
        margin: 0 auto;
        padding: 0 20px;
    }

    @media (min-width: 600px) {
        .kiosk-service-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
    }

    @media (min-width: 992px) {
        .kiosk-service-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 26px;
        }
    }

    @media (min-width: 1360px) {
        .kiosk-service-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
    }

    /* === FORM CONTAINER === */
    .booking-card {
        border-radius: 28px !important;
        border: 2px solid #334155 !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4) !important;
        background: #ffffff !important;
        max-width: 1240px;
        width: 100%;
    }

    .kiosk-input {
        background-color: #f8fafc !important;
        border: 2px solid #cbd5e1 !important;
        border-radius: 16px !important;
        font-size: 1.25rem !important;
        padding: 14px 18px !important;
        height: auto !important;
        font-weight: 700;
        color: #0f172a !important;
        transition: border-color 0.15s, background-color 0.15s;
    }

    .kiosk-input:focus, .kiosk-input.active-numpad-field {
        border-color: #0284c7 !important;
        background-color: #f0f9ff !important;
        box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.18) !important;
        outline: none;
    }

    .input-numpad-active-tag {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        background: #0284c7;
        color: #ffffff;
        padding: 2px 10px;
        border-radius: 999px;
        display: none;
    }

    .active-numpad-field + .input-numpad-active-tag,
    .active-numpad-container .input-numpad-active-tag {
        display: inline-block;
    }

    /* Select2 overrides for Touch */
    .select2-container--bootstrap5 .select2-selection {
        background-color: #f8fafc !important;
        border: 2px solid #cbd5e1 !important;
        border-radius: 16px !important;
        min-height: 56px !important;
        padding: 12px 18px !important;
    }
    .select2-container--bootstrap5 .select2-selection--single .select2-selection__rendered {
        font-size: 1.2rem !important;
        font-weight: 700 !important;
        line-height: 1.4 !important;
        color: #0f172a !important;
    }
    .select2-dropdown {
        border: 2px solid #cbd5e1 !important;
        border-radius: 16px !important;
        box-shadow: 0 16px 36px rgba(0,0,0,0.2) !important;
    }
    .select2-results__option {
        padding: 14px 18px !important;
        font-size: 1.15rem !important;
        font-weight: 600 !important;
    }

    /* === VIRTUAL ON-SCREEN NUMPAD === */
    .numpad-container {
        background: #f1f5f9;
        border: 2px solid #cbd5e1;
        border-radius: 24px;
        padding: 20px;
    }

    .numpad-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .numpad-btn {
        background: #ffffff;
        border: 2px solid #cbd5e1;
        border-radius: 16px;
        font-size: 1.85rem;
        font-weight: 800;
        color: #0f172a;
        height: 68px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        transition: transform 0.1s, background-color 0.1s;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .numpad-btn:active {
        transform: scale(0.92);
        background-color: #e2e8f0;
    }

    .numpad-btn-action {
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .numpad-btn-clear {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .numpad-btn-clear:active {
        background: #fecaca;
    }

    .numpad-btn-backspace {
        background: #fef3c7;
        border-color: #fcd34d;
        color: #b45309;
    }
    .numpad-btn-backspace:active {
        background: #fde68a;
    }

    .numpad-target-pill {
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        border: 2px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        text-align: center;
        flex: 1;
        transition: all 0.15s;
    }

    .numpad-target-pill.active {
        border-color: #0284c7;
        background: #0284c7;
        color: #ffffff;
    }

    /* === SUCCESS SCREEN TICKET === */
    .ticket-box {
        background: #f8fafc;
        border: 3px dashed #0284c7;
        border-radius: 28px;
        padding: 36px 24px;
        text-align: center;
        position: relative;
    }

    .ticket-hero-number {
        font-size: 7rem;
        font-size: clamp(5rem, 12vw, 8.5rem);
        line-height: 1;
        font-weight: 900;
        color: #0369a1;
        letter-spacing: -3px;
        margin: 12px 0;
    }

    .countdown-track {
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .countdown-fill {
        height: 100%;
        background: #059669;
        border-radius: 999px;
        transition: width 1s linear;
    }

    /* === PRINTER STATUS BAR === */
    .printer-status-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-top: 2px solid #1e293b;
        background: #0b1120;
        cursor: pointer;
        user-select: none;
        flex-shrink: 0;
    }

    .printer-status-bar.bar-checking { color: #facc15; }
    .printer-status-bar.bar-ok       { color: #34d399; }
    .printer-status-bar.bar-err      { color: #f87171; }

    .printer-status-bar .ps-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .printer-status-bar.bar-checking .ps-dot { background: #facc15; }
    .printer-status-bar.bar-ok       .ps-dot { background: #34d399; }
    .printer-status-bar.bar-err      .ps-dot { background: #f87171; }

    .printer-flash {
        position: fixed;
        bottom: 52px;
        left: 50%;
        transform: translateX(-50%);
        background: #0f172a;
        border: 2px solid #334155;
        border-radius: 14px;
        padding: 14px 20px;
        font-size: 11px;
        color: #ffffff;
        white-space: nowrap;
        z-index: 9998;
        display: none;
        min-width: 280px;
        box-shadow: 0 16px 36px rgba(0,0,0,0.5);
    }
    .printer-flash .pf-row { display: flex; align-items: baseline; gap: 10px; margin-bottom: 6px; }
    .printer-flash .pf-row:last-child { margin-bottom: 0; }
    .printer-flash .pf-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; min-width: 80px; }
    .printer-flash .pf-val   { font-weight: 700; font-size: 11px; }
    .printer-flash .pf-val.ok   { color: #34d399; }
    .printer-flash .pf-val.warn { color: #facc15; }
    .printer-flash .pf-val.err  { color: #f87171; }
    .printer-flash .pf-hr  { border: none; border-top: 1px solid #334155; margin: 8px 0; }
    .printer-flash .pf-hint { font-size: 9.5px; color: #fb923c; }

    /* Alert Dialog Fallback */
    #kioskAlertOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.75);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .kiosk-alert-box {
        background: #ffffff;
        border-radius: 28px;
        padding: 36px 30px;
        max-width: 500px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }

    /* Touch Friendly Action Buttons */
    .btn-kiosk-action {
        min-height: 64px;
        font-size: 1.35rem !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        padding: 14px 36px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .btn-kiosk-action:active {
        transform: scale(0.96);
    }
</style>
@endpush

@section('content')
<div id="kioskRoot" class="kiosk-root">
    <div class="kiosk-overlay">

        {{-- ═══ HEADER BAR ═══ --}}
        <header class="kiosk-header d-flex justify-content-between align-items-center flex-shrink-0">
            <div class="d-flex align-items-center">
                <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0 me-5"
                     style="width:68px;height:68px;padding:8px;">
                    @if(config('institution.logo_path'))
                        <img alt="Logo" src="{{ Storage::url(config('institution.logo_path')) }}"
                             style="max-height:50px;max-width:50px;object-fit:contain;">
                    @else
                        <img alt="Logo" src="{{ asset('metronic-assets/media/logos/logo-papenajam.webp') }}"
                             style="max-height:50px;max-width:50px;object-fit:contain;">
                    @endif
                </div>
                <div>
                    <div class="text-info fw-bold fs-7 text-uppercase" style="letter-spacing:2px;">
                        Sistem Antrian PTSP
                    </div>
                    <h1 class="text-white fw-boldest fs-2 fs-lg-1 mb-0" style="letter-spacing:-0.5px;line-height:1.2;">
                        {{ config('institution.name') }}
                    </h1>
                </div>
            </div>

            <div class="text-end d-none d-md-block">
                <div class="fs-1 kiosk-clock" id="kioskClock">00:00:00</div>
                <div class="text-slate-400 fw-semibold fs-6 text-uppercase text-gray-400" id="kioskDate">---</div>
            </div>
        </header>

        {{-- ═══ LAYAR 1: PILIH LAYANAN (SERVICE SELECTOR) ═══ --}}
        <main id="screenServices" class="flex-grow-1 d-flex flex-column justify-content-center align-items-center py-10 px-6">
            <div class="text-center mb-10">
                <h2 class="text-white fw-boldest text-uppercase mb-3"
                    style="font-size:clamp(2.2rem, 4.5vw, 3.8rem); letter-spacing:-1.5px; line-height:1.15;">
                    SILAKAN PILIH LAYANAN
                </h2>
                <p class="text-gray-300 fs-3 fw-semibold text-uppercase mb-0" style="letter-spacing:1px; opacity:0.85;">
                    Sentuh salah satu kartu layanan di bawah ini
                </p>
            </div>

            @php
                $svcColors = ['svc-purple', 'svc-emerald', 'svc-cyan', 'svc-rose', 'svc-amber', 'svc-blue'];
                $svcIcons  = [
                    'fa-solid fa-file-lines',
                    'fa-solid fa-folder-open',
                    'fa-solid fa-briefcase',
                    'fa-solid fa-id-card',
                    'fa-solid fa-clipboard-check',
                    'fa-solid fa-receipt',
                    'fa-solid fa-book',
                    'fa-solid fa-scale-balanced',
                    'fa-solid fa-gavel',
                ];

                $getServicePurposeCode = function ($service) {
                    $slug = strtolower($service->slug ?? $service->name ?? '');
                    $code = strtolower($service->code ?? '');
                    if (str_contains($slug, 'daftar') || str_contains($code, 'daftar')) return 'pendaftaran';
                    if (str_contains($slug, 'info') || str_contains($slug, 'aduan') || str_contains($code, 'info')) return 'informasi_pengaduan';
                    if (str_contains($slug, 'produk') || str_contains($slug, 'hukum') || str_contains($code, 'produk')) return 'produk_hukum';
                    if (str_contains($slug, 'ecourt') || str_contains($code, 'ecourt')) return 'ecourt';
                    return '';
                };
            @endphp

            <div class="kiosk-service-grid">
                @foreach($services as $idx => $service)
                @php
                    $purposeCode = $getServicePurposeCode($service);
                    $colorClass = $svcColors[$idx % count($svcColors)];
                    $iconClass = $svcIcons[$idx % count($svcIcons)];
                @endphp
                <div data-service-id="{{ $service->id }}"
                     data-service-name="{{ e($service->name) }}"
                     data-service-purpose="{{ $purposeCode }}"
                     onclick="showBookingForm('{{ $service->id }}', '{{ addslashes($service->name) }}', '{{ $purposeCode }}')"
                     class="card service-card {{ $colorClass }}">
                    <div class="d-flex align-items-center justify-content-center mb-3" style="height:80px;">
                        @if($service->icon_svg)
                            <div style="font-size:0; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
                                {!! $service->icon_svg !!}
                            </div>
                        @else
                            <i class="{{ $iconClass }}" style="font-size:56px; color:#ffffff;"></i>
                        @endif
                    </div>
                    <h3>{{ $service->name }}</h3>
                    <div class="service-action-badge mt-auto">
                        <span>AMBIL ANTRIAN</span>
                        <i class="fa-solid fa-arrow-right fs-7"></i>
                    </div>
                </div>
                @endforeach
            </div>
        </main>

        {{-- ═══ LAYAR 2: FORM IDENTITAS LENGKAP + VIRTUAL NUMPAD ═══ --}}
        <section id="screenForm" class="flex-grow-1 d-flex align-items-center justify-content-center py-8 px-4 px-md-8 d-none">
            <div class="booking-card card">
                <div class="card-body p-8 p-lg-12">

                    {{-- Form Title & Service Indicator --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-8 pb-6 border-bottom">
                        <div>
                            <span class="badge bg-primary text-white fs-5 fw-bold px-5 py-2 rounded-pill text-uppercase mb-2 d-inline-block"
                                  id="selectedServiceBadge">Layanan</span>
                            <h2 class="fw-boldest fs-2x text-gray-900 text-uppercase mb-1" id="selectedServiceName"></h2>
                            <div class="text-gray-500 fs-4 fw-semibold">Lengkapi identitas Anda untuk mencetak nomor antrian</div>
                        </div>
                        <button type="button" onclick="backToServices()" class="btn btn-outline btn-outline-secondary fw-bold px-6 py-3 rounded-pill">
                            <i class="fa-solid fa-arrow-left me-2"></i> Ganti Layanan
                        </button>
                    </div>

                    <form id="bookingForm" onsubmit="return false;">
                        @csrf
                        <input type="hidden" id="service_id" name="service_id">
                        <input type="hidden" id="visit_purpose" name="visit_purpose">

                        <div class="row g-8">
                            {{-- Sisi Kiri: Form Inputs --}}
                            <div class="col-lg-7">
                                <div class="mb-6">
                                    <label class="fs-4 fw-bold text-gray-800 mb-2 d-block text-uppercase">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="visitor_name"
                                           id="visitor_name"
                                           class="form-control kiosk-input"
                                           placeholder="Ketik nama lengkap Anda..."
                                           required
                                           autocomplete="off">
                                </div>

                                <div class="mb-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="fs-4 fw-bold text-gray-800 mb-0 text-uppercase">
                                            Nomor NIK / Identitas (16 Digit) <span class="text-danger">*</span>
                                        </label>
                                        <span class="input-numpad-active-tag" id="nikActiveTag">Numpad Aktif</span>
                                    </div>
                                    <input type="text"
                                           name="visitor_identifier"
                                           id="visitor_identifier"
                                           class="form-control kiosk-input"
                                           placeholder="Masukkan 16 digit NIK..."
                                           maxlength="16"
                                           required
                                           autocomplete="off"
                                           onfocus="setActiveNumpadTarget('visitor_identifier')">
                                </div>

                                <div class="mb-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="fs-4 fw-bold text-gray-800 mb-0 text-uppercase">
                                            Nomor WhatsApp / HP <span class="text-danger">*</span>
                                        </label>
                                        <span class="input-numpad-active-tag" id="phoneActiveTag">Numpad Aktif</span>
                                    </div>
                                    <input type="tel"
                                           name="visitor_phone"
                                           id="visitor_phone"
                                           class="form-control kiosk-input"
                                           placeholder="Contoh: 08123456789..."
                                           maxlength="15"
                                           required
                                           autocomplete="off"
                                           onfocus="setActiveNumpadTarget('visitor_phone')">
                                </div>

                                <div class="mb-4">
                                    <label class="fs-4 fw-bold text-gray-800 mb-2 d-block text-uppercase">
                                        Asal Wilayah (Desa / Kelurahan) <span class="text-danger">*</span>
                                    </label>
                                    @if($wilayahOptions->isEmpty())
                                        <div class="alert alert-warning d-flex align-items-center p-4">
                                            <i class="fa-solid fa-triangle-exclamation fs-2 text-warning me-3"></i>
                                            <div class="fs-5 text-gray-800">
                                                Kelurahan/desa belum dikonfigurasi oleh Admin.
                                            </div>
                                        </div>
                                    @else
                                        <select class="form-select kiosk-input"
                                                data-control="select2"
                                                name="visitor_wilayah_kode"
                                                id="visitor_wilayah_kode"
                                                required>
                                            <option value=""></option>
                                            @foreach($wilayahOptions as $wilayah)
                                                <option value="{{ $wilayah->kode }}">{{ $wilayah->nama }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>

                            {{-- Sisi Kanan: Large Tactile Virtual Numpad --}}
                            <div class="col-lg-5">
                                <div class="numpad-container">
                                    <div class="text-center mb-3">
                                        <div class="fs-6 fw-bold text-gray-600 text-uppercase mb-2">Papan Ketik Angka (Numpad)</div>
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                    id="btnTargetNik"
                                                    onclick="setActiveNumpadTarget('visitor_identifier')"
                                                    class="numpad-target-pill active">
                                                NIK
                                            </button>
                                            <button type="button"
                                                    id="btnTargetPhone"
                                                    onclick="setActiveNumpadTarget('visitor_phone')"
                                                    class="numpad-target-pill">
                                                No WhatsApp
                                            </button>
                                        </div>
                                    </div>

                                    <div class="numpad-grid">
                                        <button type="button" class="numpad-btn" onclick="numpadPress('1')">1</button>
                                        <button type="button" class="numpad-btn" onclick="numpadPress('2')">2</button>
                                        <button type="button" class="numpad-btn" onclick="numpadPress('3')">3</button>

                                        <button type="button" class="numpad-btn" onclick="numpadPress('4')">4</button>
                                        <button type="button" class="numpad-btn" onclick="numpadPress('5')">5</button>
                                        <button type="button" class="numpad-btn" onclick="numpadPress('6')">6</button>

                                        <button type="button" class="numpad-btn" onclick="numpadPress('7')">7</button>
                                        <button type="button" class="numpad-btn" onclick="numpadPress('8')">8</button>
                                        <button type="button" class="numpad-btn" onclick="numpadPress('9')">9</button>

                                        <button type="button" class="numpad-btn numpad-btn-action numpad-btn-clear" onclick="numpadClear()">
                                            HAPUS
                                        </button>
                                        <button type="button" class="numpad-btn" onclick="numpadPress('0')">0</button>
                                        <button type="button" class="numpad-btn numpad-btn-action numpad-btn-backspace" onclick="numpadBackspace()">
                                            <i class="fa-solid fa-delete-left"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-4 mt-8 pt-6 border-top">
                            <button type="button" onclick="backToServices()" class="btn btn-light btn-kiosk-action w-100 w-sm-auto">
                                <i class="fa-solid fa-arrow-left"></i>
                                KEMBALI
                            </button>
                            <button type="button" id="btnSubmit" onclick="submitBookingForm()" class="btn btn-primary btn-kiosk-action w-100 w-sm-auto shadow-lg">
                                <span class="indicator-label d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-print"></i>
                                    CETAK TIKET
                                </span>
                                <span class="indicator-progress d-none">
                                    <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                                    MENCETAK...
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </section>

        {{-- ═══ LAYAR 3: TIKET BERHASIL DICETAK ═══ --}}
        <section id="screenSuccess" class="flex-grow-1 d-flex align-items-center justify-content-center py-8 px-4 d-none">
            <div class="card border-0 text-center w-100 shadow-lg" style="max-width:760px;border-radius:32px !important;background:#ffffff;">
                <div class="card-body p-8 p-lg-14">

                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-6"
                         style="width:90px;height:90px;background:#d1fae5;color:#059669;">
                        <i class="fa-solid fa-circle-check" style="font-size:52px;"></i>
                    </div>

                    <h1 class="fw-boldest fs-2x fs-lg-3x text-gray-900 text-uppercase mb-2" style="letter-spacing:-1px;">
                        TIKET BERHASIL DICETAK!
                    </h1>
                    <p class="text-gray-500 fs-3 fw-semibold mb-8">
                        Silakan ambil kertas tiket Anda di printer kiosk.
                    </p>

                    <div class="ticket-box mb-8">
                        <span class="text-primary fw-bold fs-4 text-uppercase d-block mb-1">
                            NOMOR ANTRIAN ANDA
                        </span>
                        <div class="ticket-hero-number" id="successTicketNumber">---</div>
                        <div class="text-gray-700 fw-bold fs-3 text-uppercase" id="successServiceName"></div>
                    </div>

                    <div class="mb-8">
                        <p class="text-gray-500 fs-5 fw-semibold mb-2">
                            Layar kembali otomatis dalam <span class="fw-bold text-primary fs-4" id="countdownText">20 detik</span>
                        </p>
                        <div class="countdown-track w-100">
                            <div class="countdown-fill" id="countdownBar" style="width:100%;"></div>
                        </div>
                    </div>

                    <button type="button" onclick="resetKiosk()" class="btn btn-success btn-kiosk-action px-12 shadow">
                        <i class="fa-solid fa-check-double"></i>
                        SELESAI / AMBIL TIKET
                    </button>

                </div>
            </div>
        </section>

        {{-- ═══ ALERT MODAL OVERLAY (ANDROID 5 FAIL-SAFE) ═══ --}}
        <div id="kioskAlertOverlay">
            <div class="kiosk-alert-box">
                <div style="font-size:48px; color:#ef4444; margin-bottom:14px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <h3 style="margin:0 0 10px; color:#0f172a; font-size:1.4rem; font-weight:800;" id="kioskAlertTitle">Perhatian</h3>
                <p id="kioskAlertMsg" style="color:#64748b; font-size:1.1rem; margin-bottom:26px; line-height:1.4;"></p>
                <button type="button" onclick="hideKioskAlert()"
                        class="btn btn-primary fw-bold fs-4 px-10 py-3 rounded-pill">
                    Mengerti
                </button>
            </div>
        </div>

        {{-- ═══ PRINTER STATUS BAR & FOOTER ═══ --}}
        <footer class="flex-shrink-0">
            <div id="printerFlash" class="printer-flash"></div>
            <div id="printerStatusBar" class="printer-status-bar bar-checking" onclick="showPrinterFlash()">
                <span class="ps-dot"></span>
                <span id="printerLabel">MEMERIKSA KONEKSI PRINTER...</span>
            </div>

            <div class="py-4 text-center" style="background:#080d1a;">
                <span class="text-gray-400 fw-semibold fs-7 text-uppercase" style="letter-spacing:1px; opacity:0.6;">
                    &copy; {{ date('Y') }} Sistem Antrian PTSP &bull; {{ config('institution.name') }}
                </span>
            </div>
        </footer>

    </div>
</div>

<div id="kioskLegacyConfig"
     class="d-none"
     data-print-url="{{ route('kiosk.legacy.print') }}"
     data-status-url="{{ route('kiosk.legacy.printer-status') }}"
     data-printer-enabled="{{ config('services.thermal_printer.enabled') ? '1' : '0' }}"
     data-printer-ip="{{ config('services.thermal_printer.ip', '127.0.0.1') }}"
     data-printer-port="{{ config('services.thermal_printer.port', '8008') }}"
     data-printer-device-id="{{ config('services.thermal_printer.device_id', 'local_printer') }}"
     data-institution-name="{{ e(config('institution.name')) }}"
     data-umum-service-id="{{ $umumServiceId }}"></div>
@endsection

@push('scripts')
<script>
    /* ═══════════════════════════════════════════════════════════
       ES5 SAFE JAVASCRIPT LOGIC (CHROMIUM 37-53 & ANDROID 5)
       ═══════════════════════════════════════════════════════════ */

    var currentNumpadTarget = 'visitor_identifier';
    var printerLastData    = null;
    var printerNextCheckAt = null;
    var printerCurrentState = 'checking';
    var printerFlashTimer  = null;
    var cdInterval = null;
    var cdSeconds = 20;
    var CD_TOTAL = 20;

    var kioskAlertOverlay = document.getElementById('kioskAlertOverlay');
    var kioskAlertMsg = document.getElementById('kioskAlertMsg');
    var kioskAlertTitle = document.getElementById('kioskAlertTitle');
    var kioskLegacyConfig = document.getElementById('kioskLegacyConfig');

    var kioskPrintUrl = kioskLegacyConfig ? kioskLegacyConfig.getAttribute('data-print-url') : '';
    var kioskStatusUrl = kioskLegacyConfig ? kioskLegacyConfig.getAttribute('data-status-url') : '';

    $(document).ready(function () {
        updateKioskClock();
        setInterval(updateKioskClock, 1000);

        if (kioskStatusUrl) {
            checkPrinterStatus();
            setInterval(checkPrinterStatus, 30000);
        }

        if ($.fn.select2) {
            $('#visitor_wilayah_kode').select2({
                placeholder: 'Pilih / Cari Desa atau Kelurahan...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#screenForm')
            });
        }
    });

    /* === VIRTUAL NUMPAD CONTROLLER === */
    function setActiveNumpadTarget(targetId) {
        currentNumpadTarget = targetId;

        $('.kiosk-input').removeClass('active-numpad-field');
        $('#' + targetId).addClass('active-numpad-field');

        if (targetId === 'visitor_identifier') {
            $('#btnTargetNik').addClass('active');
            $('#btnTargetPhone').removeClass('active');
            $('#nikActiveTag').show();
            $('#phoneActiveTag').hide();
        } else {
            $('#btnTargetNik').removeClass('active');
            $('#btnTargetPhone').addClass('active');
            $('#nikActiveTag').hide();
            $('#phoneActiveTag').show();
        }
    }

    function numpadPress(digit) {
        var $input = $('#' + currentNumpadTarget);
        if (!$input.length) return;

        var currentVal = $input.val() || '';
        var maxLen = currentNumpadTarget === 'visitor_identifier' ? 16 : 15;

        if (currentVal.length < maxLen) {
            $input.val(currentVal + digit);
        }
    }

    function numpadBackspace() {
        var $input = $('#' + currentNumpadTarget);
        if (!$input.length) return;

        var currentVal = $input.val() || '';
        if (currentVal.length > 0) {
            $input.val(currentVal.substring(0, currentVal.length - 1));
        }
    }

    function numpadClear() {
        var $input = $('#' + currentNumpadTarget);
        if ($input.length) {
            $input.val('');
        }
    }

    /* === SERVICE SELECTION & FORM SWITCHER === */
    function showBookingForm(id, name, purpose) {
        $('#service_id').val(id);
        $('#selectedServiceName').text(name);
        $('#selectedServiceBadge').text(name);

        var finalPurpose = purpose || '';
        if (!finalPurpose) {
            var lowerName = (name || '').toLowerCase();
            if (lowerName.indexOf('daftar') !== -1) finalPurpose = 'pendaftaran';
            else if (lowerName.indexOf('info') !== -1 || lowerName.indexOf('aduan') !== -1) finalPurpose = 'informasi_pengaduan';
            else if (lowerName.indexOf('produk') !== -1 || lowerName.indexOf('hukum') !== -1) finalPurpose = 'produk_hukum';
            else if (lowerName.indexOf('ecourt') !== -1) finalPurpose = 'ecourt';
        }
        $('#visit_purpose').val(finalPurpose);

        switchScreen('screenForm');
        setActiveNumpadTarget('visitor_identifier');

        if ($.fn.select2) {
            $('#visitor_wilayah_kode').val(null).trigger('change');
        }

        setTimeout(function () {
            $('#visitor_name').focus();
        }, 300);
    }

    function backToServices() {
        switchScreen('screenServices');
    }

    function switchScreen(screenId) {
        $('#screenServices, #screenForm, #screenSuccess').addClass('d-none').hide();
        $('#' + screenId).removeClass('d-none').show();
    }

    function resetKiosk() {
        clearCountdown();
        var form = document.getElementById('bookingForm');
        if (form) form.reset();
        $('#visit_purpose').val('');
        if ($.fn.select2) {
            $('#visitor_wilayah_kode').val(null).trigger('change');
        }
        backToServices();
    }

    /* === FORM SUBMIT & PRINT TICKET === */
    function submitBookingForm() {
        var name = $.trim($('#visitor_name').val() || '');
        var nik = $.trim($('#visitor_identifier').val() || '');
        var phone = $.trim($('#visitor_phone').val() || '');
        var wilayah = $('#visitor_wilayah_kode').val() || '';

        if (!name || name.length < 3) {
            showKioskAlert('Nama Lengkap wajib diisi minimal 3 karakter.');
            $('#visitor_name').focus();
            return;
        }

        if (!nik || nik.length < 10) {
            showKioskAlert('Nomor NIK / Identitas belum valid (minimal 10 digit).');
            setActiveNumpadTarget('visitor_identifier');
            return;
        }

        if (!phone || phone.length < 8) {
            showKioskAlert('Nomor WhatsApp / HP wajib diisi dengan benar.');
            setActiveNumpadTarget('visitor_phone');
            return;
        }

        if (!wilayah) {
            showKioskAlert('Silakan pilih Asal Wilayah (Desa/Kelurahan).');
            return;
        }

        var $btn = $('#btnSubmit');
        $btn.find('.indicator-label').addClass('d-none');
        $btn.find('.indicator-progress').removeClass('d-none');
        $btn.prop('disabled', true);

        $.ajax({
            url: kioskPrintUrl,
            type: 'POST',
            data: $('#bookingForm').serialize(),
            success: function (res) {
                if (res && res.success && res.ticket) {
                    $('#successTicketNumber').text(res.ticket.ticket_number);
                    var svcName = res.ticket.service ? res.ticket.service.name : '';
                    $('#successServiceName').text(svcName.toUpperCase());

                    switchScreen('screenSuccess');

                    if (!res.printed) {
                        showKioskAlert('Nomor antrian berhasil dibuat, tetapi printer kiosk sedang offline. Silakan catat nomor antrian atau minta bantuan petugas.');
                    }

                    startCountdown();
                } else {
                    showKioskAlert('Gagal membuat tiket antrian. Silakan coba kembali.');
                }
            },
            error: function (xhr) {
                var errorMsg = 'Maaf, terjadi kesalahan saat mencetak tiket. Silakan hubungi petugas.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var firstKey = Object.keys(errors)[0];
                    if (firstKey && errors[firstKey][0]) {
                        errorMsg = errors[firstKey][0];
                    }
                }
                showKioskAlert(errorMsg);
            },
            complete: function () {
                $btn.find('.indicator-label').removeClass('d-none');
                $btn.find('.indicator-progress').addClass('d-none');
                $btn.prop('disabled', false);
            }
        });
    }

    /* === COUNTDOWN AUTO RESET === */
    function startCountdown() {
        clearCountdown();
        cdSeconds = CD_TOTAL;
        $('#countdownBar').css('width', '100%');
        $('#countdownText').text(cdSeconds + ' detik');

        cdInterval = setInterval(function () {
            cdSeconds--;
            var pct = Math.max(0, (cdSeconds / CD_TOTAL) * 100);
            $('#countdownBar').css('width', pct + '%');
            $('#countdownText').text(cdSeconds + ' detik');

            if (cdSeconds <= 0) {
                resetKiosk();
            }
        }, 1000);
    }

    function clearCountdown() {
        if (cdInterval) {
            clearInterval(cdInterval);
            cdInterval = null;
        }
    }

    /* === ALERT DIALOG === */
    function showKioskAlert(msg, title) {
        if (!kioskAlertOverlay || !kioskAlertMsg) return;
        kioskAlertTitle.textContent = title || 'Informasi';
        kioskAlertMsg.textContent = msg || '';
        kioskAlertOverlay.style.display = 'flex';
    }

    function hideKioskAlert() {
        if (kioskAlertOverlay) {
            kioskAlertOverlay.style.display = 'none';
        }
    }

    /* === CLOCK & DATE === */
    function updateKioskClock() {
        var now = new Date();
        var h = ('0' + now.getHours()).slice(-2);
        var m = ('0' + now.getMinutes()).slice(-2);
        var s = ('0' + now.getSeconds()).slice(-2);
        $('#kioskClock').text(h + ':' + m + ':' + s);

        var days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli',
                      'Agustus','September','Oktober','November','Desember'];
        $('#kioskDate').text(
            days[now.getDay()] + ', ' + now.getDate() + ' ' +
            months[now.getMonth()] + ' ' + now.getFullYear()
        );
    }

    /* === PRINTER STATUS POLLING === */
    function checkPrinterStatus() {
        setPrinterBar('checking');
        printerNextCheckAt = Date.now() + 30000;

        $.ajax({
            url: kioskStatusUrl,
            type: 'GET',
            success: function (data) {
                printerLastData = data;
                if (printerLastData) {
                    printerLastData._checkedAt = new Date();
                }
                setPrinterBar(data.status === 'connected' ? 'connected' : 'offline');
            },
            error: function () {
                printerLastData = null;
                setPrinterBar('offline');
            }
        });
    }

    function setPrinterBar(state) {
        printerCurrentState = state;
        var bar   = document.getElementById('printerStatusBar');
        var label = document.getElementById('printerLabel');
        if (!bar || !label) return;

        bar.className = 'printer-status-bar';
        if (state === 'checking') {
            bar.className = 'printer-status-bar bar-checking';
            label.textContent = 'MEMERIKSA KONEKSI PRINTER...';
        } else if (state === 'connected') {
            bar.className = 'printer-status-bar bar-ok';
            label.textContent = 'PRINTER SIAP CETAK';
        } else {
            bar.className = 'printer-status-bar bar-err';
            var isDisabled = printerLastData && printerLastData.status === 'disabled';
            label.textContent = isDisabled ? 'PRINTER TIDAK AKTIF' : 'PRINTER TIDAK TERHUBUNG';
        }
    }

    function showPrinterFlash() {
        var flash = document.getElementById('printerFlash');
        if (!flash) return;

        var d = printerLastData;
        var html = '';

        if (printerCurrentState === 'checking' || !d) {
            var addr = d ? (d.ip + ':' + d.port) : '---';
            html = '<div class="pf-row"><span class="pf-label">Status</span><span class="pf-val warn">Memeriksa...</span></div>' +
                   '<div class="pf-row"><span class="pf-label">Alamat</span><span class="pf-val">' + addr + '</span></div>';
        } else {
            var addr = (d.ip + ':' + d.port);
            if (d.status === 'connected') {
                html = '<div class="pf-row"><span class="pf-label">Status</span><span class="pf-val ok">Terhubung</span></div>' +
                       '<div class="pf-row"><span class="pf-label">Alamat</span><span class="pf-val">' + addr + '</span></div>';
            } else {
                var errRow = d.error ? '<div class="pf-row"><span class="pf-label">Penyebab</span><span class="pf-val err">' + d.error + '</span></div>' : '';
                html = '<div class="pf-row"><span class="pf-label">Status</span><span class="pf-val err">Offline</span></div>' +
                       '<div class="pf-row"><span class="pf-label">Alamat</span><span class="pf-val">' + addr + '</span></div>' +
                       errRow;
            }
        }

        flash.innerHTML = html;
        flash.style.display = 'block';

        clearTimeout(printerFlashTimer);
        printerFlashTimer = setTimeout(function () {
            flash.style.display = 'none';
        }, 3500);
    }
</script>
@endpush
