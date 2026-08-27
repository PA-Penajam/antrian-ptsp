@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════
       ANDROID 5 DELIGHT & MOTION (PIN TV DISPLAY LOGIN - BRIGHT THEME)
       - Pure transform3d & opacity transitions
       - Zero blur filters or CPU-heavy continuous loops
       - Crisp daylight theme with on-screen tactile PIN pad
       ═══════════════════════════════════════════════════════════ */

    html, body {
        background-color: #f1f5f9;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        touch-action: manipulation;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        user-select: none;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    input {
        -webkit-user-select: auto;
        user-select: auto;
    }

    .tv-login-root {
        min-height: 100vh;
        background-color: #f8fafc;
        background-image:
            linear-gradient(135deg, rgba(248, 250, 252, 0.93) 0%, rgba(241, 245, 249, 0.90) 50%, rgba(226, 232, 240, 0.94) 100%),
            url('{{ asset('images/tv-bg-bright.webp') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px 16px;
    }

    /* Top Utility Bar */
    .tv-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        max-width: 560px;
        margin: 0 auto 12px;
        padding: 0 4px;
    }

    .tv-system-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 6px 14px;
        border-radius: 999px;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }

    .tv-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #0284c7;
    }

    .tv-clock {
        color: #0284c7;
        font-weight: 800;
        font-size: 1.1rem;
        font-variant-numeric: tabular-nums;
    }

    /* Main Card */
    .tv-login-card {
        border-radius: 28px !important;
        border: 1.5px solid #e2e8f0 !important;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08) !important;
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

    /* Error Shake */
    .card-shake-error {
        animation: shakeError 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both !important;
    }

    @keyframes shakeError {
        0%, 100% { transform: translate3d(0, 0, 0); }
        15%, 45%, 75% { transform: translate3d(-8px, 0, 0); }
        30%, 60%, 90% { transform: translate3d(8px, 0, 0); }
    }

    /* Display Emblem */
    .display-emblem {
        width: 76px;
        height: 76px;
        background: #0284c7;
        color: #ffffff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(2, 132, 199, 0.35);
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.2s;
    }

    .display-emblem.emblem-active {
        transform: scale(1.06);
        background: #0369a1;
    }

    .display-emblem.emblem-unlocking {
        transform: scale(1.1) rotate(-8deg);
        background: #059669;
        box-shadow: 0 10px 24px rgba(5, 150, 105, 0.4);
    }

    /* Input Field & Pulse */
    .tv-input {
        background-color: #ffffff !important;
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

    .tv-input:focus {
        border-color: #0284c7 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15) !important;
        outline: none;
    }

    .tv-input.key-pulse {
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
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 14px;
        color: #64748b;
        font-size: 1.1rem;
        cursor: pointer;
        z-index: 10;
        transition: background-color 0.15s, color 0.15s;
    }

    .password-toggle-btn:active {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Tactile Virtual PIN Keypad */
    .tv-numpad-container {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 20px;
        padding: 16px;
        margin-top: 16px;
        margin-bottom: 24px;
    }

    .tv-numpad-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .tv-numpad-btn {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        transition: transform 0.1s ease, background-color 0.1s ease;
    }

    .tv-numpad-btn:active, .tv-numpad-btn.btn-active-touch {
        transform: scale(0.92) !important;
        background-color: #e2e8f0 !important;
    }

    .tv-numpad-btn-action {
        font-size: 0.95rem;
        font-weight: 800;
    }

    .tv-numpad-btn-clear {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .tv-numpad-btn-clear:active {
        background: #fee2e2;
    }

    .tv-numpad-btn-backspace {
        background: #fffbeb;
        border-color: #fde68a;
        color: #d97706;
    }
    .tv-numpad-btn-backspace:active {
        background: #fef3c7;
    }

    .btn-tv-submit {
        min-height: 60px;
        font-size: 1.25rem !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        padding: 14px 28px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35) !important;
        transition: transform 0.15s ease, background-color 0.15s ease;
    }

    .btn-tv-submit:active {
        transform: scale(0.96);
    }

    /* Prefers Reduced Motion Fallback */
    @media (prefers-reduced-motion: reduce) {
        .tv-login-card, .card-shake-error, .display-emblem, .tv-numpad-btn, .tv-input {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="tv-login-root">

    {{-- Top Bar with Status & Clock --}}
    <div class="tv-top-bar">
        <div class="tv-system-pill">
            <span class="tv-pulse-dot"></span>
            <span>Display Monitor PTSP</span>
        </div>
        <div class="tv-clock" id="tvClock">00:00:00</div>
    </div>

    {{-- Main Login Card --}}
    <div class="tv-login-card card {{ $errors->has('password') ? 'card-shake-error' : '' }}">
        <div class="card-body p-8 p-lg-12">

            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="display-emblem mb-4" id="displayEmblem">
                    <i class="fa-solid fa-display fs-1" id="displayIcon"></i>
                </div>

                @if(config('institution.logo_path') && file_exists(public_path(config('institution.logo_path'))))
                    <img alt="{{ config('institution.name') }}" src="{{ asset(config('institution.logo_path')) }}"
                         style="height:44px;object-fit:contain;display:block;margin:0 auto 12px;">
                @elseif(config('institution.logo_path') && file_exists(storage_path('app/public/' . config('institution.logo_path'))))
                    <img alt="{{ config('institution.name') }}" src="{{ Storage::url(config('institution.logo_path')) }}"
                         style="height:44px;object-fit:contain;display:block;margin:0 auto 12px;">
                @endif

                <h1 class="fw-boldest fs-2x text-uppercase mb-1" style="color:#0f172a;letter-spacing:-0.5px;">
                    Monitor Antrian
                </h1>
                <p class="fs-4 fw-semibold mb-0" style="color:#64748b;">
                    TV Display &bull; {{ config('institution.name') }}
                </p>
            </div>

            {{-- Error Message Banner --}}
            @if($errors->has('password'))
                <div class="rounded-3 fw-bold fs-5 mb-6 p-4 d-flex align-items-center"
                     style="background:#fef2f2;border:2px solid #ef4444;color:#b91c1c;">
                    <i class="fa-solid fa-triangle-exclamation fs-3 me-3 text-danger"></i>
                    <div>{{ $errors->first('password') }}</div>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('tv-display.legacy.authenticate') }}" id="loginTvForm">
                @csrf

                {{-- Visual PIN Dots --}}
                <div class="pin-dots-bar" id="pinDotsBar">
                    <span class="pin-dot"></span>
                    <span class="pin-dot"></span>
                    <span class="pin-dot"></span>
                    <span class="pin-dot"></span>
                </div>

                <div class="mb-3">
                    <label class="fs-5 fw-bold mb-2 d-block text-uppercase"
                           style="color:#334155; letter-spacing:0.5px;">
                        PIN TV Display
                    </label>
                    <div class="password-input-group">
                        <input type="password"
                               name="password"
                               id="passwordInput"
                               class="form-control tv-input pe-15"
                               placeholder="Masukkan PIN TV Display..."
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
                <div class="tv-numpad-container">
                    <div class="text-center fs-7 fw-bold text-gray-500 text-uppercase mb-2">
                        Papan Tombol PIN
                    </div>
                    <div class="tv-numpad-grid">
                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('1')">1</button>
                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('2')">2</button>
                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('3')">3</button>

                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('4')">4</button>
                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('5')">5</button>
                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('6')">6</button>

                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('7')">7</button>
                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('8')">8</button>
                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('9')">9</button>

                        <button type="button" class="tv-numpad-btn tv-numpad-btn-action tv-numpad-btn-clear" onclick="loginNumpadClear()">
                            HAPUS
                        </button>
                        <button type="button" class="tv-numpad-btn" onclick="loginNumpadPress('0')">0</button>
                        <button type="button" class="tv-numpad-btn tv-numpad-btn-action tv-numpad-btn-backspace" onclick="loginNumpadBackspace()">
                            <i class="fa-solid fa-delete-left"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        id="btnTvSubmit"
                        class="btn btn-tv-submit w-100">
                    <span class="indicator-label d-flex align-items-center gap-2">
                        <i class="fa-solid fa-right-to-bracket" id="submitIcon"></i>
                        Masuk ke TV Display
                    </span>
                    <span class="indicator-progress d-none">
                        <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                        Membuka TV Display...
                    </span>
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ url('/') }}" class="text-muted hover-primary fw-semibold fs-6 text-decoration-none">
                    <i class="fa-solid fa-house me-1"></i> Kembali ke Halaman Utama
                </a>
            </div>

        </div>
    </div>

    {{-- Bottom Footer --}}
    <div class="text-center py-2">
        <span class="text-gray-600 fw-semibold fs-7 text-uppercase" style="letter-spacing:1px;">
            &copy; {{ date('Y') }} Sistem Antrian PTSP &bull; Mode Legacy Android 5
        </span>
    </div>

</div>
@endsection

@push('scripts')
<script>
    /* ═══════════════════════════════════════════════════════════
       ES5 SAFE JAVASCRIPT CONTROLLER (TV DISPLAY PIN AUTH)
       ═══════════════════════════════════════════════════════════ */

    function updateTvClock() {
        var now = new Date();
        var h = ('0' + now.getHours()).slice(-2);
        var m = ('0' + now.getMinutes()).slice(-2);
        var s = ('0' + now.getSeconds()).slice(-2);
        var el = document.getElementById('tvClock');
        if (el) {
            el.textContent = h + ':' + m + ':' + s;
        }
    }

    updateTvClock();
    setInterval(updateTvClock, 1000);

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
        var emblem = document.getElementById('displayEmblem');
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

        if (emblem) {
            if (len > 0) {
                emblem.classList.add('emblem-active');
            } else {
                emblem.classList.remove('emblem-active');
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

    document.getElementById('loginTvForm').addEventListener('submit', function () {
        var btn = document.getElementById('btnTvSubmit');
        var emblem = document.getElementById('displayEmblem');
        var icon = document.getElementById('displayIcon');

        if (emblem) {
            emblem.classList.add('emblem-unlocking');
        }
        if (icon) {
            icon.className = 'fa-solid fa-circle-check fs-1';
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
