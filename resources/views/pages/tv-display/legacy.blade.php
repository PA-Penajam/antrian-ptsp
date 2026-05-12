@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    /* === TV DISPLAY — OPTIMIZED FOR 32" TV === */
    body {
        background-color: #0f172a;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    /* === Sound Activation Overlay (WebOS Autoplay Policy) === */
    .sound-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.88);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: opacity 0.4s ease;
    }

    .sound-overlay.fade-out {
        opacity: 0;
        pointer-events: none;
    }

    .sound-overlay-icon {
        font-size: 5rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
        animation: pulse-icon 2s ease-in-out infinite;
    }

    .sound-overlay-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
    }

    .sound-overlay-subtitle {
        font-size: 1.3rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.6);
    }

    @keyframes pulse-icon {
        0%, 100% { transform: scale(1); opacity: 0.7; }
        50% { transform: scale(1.12); opacity: 1; }
    }

    .tv-root {
        height: 100vh;
        width: 100vw;
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        box-sizing: border-box;
    }

    /* === Header — biru navy cerah === */
    .tv-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #1e293b 100%);
        border-radius: 1.1rem;
        padding: 1rem 2rem;
        margin-bottom: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid rgba(96,165,250,0.15);
        box-shadow: 0 4px 24px rgba(0,0,0,0.3);
        flex-shrink: 0;
    }

    /* === Body === */
    .tv-body {
        display: flex;
        flex-direction: row;
        flex: 1;
        margin-bottom: 1.1rem;
        min-height: 0;
    }

    /* Kolom Kiri: Video (62%) */
    .tv-media-col {
        width: 62%;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        padding-right: 0.55rem;
    }

    .tv-video-card {
        background-color: #000000;
        border-radius: 1.25rem;
        overflow: hidden;
        background-size: cover;
        background-position: center;
        flex: 1;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        border: 1px solid rgba(255,255,255,0.06);
    }

    .tv-video-card video {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background-color: #000000;
        display: block;
    }

    .tv-video-overlay {
        position: relative;
        z-index: 1;
        padding: 2.5rem;
        background: linear-gradient(to top, rgba(15,23,42,0.92) 0%, rgba(15,23,42,0.4) 60%, transparent 100%);
    }

    /* Kolom Kanan: Antrian (38%) */
    .tv-queue-col {
        width: 38%;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        padding-left: 0.55rem;
    }

    /* Hero Panel: gradient biru mencolok — mudah terbaca dari jauh */
    .queue-hero {
        background: linear-gradient(145deg, #1d4ed8 0%, #2563eb 50%, #1e40af 100%);
        border-radius: 1.25rem;
        padding: 1.75rem 2rem;
        text-align: center;
        margin-bottom: 1.1rem;
        box-shadow: 0 8px 32px rgba(37,99,235,0.4);
        flex-shrink: 0;
    }

    .queue-hero .ticket-number {
        font-size: 8rem;
        font-size: clamp(5rem, 10vw, 9.5rem);
        line-height: 0.9;
        letter-spacing: -4px;
        font-weight: 900;
        color: #ffffff;
        text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .queue-hero .no-call-state {
        padding: 1.25rem 0;
        color: rgba(255,255,255,0.4);
    }

    /* Recent Calls Panel */
    .queue-recent {
        background: #1e293b;
        border-radius: 1.25rem;
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(255,255,255,0.06);
        overflow: hidden;
    }

    .recent-call-item {
        background: rgba(255,255,255,0.05);
        border-radius: 0.875rem;
        padding: 0.9rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid rgba(255,255,255,0.07);
        transition: opacity 0.5s ease;
    }

    /* Footer Marquee */
    .tv-footer {
        background: linear-gradient(135deg, #1e3a5f 0%, #1e293b 100%);
        border-radius: 999px;
        padding: 0.9rem 2.5rem;
        flex-shrink: 0;
        border: 1px solid rgba(96,165,250,0.15);
        overflow: hidden;
    }

    /* Marquee */
    @keyframes tv-marquee {
        0%   { transform: translateX(100vw); }
        100% { transform: translateX(-100%); }
    }

    .marquee-text {
        display: inline-block;
        white-space: nowrap;
        animation: tv-marquee 35s linear infinite;
    }

    /* Clock */
    .tv-clock { font-variant-numeric: tabular-nums; letter-spacing: -2px; }

    /* === Animasi panggilan masuk === */
    @keyframes callIn {
        0%   { opacity: 0; transform: scale(0.75); }
        60%  { transform: scale(1.06); }
        100% { opacity: 1; transform: scale(1); }
    }
    @keyframes slideUp {
        0%   { opacity: 0; transform: translateY(18px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes heroPulse {
        0%, 100% { box-shadow: 0 8px 32px rgba(37,99,235,0.4); }
        50%       { box-shadow: 0 8px 48px rgba(37,99,235,0.75), 0 0 0 8px rgba(37,99,235,0.15); }
    }

    .call-animate    { animation: callIn 0.55s cubic-bezier(0.34,1.56,0.64,1) both; }
    .slide-up        { animation: slideUp 0.4s ease both; }
    .hero-pulse-anim { animation: heroPulse 2s ease-in-out infinite; }

    /* Recent call item animasi masuk */
    .recent-call-item { animation: slideUp 0.35s ease both; }

    /* Video placeholder saat tidak ada video */
    .tv-video-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
    }
</style>
@endpush

@section('content')
<div class="tv-root">

    {{-- ═══ HEADER ═══ --}}
    <div class="tv-header">
        <div class="d-flex align-items-center gap-5">
            <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:56px;height:56px;background:rgba(255,255,255,0.06);border-radius:0.875rem;padding:8px;">
                @if(config('institution.logo_path'))
                    <img src="{{ Storage::url(config('institution.logo_path')) }}"
                         alt="Logo"
                         style="max-height:40px;max-width:40px;object-fit:contain;">
                @else
                    <img src="{{ asset('metronic-assets/media/logos/logo-papenajam.webp') }}"
                         alt="Logo"
                         style="max-height:40px;max-width:40px;object-fit:contain;">
                @endif
            </div>
            <div>
                <div class="text-white fw-boldest fs-1 mb-0" style="letter-spacing:-0.5px;line-height:1.2;">
                    {{ config('institution.name') }}
                </div>
                <span class="fw-semibold fs-6 text-uppercase"
                      style="color:rgba(255,255,255,0.7);letter-spacing:2px;">
                    Sistem Antrian Digital
                </span>
            </div>
        </div>

        <div class="text-end">
            <div class="text-white fw-boldest fs-3x mb-0 tv-clock" id="clockDisplay">00:00:00</div>
            <div class="fw-semibold fs-6 text-uppercase mt-1" style="color:rgba(255,255,255,0.7);" id="dateDisplay">---</div>
        </div>
    </div>

    {{-- ═══ BODY ═══ --}}
    <div class="tv-body">

        {{-- KIRI: Media Player --}}
        <div class="tv-media-col">
            <div class="tv-video-card">
                <video id="tvPlayer" autoplay muted playsinline></video>

                {{-- Placeholder tampil saat tidak ada video --}}
                <div class="tv-video-placeholder" id="videoPlaceholder">
                    <i class="ki-duotone ki-screen fs-5hx mb-6" style="color:rgba(255,255,255,0.12);">
                        <span class="path1"></span><span class="path2"></span>
                        <span class="path3"></span><span class="path4"></span>
                    </i>
                    <div class="fw-bold fs-3 text-uppercase" style="color:rgba(255,255,255,0.2);letter-spacing:2px;">
                        Informasi Layanan
                    </div>
                </div>

                <div class="tv-video-overlay">
                    <span class="badge badge-light-primary fs-5 fw-bold px-5 py-2 rounded-pill mb-4 d-inline-block text-uppercase">
                        Informasi Layanan
                    </span>
                    <div class="text-white fw-boldest fs-2x mb-1" style="letter-spacing:-0.5px;">
                        {{ config('institution.name') }}
                    </div>
                    <div class="fw-semibold fs-4" style="color:rgba(255,255,255,0.5);">
                        Budayakan Antre demi Kenyamanan Bersama
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: Panel Antrian --}}
        <div class="tv-queue-col">

            {{-- Hero: Sedang Dipanggil --}}
            <div class="queue-hero">
                {{-- Header kartu — label + pulse LIVE badge --}}
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <span class="fw-bold fs-6 text-uppercase" style="color:rgba(255,255,255,0.7);letter-spacing:2px;">
                        Sedang Dipanggil
                    </span>
                    <div class="d-flex align-items-center gap-2 pulse pulse-success">
                        <span class="pulse-ring border-2"></span>
                        <span class="badge fs-7 fw-bold px-4 py-2 rounded-pill text-uppercase"
                              style="background:rgba(255,255,255,0.2);color:#fff;">
                            LIVE
                        </span>
                    </div>
                </div>

                {{-- State: tidak ada panggilan --}}
                <div id="noCallState" class="no-call-state">
                    <i class="ki-duotone ki-time fs-3hx mb-3 d-block" style="color:rgba(255,255,255,0.6);">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="fw-bold fs-3 text-uppercase" style="color:rgba(255,255,255,0.7);letter-spacing:1px;">
                        Menunggu Panggilan
                    </div>
                </div>

                {{-- State: ada panggilan --}}
                <div id="activeCallState" class="d-none">
                    <div class="ticket-number fw-boldest text-white call-animate" id="activeTicketNumber"></div>
                    <div class="rounded-pill py-2 px-8 d-inline-block mb-3 slide-up"
                         style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);">
                        <span class="text-white fw-boldest fs-2hx" style="letter-spacing:-0.5px;" id="activeCounterName">
                            LOKET
                        </span>
                    </div>
                    <div class="fw-semibold fs-3 text-uppercase slide-up" style="color:rgba(255,255,255,0.7);" id="activeServiceName">
                    </div>
                    <div class="fw-semibold fs-4 text-uppercase slide-up mt-2" style="color:rgba(255,255,255,0.5);" id="activeVisitPurpose">
                    </div>
                </div>
            </div>

            {{-- Recent Calls --}}
            <div class="queue-recent">
                <div class="fw-bold fs-5 text-uppercase mb-5"
                     style="color:rgba(255,255,255,0.55);letter-spacing:2px;">
                    Panggilan Terakhir
                </div>
                <div id="recentCallsContainer" class="d-flex flex-column gap-3">
                    <div class="recent-call-item justify-content-center">
                        <span class="fw-semibold fs-5" style="color:rgba(255,255,255,0.6);">
                            Menunggu data antrian...
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══ FOOTER MARQUEE ═══ --}}
    <div class="tv-footer">
        <div class="overflow-hidden">
            <div class="marquee-text">
                <span class="text-white fw-bold fs-2">
                    &bull;&nbsp; Selamat Datang di {{ config('institution.name') }} &nbsp;&nbsp;&nbsp;
                    &bull;&nbsp; Ambil Nomor Antrian pada Mesin Kiosk &nbsp;&nbsp;&nbsp;
                    &bull;&nbsp; Pastikan Anda Membawa Persyaratan yang Diperlukan &nbsp;&nbsp;&nbsp;
                    &bull;&nbsp; Harap Menunggu dengan Tertib &nbsp;&nbsp;&nbsp;
                </span>
            </div>
        </div>
    </div>

