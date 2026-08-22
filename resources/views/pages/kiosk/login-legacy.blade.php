@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════
       ANDROID 5 DELIGHT & MOTION (PIN TOUCH-OPTIMIZED KIOSK LOGIN)
       - Pure transform3d & opacity transitions
       - Zero blur filters or CPU-heavy continuous loops
       - Tactile touch feedback with PIN indicator & card entrance
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

    input {
        -webkit-user-select: auto;
        user-select: auto;
    }

    .login-root {
        min-height: 100vh;
        background-color: #0f172a;
        background-image: radial-gradient(circle at 50% 0%, #1e293b 0%, #0f172a 100%);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px 16px;
    }

    /* Top Utility Bar */
    .login-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        max-width: 560px;
        margin: 0 auto 12px;
        padding: 0 4px;
    }

    .login-system-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #1e293b;
        color: #94a3b8;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 6px 14px;
        border-radius: 999px;
        border: 1px solid #334155;
    }

    .login-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #34d399;
    }

    .login-clock {
        color: #38bdf8;
        font-weight: 800;
        font-size: 1.1rem;
        font-variant-numeric: tabular-nums;
    }

    /* Main Login Card */
    .login-card {
        border-radius: 28px !important;
        border: 2px solid #334155 !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45) !important;
        background: #ffffff !important;
        width: 100%;
        max-width: 560px;
        margin: auto;
        transform: translate3d(0, 0, 0);
        animation: cardEntrance 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes cardEntrance {
        0% {
            opacity: 0;
            transform: translate3d(0, 20px, 0);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    /* Error Shake Animation */
    .card-shake-error {
        animation: shakeError 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both !important;
    }

    @keyframes shakeError {
        0%, 100% { transform: translate3d(0, 0, 0); }
        15%, 45%, 75% { transform: translate3d(-8px, 0, 0); }
        30%, 60%, 90% { transform: translate3d(8px, 0, 0); }
    }

    /* Lock Emblem with Interactive State */
    .lock-emblem {
        width: 76px;
        height: 76px;
        background: #0284c7;
        color: #ffffff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 24px rgba(2, 132, 199, 0.35);
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.2s;
    }

    .lock-emblem.emblem-active {
        transform: scale(1.06);
        background: #0369a1;
    }

    .lock-emblem.emblem-unlocking {
        transform: scale(1.1) rotate(-8deg);
        background: #059669;
        box-shadow: 0 10px 24px rgba(5, 150, 105, 0.4);
    }

    /* Input Field & Pulse */
    .kiosk-input {
        background-color: #f8fafc !important;
        border: 2px solid #cbd5e1 !important;
        border-radius: 16px !important;
        font-size: 1.35rem !important;
        padding: 14px 18px !important;
        height: auto !important;
        font-weight: 700;
        color: #0f172a !important;
        letter-spacing: 2px;
        transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
    }

    .kiosk-input:focus {
        border-color: #0284c7 !important;
        background-color: #f0f9ff !important;
        box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.18) !important;
        outline: none;
    }

    .kiosk-input.key-pulse {
        border-color: #0284c7 !important;
        background-color: #e0f2fe !important;
    }

    /* PIN Counter Dots */
    .pin-dots-bar {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-bottom: 12px;
        min-height: 14px;
    }

    .pin-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #cbd5e1;
        transition: transform 0.15s ease, background-color 0.15s ease;
    }

    .pin-dot.filled {
        background: #0284c7;
        transform: scale(1.2);
    }

    /* PIN Input Wrapper */
    .password-input-group {
        position: relative;
    }

    .password-toggle-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: #e2e8f0;
        border: none;
        border-radius: 12px;
        padding: 10px 14px;
        color: #475569;
        font-size: 1.1rem;
        cursor: pointer;
        z-index: 10;
        transition: background-color 0.15s;
    }

    .password-toggle-btn:active {
        background: #cbd5e1;
    }

    /* Tactile Virtual PIN Keypad */
    .login-numpad-container {
        background: #f1f5f9;
        border: 2px solid #cbd5e1;
        border-radius: 20px;
        padding: 16px;
        margin-top: 16px;
        margin-bottom: 24px;
    }

    .login-numpad-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .login-numpad-btn {
        background: #ffffff;
        border: 2px solid #cbd5e1;
        border-radius: 14px;
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        transition: transform 0.1s ease, background-color 0.1s ease;
    }

    .login-numpad-btn:active, .login-numpad-btn.btn-active-touch {
        transform: scale(0.92) !important;
        background-color: #e2e8f0 !important;
    }

    .login-numpad-btn-action {
        font-size: 0.95rem;
        font-weight: 800;
    }

    .login-numpad-btn-clear {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .login-numpad-btn-clear:active {
        background: #fecaca;
    }

    .login-numpad-btn-backspace {
        background: #fef3c7;
        border-color: #fcd34d;
        color: #b45309;
    }
    .login-numpad-btn-backspace:active {
        background: #fde68a;
    }

    .btn-kiosk-submit {
        min-height: 60px;
        font-size: 1.3rem !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        padding: 14px 28px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
        transition: transform 0.15s ease, background-color 0.15s ease;
    }

    .btn-kiosk-submit:active {
        transform: scale(0.96);
    }

    /* Prefers Reduced Motion Fallback */
    @media (prefers-reduced-motion: reduce) {
        .login-card, .card-shake-error, .lock-emblem, .login-numpad-btn, .kiosk-input {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="login-root">

    {{-- Top Bar with System Status & Live Clock --}}
    <div class="login-top-bar">
        <div class="login-system-pill">
            <span class="login-pulse-dot"></span>
            <span>Terminal PTSP</span>
        </div>
        <div class="login-clock" id="loginClock">00:00:00</div>
    </div>

    {{-- Main Login Card --}}
    <div class="login-card card {{ $errors->has('password') ? 'card-shake-error' : '' }}">
        <div class="card-body p-8 p-lg-12">

            {{-- Brand Header --}}
            <div class="text-center mb-6">
                <div class="lock-emblem mb-4" id="lockEmblem">
                    <i class="fa-solid fa-lock fs-1" id="lockIcon"></i>
                </div>

                @if(config('institution.logo_path'))
                    <img alt="Logo" src="{{ Storage::url(config('institution.logo_path')) }}"
                         style="height:44px;object-fit:contain;display:block;margin:0 auto 12px;">
                @else
                    <img alt="Logo" src="{{ asset('metronic-assets/media/logos/logo-papenajam.webp') }}"
                         style="height:44px;object-fit:contain;display:block;margin:0 auto 12px;">
                @endif

                <h1 class="fw-boldest fs-2x text-gray-900 text-uppercase mb-1" style="letter-spacing:-0.5px;">
                    Akses Kiosk Legacy
                </h1>
                <p class="text-gray-500 fs-4 fw-semibold mb-0">
                    {{ config('institution.name') }}
                </p>
            </div>

            {{-- Error Message Banner --}}
            @if($errors->has('password'))
                <div class="rounded-3 fw-bold fs-5 mb-6 p-4 d-flex align-items-center"
                     style="background:#fee2e2;border:2px solid #f87171;color:#991b1b;">
                    <i class="fa-solid fa-triangle-exclamation fs-3 me-3 text-danger"></i>
                    <div>{{ $errors->first('password') }}</div>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('kiosk.legacy.authenticate') }}" id="loginKioskForm">
                @csrf

                {{-- Visual PIN Dots --}}
                <div class="pin-dots-bar" id="pinDotsBar">
                    <span class="pin-dot"></span>
                    <span class="pin-dot"></span>
                    <span class="pin-dot"></span>
                    <span class="pin-dot"></span>
                </div>

                <div class="mb-3">
                    <label class="fs-4 fw-bold text-gray-800 mb-2 d-block text-uppercase"
                           style="letter-spacing:0.5px;">
                        PIN Kiosk
                    </label>
                    <div class="password-input-group">
                        <input type="password"
                               name="password"
                               id="passwordInput"
                               class="form-control kiosk-input pe-15"
                               placeholder="Masukkan PIN Kiosk..."
                               inputmode="numeric"
                               autofocus
                               autocomplete="current-password"
                               oninput="handlePasswordChange()"
                               required>
                        <button type="button"
                                class="password-toggle-btn"
                                onclick="togglePasswordVisibility()"
                                title="Lihat / Sembunyikan PIN">
                            <i class="fa-solid fa-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Built-in On-Screen Keypad --}}
                <div class="login-numpad-container">
                    <div class="text-center fs-7 fw-bold text-gray-600 text-uppercase mb-2">
                        Papan Tombol PIN
                    </div>
                    <div class="login-numpad-grid">
                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('1')">1</button>
                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('2')">2</button>
                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('3')">3</button>

                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('4')">4</button>
                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('5')">5</button>
                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('6')">6</button>

                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('7')">7</button>
                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('8')">8</button>
                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('9')">9</button>

                        <button type="button" class="login-numpad-btn login-numpad-btn-action login-numpad-btn-clear" onclick="loginNumpadClear()">
                            HAPUS
                        </button>
                        <button type="button" class="login-numpad-btn" onclick="loginNumpadPress('0')">0</button>
                        <button type="button" class="login-numpad-btn login-numpad-btn-action login-numpad-btn-backspace" onclick="loginNumpadBackspace()">
                            <i class="fa-solid fa-delete-left"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        id="btnLoginSubmit"
                        class="btn btn-primary btn-kiosk-submit w-100 shadow">
                    <span class="indicator-label d-flex align-items-center gap-2">
                        <i class="fa-solid fa-right-to-bracket" id="submitIcon"></i>
                        Masuk ke Kiosk
                    </span>
                    <span class="indicator-progress d-none">
                        <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                        Membuka Kiosk...
                    </span>
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ url('/') }}" class="text-gray-500 hover-primary fw-semibold fs-6 text-decoration-none">
                    <i class="fa-solid fa-house me-1"></i> Kembali ke Halaman Utama
                </a>
            </div>

        </div>
    </div>

    {{-- Bottom Footer --}}
    <div class="text-center py-2">
        <span class="text-gray-400 fw-semibold fs-7 text-uppercase" style="opacity:0.6; letter-spacing:1px;">
            &copy; {{ date('Y') }} Sistem Antrian PTSP &bull; Mode Legacy Android 5
        </span>
    </div>

