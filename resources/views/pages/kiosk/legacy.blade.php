@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════
       ANDROID 5 (CHROMIUM 37-53) HIGH-PERFORMANCE BRIGHT THEME
       - Clean, crisp bright background with high contrast for PTSP lobby
       - Hardware-accelerated solid flat design (translate3d, scale, opacity)
       - Zero blur filters, zero color-mix, zero CPU-draining loops
       - 300ms touch delay removal & instant tactile feedback
       - Authentic thermal ticket dispensing delight
       ═══════════════════════════════════════════════════════════ */

    html, body {
        background-color: #f8fafc;
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
        background-color: #eef2f8;
        background-image:
            linear-gradient(180deg, rgba(238, 242, 248, 0.72) 0%, rgba(224, 232, 246, 0.76) 100%),
            url("{{ asset('images/kiosk-bg-bright.webp') }}");
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        min-height: 100vh;
        width: 100%;
        display: -webkit-flex;
        display: flex;
        -webkit-flex-direction: column;
        flex-direction: column;
    }

    .kiosk-overlay {
        min-height: 100vh;
        display: -webkit-flex;
        display: flex;
        -webkit-flex-direction: column;
        flex-direction: column;
        -webkit-justify-content: space-between;
        justify-content: space-between;
    }

    /* === SCREEN TRANSITIONS === */
    .screen-enter {
        -webkit-animation: screenEntrance 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation: screenEntrance 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @-webkit-keyframes screenEntrance {
        0% {
            opacity: 0;
            -webkit-transform: translate3d(0, 16px, 0);
            transform: translate3d(0, 16px, 0);
        }
        100% {
            opacity: 1;
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes screenEntrance {
        0% {
            opacity: 0;
            transform: translate3d(0, 16px, 0);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    /* === HEADER (BRIGHT CLEAN NAVBAR) === */
    .kiosk-header {
        background: rgba(255, 255, 255, 0.96);
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
        padding: 14px 28px;
    }

    .kiosk-clock {
        font-variant-numeric: tabular-nums;
        letter-spacing: -1px;
        font-weight: 800;
        color: #0891b2;
    }

    /* === SERVICE CARDS (VIBRANT CHROMATIC JEWEL PALETTE) === */
    .service-card {
        border: 2px solid rgba(255, 255, 255, 0.28) !important;
        border-radius: 26px !important;
        cursor: pointer;
        transition: -webkit-transform 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        overflow: hidden;
        min-height: 250px;
        position: relative;
        color: #ffffff !important;
        display: -webkit-flex;
        display: flex;
        -webkit-flex-direction: column;
        flex-direction: column;
        -webkit-justify-content: center;
        justify-content: center;
        -webkit-align-items: center;
        align-items: center;
        text-align: center;
        padding: 32px 24px !important;
        -webkit-animation: cardCascadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation: cardCascadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .service-card:nth-child(1) { -webkit-animation-delay: 0.02s; animation-delay: 0.02s; }
    .service-card:nth-child(2) { -webkit-animation-delay: 0.05s; animation-delay: 0.05s; }
    .service-card:nth-child(3) { -webkit-animation-delay: 0.08s; animation-delay: 0.08s; }
    .service-card:nth-child(4) { -webkit-animation-delay: 0.11s; animation-delay: 0.11s; }
    .service-card:nth-child(5) { -webkit-animation-delay: 0.14s; animation-delay: 0.14s; }
    .service-card:nth-child(6) { -webkit-animation-delay: 0.17s; animation-delay: 0.17s; }
    .service-card:nth-child(7) { -webkit-animation-delay: 0.20s; animation-delay: 0.20s; }
    .service-card:nth-child(8) { -webkit-animation-delay: 0.23s; animation-delay: 0.23s; }
    .service-card:nth-child(9) { -webkit-animation-delay: 0.26s; animation-delay: 0.26s; }

    @-webkit-keyframes cardCascadeIn {
        0% {
            opacity: 0;
            -webkit-transform: translate3d(0, 24px, 0) scale(0.95);
            transform: translate3d(0, 24px, 0) scale(0.95);
        }
        100% {
            opacity: 1;
            -webkit-transform: translate3d(0, 0, 0) scale(1);
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @keyframes cardCascadeIn {
        0% {
            opacity: 0;
            transform: translate3d(0, 24px, 0) scale(0.95);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    .service-card:active {
        -webkit-transform: scale(0.96) translate3d(0, 4px, 0) !important;
        transform: scale(0.96) translate3d(0, 4px, 0) !important;
        filter: brightness(1.08);
    }

    .service-card .svc-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.18);
        border: 2px solid rgba(255, 255, 255, 0.35);
        display: -webkit-flex;
        display: flex;
        -webkit-align-items: center;
        align-items: center;
        -webkit-justify-content: center;
        justify-content: center;
        margin-bottom: 14px;
        transition: -webkit-transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s ease;
    }

    .service-card:active .svc-icon-wrapper {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
        background: rgba(255, 255, 255, 0.3);
    }

    /* === VIBRANT CHROMATIC JEWEL THEMES (WCAG AAA contrast, Android 5 safe) === */
    .svc-blue {
        background-color: #1e40af !important;
        background-image: linear-gradient(145deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%) !important;
        box-shadow: 0 14px 32px rgba(37, 99, 235, 0.38), 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    }
    .svc-teal {
        background-color: #0f766e !important;
        background-image: linear-gradient(145deg, #0d9488 0%, #0f766e 50%, #115e59 100%) !important;
        box-shadow: 0 14px 32px rgba(13, 148, 136, 0.38), 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    }
    .svc-amber {
        background-color: #b45309 !important;
        background-image: linear-gradient(145deg, #f59e0b 0%, #d97706 50%, #b45309 100%) !important;
        box-shadow: 0 14px 32px rgba(217, 119, 6, 0.38), 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    }
    .svc-emerald {
        background-color: #047857 !important;
        background-image: linear-gradient(145deg, #10b981 0%, #059669 50%, #047857 100%) !important;
        box-shadow: 0 14px 32px rgba(5, 150, 105, 0.38), 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    }
    .svc-purple {
        background-color: #6d28d9 !important;
        background-image: linear-gradient(145deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%) !important;
        box-shadow: 0 14px 32px rgba(124, 58, 237, 0.38), 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    }
    .svc-rose {
        background-color: #be123c !important;
        background-image: linear-gradient(145deg, #f43f5e 0%, #e11d48 50%, #be123c 100%) !important;
        box-shadow: 0 14px 32px rgba(225, 29, 72, 0.38), 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    }
    .svc-cyan {
        background-color: #0e7490 !important;
        background-image: linear-gradient(145deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%) !important;
        box-shadow: 0 14px 32px rgba(8, 145, 178, 0.38), 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    }

    .service-card h3 {
        color: #ffffff !important;
        font-weight: 900 !important;
        font-size: 1.45rem !important;
        line-height: 1.25 !important;
        margin-top: 4px;
        margin-bottom: 18px;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .service-action-badge {
        background: rgba(255, 255, 255, 0.25);
        border: 1.5px solid rgba(255, 255, 255, 0.45);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.85rem;
        letter-spacing: 1px;
        padding: 10px 24px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
        transition: background-color 0.15s ease, -webkit-transform 0.15s ease, transform 0.15s ease;
    }

    .service-card:active .service-action-badge {
        background: rgba(255, 255, 255, 0.4);
        -webkit-transform: scale(1.04);
        transform: scale(1.04);
    }

    /* === GRID SYSTEM FOR SERVICES (ADAPTIVE AUTO-FIT) === */
    .kiosk-service-grid {
        display: -webkit-flex;
        display: flex;
        -webkit-flex-wrap: wrap;
        flex-wrap: wrap;
        -webkit-justify-content: center;
        justify-content: center;
        gap: 26px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .kiosk-service-grid .service-card {
        -webkit-flex: 1 1 320px;
        flex: 1 1 320px;
        max-width: 420px;
        min-width: 280px;
    }

    /* === FORM CONTAINER === */
    .booking-card {
        border-radius: 24px !important;
        border: 2px solid #e2e8f0 !important;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08) !important;
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
        transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
    }

    .kiosk-input:focus, .kiosk-input.active-numpad-field {
        border-color: #0891b2 !important;
        background-color: #f0f9ff !important;
        box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.18) !important;
        outline: none;
    }

    .kiosk-input.key-pulse {
        border-color: #0891b2 !important;
        background-color: #f8fcfd !important;
    }

    .input-numpad-active-tag {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        background: #0891b2;
        color: #ffffff;
        padding: 3px 12px;
        border-radius: 999px;
        display: none;
        letter-spacing: 0.5px;
    }

    .active-numpad-field + .input-numpad-active-tag,
    .active-numpad-container .input-numpad-active-tag {
        display: inline-block;
    }

    /* NIK Progress Indicator Bar */
    .nik-progress-track {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 6px;
        padding: 4px 6px;
        background: #f1f5f9;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .nik-progress-dot {
        flex: 1;
        height: 6px;
        border-radius: 8px;
        background: #cbd5e1;
        transition: background-color 0.15s ease, -webkit-transform 0.15s ease, transform 0.15s ease;
    }

    .nik-progress-dot.filled {
        background: #0891b2;
        -webkit-transform: scaleY(1.3);
        transform: scaleY(1.3);
    }

    .nik-progress-dot.completed {
        background: #10b981;
    }

    .nik-status-badge {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 2px 8px;
        border-radius: 8px;
        background: #e2e8f0;
        color: #475569;
    }

    .nik-status-badge.ready {
        background: #d1fae5;
        color: #065f46;
    }

    /* Validation Shake */
    .input-shake-error {
        -webkit-animation: shakeField 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both !important;
        animation: shakeField 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both !important;
        border-color: #ef4444 !important;
        background-color: #fee2e2 !important;
    }

    @-webkit-keyframes shakeField {
        0%, 100% { -webkit-transform: translate3d(0, 0, 0); transform: translate3d(0, 0, 0); }
        15%, 45%, 75% { -webkit-transform: translate3d(-8px, 0, 0); transform: translate3d(-8px, 0, 0); }
        30%, 60%, 90% { -webkit-transform: translate3d(8px, 0, 0); transform: translate3d(8px, 0, 0); }
    }

    @keyframes shakeField {
        0%, 100% { transform: translate3d(0, 0, 0); }
        15%, 45%, 75% { transform: translate3d(-8px, 0, 0); }
        30%, 60%, 90% { transform: translate3d(8px, 0, 0); }
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
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        line-height: 1.4 !important;
        color: #0f172a !important;
    }
    .select2-dropdown {
        border: 2px solid #cbd5e1 !important;
        border-radius: 16px !important;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12) !important;
    }
    .select2-results__option {
        padding: 14px 18px !important;
        font-size: 1.25rem !important;
        font-weight: 600 !important;
    }

    /* === VIRTUAL ON-SCREEN NUMPAD === */
    .numpad-container {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
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
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        height: 68px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        transition: -webkit-transform 0.1s ease, transform 0.1s ease, background-color 0.1s ease;
        box-shadow: 0 4px 8px rgba(15, 23, 42, 0.04);
    }

    .numpad-btn:active, .numpad-btn.btn-active-touch {
        -webkit-transform: scale(0.92) !important;
        transform: scale(0.92) !important;
        background-color: #e2e8f0 !important;
    }

    .numpad-btn-action {
        font-size: 1rem;
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
        padding: 10px 16px;
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        border: 2px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        text-align: center;
        flex: 1;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }

    .numpad-target-pill.active {
        border-color: #0891b2;
        background: #0891b2;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(8, 145, 178, 0.3);
    }

    /* === SUCCESS SCREEN: REALISTIC THERMAL TICKET DISPENSING === */
    .printer-dispenser-housing {
        width: 100%;
        max-width: 720px;
        margin: 0 auto;
        position: relative;
    }

    .printer-slot-bezel {
        background: #1e293b;
        border: 3px solid #334155;
        border-radius: 24px 24px 0 0;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: inset 0 -4px 10px rgba(0, 0, 0, 0.4);
        position: relative;
        z-index: 2;
    }

    .printer-slot-slit {
        height: 6px;
        background: #0f172a;
        border-radius: 999px;
        flex: 1;
        margin: 0 16px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.8);
    }

    .printer-slot-light {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 8px #10b981;
    }

    .ticket-box {
        background: #ffffff;
        border: 2px solid #cbd5e1;
        border-top: 4px dashed #0891b2;
        border-radius: 0 0 24px 24px;
        padding: 36px 28px;
        text-align: center;
        position: relative;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
        -webkit-animation: ticketDispense 0.55s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation: ticketDispense 0.55s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @-webkit-keyframes ticketDispense {
        0% {
            opacity: 0;
            -webkit-transform: translate3d(0, -40px, 0) scaleY(0.6);
            transform: translate3d(0, -40px, 0) scaleY(0.6);
        }
        70% {
            -webkit-transform: translate3d(0, 6px, 0) scaleY(1.02);
            transform: translate3d(0, 6px, 0) scaleY(1.02);
        }
        100% {
            opacity: 1;
            -webkit-transform: translate3d(0, 0, 0) scaleY(1);
            transform: translate3d(0, 0, 0) scaleY(1);
        }
    }

    @keyframes ticketDispense {
        0% {
            opacity: 0;
            transform: translate3d(0, -40px, 0) scaleY(0.6);
        }
        70% {
            transform: translate3d(0, 6px, 0) scaleY(1.02);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0) scaleY(1);
        }
    }

    .ticket-hero-number {
        font-size: clamp(5rem, 12vw, 8.5rem);
        line-height: 1;
        font-weight: 900;
        color: #0891b2;
        letter-spacing: -3px;
        margin: 12px 0;
        font-variant-numeric: tabular-nums;
        -webkit-animation: stampIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) 0.25s both;
        animation: stampIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) 0.25s both;
    }

    @-webkit-keyframes stampIn {
        0% {
            opacity: 0;
            -webkit-transform: scale(1.3) rotate(-3deg);
            transform: scale(1.3) rotate(-3deg);
        }
        100% {
            opacity: 1;
            -webkit-transform: scale(1) rotate(0deg);
            transform: scale(1) rotate(0deg);
        }
    }

    @keyframes stampIn {
        0% {
            opacity: 0;
            transform: scale(1.3) rotate(-3deg);
        }
        100% {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }
    }

    .success-check-emblem {
        width: 84px;
        height: 84px;
        background: #d1fae5;
        color: #10b981;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 24px rgba(5, 150, 105, 0.25);
        -webkit-animation: checkPop 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation: checkPop 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @-webkit-keyframes checkPop {
        0% { opacity: 0; -webkit-transform: scale(0.3); transform: scale(0.3); }
        80% { -webkit-transform: scale(1.15); transform: scale(1.15); }
        100% { opacity: 1; -webkit-transform: scale(1); transform: scale(1); }
    }

    @keyframes checkPop {
        0% { opacity: 0; transform: scale(0.3); }
        80% { transform: scale(1.15); }
        100% { opacity: 1; transform: scale(1); }
    }

    .countdown-track {
        height: 12px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .countdown-fill {
        height: 100%;
        width: 100%;
        background: #10b981;
        transform-origin: left center;
        transition: -webkit-transform 1s linear, transform 1s linear, background-color 0.3s ease;
    }

    .countdown-fill.countdown-urgent {
        background: #ef4444 !important;
    }

    .countdown-text-urgent {
        color: #ef4444 !important;
        -webkit-animation: pulseText 0.5s ease-in-out infinite alternate;
        animation: pulseText 0.5s ease-in-out infinite alternate;
    }

    @-webkit-keyframes pulseText {
        0% { opacity: 0.75; -webkit-transform: scale(1); transform: scale(1); }
        100% { opacity: 1; -webkit-transform: scale(1.08); transform: scale(1.08); }
    }

    @keyframes pulseText {
        0% { opacity: 0.75; transform: scale(1); }
        100% { opacity: 1; transform: scale(1.08); }
    }

    /* === PRINTER STATUS BAR (BRIGHT THEME) === */
    .printer-status-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-top: 2px solid #e2e8f0;
        background: #ffffff;
        cursor: pointer;
        user-select: none;
        flex-shrink: 0;
        transition: background-color 0.15s ease;
    }

    .printer-status-bar:active {
        background: #f1f5f9;
    }

    .printer-status-bar.bar-checking { color: #b45309; }
    .printer-status-bar.bar-ok       { color: #047857; }
    .printer-status-bar.bar-err      { color: #b91c1c; }

    .printer-status-bar .ps-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .printer-status-bar.bar-checking .ps-dot { background: #f59e0b; }
    .printer-status-bar.bar-ok       .ps-dot {
        background: #10b981;
        box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
    }
    .printer-status-bar.bar-err      .ps-dot { background: #ef4444; }

    .printer-flash {
        position: fixed;
        bottom: 56px;
        left: 50%;
        -webkit-transform: translateX(-50%);
        transform: translateX(-50%);
        background: #0f172a;
        border: 2px solid #334155;
        border-radius: 16px;
        padding: 16px 22px;
        font-size: 12px;
        color: #ffffff;
        white-space: nowrap;
        z-index: 9998;
        display: none;
        min-width: 300px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.35);
    }
    .printer-flash .pf-row { display: flex; align-items: baseline; gap: 10px; margin-bottom: 6px; }
    .printer-flash .pf-row:last-child { margin-bottom: 0; }
    .printer-flash .pf-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; min-width: 80px; }
    .printer-flash .pf-val   { font-weight: 700; font-size: 12px; }
    .printer-flash .pf-val.ok   { color: #34d399; }
    .printer-flash .pf-val.warn { color: #f59e0b; }
    .printer-flash .pf-val.err  { color: #f87171; }
    .printer-flash .pf-hr  { border: none; border-top: 1px solid #334155; margin: 8px 0; }
    .printer-flash .pf-hint { font-size: 0.75rem; color: #f59e0b; }

    /* Alert Dialog Fallback */
    #kioskAlertOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .kiosk-alert-box {
        background: #ffffff;
        border-radius: 24px;
        padding: 36px 30px;
        max-width: 500px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
        -webkit-animation: screenEntrance 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation: screenEntrance 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* Touch Friendly Action Buttons */
    .btn-kiosk-action {
        min-height: 64px;
        font-size: 1.25rem !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        padding: 14px 36px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: -webkit-transform 0.1s ease, transform 0.1s ease, box-shadow 0.15s ease;
    }
    .btn-kiosk-action:active {
        -webkit-transform: scale(0.96);
        transform: scale(0.96);
    }

    /* Reduced Motion Fallback */
    @media (prefers-reduced-motion: reduce) {
        .screen-enter, .service-card, .ticket-box, .ticket-hero-number,
        .success-check-emblem, .input-shake-error, .countdown-text-urgent, .kiosk-alert-box {
            -webkit-animation: none !important;
            animation: none !important;
            transition: none !important;
        }
    }

    /* Spinner for loading state */
    @-webkit-keyframes spinIcon {
        0%   { -webkit-transform: rotate(0deg); transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); transform: rotate(360deg); }
    }
    @keyframes spinIcon {
        0%   { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div id="kioskRoot" class="kiosk-root">
    <div class="kiosk-overlay">

        {{-- ═══ HEADER BAR ═══ --}}
        <header class="kiosk-header d-flex justify-content-between align-items-center flex-shrink-0">
            <div class="d-flex align-items-center">
                <div class="bg-white rounded-circle shadow-sm border border-2 border-slate-200 d-flex align-items-center justify-content-center flex-shrink-0 me-5"
                     style="width:68px;height:68px;padding:4px;overflow:hidden;">
                    @if(config('institution.logo_path') && file_exists(public_path(config('institution.logo_path'))))
                        <img alt="{{ config('institution.name') }}" src="{{ asset(config('institution.logo_path')) }}"
                             style="max-height:54px;max-width:54px;object-fit:contain;">
                    @elseif(config('institution.logo_path') && file_exists(storage_path('app/public/' . config('institution.logo_path'))))
                        <img alt="{{ config('institution.name') }}" src="{{ Storage::url(config('institution.logo_path')) }}"
                             style="max-height:54px;max-width:54px;object-fit:contain;">
                    @else
                        <div class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #0891b2, #0e7490);">
                            <svg style="width:34px;height:34px;fill:#ffffff;" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/></svg>
                        </div>
                    @endif
                </div>
                <div>
                    <div class="text-primary fw-bold fs-7 text-uppercase" style="letter-spacing:2px;">
                        Sistem Antrian PTSP
                    </div>
                    <h1 class="text-gray-900 fw-boldest fs-2 fs-lg-1 mb-0" style="letter-spacing:-0.5px;line-height:1.2;">
                        {{ config('institution.name') }}
                    </h1>
                </div>
            </div>

            <div class="text-end d-none d-md-block">
                <div class="fs-1 kiosk-clock" id="kioskClock">00:00:00</div>
                <div class="text-gray-500 fw-semibold fs-6 text-uppercase" id="kioskDate">---</div>
            </div>
        </header>

        {{-- ═══ LAYAR PILIH LAYANAN (SERVICE SELECTOR) ═══ --}}
        <main id="screenServices" class="screen-enter flex-grow-1 d-flex flex-column justify-content-center align-items-center py-10 px-6">
            <div class="text-center mb-10">
                <h2 class="text-gray-900 fw-boldest text-uppercase mb-3"
                    style="font-size:clamp(2.5rem, 6vw, 4.5rem); letter-spacing:-1.5px; line-height:1.15;">
                    SILAKAN PILIH LAYANAN
                </h2>
                <p class="text-gray-600 fs-3 fw-semibold text-uppercase mb-0" style="letter-spacing:1px;">
                    Sentuh salah satu kartu layanan di bawah ini
                </p>
            </div>

            @php
                $svcColors = ['svc-blue', 'svc-teal', 'svc-amber', 'svc-emerald', 'svc-purple', 'svc-rose', 'svc-cyan'];

                $getServiceColor = function ($service, $idx) use ($svcColors) {
                    $slug = strtolower($service->slug ?? $service->name ?? '');
                    $code = strtolower($service->code ?? '');
                    if (str_contains($slug, 'daftar') || str_contains($code, 'daftar')) return 'svc-blue';
                    if (str_contains($slug, 'info') || str_contains($slug, 'aduan') || str_contains($code, 'info')) return 'svc-teal';
                    if (str_contains($slug, 'produk') || str_contains($slug, 'hukum') || str_contains($slug, 'akta') || str_contains($code, 'produk')) return 'svc-amber';
                    if (str_contains($slug, 'ecourt') || str_contains($slug, 'posbakum') || str_contains($code, 'ecourt')) return 'svc-emerald';
                    if (str_contains($slug, 'prioritas') || str_contains($slug, 'khusus') || str_contains($code, 'prioritas')) return 'svc-purple';
                    if (str_contains($slug, 'kasir') || str_contains($slug, 'bayar') || str_contains($code, 'kasir')) return 'svc-rose';
                    return $svcColors[$idx % count($svcColors)];
                };

                // Inline SVG path data, Material Icons style (no FA dependency — Android 5 safe)
                $svcSvgPaths = [
                    'M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z', // file-lines
                    'M20 6h-2.18c.07-.44.18-.88.18-1.35C18 2.51 15.5 0 12.35 0 10.7 0 9.25.65 8.2 1.68L4 6H2v13h2v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h2V6h-2zm-9.85-2.9C10.77 2.4 11.54 2 12.35 2c1.56 0 2.65 1.19 2.65 2.65 0 .31-.07.6-.17.87L9.21 6H6.31l4.84-2.9zM20 17H4V8h16v9z', // folder
                    'M20 6h-2.18c.07-.44.18-.88.18-1.35C18 2.51 15.5 0 12.35 0 10.7 0 9.25.65 8.2 1.68L4 6H2v13h2v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h2V6h-2zm-9.85-2.9C10.77 2.4 11.54 2 12.35 2c1.56 0 2.65 1.19 2.65 2.65 0 .31-.07.6-.17.87L9.21 6H6.31l4.84-2.9zM20 17H4V8h16v9z', // briefcase variant
                    'M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z', // id-card
                    'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l7.59-7.59L21 8l-9 9z', // clipboard-check
                    'M18 17H6v-2h12v2zm0-4H6v-2h12v2zm0-4H6V7h12v2zM3 22l1.5-1.5L6 22l1.5-1.5L9 22l1.5-1.5L12 22l1.5-1.5L15 22l1.5-1.5L18 22l1.5-1.5L21 22V2l-1.5 1.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2 4.5 3.5 3 2v20z', // receipt
                    'M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z', // book
                    'M12 3L1 9l11 6 9-4.91V17h2V9M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z', // scale-balanced
                    'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z', // gavel/triangle
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
                    $colorClass = $getServiceColor($service, $idx);
                    $svgPath = $svcSvgPaths[$idx % count($svcSvgPaths)];
                @endphp
                <div data-service-id="{{ $service->id }}"
                     data-service-name="{{ e($service->name) }}"
                     data-service-purpose="{{ $purposeCode }}"
                     onclick="showBookingForm('{{ $service->id }}', '{{ addslashes($service->name) }}', '{{ $purposeCode }}')"
                     class="card service-card {{ $colorClass }}">
                    <div class="svc-icon-wrapper">
                        @if($service->icon_svg)
                            <div style="font-size:0; width:48px; height:48px; display:flex; align-items:center; justify-content:center;">
                                {!! $service->icon_svg !!}
                            </div>
                        @else
                            <svg style="width:44px;height:44px;fill:#ffffff;" viewBox="0 0 24 24"><path d="{{ $svgPath }}"/></svg>
                        @endif
                    </div>
                    <h3>{{ $service->name }}</h3>
                    <div class="service-action-badge mt-auto">
                        <span>AMBIL ANTRIAN</span>
                        <svg style="width:14px;height:14px;fill:#ffffff;flex-shrink:0;" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                    </div>
                </div>
                @endforeach
            </div>
        </main>

        {{-- ═══ LAYAR 2: FORM IDENTITAS LENGKAP + VIRTUAL NUMPAD ═══ --}}
        <section id="screenForm" class="flex-grow-1 d-flex align-items-center justify-content-center py-4 py-md-6 px-4 px-md-8 d-none">
            <div class="booking-card card">
                <div class="card-body p-5 p-md-7 p-lg-8">

                    {{-- Form Title & Service Indicator --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-4 border-bottom">
                        <div>
                            <span class="badge bg-primary text-white fs-6 fw-bold px-4 py-1.5 rounded-pill text-uppercase mb-1 d-inline-block"
                                  id="selectedServiceBadge">Layanan</span>
                            <h2 class="fw-boldest fs-2x text-gray-900 text-uppercase mb-0" id="selectedServiceName"></h2>
                            <div class="text-gray-500 fs-5 fw-semibold">Lengkapi identitas Anda untuk mencetak nomor antrian</div>
                        </div>
                        <button type="button" onclick="backToServices()" class="btn btn-outline btn-outline-secondary fw-bold px-5 py-2.5 rounded-pill">
                            <svg style="width:18px;height:18px;fill:currentColor;flex-shrink:0;" viewBox="0 0 24 24"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg> Ganti Layanan
                        </button>
                    </div>

                    <form id="bookingForm" onsubmit="return false;">
                        @csrf
                        <input type="hidden" id="service_id" name="service_id">
                        <input type="hidden" id="visit_purpose" name="visit_purpose">

                        <div class="row g-6">
                            {{-- Sisi Kiri: Form Inputs --}}
                            <div class="col-lg-7">
                                <div class="mb-4" id="wrapperVisitorName">
                                    <label class="fs-5 fw-bold text-gray-800 mb-1 d-block text-uppercase">
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

                                <div class="mb-4" id="wrapperVisitorNik">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="fs-5 fw-bold text-gray-800 mb-0 text-uppercase">
                                            Nomor NIK / Identitas (16 Digit) <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="nik-status-badge" id="nikStatusBadge">0/16 Digit</span>
                                            <span class="input-numpad-active-tag" id="nikActiveTag">Numpad Aktif</span>
                                        </div>
                                    </div>
                                    <input type="text"
                                           name="visitor_identifier"
                                           id="visitor_identifier"
                                           class="form-control kiosk-input"
                                           placeholder="Masukkan 16 digit NIK..."
                                           maxlength="16"
                                           required
                                           autocomplete="off"
                                           oninput="updateNikProgress()"
                                           onfocus="setActiveNumpadTarget('visitor_identifier')">
                                    <div class="nik-progress-track" id="nikProgressTrack">
                                        @for($i = 0; $i < 16; $i++)
                                            <span class="nik-progress-dot" data-dot="{{ $i }}"></span>
                                        @endfor
                                    </div>
                                </div>

                                <div class="mb-4" id="wrapperVisitorPhone">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="fs-5 fw-bold text-gray-800 mb-0 text-uppercase">
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

                                <div class="mb-2" id="wrapperVisitorWilayah">
                                    <label class="fs-5 fw-bold text-gray-800 mb-1 d-block text-uppercase">
                                        Asal Wilayah (Desa / Kelurahan) <span class="text-danger">*</span>
                                    </label>
                                    @if($wilayahOptions->isEmpty())
                                        <div class="alert alert-warning d-flex align-items-center p-3">
                                            <svg style="width:28px;height:28px;fill:#f59e0b;flex-shrink:0;margin-right:10px;" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                                            <div class="fs-6 text-gray-800">
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
                                    <div class="text-center mb-2">
                                        <div class="fs-7 fw-bold text-gray-600 text-uppercase mb-1.5">Papan Ketik Angka (Numpad)</div>
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                    id="btnTargetNik"
                                                    onclick="setActiveNumpadTarget('visitor_identifier')"
                                                    class="numpad-target-pill active">
                                                NIK (16 Digit)
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
                                            <svg style="width:24px;height:24px;fill:#b45309;" viewBox="0 0 24 24"><path d="M22 3H7c-.69 0-1.23.35-1.59.88L0 12l5.41 8.11c.36.53.9.89 1.59.89h15c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-3 12.59L17.59 17 14 13.41 10.41 17 9 15.59 12.59 12 9 8.41 10.41 7 14 10.59 17.59 7 19 8.41 15.41 12 19 15.59z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mt-4 pt-3 border-top">
                            <button type="button" onclick="backToServices()" class="btn btn-light btn-kiosk-action w-100 w-sm-auto">
                                <svg style="width:20px;height:20px;fill:currentColor;" viewBox="0 0 24 24"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                                KEMBALI
                            </button>
                            <button type="button" id="btnSubmit" onclick="submitBookingForm()" class="btn btn-primary btn-kiosk-action w-100 w-sm-auto shadow-lg">
                                <span class="indicator-label d-flex align-items-center gap-2">
                                    <svg style="width:20px;height:20px;fill:#ffffff;" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
                                    CETAK TIKET
                                </span>
                                <span class="indicator-progress d-none">
                                    <svg style="width:20px;height:20px;fill:#ffffff;margin-right:8px;-webkit-animation:spinIcon 1s linear infinite;animation:spinIcon 1s linear infinite;" viewBox="0 0 24 24"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
                                    MENCETAK...
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </section>

        {{-- ═══ LAYAR 3: TIKET BERHASIL DICETAK (THERMAL PRINTER DISPENSER DELIGHT) ═══ --}}
        <section id="screenSuccess" class="flex-grow-1 d-flex align-items-center justify-content-center py-4 py-md-6 px-4 d-none">
            <div class="printer-dispenser-housing">

                {{-- Printer Dispenser Top Bezel --}}
                <div class="printer-slot-bezel">
                    <span class="text-white fw-bold fs-7 text-uppercase" style="letter-spacing:1px; opacity:0.9; display:inline-flex; align-items:center; gap:8px;">
                        <svg style="width:16px;height:16px;fill:#67e8f9;" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
                        Thermal Ticket Dispenser
                    </span>
                    <div class="printer-slot-slit"></div>
                    <div class="printer-slot-light"></div>
                </div>

                {{-- Physical Ticket Slip with Serrated Top Tear --}}
                <div class="ticket-box card border-0">
                    <div class="card-body p-4 p-md-6">

                        <div class="success-check-emblem mb-3">
                            <svg style="width:clamp(2.2rem, 5vw, 3.5rem);height:clamp(2.2rem, 5vw, 3.5rem);fill:#10b981;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        </div>

                        <h1 class="fw-boldest fs-1 fs-lg-2x text-gray-900 text-uppercase mb-1" style="letter-spacing:-0.5px;">
                            TIKET BERHASIL DICETAK!
                        </h1>
                        <p class="text-gray-600 fs-4 fw-semibold mb-4">
                            Silakan ambil kertas tiket Anda di printer kiosk.
                        </p>

                        <div class="py-4 px-4 mb-4 rounded-4" style="background:#f0f9ff;border:2px dashed #0891b2;">
                            <span class="text-primary fw-bold fs-5 text-uppercase d-block mb-1" style="letter-spacing:1px;">
                                NOMOR ANTRIAN ANDA
                            </span>
                            <div class="ticket-hero-number" id="successTicketNumber">---</div>
                            <div class="text-gray-900 fw-bolder fs-3 text-uppercase" id="successServiceName"></div>
                        </div>

                        <div class="mb-5">
                            <p class="text-gray-600 fs-6 fw-semibold mb-2">
                                Layar kembali otomatis dalam <span class="fw-bold text-primary fs-5" id="countdownText">20 detik</span>
                            </p>
                            <div class="countdown-track w-100">
                                <div class="countdown-fill" id="countdownBar" style="width:100%;"></div>
                            </div>
                        </div>

                        <button type="button" onclick="resetKiosk()" class="btn btn-success btn-kiosk-action px-10 shadow">
                            <svg style="width:20px;height:20px;fill:#ffffff;" viewBox="0 0 24 24"><path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/></svg>
                            SELESAI / AMBIL TIKET
                        </button>

                    </div>
                </div>

            </div>
        </section>

        {{-- ═══ ALERT MODAL OVERLAY (ANDROID 5 FAIL-SAFE) ═══ --}}
        <div id="kioskAlertOverlay">
            <div class="kiosk-alert-box">
                <div style="font-size:clamp(2.5rem, 6vw, 4.5rem); color:#ef4444; margin-bottom:14px;">
                    <svg style="width:clamp(2.5rem, 6vw, 4.5rem);height:clamp(2.5rem, 6vw, 4.5rem);fill:#ef4444;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                </div>
                <h3 style="margin:0 0 10px; color:#0f172a; font-size:1.25rem; font-weight:800;" id="kioskAlertTitle">Perhatian</h3>
                <p id="kioskAlertMsg" style="color:#64748b; font-size:1rem; margin-bottom:26px; line-height:1.4;"></p>
                <button type="button" onclick="hideKioskAlert()"
                        class="btn btn-primary fw-bold fs-4 px-10 py-3 rounded-pill">
                    Mengerti
                </button>
            </div>
        </div>

        {{-- ═══ PRINTER STATUS BAR & FOOTER (BRIGHT THEME) ═══ --}}
        <footer class="flex-shrink-0">
            <div id="printerFlash" class="printer-flash"></div>
            <div id="printerStatusBar" class="printer-status-bar bar-checking" onclick="showPrinterFlash()">
                <span class="ps-dot"></span>
                <span id="printerLabel">MEMERIKSA KONEKSI PRINTER...</span>
            </div>

            <div class="py-4 text-center" style="background:#f1f5f9; border-top:1px solid #e2e8f0;">
                <span class="text-gray-500 fw-semibold fs-7 text-uppercase" style="letter-spacing:1px;">
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

    /* === SENSORY AUDIO DELIGHT (WEB AUDIO API SYNTHESIS - SAFE FALLBACK) === */
    var audioCtx = null;
    function getAudioContext() {
        try {
            var AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass && !audioCtx) {
                audioCtx = new AudioContextClass();
            }
        } catch (e) {}
        return audioCtx;
    }

    function playKeyTone() {
        try {
            var ctx = getAudioContext();
            if (!ctx) return;
            if (ctx.state === 'suspended') ctx.resume();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(800, ctx.currentTime);
            gain.gain.setValueAtTime(0.04, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.04);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.04);
        } catch (e) {}
    }

    function playSuccessChime() {
        try {
            var ctx = getAudioContext();
            if (!ctx) return;
            if (ctx.state === 'suspended') ctx.resume();

            var now = ctx.currentTime;
            var notes = [523.25, 659.25]; // C5, E5
            for (var i = 0; i < notes.length; i++) {
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(notes[i], now + (i * 0.12));
                gain.gain.setValueAtTime(0.08, now + (i * 0.12));
                gain.gain.exponentialRampToValueAtTime(0.001, now + (i * 0.12) + 0.25);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now + (i * 0.12));
                osc.stop(now + (i * 0.12) + 0.25);
            }
        } catch (e) {}
    }

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

    function triggerInputPulse(inputElement) {
        if (!inputElement) return;
        inputElement.classList.add('key-pulse');
        setTimeout(function () {
            inputElement.classList.remove('key-pulse');
        }, 120);
    }

    function numpadPress(digit) {
        var input = document.getElementById(currentNumpadTarget);
        if (!input) return;

        var currentVal = input.value || '';
        var maxLen = currentNumpadTarget === 'visitor_identifier' ? 16 : 15;

        if (currentVal.length < maxLen) {
            input.value = currentVal + digit;
            triggerInputPulse(input);
            playKeyTone();
            if (currentNumpadTarget === 'visitor_identifier') {
                updateNikProgress();
            }
        }
    }

    function numpadBackspace() {
        var input = document.getElementById(currentNumpadTarget);
        if (!input) return;

        var currentVal = input.value || '';
        if (currentVal.length > 0) {
            input.value = currentVal.substring(0, currentVal.length - 1);
            triggerInputPulse(input);
            playKeyTone();
            if (currentNumpadTarget === 'visitor_identifier') {
                updateNikProgress();
            }
        }
    }

    function numpadClear() {
        var input = document.getElementById(currentNumpadTarget);
        if (input) {
            input.value = '';
            triggerInputPulse(input);
            playKeyTone();
            if (currentNumpadTarget === 'visitor_identifier') {
                updateNikProgress();
            }
        }
    }

    function updateNikProgress() {
        var input = document.getElementById('visitor_identifier');
        var track = document.getElementById('nikProgressTrack');
        var badge = document.getElementById('nikStatusBadge');
        if (!input || !track) return;

        var val = input.value || '';
        var len = val.length;
        var dots = track.querySelectorAll('.nik-progress-dot');

        for (var i = 0; i < dots.length; i++) {
            if (i < len) {
                dots[i].classList.add('filled');
                if (len >= 16) {
                    dots[i].classList.add('completed');
                } else {
                    dots[i].classList.remove('completed');
                }
            } else {
                dots[i].classList.remove('filled');
                dots[i].classList.remove('completed');
            }
        }

        if (badge) {
            if (len >= 16) {
                badge.textContent = '16/16 LENGKAP ✓';
                badge.className = 'nik-status-badge ready';
            } else {
                badge.textContent = len + '/16 DIGIT';
                badge.className = 'nik-status-badge';
            }
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
        updateNikProgress();

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
        var screens = ['screenServices', 'screenForm', 'screenSuccess'];
        for (var i = 0; i < screens.length; i++) {
            var el = document.getElementById(screens[i]);
            if (el) {
                el.classList.add('d-none');
                el.classList.remove('screen-enter');
                el.style.display = 'none';
            }
        }

        var targetEl = document.getElementById(screenId);
        if (targetEl) {
            targetEl.classList.remove('d-none');
            targetEl.style.display = '';
            
            if (window.requestAnimationFrame) {
                window.requestAnimationFrame(function () {
                    targetEl.classList.add('screen-enter');
                });
            } else {
                void targetEl.offsetWidth;
                targetEl.classList.add('screen-enter');
            }
        }
    }

    function resetKiosk() {
        clearCountdown();
        var form = document.getElementById('bookingForm');
        if (form) { form.reset(); }
        $('#visit_purpose').val('');
        updateNikProgress();
        if ($.fn.select2) {
            $('#visitor_wilayah_kode').val(null).trigger('change');
        }
        backToServices();
    }

    /* === FORM SUBMIT & PRINT TICKET === */
    function shakeField(wrapperId, inputId) {
        var $el = $('#' + inputId);
        $el.addClass('input-shake-error');
        setTimeout(function () {
            $el.removeClass('input-shake-error');
        }, 500);
    }

    function submitBookingForm() {
        var name = $.trim($('#visitor_name').val() || '');
        var nik = $.trim($('#visitor_identifier').val() || '');
        var phone = $.trim($('#visitor_phone').val() || '');
        var wilayah = $('#visitor_wilayah_kode').val() || '';

        if (!name || name.length < 3) {
            shakeField('wrapperVisitorName', 'visitor_name');
            showKioskAlert('Nama Lengkap wajib diisi minimal 3 karakter.');
            $('#visitor_name').focus();
            return;
        }

        if (!nik || nik.length < 10) {
            shakeField('wrapperVisitorNik', 'visitor_identifier');
            showKioskAlert('Nomor NIK / Identitas belum valid (minimal 10 digit).');
            setActiveNumpadTarget('visitor_identifier');
            return;
        }

        if (!phone || phone.length < 8) {
            shakeField('wrapperVisitorPhone', 'visitor_phone');
            showKioskAlert('Nomor WhatsApp / HP wajib diisi dengan benar.');
            setActiveNumpadTarget('visitor_phone');
            return;
        }

        if (!wilayah) {
            shakeField('wrapperVisitorWilayah', 'visitor_wilayah_kode');
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
                    playSuccessChime();

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
        
        var cdBar = document.getElementById('countdownBar');
        if (cdBar) {
            cdBar.style.webkitTransform = 'scaleX(1)';
            cdBar.style.transform = 'scaleX(1)';
            cdBar.classList.remove('countdown-urgent');
        }
        $('#countdownText').text(cdSeconds + ' detik').removeClass('countdown-text-urgent');

        cdInterval = setInterval(function () {
            cdSeconds--;
            var pct = Math.max(0, (cdSeconds / CD_TOTAL));
            
            if (cdBar) {
                cdBar.style.webkitTransform = 'scaleX(' + pct + ')';
                cdBar.style.transform = 'scaleX(' + pct + ')';
            }
            $('#countdownText').text(cdSeconds + ' detik');

            if (cdSeconds <= 5) {
                $('#countdownBar').addClass('countdown-urgent');
                $('#countdownText').addClass('countdown-text-urgent');
            }

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

    /* === IDLE TIMEOUT — return to service selector after 90s of no interaction === */
    var idleTimer = null;
    var IDLE_MS = 90000;

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        var services = document.getElementById('screenServices');
        var isOnServices = services && services.style.display !== 'none' && !services.classList.contains('d-none');
        if (isOnServices) { return; }
        idleTimer = setTimeout(function () {
            if (cdInterval) { clearCountdown(); }
            var form = document.getElementById('bookingForm');
            if (form) { form.reset(); }
            switchScreen('screenServices');
        }, IDLE_MS);
    }

    document.addEventListener('touchstart', resetIdleTimer, true);
    document.addEventListener('click', resetIdleTimer, true);
    resetIdleTimer();
</script>
@endpush