</div>

{{-- Overlay aktivasi suara — diperlukan untuk memenuhi Autoplay Policy browser (khususnya WebOS LG) --}}
<div id="soundOverlay" class="sound-overlay">
    <i class="ki-duotone ki-speaker sound-overlay-icon">
        <span class="path1"></span>
        <span class="path2"></span>
    </i>
    <div class="sound-overlay-title">Tekan Tombol Apa Saja</div>
    <div class="sound-overlay-subtitle">untuk Mengaktifkan Suara</div>
</div>

<audio id="ttsAudio" preload="auto"></audio>
@endsection

@push('scripts')
<script>
    var playlist         = [];
    var currentVideoIdx  = 0;
    var tvPlayer         = document.getElementById('tvPlayer');
    var audioPlayer      = document.getElementById('ttsAudio');
    var lastAnnouncedId  = null;
    var fetchErrCount    = 0;
    var isPageVisible    = true;
    var fetchStateInterval = null;
    var pendingAnnouncementText = '';
    var currentAudioObjectUrl = null;
    var playGuardTimer = null;
    var videoPausedForTts = false;
    var wasVideoPlayingBeforeTts = false;

    var VIDEO_VOLUME            = {{ config('tv.video_volume') }};
    var VIDEO_VOLUME_DURING_TTS = {{ config('tv.video_volume_during_tts') }};
    var TTS_VOLUME              = {{ config('tv.tts_volume') }};
    var SILENT_AUDIO            = 'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA';
    var isSamsungTv             = /Tizen|SMART-TV|SamsungBrowser|Maple/i.test(navigator.userAgent);

    $(document).ready(function () {
        updateClock();
        setInterval(updateClock, 1000);

        fetchState();
        fetchStateInterval = setInterval(fetchState, 5000); // Mengurangi frekuensi polling dari 3000ms ke 5000ms.

        tvPlayer.addEventListener('ended', function () {
            if (playlist.length === 0) { return; }
            currentVideoIdx = (currentVideoIdx + 1) % playlist.length;
            playCurrentVideo();
        });

        audioPlayer.addEventListener('playing', function () {
            clearPlayGuard();
            pendingAnnouncementText = '';
        });

        audioPlayer.addEventListener('ended', function () {
            clearPlayGuard();
            revokeAudioObjectUrl();
            endTtsPlayback();
        });

        audioPlayer.addEventListener('error', function () {
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

        document.addEventListener('visibilitychange', function() {
            isPageVisible = !document.hidden;

            if (isPageVisible) {
                // Page became visible - resume operations
                resumeOperations();
            } else {
                // Page hidden - pause operations
                pauseOperations();
            }
        });
    });

    function pauseOperations() {
        // Pause marquee animation
        var marquee = document.querySelector('.marquee-text');
        if (marquee) {
            marquee.style.animationPlayState = 'paused';
        }

        // Pause video to save resources
        if (tvPlayer && !tvPlayer.paused) {
            tvPlayer.pause();
        }

        // Clear polling interval (will be restarted on resume)
        clearInterval(fetchStateInterval);
        fetchStateInterval = null;
    }

    var soundActivated = false;

    function activateSound() {
        if (soundActivated) { return; }
        soundActivated = true;

        // Hapus event listeners supaya tidak terpicu ulang
        document.removeEventListener('click', activateSound);
        document.removeEventListener('keydown', activateSound);

        setVideoAmbientVolume(VIDEO_VOLUME);

        if (tvPlayer && playlist.length > 0 && tvPlayer.paused) {
            tvPlayer.play().catch(function () {
                /* Video autoplay retry failed. */
            });
        }

        // Unlock audio element yang SEBENARNYA dipakai (#ttsAudio) dengan silent WAV.
        // Pada Samsung/Tizen, video dipause sebentar agar audio element bisa di-unlock tanpa membuat video plane blank.
        var shouldResumeVideo = isSamsungTv && tvPlayer && !tvPlayer.paused && !tvPlayer.ended;
        if (isSamsungTv && tvPlayer && !tvPlayer.paused) {
            tvPlayer.pause();
        }

        audioPlayer.pause();
        audioPlayer.src = SILENT_AUDIO;
        audioPlayer.volume = TTS_VOLUME;
        audioPlayer.load();

        var finishAudioUnlock = function () {
            audioPlayer.pause();
            audioPlayer.removeAttribute('src');
            audioPlayer.load();
            audioPlayer.volume = TTS_VOLUME;

            if ((!isSamsungTv || shouldResumeVideo) && playlist.length > 0) {
                playCurrentVideo();
            }
        };

        var unlockPromise = audioPlayer.play();
        if (unlockPromise && typeof unlockPromise.catch === 'function') {
            unlockPromise
                .then(finishAudioUnlock)
                .catch(finishAudioUnlock);
        } else {
            finishAudioUnlock();
        }

        // Fade-out dan hapus overlay
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

    function setVideoAmbientVolume(volume) {
        if (!tvPlayer) { return; }

        if (isSamsungTv) {
            tvPlayer.muted = !soundActivated;
            tvPlayer.volume = soundActivated ? volume : 0;

            return;
        }

        tvPlayer.muted = !soundActivated;
        tvPlayer.volume = volume;
    }

    function beginTtsPlayback() {
        if (isSamsungTv) {
            if (!videoPausedForTts) {
                wasVideoPlayingBeforeTts = tvPlayer && !tvPlayer.paused && !tvPlayer.ended;

                if (tvPlayer && !tvPlayer.paused) {
                    tvPlayer.pause();
                }

                videoPausedForTts = true;
            }

            return;
        }

        setVideoAmbientVolume(VIDEO_VOLUME_DURING_TTS);
    }

    function endTtsPlayback() {
        if (isSamsungTv) {
            if (videoPausedForTts && wasVideoPlayingBeforeTts && playlist.length > 0) {
                playCurrentVideo();
            }

            videoPausedForTts = false;
            wasVideoPlayingBeforeTts = false;

            return;
        }

        setVideoAmbientVolume(VIDEO_VOLUME);
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

    function resumeOperations() {
        // Resume marquee animation
        var marquee = document.querySelector('.marquee-text');
        if (marquee) {
            marquee.style.animationPlayState = 'running';
        }

        // Resume video if playlist exists
        if (tvPlayer && playlist.length > 0 && tvPlayer.paused) {
            tvPlayer.play().catch(function() {
                // Autoplay blocked, will try on next user interaction
            });
        }

        // Immediate state refresh
        fetchState();

        // Restart polling interval
        if (!fetchStateInterval) {
            fetchStateInterval = setInterval(fetchState, 5000);
        }
    }

    function updateClock() {
        var now = new Date();
        $('#clockDisplay').text(now.toLocaleTimeString('id-ID', { hour12: false }));
        $('#dateDisplay').text(now.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        }));
    }

    function playCurrentVideo() {
        if (playlist.length === 0) { return; }

        if (tvPlayer.getAttribute('src') !== playlist[currentVideoIdx]) {
            tvPlayer.src = playlist[currentVideoIdx];
            tvPlayer.load();
        }

        setVideoAmbientVolume(VIDEO_VOLUME);
        tvPlayer.play().catch(function () {
            /* Autoplay diblokir browser — menunggu interaksi pengguna */
        });
    }

    function fetchState() {
        $.ajax({
            url: '{{ route("tv-display.legacy.api") }}',
            type: 'GET',
            timeout: 5000,
            success: function (response) {
                fetchErrCount = 0;
                if (response.success) { updateUI(response.data); }
            },
            error: function () {
                fetchErrCount++;
                if (fetchErrCount >= 5) {
                    console.warn('TV Display: Koneksi ke server bermasalah (' + fetchErrCount + 'x gagal).');
                }
            }
        });
    }

    function updateUI(data) {
        /* Inisialisasi playlist video (hanya sekali) */
        if (playlist.length === 0 && data.videos && data.videos.length > 0) {
            playlist = data.videos;
            playCurrentVideo();
            $('#videoPlaceholder').hide();
        }

        /* Update panggilan aktif */
        if (data.currentCalls && data.currentCalls.length > 0) {
            var active = data.currentCalls[0];

            $('#noCallState').addClass('d-none');
            $('#activeCallState').removeClass('d-none');
            /* Re-trigger animasi masuk dengan replace elemen */
            var activeTicketNumber = $('#activeTicketNumber');
            activeTicketNumber.text(active.ticket_number).removeClass('call-animate');

            if (activeTicketNumber.length > 0 && activeTicketNumber[0]) {
                void activeTicketNumber[0].offsetWidth;
            }

            activeTicketNumber.addClass('call-animate');
            $('#activeCounterName').text(active.counter ? active.counter.name.toUpperCase() : 'LOKET');
            $('#activeServiceName').text(active.service ? active.service.name : '');
            $('#activeVisitPurpose').text(active.visit_purpose ? formatVisitPurpose(active.visit_purpose) : '');
            /* Aktifkan pulse glow pada hero card */
            $('.queue-hero').addClass('hero-pulse-anim');

            var callId = active.id + '-' + active.called_at;
            if (lastAnnouncedId !== callId) {
                lastAnnouncedId = callId;
                playAnnouncer(active);
            }
        } else {
            $('#noCallState').removeClass('d-none');
            $('#activeCallState').addClass('d-none');
            $('.queue-hero').removeClass('hero-pulse-anim');
        }

        /* Update daftar panggilan terakhir dengan diff-based approach */
        if (data.recentCalls && data.recentCalls.length > 0) {
            var skip = (data.currentCalls && data.currentCalls.length > 0 &&
                        data.recentCalls[0].id === data.currentCalls[0].id) ? 1 : 0;
            var callsToShow = [];

            for (var i = skip; i < data.recentCalls.length && i < skip + 4; i++) {
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

    function renderRecentCallItem(call, opacity) {
        var serviceName = call.service ? call.service.name : '';
        var counterName = call.counter ? call.counter.name : '-';
        var initial     = call.ticket_number.charAt(0);
        var visitPurpose = call.visit_purpose ? formatVisitPurpose(call.visit_purpose) : '';
        var purposeHtml = visitPurpose
            ? '<div class="visit-purpose fw-semibold fs-7 text-uppercase" style="color:rgba(255,255,255,0.5);">' + visitPurpose + '</div>'
            : '';

        return '<div class="recent-call-item" id="call-' + call.id + '" style="opacity:' + opacity + ';">' +
            '<div class="d-flex align-items-center gap-4">' +
                '<div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"' +
                     ' style="width:46px;height:46px;background:rgba(96,165,250,0.25);">' +
                    '<span class="fw-boldest fs-4" style="color:#93c5fd;">' + initial + '</span>' +
                '</div>' +
                '<div>' +
                    '<div class="ticket-number text-white fw-boldest fs-1 ls-n1" style="line-height:1.1;">' +
                        call.ticket_number +
                    '</div>' +
                    '<div class="service-name fw-semibold fs-6 text-uppercase" style="color:rgba(255,255,255,0.7);">' +
                        serviceName +
                    '</div>' +
                    purposeHtml +
                '</div>' +
            '</div>' +
            '<div class="counter-name badge badge-light-primary fs-4 fw-boldest px-5 py-2 rounded-pill text-uppercase">' +
                counterName +
            '</div>' +
        '</div>';
    }

    function renderEmptyState() {
        return '<div class="recent-call-item justify-content-center empty-state">' +
               '<span class="fw-semibold fs-4" style="color:rgba(255,255,255,0.18);">Belum ada panggilan</span>' +
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
            var opacity = Math.max(0.25, 1 - (index * 0.2));
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
                    var newHtml = renderRecentCallItem(call, opacity);
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newHtml;

                    var newElement = tempDiv.firstElementChild;
                    newElement.id = expectedId;
                    container.replaceChild(newElement, existingItem);
                }
            } else {
                var html = renderRecentCallItem(call, opacity);
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
        beginTtsPlayback();

        if (!('speechSynthesis' in window)) {
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
            endTtsPlayback();
        };
        utterance.onerror = function () {
            endTtsPlayback();
        };

        window.speechSynthesis.speak(utterance);
    }

    function playMiniMaxAudio(audioUrl, fallbackText) {
        fetch(audioUrl, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('AUDIO_HTTP_' + response.status);
                }

                return response.blob();
            })
            .then(function (audioBlob) {
                if (!audioBlob || audioBlob.size <= 0) {
                    throw new Error('AUDIO_EMPTY_BLOB');
                }

                var blobType = (audioBlob.type || '').toLowerCase();
                if (blobType && blobType.indexOf('audio') === -1 && blobType.indexOf('octet-stream') === -1) {
                    throw new Error('AUDIO_INVALID_BLOB_TYPE_' + blobType);
                }

                clearPlayGuard();
                revokeAudioObjectUrl();

                currentAudioObjectUrl = URL.createObjectURL(audioBlob);
                audioPlayer.src = currentAudioObjectUrl;

                playGuardTimer = setTimeout(function () {
                    if (pendingAnnouncementText !== '') {
                        var guardText = pendingAnnouncementText;
                        pendingAnnouncementText = '';
                        speakWithBrowserTts(guardText);
                    }
                }, 2500);

                audioPlayer.volume = TTS_VOLUME;
                beginTtsPlayback();
                var playPromise = audioPlayer.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function () {
                        // Bersihkan src agar tidak memicu error event setelah blob dicabut
                        audioPlayer.src = '';
                        speakWithBrowserTts(fallbackText);
                    });
                }
            })
            .catch(function () {
                // Fetch gagal — pastikan src bersih sebelum fallback ke browser TTS
                audioPlayer.src = '';
                speakWithBrowserTts(fallbackText);
            });
    }

    function playAnnouncer(call) {
        var loket    = call.counter ? call.counter.name : 'Loket';
        var ttsNomor = call.ticket_number.replace(/^([A-Za-z]+)0+(.*)$/, '$1$2');
        var text = 'Nomor antrian ' + ttsNomor + ', silakan menuju ' + loket + '.';

        pendingAnnouncementText = text;

        $.ajax({
            url: '{{ route("tv-display.tts.announcement") }}',
            type: 'GET',
            dataType: 'json',
            timeout: 10000,
            data: {
                text: text,
            },
            success: function (response) {
                if (response && response.audio_url) {
                    playMiniMaxAudio(response.audio_url, text);

                    return;
                }

                // Provider browser: clear pending agar error handler tidak double-trigger
                pendingAnnouncementText = '';
                speakWithBrowserTts(text);
            },
            error: function () {
                // AJAX gagal (sesi habis, dll): clear pending sebelum fallback
                pendingAnnouncementText = '';
                speakWithBrowserTts(text);
            }
        });
    }
</script>
@endpush