</div>
@endsection

@push('scripts')
<script>
    /* ═══════════════════════════════════════════════════════════
       ES5 SAFE JAVASCRIPT CONTROLLER (ANDROID 5 PIN AUTH)
       ═══════════════════════════════════════════════════════════ */

    function updateLoginClock() {
        var now = new Date();
        var h = ('0' + now.getHours()).slice(-2);
        var m = ('0' + now.getMinutes()).slice(-2);
        var s = ('0' + now.getSeconds()).slice(-2);
        var el = document.getElementById('loginClock');
        if (el) {
            el.textContent = h + ':' + m + ':' + s;
        }
    }

    updateLoginClock();
    setInterval(updateLoginClock, 1000);

    function triggerKeyPulse() {
        var input = document.getElementById('passwordInput');
        if (!input) return;

        input.classList.add('key-pulse');
        setTimeout(function () {
            input.classList.remove('key-pulse');
        }, 120);
    }

    function updatePinDots() {
        var input = document.getElementById('passwordInput');
        var dotsContainer = document.getElementById('pinDotsBar');
        var lockEmblem = document.getElementById('lockEmblem');
        if (!input || !dotsContainer) return;

        var len = (input.value || '').length;
        var dots = dotsContainer.querySelectorAll('.pin-dot');

        for (var i = 0; i < dots.length; i++) {
            if (i < len) {
                dots[i].classList.add('filled');
            } else {
                dots[i].classList.remove('filled');
            }
        }

        if (lockEmblem) {
            if (len > 0) {
                lockEmblem.classList.add('emblem-active');
            } else {
                lockEmblem.classList.remove('emblem-active');
            }
        }
    }

    function handlePasswordChange() {
        updatePinDots();
    }

    function togglePasswordVisibility() {
        var input = document.getElementById('passwordInput');
        var icon = document.getElementById('passwordToggleIcon');
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-solid fa-eye';
        }
    }

    function loginNumpadPress(digit) {
        var input = document.getElementById('passwordInput');
        if (!input) return;
        input.value = (input.value || '') + digit;
        triggerKeyPulse();
        updatePinDots();
    }

    function loginNumpadBackspace() {
        var input = document.getElementById('passwordInput');
        if (!input) return;
        var val = input.value || '';
        if (val.length > 0) {
            input.value = val.substring(0, val.length - 1);
        }
        triggerKeyPulse();
        updatePinDots();
    }

    function loginNumpadClear() {
        var input = document.getElementById('passwordInput');
        if (input) {
            input.value = '';
        }
        triggerKeyPulse();
        updatePinDots();
    }

    document.getElementById('loginKioskForm').addEventListener('submit', function () {
        var btn = document.getElementById('btnLoginSubmit');
        var emblem = document.getElementById('lockEmblem');
        var lockIcon = document.getElementById('lockIcon');

        if (emblem) {
            emblem.classList.add('emblem-unlocking');
        }
        if (lockIcon) {
            lockIcon.className = 'fa-solid fa-unlock fs-1';
        }

        if (btn) {
            var label = btn.querySelector('.indicator-label');
            var progress = btn.querySelector('.indicator-progress');
            if (label) label.classList.add('d-none');
            if (progress) progress.classList.remove('d-none');
            btn.disabled = true;
        }
    });
</script>
@endpush
