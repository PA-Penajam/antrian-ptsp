@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════
       ANDROID 5 & SMART TV (CHROMIUM 37-53 / WEBOS / TIZEN)
       IMPECCABLE TYPESET: HIGH-LEGIBILITY DIGITAL QUEUE DISPLAY
       - Precision typographic scale, tabular figures & optical kerning
       - 14:1+ WCAG AAA readability for 8-15m viewing distances
       - Strict 100vh / 100vw zero-overflow layout budgeting
       - 60 FPS hardware-accelerated animations (transform3d & opacity)
       - Web Audio synthesized dual-tone airport chime
       ═══════════════════════════════════════════════════════════ */

    *, *::before, *::after {
        box-sizing: border-box;
    }

    body {
        background-color: #f1f5f9;
        margin: 0;
        padding: 0;
        overflow: hidden;
        -webkit-user-select: none;
        user-select: none;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: auto;
    }

    .tv-root {
        height: 100vh;
        width: 100vw;
        display: flex;
        flex-direction: column;
        padding: 1.25rem 1.75rem;
        box-sizing: border-box;
        background-color: #f8fafc;
        background-image:
            linear-gradient(135deg, rgba(248, 250, 252, 0.93) 0%, rgba(241, 245, 249, 0.90) 50%, rgba(226, 232, 240, 0.94) 100%),
            image-set(url('{{ asset('metronic-assets/media/misc/tv-bg-bright.webp') }}') type('image/webp'), url('{{ asset('metronic-assets/media/misc/tv-bg-bright.jpg') }}') type('image/jpeg'));
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        gap: 1rem;
        contain: layout paint;
    }

    /* === Sound Activation Overlay (WebOS & Smart TV Autoplay Policy) === */
    .sound-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 23, 42, 0.9);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .sound-overlay.fade-out {
        opacity: 0;
        pointer-events: none;
    }

     .sound-overlay-icon-box {
        position: relative;
        width: 112px;
        height: 112px;
        border-radius: 16px;
        background: #0e7490;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        box-shadow: 0 12px 36px rgba(14, 116, 144, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .sound-overlay-ripple {
        position: absolute;
        inset: -10px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        opacity: 0.5;
    }

    .sound-overlay-title {
        font-size: 2rem;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.02em;
        margin-bottom: 0.45rem;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.35);
        line-height: 1.15;
    }

    .sound-overlay-subtitle {
        font-size: 1rem;
        font-weight: 500;
        color: #cbd5e1;
        letter-spacing: 0.01em;
        line-height: 1.4;
    }

    .sound-overlay-btn {
        margin-top: 1.5rem;
        background: #0e7490;
        color: #ffffff;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        padding: 14px 32px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.28);
        box-shadow: 0 8px 24px rgba(14, 116, 144, 0.35);
        transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
    }

    .sound-overlay-btn:hover {
        background: #0891b2;
        box-shadow: 0 10px 28px rgba(14, 116, 144, 0.4);
    }

    .sound-overlay-btn:active {
        transform: scale(0.98);
    }

    .sound-overlay-btn:focus-visible {
        outline: 2px solid #ffffff;
        outline-offset: 3px;
    }

    /* === BRIGHT HEADER BAR (TYPESET) === */
    .tv-header {
        background: #ffffff;
        border-radius: 16px;
        padding: 0.85rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        flex-shrink: 0;
        position: relative;
    }

    .tv-clock {
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum" 1, "zero" 1;
        letter-spacing: -0.03em;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }

    .tv-sync-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #047857;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        line-height: 1;
    }

     .tv-sync-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 8px #10b981;
    }

    /* === FULL QUEUE BODY LAYOUT (54% HERO / 46% RECENT) === */
    .tv-body {
        display: flex;
        flex-direction: row;
        flex: 1;
        min-height: 0;
        gap: 1.5rem;
        contain: layout paint;
    }

    .tv-hero-col {
        width: 54%;
        display: flex;
        flex-direction: column;
        min-height: 0;
        contain: layout paint;
    }

    .tv-recent-col {
        width: 46%;
        display: flex;
        flex-direction: column;
        min-height: 0;
        contain: layout paint;
    }

    /* === HERO ACTIVE CALL CARD (OVERDRIVE CHROMATIC PALETTES) === */
    .queue-hero {
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.22);
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: background 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
    }

    /* Default / Blue Theme (Pendaftaran) */
    .queue-hero, .queue-hero.hero-theme-blue {
        background-color: #1e40af;
        background-image:
            linear-gradient(145deg, rgba(30, 64, 175, 0.84) 0%, rgba(37, 99, 235, 0.80) 50%, rgba(29, 78, 216, 0.88) 100%),
            url('{{ asset('metronic-assets/media/misc/tv-hero-card-bg.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 12px 40px rgba(37, 99, 235, 0.35);
    }

    /* Teal Theme (Informasi & Pengaduan) */
    .queue-hero.hero-theme-teal {
        background-color: #0f766e;
        background-image:
            linear-gradient(145deg, rgba(15, 118, 110, 0.85) 0%, rgba(13, 148, 136, 0.82) 50%, rgba(17, 94, 89, 0.90) 100%),
            url('{{ asset('metronic-assets/media/misc/tv-hero-card-bg.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 12px 40px rgba(13, 148, 136, 0.35);
    }

    /* Amber Theme (Produk Hukum) */
    .queue-hero.hero-theme-amber {
        background-color: #b45309;
        background-image:
            linear-gradient(145deg, rgba(180, 83, 9, 0.86) 0%, rgba(217, 119, 6, 0.84) 50%, rgba(146, 64, 14, 0.90) 100%),
            url('{{ asset('metronic-assets/media/misc/tv-hero-card-bg.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 12px 40px rgba(217, 119, 6, 0.35);
    }

    /* Emerald Theme (eCourt & Posbakum) */
    .queue-hero.hero-theme-emerald {
        background-color: #047857;
        background-image:
            linear-gradient(145deg, rgba(4, 120, 87, 0.86) 0%, rgba(5, 150, 105, 0.84) 50%, rgba(6, 95, 70, 0.90) 100%),
            url('{{ asset('metronic-assets/media/misc/tv-hero-card-bg.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 12px 40px rgba(5, 150, 105, 0.35);
    }

    /* Purple Theme (Prioritas & Khusus) */
    .queue-hero.hero-theme-purple {
        background-color: #6d28d9;
        background-image:
            linear-gradient(145deg, rgba(109, 40, 217, 0.86) 0%, rgba(124, 58, 237, 0.84) 50%, rgba(91, 33, 182, 0.90) 100%),
            url('{{ asset('metronic-assets/media/misc/tv-hero-card-bg.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 12px 40px rgba(124, 58, 237, 0.35);
    }

    .queue-hero.hero-pulse-anim {
        border-color: rgba(255, 255, 255, 0.65);
        box-shadow: 0 14px 40px rgba(255, 255, 255, 0.18);
    }

    .queue-hero .ticket-number {
        font-size: clamp(4.5rem, 9vw, 8.5rem);
        line-height: 0.95;
        letter-spacing: -0.04em;
        font-weight: 800;
        color: #ffffff;
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum" 1, "zero" 1;
        text-shadow: 0 4px 18px rgba(0, 0, 0, 0.28);
        margin: 0.2rem 0 0.85rem;
        position: relative;
        display: inline-block;
        overflow-wrap: break-word;
        word-break: break-word;
        max-width: 100%;
    }

    .counter-hero-badge {
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.32);
        border-radius: 12px;
        padding: 6px 18px;
        display: inline-block;
    }

    /* Calling Multi-Chroma 5-Band Audio Equalizer Animation */
    .audio-equalizer {
        display: inline-flex;
        align-items: flex-end;
        gap: 3px;
        height: 18px;
        margin-left: 8px;
        vertical-align: middle;
        background: rgba(0, 0, 0, 0.18);
        padding: 3px 6px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .eq-bar {
        width: 4px;
        border-radius: 2px;
        opacity: 0.9;
    }
    .eq-bar:nth-child(1) { height: 40%; background: #38bdf8; }
    .eq-bar:nth-child(2) { height: 95%; background: #22d3ee; }
    .eq-bar:nth-child(3) { height: 60%; background: #34d399; }
    .eq-bar:nth-child(4) { height: 100%; background: #fbbf24; }
    .eq-bar:nth-child(5) { height: 75%; background: #f472b6; }

    /* Live Call Crimson Beacon Badge */
    .hero-live-badge {
        background: rgba(239, 68, 68, 0.22);
        color: #ffffff;
        letter-spacing: 0.08em;
        font-weight: 700;
        font-size: 0.7rem;
        border: 1px solid rgba(254, 202, 202, 0.42);
        text-transform: uppercase;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #ef4444;
        box-shadow: 0 0 8px #ef4444;
        display: inline-block;
    }

    /* Animasi panggilan masuk — single authored entrance */
    .call-animate {
        -webkit-animation: callInHero 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation: callInHero 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @-webkit-keyframes callInHero {
        0% {
            opacity: 0;
            -webkit-transform: scale(0.96) translate3d(0, 10px, 0);
            transform: scale(0.96) translate3d(0, 10px, 0);
        }
        100% {
            opacity: 1;
            -webkit-transform: scale(1) translate3d(0, 0, 0);
            transform: scale(1) translate3d(0, 0, 0);
        }
    }

    @keyframes callInHero {
        0% {
            opacity: 0;
            transform: scale(0.96) translate3d(0, 10px, 0);
        }
        100% {
            opacity: 1;
            transform: scale(1) translate3d(0, 0, 0);
        }
    }

    .slide-up {
        -webkit-animation: slideUpIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation: slideUpIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @-webkit-keyframes slideUpIn {
        0% { opacity: 0; -webkit-transform: translate3d(0, 14px, 0); transform: translate3d(0, 14px, 0); }
        100% { opacity: 1; -webkit-transform: translate3d(0, 0, 0); transform: translate3d(0, 0, 0); }
    }

    @keyframes slideUpIn {
        0% { opacity: 0; transform: translate3d(0, 14px, 0); }
        100% { opacity: 1; transform: translate3d(0, 0, 0); }
    }

    /* Living Idle Radar Beacon (LG WebOS GPU Optimized) */
    .idle-radar-wrapper {
        position: relative;
        width: 96px;
        height: 96px;
        display: -webkit-flex;
        display: flex;
        -webkit-align-items: center;
        align-items: center;
        -webkit-justify-content: center;
        justify-content: center;
    }

    .radar-core-icon {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.5);
        display: -webkit-flex;
        display: flex;
        -webkit-align-items: center;
        align-items: center;
        -webkit-justify-content: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        z-index: 2;
    }

    .radar-ping-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.45);
        -webkit-animation: radarPing 2.8s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        animation: radarPing 2.8s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        z-index: 1;
    }

    .radar-ping-ring-outer {
        position: absolute;
        inset: -14px;
        border-radius: 50%;
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        -webkit-animation: radarPing 2.8s cubic-bezier(0.16, 1, 0.3, 1) 1.2s infinite;
        animation: radarPing 2.8s cubic-bezier(0.16, 1, 0.3, 1) 1.2s infinite;
        z-index: 1;
    }

    @-webkit-keyframes radarPing {
        0% { -webkit-transform: scale(0.85); opacity: 0.85; }
        100% { -webkit-transform: scale(1.45); opacity: 0; }
    }

    @keyframes radarPing {
        0% { transform: scale(0.85); opacity: 0.85; }
        100% { transform: scale(1.45); opacity: 0; }
    }

    .idle-service-pill {
        display: -webkit-inline-flex;
        display: inline-flex;
        -webkit-align-items: center;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    .idle-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .idle-dot.bg-cyan { background-color: #38bdf8; box-shadow: 0 0 6px #38bdf8; }
    .idle-dot.bg-teal { background-color: #2dd4bf; box-shadow: 0 0 6px #2dd4bf; }
    .idle-dot.bg-amber { background-color: #fbbf24; box-shadow: 0 0 6px #fbbf24; }
    .idle-dot.bg-emerald { background-color: #34d399; box-shadow: 0 0 6px #34d399; }

    .hero-call-glow {
        -webkit-animation: heroGlowPulse 2s ease-in-out infinite;
        animation: heroGlowPulse 2s ease-in-out infinite;
    }

    @-webkit-keyframes heroGlowPulse {
        0% { border-color: rgba(255, 255, 255, 0.4); box-shadow: 0 12px 40px rgba(37, 99, 235, 0.35); }
        50% { border-color: rgba(255, 255, 255, 0.85); box-shadow: 0 16px 50px rgba(255, 255, 255, 0.22); }
        100% { border-color: rgba(255, 255, 255, 0.4); box-shadow: 0 12px 40px rgba(37, 99, 235, 0.35); }
    }

    @keyframes heroGlowPulse {
        0% { border-color: rgba(255, 255, 255, 0.4); box-shadow: 0 12px 40px rgba(37, 99, 235, 0.35); }
        50% { border-color: rgba(255, 255, 255, 0.85); box-shadow: 0 16px 50px rgba(255, 255, 255, 0.22); }
        100% { border-color: rgba(255, 255, 255, 0.4); box-shadow: 0 12px 40px rgba(37, 99, 235, 0.35); }
    }

    .radar-pulse-icon { opacity: 0.75; }

    /* === RECENT CALLS PANEL (TYPESET THEME) === */
    .queue-recent {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.15rem 1.25rem;
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        contain: layout paint;
    }

    .recent-calls-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1;
        min-height: 0;
        overflow: hidden;
        justify-content: flex-start;
    }

    .recent-call-item {
        background: #f8fafc;
        border-radius: 12px;
        padding: 0.85rem 1.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        transition: opacity 0.3s ease, border-color 0.25s ease;
        -webkit-animation: slideUpIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation: slideUpIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        overflow: hidden;
        min-width: 0;
    }

    .recent-call-item .ticket-number {
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum" 1, "zero" 1;
        font-weight: 900;
        letter-spacing: -1px;
    }

    .recent-counter-badge {
        background: #0e7490;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 6px 14px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        flex-shrink: 0;
        max-width: 42%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* === BRIGHT COLORFUL FOOTER MARQUEE === */
    .tv-footer {
        background: #ffffff;
        border-radius: 12px;
        padding: 0.65rem 1.15rem;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    }

    .tv-footer-badge {
        background: #0f172a;
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 5px 12px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }

    @keyframes tv-marquee {
        0%   { -webkit-transform: translate3d(100vw, 0, 0); transform: translate3d(100vw, 0, 0); }
        100% { -webkit-transform: translate3d(-100%, 0, 0); transform: translate3d(-100%, 0, 0); }
    }

    @-webkit-keyframes tv-marquee {
        0%   { -webkit-transform: translate3d(100vw, 0, 0); transform: translate3d(100vw, 0, 0); }
        100% { -webkit-transform: translate3d(-100%, 0, 0); transform: translate3d(-100%, 0, 0); }
    }

    .marquee-text {
        display: inline-block;
        white-space: nowrap;
        -webkit-animation: tv-marquee 35s linear infinite;
        animation: tv-marquee 35s linear infinite;
    }

    .tv-debug-panel {
        position: fixed;
        left: 1rem;
        bottom: 1rem;
        z-index: 10000;
        display: none;
        width: min(46rem, calc(100vw - 2rem));
        max-height: 45vh;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.28);
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.94);
        color: #f8fafc;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.45);
        font-family: Consolas, Monaco, monospace;
        font-size: 0.72rem;
        line-height: 1.35;
    }

    .tv-debug-panel.is-visible {
        display: block;
    }

    .tv-debug-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.65rem 0.8rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.16);
        color: #ffffff;
        font-weight: 700;
    }

    .tv-debug-lines {
        max-height: 36vh;
        overflow-y: auto;
        padding: 0.35rem 0.8rem;
    }

    .tv-debug-line {
        padding: 0.32rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        word-break: break-word;
    }

    /* Prefers reduced motion */
    @media (prefers-reduced-motion: reduce) {
        .call-animate, .slide-up, .hero-pulse-anim, .recent-call-item,
        .sound-overlay-icon-box, .sound-overlay-ripple, .eq-bar, .tv-sync-dot, .live-dot, .radar-pulse-icon {
            -webkit-animation: none !important;
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="tv-root">

    {{-- ═══ HEADER (BRIGHT TYPESET THEME) ═══ --}}
    <div class="tv-header">
        <div class="d-flex align-items-center gap-4">
            <div class="bg-white rounded-circle shadow-sm border border-2 border-slate-200 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:58px;height:58px;padding:4px;overflow:hidden;">
                @if(config('institution.logo_path') && file_exists(public_path(config('institution.logo_path'))))
                    <img alt="{{ config('institution.name') }}" src="{{ asset(config('institution.logo_path')) }}"
                         style="max-height:46px;max-width:46px;object-fit:contain;">
                @elseif(config('institution.logo_path') && file_exists(storage_path('app/public/' . config('institution.logo_path'))))
                    <img alt="{{ config('institution.name') }}" src="{{ Storage::url(config('institution.logo_path')) }}"
                         style="max-height:46px;max-width:46px;object-fit:contain;">
                @else
                    <div class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #0891b2, #0e7490);">
                        <svg style="width:28px;height:28px;fill:#ffffff;" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/></svg>
                    </div>
                @endif
            </div>
            <div>
                <div class="fw-boldest fs-2 fs-lg-1 mb-0" style="color:#0f172a;letter-spacing:-0.5px;line-height:1.15;">
                    {{ config('institution.name') }}
                </div>
                <div class="d-flex align-items-center gap-3 mt-0.5">
                    <span class="fw-bold fs-7 text-uppercase"
                          style="color:#0e7490;letter-spacing:0.08em;">
                        Sistem Antrian Digital PTSP
                    </span>
                    <span class="tv-sync-pill">
                        <span class="tv-sync-dot"></span>
                        <span>ONLINE SYNC</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="text-end">
            <div class="tv-clock fs-2x fs-lg-3x mb-0" id="clockDisplay">00:00:00</div>
            <div class="fw-bold fs-5 text-uppercase mt-0.5" style="color:#64748b;letter-spacing:0.5px;" id="dateDisplay">---</div>
        </div>
    </div>

    {{-- ═══ BODY (FULL SCREEN QUEUE: 54% HERO / 46% RECENT) ═══ --}}
    <div class="tv-body">

        {{-- KOLOM KIRI (54%): Hero Sedang Dipanggil --}}
        <div class="tv-hero-col">
            <div class="queue-hero" id="heroCard">
                {{-- Header kartu — label + multi-chroma equalizer + LIVE beacon --}}
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="fw-bold fs-7 text-uppercase" style="color:rgba(255,255,255,0.92);letter-spacing:0.12em;">
                            Sedang Dipanggil
                        </span>
                        <span class="audio-equalizer d-none" id="heroEqualizer">
                            <span class="eq-bar"></span>
                            <span class="eq-bar"></span>
                            <span class="eq-bar"></span>
                            <span class="eq-bar"></span>
                            <span class="eq-bar"></span>
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge fs-6 px-3.5 py-1.5 rounded-pill text-uppercase hero-live-badge d-flex align-items-center gap-2">
                            <span class="live-dot"></span>
                            <span>LIVE CALL</span>
                        </span>
                    </div>
                </div>

                {{-- State: tidak ada panggilan (Living Radar Beacon & Multi-Counter Overview) --}}
                <div id="noCallState" class="no-call-state py-6 my-auto d-flex flex-column align-items-center justify-content-center">
                    <div class="idle-radar-wrapper mb-3">
                        <div class="radar-ping-ring"></div>
                        <div class="radar-ping-ring-outer"></div>
                        <div class="radar-core-icon">
                            <div id="greetingIcon" class="text-white d-flex align-items-center justify-content-center" style="width:100%;height:100%;">
                                <svg style="width:38px;height:38px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                            </div>
                        </div>
                    </div>
                    <div id="greetingTitle" class="fw-boldest fs-1 fs-lg-2hx text-uppercase text-white mb-1" style="letter-spacing:0.04em;">
                        Selamat Datang
                    </div>
                    <div id="greetingSubtitle" class="fw-semibold fs-5 text-white-50 mb-4" style="letter-spacing:0.02em;">
                        Sistem Antrian PTSP Siap Melayani Anda
                    </div>

                    {{-- Multi-Loket Overview Grid on Idle --}}
                    <div class="idle-counters-overview w-100 px-3 mt-1">
                        <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                            <span class="idle-service-pill">
                                <span class="idle-dot bg-cyan"></span>
                                <span>LOKET 1 • PENDAFTARAN</span>
                            </span>
                            <span class="idle-service-pill">
                                <span class="idle-dot bg-teal"></span>
                                <span>LOKET 2 • INFORMASI & PENGADUAN</span>
                            </span>
                            <span class="idle-service-pill">
                                <span class="idle-dot bg-amber"></span>
                                <span>LOKET 3 • PRODUK HUKUM</span>
                            </span>
                            <span class="idle-service-pill">
                                <span class="idle-dot bg-emerald"></span>
                                <span>LOKET 4 • eCOURT / POSBAKUM</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- State: ada panggilan --}}
                <div id="activeCallState" class="d-none my-auto">
                    <div class="ticket-number call-animate" id="activeTicketNumber"></div>
                    <div class="counter-hero-badge mb-3 slide-up">
                        <span class="text-white fw-boldest fs-1 fs-lg-2hx" style="letter-spacing:0.5px;" id="activeCounterName">
                            LOKET
                        </span>
                    </div>
                    <div class="fw-bold fs-1 fs-lg-2hx text-uppercase text-white slide-up mb-1" style="letter-spacing:-0.5px;" id="activeServiceName">
                    </div>
                    <div class="slide-up mt-1">
                        <span class="badge fs-4 fw-bold px-4 py-1.5 rounded-pill text-uppercase"
                              style="background:rgba(255,255,255,0.2);color:#ffffff;border:1px solid rgba(255,255,255,0.35);letter-spacing:0.5px;"
                              id="activeVisitPurpose">
                        </span>
                    </div>
                </div>

                {{-- Bottom hint inside Hero card --}}
                <div class="text-center pt-2" style="border-top:1px solid rgba(255,255,255,0.22);">
                    <span class="fs-6 fw-semibold text-uppercase" style="color:rgba(255,255,255,0.88);letter-spacing:1.2px;">
                        Silakan Menuju Loket yang Tertera Saat Nomor Anda Dipanggil
                    </span>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (46%): Panggilan Terakhir (Typeset Theme) --}}
        <div class="tv-recent-col">
            <div class="queue-recent">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-slate-200 flex-shrink-0">
                    <div>
                        <div class="fw-boldest fs-4 text-uppercase" style="color:#0f172a;letter-spacing:1.5px;">
                            Panggilan Terakhir
                        </div>
                        <span class="fs-7 fw-semibold" style="color:#64748b;letter-spacing:0.2px;">Riwayat pemanggilan tiket loket</span>
                    </div>
                    <span class="badge bg-light-primary text-primary border border-primary-subtle fs-7 fw-bold px-3.5 py-1.5 rounded-pill text-uppercase" style="letter-spacing:0.5px;">
                        Layanan PTSP
                    </span>
                </div>
                <div id="recentCallsContainer" class="recent-calls-list">
                    <div class="recent-call-item justify-content-center py-10">
                        <span class="fw-semibold fs-4 text-muted">
                            Menunggu data antrian...
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ FOOTER MARQUEE (TYPESET BROADCAST STYLE) ═══ --}}
    <div class="tv-footer">
        <div class="tv-footer-badge">
            <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
            <span>INFO PTSP</span>
        </div>
        <div class="overflow-hidden flex-grow-1">
            <div class="marquee-text">
                <span class="fw-bold fs-3" style="color:#0f172a;letter-spacing:0.3px;">
                    <span style="color:#2563eb;">&bull;</span>&nbsp; Selamat Datang di {{ config('institution.name') }} &nbsp;&nbsp;&nbsp;
                    <span style="color:#059669;">&bull;</span>&nbsp; Ambil Nomor Antrian pada Mesin Kiosk Mandiri &nbsp;&nbsp;&nbsp;
                    <span style="color:#d97706;">&bull;</span>&nbsp; Pastikan Anda Membawa Persyaratan Dokumen yang Lengkap &nbsp;&nbsp;&nbsp;
                    <span style="color:#7c3aed;">&bull;</span>&nbsp; Harap Menunggu Panggilan dengan Tertib & Nyaman
                </span>
            </div>
        </div>
    </div>

</div>

{{-- Overlay aktivasi suara — ramah Autoplay Policy browser (WebOS LG & Smart TV) --}}
<div id="soundOverlay" class="sound-overlay">
    <div class="sound-overlay-icon-box">
        <div class="sound-overlay-ripple"></div>
        <svg class="text-white" style="width:52px;height:52px;stroke:currentColor;fill:none;stroke-width:1.8;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>
    </div>
    <div class="sound-overlay-title">Sentuh / Tekan Tombol</div>
    <div class="sound-overlay-subtitle">untuk Mengaktifkan Suara Panggilan Antrian</div>
    <button type="button" class="sound-overlay-btn d-flex align-items-center justify-content-center gap-2" onclick="activateSound()">
        <svg style="width:18px;height:18px;fill:currentColor;" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
        <span>Aktifkan Suara Sekarang</span>
    </button>
</div>

<audio id="ttsAudio" preload="auto"></audio>

<div id="tvDebugPanel" class="tv-debug-panel">
    <div class="tv-debug-header">
        <span>TV Debug</span>
        <span id="tvDebugCount">0 logs</span>
    </div>
    <div id="tvDebugLines" class="tv-debug-lines"></div>
</div>
@endsection

@push('scripts')
<script>
    var audioPlayer      = document.getElementById('ttsAudio');
    var lastAnnouncedId  = null;
    var fetchErrCount    = 0;
    var isPageVisible    = true;
    var fetchStateInterval = null;
    var pendingAnnouncementText = '';
    var currentAudioObjectUrl = null;
    var playGuardTimer = null;
    var queuedAnnouncementCall = null;
    var queuedAnnouncementId = null;
    var ttsAudioContext = null;
    var currentAudioSource = null;
    var debugEnabled = window.location.search.indexOf('debug=1') !== -1 || window.location.hash.indexOf('debug') !== -1;
    var debugLines = [];

    var TTS_VOLUME    = {{ config('tv.tts_volume', 1.0) }};
    var SILENT_AUDIO  = 'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA';
    var isSamsungTv   = /Tizen|SMART-TV|SamsungBrowser|Maple/i.test(navigator.userAgent);
    var isLgTv        = /Web0S|WebOS|webOS|LG Browser|NetCast/i.test(navigator.userAgent);

    function onDomReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    onDomReady(function () {
        initDebugPanel();
        tvDebug('init', {
            userAgent: navigator.userAgent,
            isLgTv: isLgTv,
            isSamsungTv: isSamsungTv,
            hasAudio: !!audioPlayer,
            volume: TTS_VOLUME
        });

        updateClock();
        setInterval(updateClock, 1000);

        fetchState();
        fetchStateInterval = setInterval(fetchState, 5000);

        audioPlayer.addEventListener('playing', function () {
            tvDebug('tts audio event playing', mediaState(audioPlayer));
            clearPlayGuard();
            pendingAnnouncementText = '';
        });

        audioPlayer.addEventListener('ended', function () {
            tvDebug('tts audio event ended', mediaState(audioPlayer));
            clearPlayGuard();
            revokeAudioObjectUrl();
            endTtsPlayback();
        });

        audioPlayer.addEventListener('error', function () {
            tvDebug('tts audio event error', mediaState(audioPlayer));
            clearPlayGuard();
            revokeAudioObjectUrl();

            if (pendingAnnouncementText !== '') {
                var fallbackText = pendingAnnouncementText;
                pendingAnnouncementText = '';
                speakWithBrowserTts(fallbackText);
                return;
            }

            endTtsPlayback();
        });

        document.addEventListener('click', activateSound);
        document.addEventListener('keydown', activateSound);

        document.addEventListener('visibilitychange', function () {
            isPageVisible = !document.hidden;
            tvDebug('visibility changed', { isPageVisible: isPageVisible });

            if (isPageVisible) {
                resumeOperations();
            } else {
                pauseOperations();
            }
        });
    });

    function initDebugPanel() {
        if (!debugEnabled) { return; }

        var panel = document.getElementById('tvDebugPanel');
        if (panel) {
            panel.classList.add('is-visible');
        }
    }

    function escapeDebugHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function shortUrl(value) {
        if (!value) { return ''; }

        return value.length > 90 ? value.slice(0, 90) + '...' : value;
    }

    function debugError(error) {
        if (!error) { return null; }

        return {
            name: error.name || '',
            message: error.message || String(error),
            code: error.code || ''
        };
    }

    function mediaState(element) {
        if (!element) { return null; }

        return {
            paused: element.paused,
            ended: element.ended,
            muted: element.muted,
            volume: element.volume,
            readyState: element.readyState,
            networkState: element.networkState,
            currentTime: Number(element.currentTime || 0).toFixed(2),
            src: shortUrl(element.currentSrc || element.src || ''),
            error: element.error ? {
                code: element.error.code,
                message: element.error.message || ''
            } : null
        };
    }

    function tvDebug(message, context) {
        var detail = '';
        if (context) {
            try {
                detail = ' ' + JSON.stringify(context);
            } catch (error) {
                detail = ' [context unavailable]';
            }
        }

        var line = new Date().toLocaleTimeString('id-ID', { hour12: false }) + ' ' + message + detail;
        if (window.console && window.console.log) {
            window.console.log('[TV]', message, context || '');
        }

        if (!debugEnabled) { return; }

        debugLines.unshift(line);
        debugLines = debugLines.slice(0, 24);

        var linesContainer = document.getElementById('tvDebugLines');
        var countElement = document.getElementById('tvDebugCount');
        if (countElement) {
            countElement.textContent = debugLines.length + ' logs';
        }
        if (!linesContainer) { return; }

        linesContainer.innerHTML = debugLines
            .map(function (debugLine) {
                return '<div class="tv-debug-line">' + escapeDebugHtml(debugLine) + '</div>';
            })
            .join('');
    }

    function pauseOperations() {
        tvDebug('pause operations');

        var marquee = document.querySelector('.marquee-text');
        if (marquee) {
            marquee.style.animationPlayState = 'paused';
        }

        clearInterval(fetchStateInterval);
        fetchStateInterval = null;
    }

    var soundActivated = false;

    function activateSound() {
        if (soundActivated) {
            tvDebug('activate sound skipped: already active');
            return;
        }
        soundActivated = true;
        tvDebug('activate sound start', {
            isLgTv: isLgTv,
            isSamsungTv: isSamsungTv,
            audio: mediaState(audioPlayer)
        });

        document.removeEventListener('click', activateSound);
        document.removeEventListener('keydown', activateSound);

        unlockWebAudioContext();

        audioPlayer.pause();
        audioPlayer.src = SILENT_AUDIO;
        audioPlayer.volume = isLgTv ? 0 : TTS_VOLUME;
        if (!isLgTv) {
            audioPlayer.load();
        }

        var finishAudioUnlock = function () {
            clearTtsAudioSource();
            audioPlayer.volume = TTS_VOLUME;
            tvDebug('activate audio unlock finished', {
                audio: mediaState(audioPlayer)
            });

            playQueuedAnnouncement();
        };

        var unlockPromise = audioPlayer.play();
        if (unlockPromise && typeof unlockPromise.catch === 'function') {
            unlockPromise
                .then(function () {
                    tvDebug('silent unlock play resolved', mediaState(audioPlayer));
                    finishAudioUnlock();
                })
                .catch(function (error) {
                    tvDebug('silent unlock play failed', debugError(error));
                    finishAudioUnlock();
                });
        } else {
            finishAudioUnlock();
        }

        var overlay = document.getElementById('soundOverlay');
        if (overlay) {
            overlay.classList.add('fade-out');
            setTimeout(function () {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 400);
        }
    }

    function playAttentionChime() {
        var context = getTtsAudioContext();
        if (!context) {
            return Promise.resolve();
        }

        if (context.state === 'suspended' && typeof context.resume === 'function') {
            context.resume();
        }

        return new Promise(function (resolve) {
            try {
                var now = context.currentTime;
                // Elegant two-tone airport/waiting hall chime: C5 (523.25 Hz) -> G5 (783.99 Hz)
                var notes = [
                    { freq: 523.25, time: 0, duration: 0.35 },
                    { freq: 783.99, time: 0.28, duration: 0.55 }
                ];

                for (var i = 0; i < notes.length; i++) {
                    var note = notes[i];
                    var osc = context.createOscillator();
                    var gain = context.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(note.freq, now + note.time);

                    gain.gain.setValueAtTime(0.12 * TTS_VOLUME, now + note.time);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + note.time + note.duration);

                    osc.connect(gain);
                    gain.connect(context.destination);

                    osc.start(now + note.time);
                    osc.stop(now + note.time + note.duration);
                }

                setTimeout(resolve, 650);
            } catch (e) {
                resolve();
            }
        });
    }

    function beginTtsPlayback() {
        tvDebug('begin tts playback', {
            audio: mediaState(audioPlayer)
        });

        var equalizer = document.getElementById('heroEqualizer');
        if (equalizer) {
            equalizer.classList.remove('d-none');
        }
    }

    function endTtsPlayback() {
        tvDebug('end tts playback', {
            audio: mediaState(audioPlayer)
        });

        var equalizer = document.getElementById('heroEqualizer');
        if (equalizer) {
            equalizer.classList.add('d-none');
        }
    }

    function clearPlayGuard() {
        if (playGuardTimer) {
            clearTimeout(playGuardTimer);
            playGuardTimer = null;
        }
    }

    function revokeAudioObjectUrl() {
        if (currentAudioObjectUrl) {
            URL.revokeObjectURL(currentAudioObjectUrl);
            currentAudioObjectUrl = null;
        }
    }

    function clearTtsAudioSource() {
        if (!audioPlayer) { return; }

        audioPlayer.pause();
        audioPlayer.removeAttribute('src');
        if (!isLgTv) {
            audioPlayer.load();
        }
    }

    function getTtsAudioContext() {
        var AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) { return null; }

        if (!ttsAudioContext) {
            ttsAudioContext = new AudioContextClass();
        }

        return ttsAudioContext;
    }

    function unlockWebAudioContext() {
        var context = getTtsAudioContext();
        if (!context) {
            tvDebug('web audio unavailable');

            return;
        }

        if (context.state === 'suspended' && typeof context.resume === 'function') {
            context.resume()
                .then(function () {
                    tvDebug('web audio context resumed', { state: context.state });
                })
                .catch(function (error) {
                    tvDebug('web audio context resume failed', debugError(error));
                });
        }

        try {
            var buffer = context.createBuffer(1, 1, 22050);
            var source = context.createBufferSource();
            source.buffer = buffer;
            source.connect(context.destination);
            source.start(0);
            tvDebug('web audio context unlocked', { state: context.state });
        } catch (error) {
            tvDebug('web audio unlock failed', debugError(error));
        }
    }

    function decodeTtsAudio(context, audioBuffer) {
        return new Promise(function (resolve, reject) {
            try {
                var decodePromise = context.decodeAudioData(audioBuffer, resolve, reject);
                if (decodePromise && typeof decodePromise.then === 'function') {
                    decodePromise.then(resolve).catch(reject);
                }
            } catch (error) {
                reject(error);
            }
        });
    }

    function stopCurrentTtsSource() {
        if (!currentAudioSource) { return; }

        try {
            currentAudioSource.stop(0);
        } catch (error) {
            // Source may already be stopped.
        }

        try {
            currentAudioSource.disconnect();
        } catch (error) {
            // Source may already be disconnected.
        }

        currentAudioSource = null;
    }

    function resumeOperations() {
        tvDebug('resume operations');

        var marquee = document.querySelector('.marquee-text');
        if (marquee) {
            marquee.style.animationPlayState = 'running';
        }

        fetchState();

        if (!fetchStateInterval) {
            fetchStateInterval = setInterval(fetchState, 5000);
        }
    }

    function updateClock() {
        var now = new Date();
        var clockEl = document.getElementById('clockDisplay');
        var dateEl = document.getElementById('dateDisplay');
        if (clockEl) { clockEl.textContent = now.toLocaleTimeString('id-ID', { hour12: false }); }
        if (dateEl) { dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }

        // Time-aware greeting for the empty state
        var hour = now.getHours();
        var greeting = 'Selamat Pagi';
        var iconSvg = '<svg style="width:38px;height:38px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
        
        if (hour >= 11 && hour < 15) {
            greeting = 'Selamat Siang';
            iconSvg = '<svg style="width:38px;height:38px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
        } else if (hour >= 15 && hour < 18) {
            greeting = 'Selamat Sore';
            iconSvg = '<svg style="width:38px;height:38px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>';
        } else if (hour >= 18 || hour < 4) {
            greeting = 'Selamat Malam';
            iconSvg = '<svg style="width:38px;height:38px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>';
        }

        var greetingIcon = document.getElementById('greetingIcon');
        var greetingTitle = document.getElementById('greetingTitle');
        var greetingSubtitle = document.getElementById('greetingSubtitle');
        
        if (greetingIcon && greetingIcon.innerHTML !== iconSvg) {
            greetingIcon.innerHTML = iconSvg;
        }
        if (greetingTitle && greetingTitle.textContent !== greeting) {
            greetingTitle.textContent = greeting;
        }
        if (greetingSubtitle && greetingSubtitle.textContent !== 'Sistem Antrian PTSP Siap Melayani Anda') {
            greetingSubtitle.textContent = 'Sistem Antrian PTSP Siap Melayani Anda';
        }
    }

    function fetchState() {
        var controller = null;
        var timeoutId = null;
        if (typeof AbortController !== 'undefined') {
            controller = new AbortController();
            timeoutId = setTimeout(function () { try { controller.abort(); } catch (e) {} }, 5000);
        }
        fetch('{{ route("tv-display.legacy.api") }}', {
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': window.TV_CSRF_TOKEN || '', 'Accept': 'application/json' },
            signal: controller ? controller.signal : undefined
        }).then(function (res) {
            if (timeoutId) { clearTimeout(timeoutId); }
            if (!res.ok) { throw new Error('HTTP_' + res.status); }
            return res.json();
        }).then(function (response) {
            fetchErrCount = 0;
            if (response.success) { updateUI(response.data); }
        }).catch(function () {
            if (timeoutId) { clearTimeout(timeoutId); }
            fetchErrCount++;
            tvDebug('state fetch failed', { fetchErrCount: fetchErrCount });
            if (fetchErrCount >= 5) {
                console.warn('TV Display: Koneksi ke server bermasalah (' + fetchErrCount + 'x gagal).');
            }
        });
    }

    /* Helper: Semantic Color Palette by Ticket Prefix or Service */
    function getCategoryTheme(ticketNumber, serviceName) {
        var char = (ticketNumber || 'A').toUpperCase().charAt(0);
        var sName = serviceName || '';

        if (char === 'A' || /daftar|mohon/i.test(sName)) {
            return {
                themeClass: 'hero-theme-blue',
                avatarBg: '#e0f2fe',
                avatarBorder: '#bae6fd',
                avatarColor: '#0284c7',
                borderLeft: '#0284c7',
                counterBadgeBg: '#0284c7',
                purposeColor: '#0284c7'
            };
        } else if (char === 'B' || /info|aduan/i.test(sName)) {
            return {
                themeClass: 'hero-theme-teal',
                avatarBg: '#ccfbf1',
                avatarBorder: '#99f6e4',
                avatarColor: '#0d9488',
                borderLeft: '#0d9488',
                counterBadgeBg: '#0d9488',
                purposeColor: '#0f766e'
            };
        } else if (char === 'C' || /produk|hukum|akta|salinan/i.test(sName)) {
            return {
                themeClass: 'hero-theme-amber',
                avatarBg: '#fef3c7',
                avatarBorder: '#fde68a',
                avatarColor: '#d97706',
                borderLeft: '#d97706',
                counterBadgeBg: '#d97706',
                purposeColor: '#b45309'
            };
        } else if (char === 'D' || char === 'E' || /ecourt|court|posbakum/i.test(sName)) {
            return {
                themeClass: 'hero-theme-emerald',
                avatarBg: '#d1fae5',
                avatarBorder: '#a7f3d0',
                avatarColor: '#059669',
                borderLeft: '#059669',
                counterBadgeBg: '#059669',
                purposeColor: '#047857'
            };
        } else if (char === 'P' || char === 'U' || char === 'K' || /prioritas|khusus|difabel/i.test(sName)) {
            return {
                themeClass: 'hero-theme-purple',
                avatarBg: '#ede9fe',
                avatarBorder: '#ddd6fe',
                avatarColor: '#7c3aed',
                borderLeft: '#7c3aed',
                counterBadgeBg: '#7c3aed',
                purposeColor: '#6d28d9'
            };
        } else {
            return {
                themeClass: 'hero-theme-blue',
                avatarBg: '#e0f2fe',
                avatarBorder: '#bae6fd',
                avatarColor: '#0284c7',
                borderLeft: '#0284c7',
                counterBadgeBg: '#0284c7',
                purposeColor: '#0284c7'
            };
        }
    }

    function el(id) { return document.getElementById(id); }

    function updateUI(data) {
        if (data.currentCalls && data.currentCalls.length > 0) {
            var active = data.currentCalls[0];
            var serviceName = active.service ? active.service.name : '';
            var theme = getCategoryTheme(active.ticket_number, serviceName);

            var noCallEl = el('noCallState');
            var activeCallEl = el('activeCallState');
            if (noCallEl) { noCallEl.classList.add('d-none'); }
            if (activeCallEl) { activeCallEl.classList.remove('d-none'); }

            var heroCard = el('heroCard');
            if (heroCard) {
                heroCard.className = heroCard.className.replace(/hero-theme-\w+/g, '').trim() + ' ' + theme.themeClass;
                heroCard.classList.add('hero-pulse-anim', 'hero-call-glow');
            }

            var activeTicketNumber = el('activeTicketNumber');
            if (activeTicketNumber) {
                activeTicketNumber.textContent = active.ticket_number;
                activeTicketNumber.classList.remove('call-animate');
                void activeTicketNumber.offsetWidth;
                activeTicketNumber.classList.add('call-animate');
            }
            var counterEl = el('activeCounterName');
            if (counterEl) { counterEl.textContent = active.counter ? active.counter.name.toUpperCase() : 'LOKET'; }
            var serviceEl = el('activeServiceName');
            if (serviceEl) { serviceEl.textContent = serviceName; }

            var visitPurposeEl = el('activeVisitPurpose');
            if (visitPurposeEl) {
                if (active.visit_purpose) {
                    visitPurposeEl.textContent = formatVisitPurpose(active.visit_purpose);
                    visitPurposeEl.classList.remove('d-none');
                } else {
                    visitPurposeEl.classList.add('d-none');
                }
            }

            var callId = active.id + '-' + active.called_at;
            if (lastAnnouncedId !== callId) {
                playAnnouncerWhenReady(active, callId);
            }
        } else {
            var noCallEl2 = el('noCallState');
            var activeCallEl2 = el('activeCallState');
            if (noCallEl2) { noCallEl2.classList.remove('d-none'); }
            if (activeCallEl2) { activeCallEl2.classList.add('d-none'); }
            var heroCardIdle = el('heroCard');
            if (heroCardIdle) {
                heroCardIdle.classList.remove('hero-pulse-anim', 'hero-call-glow', 'hero-theme-teal', 'hero-theme-amber', 'hero-theme-emerald', 'hero-theme-purple');
                if (!heroCardIdle.classList.contains('hero-theme-blue')) { heroCardIdle.classList.add('hero-theme-blue'); }
            }
            var equalizer = el('heroEqualizer');
            if (equalizer) { equalizer.classList.add('d-none'); }
        }

        /* Update daftar panggilan terakhir dengan diff-based approach */
        if (data.recentCalls && data.recentCalls.length > 0) {
            var skip = (data.currentCalls && data.currentCalls.length > 0 &&
                        data.recentCalls[0].id === data.currentCalls[0].id) ? 1 : 0;
            var callsToShow = [];

            for (var i = skip; i < data.recentCalls.length && i < skip + 5; i++) {
                callsToShow.push(data.recentCalls[i]);
            }

            updateRecentCallsDOM(callsToShow);
        } else {
            updateRecentCallsDOM([]);
        }
    }

    function formatVisitPurpose(purpose) {
        var map = {
            'pendaftaran': 'Pendaftaran',
            'informasi_pengaduan': 'Informasi & Pengaduan',
            'produk_hukum': 'Pengambilan Produk Hukum',
            'ecourt': 'eCourt'
        };
        return map[purpose] || purpose;
    }

    function renderRecentCallItem(call, opacity, index) {
        var serviceName = escapeDebugHtml(call.service ? call.service.name : '');
        var counterName = escapeDebugHtml(call.counter ? call.counter.name : '-');
        var initial     = escapeDebugHtml(call.ticket_number.charAt(0));
        var ticketNumber = escapeDebugHtml(call.ticket_number);
        var visitPurpose = escapeDebugHtml(call.visit_purpose ? formatVisitPurpose(call.visit_purpose) : '');
        var theme       = getCategoryTheme(call.ticket_number, call.service ? call.service.name : '');

        var purposeHtml = visitPurpose
            ? '<div class="visit-purpose fw-semibold fs-6 text-uppercase mt-0.5" style="color:' + theme.purposeColor + ';letter-spacing:0.2px;">' + visitPurpose + '</div>'
            : '';

        var justCalledBadge = (index === 0)
            ? '<span class="badge bg-light-success text-success border border-success-subtle fw-boldest fs-8 px-2.5 py-1 rounded-pill text-uppercase d-inline-flex align-items-center gap-1.5 ms-2" style="letter-spacing:0.5px;">' +
              '<span class="live-dot" style="width:6px;height:6px;background-color:#10b981;box-shadow:0 0 6px #10b981;"></span>Baru Saja</span>'
            : '';

        return '<div class="recent-call-item" id="call-' + call.id + '" style="opacity:' + opacity + ';">' +
            '<div class="d-flex align-items-center gap-3.5 min-w-0">' +
                '<div class="rounded-circle flex-shrink-0"' +
                     ' style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;background:' + theme.avatarBg + ';border:1.5px solid ' + theme.avatarBorder + ';">' +
                    '<span class="fw-boldest fs-3" style="color:' + theme.avatarColor + ';line-height:1;display:flex;align-items:center;justify-content:center;text-align:center;">' + initial + '</span>' +
                '</div>' +
                '<div class="overflow-hidden text-truncate">' +
                    '<div class="d-flex align-items-center">' +
                        '<span class="ticket-number text-dark fw-boldest fs-2hx ls-n1" style="line-height:1.1;">' +
                            ticketNumber +
                        '</span>' +
                        justCalledBadge +
                    '</div>' +
                    '<div class="service-name fw-bold fs-5 text-uppercase text-truncate" style="color:#334155;letter-spacing:-0.2px;">' +
                        serviceName +
                    '</div>' +
                    purposeHtml +
                '</div>' +
            '</div>' +
            '<div class="counter-name recent-counter-badge text-uppercase fs-5" style="background-color:' + theme.counterBadgeBg + ';">' +
                counterName +
            '</div>' +
        '</div>';
    }

    function renderEmptyState() {
        return '<div class="recent-call-item justify-content-center empty-state py-12">' +
               '<span class="fw-semibold fs-3 text-muted">Belum ada panggilan antrian</span>' +
               '</div>';
    }

    function updateRecentCallsDOM(newCalls) {
        var container = document.getElementById('recentCallsContainer');
        var existingItems = container.children;

        if (newCalls.length === 0) {
            if (existingItems.length !== 1 || !existingItems[0].classList.contains('empty-state')) {
                container.innerHTML = renderEmptyState();
            }
            return;
        }

        newCalls.forEach(function (call, index) {
            var opacity = Math.max(0.4, 1 - (index * 0.15));
            var expectedId = 'call-' + call.id;
            var existingItem = existingItems[index];

            if (existingItem && existingItem.id === expectedId) {
                var currentTicket = existingItem.querySelector('.ticket-number');
                var currentService = existingItem.querySelector('.service-name');
                var currentCounter = existingItem.querySelector('.counter-name');
                var currentPurpose = existingItem.querySelector('.visit-purpose');

                var serviceName = call.service ? call.service.name : '';
                var counterName = call.counter ? call.counter.name : '-';
                var visitPurpose = call.visit_purpose ? formatVisitPurpose(call.visit_purpose) : '';
                var currentPurposeText = currentPurpose ? currentPurpose.textContent.trim() : '';

                var hasChanged = (
                    (currentTicket && currentTicket.textContent.trim() !== call.ticket_number) ||
                    (currentService && currentService.textContent.trim() !== serviceName) ||
                    (currentCounter && currentCounter.textContent.trim() !== counterName) ||
                    (currentPurposeText !== visitPurpose)
                );

                if (hasChanged) {
                    var newHtml = renderRecentCallItem(call, opacity, index);
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newHtml;

                    var newElement = tempDiv.firstElementChild;
                    newElement.id = expectedId;
                    container.replaceChild(newElement, existingItem);
                }
            } else {
                var html = renderRecentCallItem(call, opacity, index);
                var wrapper = document.createElement('div');
                wrapper.innerHTML = html;

                var element = wrapper.firstElementChild;
                element.id = expectedId;

                if (existingItem) {
                    container.replaceChild(element, existingItem);
                } else {
                    container.appendChild(element);
                }
            }
        });

        while (existingItems.length > newCalls.length) {
            container.removeChild(existingItems[existingItems.length - 1]);
        }
    }

    function speakWithBrowserTts(text) {
        clearPlayGuard();
        revokeAudioObjectUrl();
        tvDebug('browser tts start', {
            hasSpeechSynthesis: 'speechSynthesis' in window,
            text: text
        });
        beginTtsPlayback();

        if (!('speechSynthesis' in window)) {
            tvDebug('browser tts unavailable');
            endTtsPlayback();
            return;
        }

        window.speechSynthesis.cancel();

        var utterance = new SpeechSynthesisUtterance(text.replace(/,/g, ' '));
        utterance.lang = 'id-ID';
        utterance.rate = 0.95;
        utterance.pitch = 1;
        utterance.volume = TTS_VOLUME;
        utterance.onend = function () {
            tvDebug('browser tts ended');
            endTtsPlayback();
        };
        utterance.onerror = function (event) {
            tvDebug('browser tts error', {
                error: event.error || ''
            });
            endTtsPlayback();
        };

        window.speechSynthesis.speak(utterance);
    }

    function playAnnouncerWhenReady(call, callId) {
        if (!soundActivated) {
            var isNewQueuedAnnouncement = queuedAnnouncementId !== callId;
            queuedAnnouncementCall = call;
            queuedAnnouncementId = callId;
            if (isNewQueuedAnnouncement) {
                tvDebug('announcement deferred until sound activation', {
                    callId: callId,
                    ticketNumber: call.ticket_number,
                    counter: call.counter ? call.counter.name : ''
                });
            }

            return;
        }

        lastAnnouncedId = callId;
        queuedAnnouncementCall = null;
        queuedAnnouncementId = null;
        tvDebug('new active call', {
            callId: callId,
            ticketNumber: call.ticket_number,
            counter: call.counter ? call.counter.name : ''
        });
        playAnnouncer(call);
    }

    function playQueuedAnnouncement() {
        if (!soundActivated || !queuedAnnouncementCall || !queuedAnnouncementId) {
            return;
        }

        if (lastAnnouncedId === queuedAnnouncementId) {
            queuedAnnouncementCall = null;
            queuedAnnouncementId = null;

            return;
        }

        var call = queuedAnnouncementCall;
        var callId = queuedAnnouncementId;
        queuedAnnouncementCall = null;
        queuedAnnouncementId = null;
        lastAnnouncedId = callId;

        tvDebug('announcement replay after sound activation', {
            callId: callId,
            ticketNumber: call.ticket_number,
            counter: call.counter ? call.counter.name : ''
        });
        playAnnouncer(call);
    }

    function playTtsAudioSource(sourceUrl, fallbackText, sourceType) {
        audioPlayer.src = sourceUrl;
        tvDebug('tts audio source prepared', {
            sourceType: sourceType,
            audio: mediaState(audioPlayer)
        });

        playGuardTimer = setTimeout(function () {
            if (pendingAnnouncementText !== '') {
                tvDebug('tts play guard fallback', { text: pendingAnnouncementText });
                var guardText = pendingAnnouncementText;
                pendingAnnouncementText = '';
                clearTtsAudioSource();
                speakWithBrowserTts(guardText);
            }
        }, 7000);

        audioPlayer.volume = TTS_VOLUME;
        beginTtsPlayback();
        var playPromise = audioPlayer.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise
                .then(function () {
                    tvDebug('tts audio play resolved', mediaState(audioPlayer));
                })
                .catch(function (error) {
                    tvDebug('tts audio play failed', debugError(error));
                    clearTtsAudioSource();
                    speakWithBrowserTts(fallbackText);
                });
        }
    }

    function playTtsWithWebAudio(audioUrl, fallbackText) {
        var context = getTtsAudioContext();
        if (!context || typeof window.fetch !== 'function') {
            return false;
        }

        tvDebug('tts web audio fetch start', { audioUrl: shortUrl(audioUrl) });
        fetch(audioUrl, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store'
        })
            .then(function (response) {
                tvDebug('tts web audio fetch response', {
                    status: response.status,
                    ok: response.ok,
                    contentType: response.headers ? response.headers.get('content-type') : ''
                });

                if (!response.ok) {
                    throw new Error('AUDIO_HTTP_' + response.status);
                }

                return response.arrayBuffer();
            })
            .then(function (audioBuffer) {
                if (!audioBuffer || audioBuffer.byteLength <= 0) {
                    throw new Error('AUDIO_EMPTY_BUFFER');
                }

                return decodeTtsAudio(context, audioBuffer);
            })
            .then(function (decodedBuffer) {
                if (context.state === 'suspended' && typeof context.resume === 'function') {
                    return context.resume().then(function () {
                        return decodedBuffer;
                    });
                }

                return decodedBuffer;
            })
            .then(function (decodedBuffer) {
                stopCurrentTtsSource();

                var source = context.createBufferSource();
                var gainNode = context.createGain();
                source.buffer = decodedBuffer;
                gainNode.gain.value = TTS_VOLUME;
                source.connect(gainNode);
                gainNode.connect(context.destination);

                currentAudioSource = source;
                beginTtsPlayback();
                pendingAnnouncementText = '';

                source.onended = function () {
                    if (currentAudioSource === source) {
                        currentAudioSource = null;
                    }

                    clearPlayGuard();
                    endTtsPlayback();
                    tvDebug('tts web audio ended', { duration: decodedBuffer.duration });
                };

                playGuardTimer = setTimeout(function () {
                    if (currentAudioSource === source) {
                        tvDebug('tts web audio guard restore', { duration: decodedBuffer.duration });
                        stopCurrentTtsSource();
                        clearPlayGuard();
                        endTtsPlayback();
                    }
                }, Math.max(7000, Math.ceil(decodedBuffer.duration * 1000) + 1500));

                source.start(0);
                tvDebug('tts web audio started', {
                    duration: decodedBuffer.duration,
                    contextState: context.state
                });
            })
            .catch(function (error) {
                tvDebug('tts web audio failed, fallback direct audio', debugError(error));
                clearPlayGuard();
                stopCurrentTtsSource();
                endTtsPlayback();
                playTtsAudioSource(audioUrl, fallbackText, 'direct-fallback');
            });

        return true;
    }

    function playMiniMaxAudio(audioUrl, fallbackText) {
        clearPlayGuard();
        revokeAudioObjectUrl();
        stopCurrentTtsSource();

        if (playTtsWithWebAudio(audioUrl, fallbackText)) {
            return;
        }

        tvDebug('tts direct audio selected', {
            audioUrl: shortUrl(audioUrl),
            isLgTv: isLgTv,
            isSamsungTv: isSamsungTv
        });
        playTtsAudioSource(audioUrl, fallbackText, 'direct');
    }

    function playAnnouncer(call) {
        var loket    = call.counter ? call.counter.name : 'Loket';
        var ttsNomor = call.ticket_number.replace(/^([A-Za-z]+)0+(.*)$/, '$1$2');
        var text = 'Nomor antrian ' + ttsNomor + ', silakan menuju ' + loket + '.';

        pendingAnnouncementText = text;
        tvDebug('announcer start', {
            ticketNumber: call.ticket_number,
            text: text,
            soundActivated: soundActivated
        });

        playAttentionChime().then(function () {
            var ttsController = null;
            var ttsTimeout = null;
            if (typeof AbortController !== 'undefined') {
                ttsController = new AbortController();
                ttsTimeout = setTimeout(function () { try { ttsController.abort(); } catch (e) {} }, 10000);
            }
            fetch('{{ route("tv-display.tts.announcement") }}?text=' + encodeURIComponent(text), {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.TV_CSRF_TOKEN || '' },
                signal: ttsController ? ttsController.signal : undefined
            }).then(function (res) {
                if (ttsTimeout) { clearTimeout(ttsTimeout); }
                if (!res.ok) { throw new Error('TTS_' + res.status); }
                return res.json();
            }).then(function (response) {
                tvDebug('announcer endpoint success', {
                    provider: response && response.provider ? response.provider : '',
                    audioUrl: response && response.audio_url ? shortUrl(response.audio_url) : ''
                });
                if (response && response.audio_url) {
                    playMiniMaxAudio(response.audio_url, text);
                    return;
                }
                pendingAnnouncementText = '';
                speakWithBrowserTts(text);
            }).catch(function (err) {
                if (ttsTimeout) { clearTimeout(ttsTimeout); }
                tvDebug('announcer endpoint failed', { error: err ? err.message : '' });
                pendingAnnouncementText = '';
                speakWithBrowserTts(text);
            });
        });
    }
</script>
@endpush
