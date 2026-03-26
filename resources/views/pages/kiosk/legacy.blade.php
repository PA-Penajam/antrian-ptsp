@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    /* === KIOSK — METRONIC DEMO 10 LAUNCHER STYLE === */
    body {
        background-color: #0f0f1a;
        margin: 0;
        padding: 0;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .kiosk-root {
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        min-height: 100vh;
        width: 100%;
    }

    .kiosk-overlay {
        background: linear-gradient(to bottom, rgba(15,15,35,0.08) 0%, rgba(10,10,28,0.15) 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* === Service Launcher Cards (Demo 10 exact style) === */
    .service-card {
        border: none !important;
        border-radius: 24px !important;
        cursor: pointer;
        transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.22s ease;
        overflow: hidden;
        min-height: 275px;
        position: relative;
    }
    .service-card:hover  { transform: translateY(-8px) scale(1.02); box-shadow: 0 32px 64px rgba(0,0,0,0.45) !important; }
    .service-card:active { transform: scale(0.95); }

    /* Demo 10 brand palette */
    .svc-purple  { background-color: #A838FF !important; }
    .svc-red     { background-color: #F9666E !important; }
    .svc-green   { background-color: #35D29A !important; }
    .svc-yellow  { background-color: #D5D83D !important; }
    .svc-info    { background-color: #009EF7 !important; }
    .svc-primary { background-color: #0095E8 !important; }

    /* === Booking Form === */
    .booking-card {
        border-radius: 32px !important;
        border: none !important;
        box-shadow: 0 40px 80px rgba(0,0,0,0.55) !important;
        background: #fff;
    }

    .kiosk-input {
        background-color: #f5f8fa !important;
        border: 2px solid #eff2f5 !important;
        border-radius: 16px !important;
        font-size: 1.35rem !important;
        padding: 1.2rem 1.75rem !important;
        height: auto !important;
        font-weight: 600;
        color: #181c32 !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .kiosk-input:focus {
        border-color: #009EF7 !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(0,158,247,0.12) !important;
        outline: none;
    }

    /* Select2 override untuk kiosk input style */
    .select2-container--bootstrap5 .select2-selection {
        background-color: #f5f8fa !important;
        border: 2px solid #eff2f5 !important;
        border-radius: 16px !important;
        min-height: auto !important;
        padding: 1.2rem 1.75rem !important;
    }
    .select2-container--bootstrap5 .select2-selection--single .select2-selection__rendered {
        font-size: 1.35rem !important;
        font-weight: 600 !important;
        line-height: 1.4 !important;
        padding: 0 !important;
        color: #181c32 !important;
    }
    .select2-container--bootstrap5 .select2-selection--single .select2-selection__placeholder {
        color: #a1a5b7 !important;
        font-weight: 500 !important;
    }

    /* === Ticket Display === */
    .ticket-hero-number {
        font-size: clamp(6rem, 14vw, 11rem);
        line-height: 0.9;
        letter-spacing: -5px;
        font-weight: 900;
        color: #009EF7;
    }

    /* === Countdown === */
    .countdown-track {
        height: 6px;
        background: #eff2f5;
        border-radius: 999px;
        overflow: hidden;
    }
    .countdown-fill {
        height: 100%;
        background: linear-gradient(90deg, #50cd89, #009EF7);
        border-radius: 999px;
        transition: width 1s linear;
    }

    /* === Clock === */
    .kiosk-clock { font-variant-numeric: tabular-nums; letter-spacing: -2px; }

    /* Icon hover animation pada kartu */
    .service-card:hover .svc-illustration { transform: scale(1.08) rotate(3deg); }
    .svc-illustration { transition: transform 0.3s ease; }

    /* === Teks navy untuk background terang === */
    .kiosk-overlay .text-white {
        color: #1b3d6e !important;
    }
    /* Kartu layanan tetap putih */
    .service-card .text-white,
    .service-card .ki-duotone {
        color: #ffffff !important;
    }
    /* Footer */
    .kiosk-footer-text {
        color: rgba(27,61,110,0.45) !important;
    }
</style>
@endpush

@section('content')
<div class="kiosk-root" style="background-image: url('/metronic-assets/media/auth/bg10.jpeg');">
<div class="kiosk-overlay">

    {{-- ═══ HEADER ═══ --}}
    <div class="d-flex flex-stack px-10 py-8 flex-shrink-0">
        <div class="d-flex align-items-center gap-6">
            <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:72px;height:72px;padding:10px;">
                @if(config('institution.logo_path'))
                    <img alt="Logo" src="{{ Storage::url(config('institution.logo_path')) }}"
                         style="max-height:50px;max-width:50px;object-fit:contain;">
                @else
                    <img alt="Logo" src="{{ asset('metronic-assets/media/logos/logo-papenajam.webp') }}"
                         style="max-height:50px;max-width:50px;object-fit:contain;">
                @endif
            </div>
            <div>
                <div class="text-white fw-semibold fs-6 text-uppercase mb-1"
                     style="opacity:0.45;letter-spacing:2px;">Sistem Antrian Digital</div>
                <h1 class="text-white fw-boldest fs-2x mb-0" style="letter-spacing:-1px;line-height:1.1;">
                    {{ config('institution.name') }}
                </h1>
            </div>
        </div>

        <div class="text-end d-none d-md-block">
            <h2 class="text-white fw-boldest fs-3x mb-0 kiosk-clock" id="kioskClock">00:00:00</h2>
            <div class="text-white fw-semibold fs-6 text-uppercase mt-1"
                 style="opacity:0.45;" id="kioskDate">---</div>
        </div>
    </div>

    {{-- ═══ LAYAR 1: LAUNCHER SERVICES ═══ --}}
    <div id="screenServices" class="flex-grow-1 d-flex flex-column flex-center px-10 pb-8">
        <div class="text-center mb-12">
            <h1 class="text-white fw-boldest text-uppercase mb-5"
                style="font-size:clamp(2.5rem,5vw,4.5rem);letter-spacing:-3px;line-height:1.1;">
                SILAKAN PILIH LAYANAN
            </h1>
            <div class="d-inline-flex align-items-center gap-3">
                <span class="bullet bullet-dot bg-primary" style="width:8px;height:8px;"></span>
                <span class="text-white fw-semibold fs-3 text-uppercase"
                      style="opacity:0.5;letter-spacing:1px;">Sentuh pada layanan yang Anda butuhkan</span>
                <span class="bullet bullet-dot bg-primary" style="width:8px;height:8px;"></span>
            </div>
        </div>

        @php
            $svcColors = ['svc-purple','svc-red','svc-green','svc-yellow','svc-info','svc-primary'];
            $svcIcons  = [
                'ki-document',
                'ki-folder',
                'ki-briefcase',
                'ki-badge',
                'ki-clipboard',
                'ki-bill',
                'ki-book',
                'ki-key-square',
                'ki-scan-barcode',
                'ki-abstract-26',
                'ki-category',
                'ki-file-added',
            ];
        @endphp

        <div class="row g-7 w-100 justify-content-center" style="max-width:1400px;">
            @foreach($services as $idx => $service)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div onclick="showBookingForm({{ $service->id }}, '{{ addslashes($service->name) }}')"
                     class="card service-card {{ $svcColors[$idx % count($svcColors)] }} shadow-lg">
                    <div class="card-body d-flex flex-column flex-center text-center p-10">
                        <div class="d-flex align-items-center justify-content-center mb-6 svc-illustration"
                             style="height:115px;">
                            @if($service->icon_svg)
                                <div style="font-size:0;">
                                    {!! str_replace('<svg', '<svg style="width:90px;height:90px;" fill="white"', $service->icon_svg) !!}
                                </div>
                            @else
                                <i class="ki-duotone {{ $svcIcons[$idx % count($svcIcons)] }} text-white"
                                   style="font-size:90px;">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            @endif
                        </div>
                        <h3 class="text-white fw-boldest fs-2 text-uppercase mb-4 lh-sm" style="letter-spacing:-0.5px;">
                            {{ $service->name }}
                        </h3>
                        <div class="d-inline-flex align-items-center gap-2 py-3 px-5 rounded-pill fw-bold fs-6 text-uppercase text-white"
                             style="background:rgba(255,255,255,0.18);letter-spacing:0.5px;">
                            <i class="ki-duotone ki-arrow-right fs-6 text-white">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            AMBIL ANTRIAN
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ LAYAR 2: BOOKING FORM ═══ --}}
    <div id="screenForm" class="flex-grow-1 d-flex flex-center px-10 d-none">
        <div class="booking-card card w-100" style="max-width:880px;">
            <div class="card-body p-15 p-lg-20">

                <div class="text-center mb-12">
                    <span class="badge badge-light-primary fs-4 fw-bold px-8 py-3 rounded-pill text-uppercase mb-5 d-inline-block"
                          id="selectedServiceBadge">Layanan</span>
                    <h2 class="fw-boldest fs-3x text-gray-900 text-uppercase mb-3"
                        style="letter-spacing:-1.5px;" id="selectedServiceName"></h2>
                    <p class="text-gray-400 fs-2 fw-semibold mb-0">
                        Lengkapi data diri Anda untuk mengambil nomor antrian
                    </p>
                </div>

                <form id="bookingForm">
                    @csrf
                    <input type="hidden" id="service_id" name="service_id">

                    <div class="mb-8">
                        <label class="fs-3 fw-bold text-gray-700 mb-3 ms-2 text-uppercase d-block">
                            Nama Lengkap
                        </label>
                        <input type="text" name="visitor_name" id="visitor_name"
                               class="form-control kiosk-input"
                               placeholder="Ketik nama lengkap Anda..." required>
                    </div>

                    <div class="row g-8 mb-8">
                        <div class="col-md-6">
                            <label class="fs-3 fw-bold text-gray-700 mb-3 ms-2 text-uppercase d-block">
                                Nomor NIK / Identitas
                            </label>
                            <input type="text" name="visitor_identifier" id="visitor_identifier"
                                   class="form-control kiosk-input"
                                   placeholder="16 Digit NIK..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="fs-3 fw-bold text-gray-700 mb-3 ms-2 text-uppercase d-block">
                                Nomor WhatsApp
                            </label>
                            <input type="tel" name="visitor_phone" id="visitor_phone"
                                   class="form-control kiosk-input"
                                   placeholder="08xx..." required>
                        </div>
                    </div>

                    <div class="mb-12">
                        <label class="fs-3 fw-bold text-gray-700 mb-3 ms-2 text-uppercase d-block">
                            Asal Wilayah
                        </label>
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
                    </div>

                    <div class="d-flex align-items-center justify-content-between gap-5">
                        <button type="button" onclick="backToServices()"
                                class="btn btn-light btn-lg fs-2 fw-bold px-12 py-6 rounded-pill">
                            <i class="ki-duotone ki-arrow-left fs-2 me-2">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            KEMBALI
                        </button>
                        <button type="submit" id="btnSubmit"
                                class="btn btn-primary btn-lg fs-1 fw-boldest px-16 py-6 rounded-pill shadow">
                            <span class="indicator-label d-flex align-items-center gap-3">
                                <i class="ki-duotone ki-printer fs-1">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                                CETAK TIKET
                            </span>
                            <span class="indicator-progress d-none">
                                <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                                MENCETAK...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══ LAYAR 3: SUCCESS ═══ --}}
    <div id="screenSuccess" class="flex-grow-1 d-flex flex-center px-10 d-none">
        <div class="card border-0 text-center w-100 shadow-lg" style="max-width:720px;border-radius:40px !important;">
            <div class="card-body p-15 p-lg-20">

                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-8"
                     style="width:100px;height:100px;background:#e8fff3;">
                    <i class="ki-duotone ki-check-circle fs-4hx text-success">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                </div>

                <h1 class="fw-boldest fs-3x text-gray-900 text-uppercase mb-4" style="letter-spacing:-1.5px;">
                    TIKET BERHASIL DICETAK!
                </h1>
                <p class="text-gray-400 fs-2 fw-semibold mb-10">
                    Silakan ambil tiket Anda dan tunggu di ruang tunggu.
                </p>

                <div class="rounded-30px p-12 mb-10 position-relative overflow-hidden"
                     style="background:linear-gradient(135deg,#f1f7ff 0%,#e8f4ff 100%);border:2px dashed #009EF7;">
                    <span class="text-primary fw-bold fs-3 text-uppercase d-block mb-3 position-relative">
                        NOMOR ANTRIAN ANDA
                    </span>
                    <div class="ticket-hero-number position-relative" id="successTicketNumber">---</div>
                    <div class="text-gray-400 fw-bold fs-4 text-uppercase mt-3 position-relative"
                         id="successServiceName"></div>
                </div>

                <div class="mb-10">
                    <p class="text-gray-400 fs-5 fw-semibold mb-3">
                        Halaman kembali otomatis dalam
                        <span class="fw-bold text-primary" id="countdownText">20 detik</span>
                    </p>
                    <div class="countdown-track w-100">
                        <div class="countdown-fill" id="countdownBar" style="width:100%;"></div>
                    </div>
                </div>

                <button onclick="resetKiosk()"
                        class="btn btn-success btn-lg fs-1 fw-boldest px-16 py-6 rounded-pill shadow">
                    <i class="ki-duotone ki-exit-right fs-1 me-2">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    SELESAI
                </button>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="py-7 text-center flex-shrink-0">
        <span class="kiosk-footer-text fw-semibold fs-6 text-uppercase" style="letter-spacing:1px;">
            &copy; {{ date('Y') }} Sistem Antrian Digital &bull; {{ config('institution.name') }}
        </span>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
    var eposPrinter   = null;
    var cdInterval    = null;
    var cdSeconds     = 20;
    var CD_TOTAL      = 20;

    $(document).ready(function () {
        initPrinter();
        updateKioskClock();
        setInterval(updateKioskClock, 1000);

        if ($.fn.select2) {
            $('#visitor_wilayah_kode').select2({
                placeholder: 'Cari Desa / Kelurahan...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#screenForm')
            });
        }

        $('#bookingForm').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#btnSubmit');
            $btn.find('.indicator-label').addClass('d-none');
            $btn.find('.indicator-progress').removeClass('d-none');
            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("kiosk.legacy.print") }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    if (res.success && res.ticket) {
                        $('#successTicketNumber').text(res.ticket.ticket_number);
                        $('#successServiceName').text(
                            res.ticket.service ? res.ticket.service.name.toUpperCase() : ''
                        );
                        switchScreen('screenSuccess');
                        printTicket(res.ticket);
                        startCountdown();
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Maaf, terjadi kesalahan saat mencetak tiket. Silakan hubungi petugas.',
                        icon: 'error',
                        buttonsStyling: false,
                        confirmButtonText: 'Mengerti',
                        customClass: { confirmButton: 'btn btn-primary px-10' }
                    });
                },
                complete: function () {
                    $btn.find('.indicator-label').removeClass('d-none');
                    $btn.find('.indicator-progress').addClass('d-none');
                    $btn.prop('disabled', false);
                }
            });
        });
    });

    function updateKioskClock() {
        var now = new Date();
        $('#kioskClock').text(now.toLocaleTimeString('id-ID', { hour12: false }));
        $('#kioskDate').text(now.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        }));
    }

    function showBookingForm(id, name) {
        $('#service_id').val(id);
        $('#selectedServiceName').text(name);
        $('#selectedServiceBadge').text(name);
        switchScreen('screenForm');
        if ($.fn.select2) $('#visitor_wilayah_kode').val(null).trigger('change');
        setTimeout(function () { $('#visitor_name').focus(); }, 500);
    }

    function backToServices() {
        switchScreen('screenServices');
    }

    function resetKiosk() {
        clearCountdown();
        $('#bookingForm')[0].reset();
        if ($.fn.select2) $('#visitor_wilayah_kode').val(null).trigger('change');
        backToServices();
    }

    function switchScreen(screenId) {
        $('#screenServices, #screenForm, #screenSuccess').addClass('d-none').hide();
        $('#' + screenId).removeClass('d-none').hide().fadeIn(350);
    }

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
            if (cdSeconds <= 0) { resetKiosk(); }
        }, 1000);
    }

    function clearCountdown() {
        if (cdInterval) { clearInterval(cdInterval); cdInterval = null; }
    }

    function initPrinter() {
        var printerEnabled = {{ config('services.thermal_printer.enabled') ? 'true' : 'false' }};
        if (!printerEnabled || typeof epson === 'undefined') { return; }
        var ePosDevice = new epson.ePOSDevice();
        ePosDevice.connect(
            '{{ config('services.thermal_printer.ip', '127.0.0.1') }}',
            '{{ config('services.thermal_printer.port', '8008') }}',
            function (data) {
                if (data === 'OK' || data === 'SSL_CONNECT_OK') {
                    ePosDevice.createDevice(
                        '{{ config('services.thermal_printer.device_id', 'local_printer') }}',
                        ePosDevice.DEVICE_TYPE_PRINTER,
                        { crypto: false, buffer: false },
                        function (deviceObj, retcode) {
                            if (retcode === 'OK') { eposPrinter = deviceObj; }
                        }
                    );
                }
            }
        );
    }

    function printTicket(ticketData) {
        if (!eposPrinter) { return; }
        var institutionName = '{{ config('institution.name') }}';
        eposPrinter.addTextAlign(eposPrinter.ALIGN_CENTER);
        eposPrinter.addTextSize(1, 1);
        eposPrinter.addText(institutionName + '\n');
        eposPrinter.addText(new Date().toLocaleString('id-ID') + '\n\n');
        eposPrinter.addTextSize(2, 2);
        eposPrinter.addText((ticketData.service ? ticketData.service.name.toUpperCase() : 'LAYANAN') + '\n\n');
        eposPrinter.addTextSize(4, 4);
        eposPrinter.addTextStyle(false, false, true, eposPrinter.COLOR_1);
        eposPrinter.addText(ticketData.ticket_number + '\n');
        eposPrinter.addTextStyle(false, false, false, eposPrinter.COLOR_1);
        eposPrinter.addTextSize(1, 1);
        eposPrinter.addText('\nNama: ' + (ticketData.visitor_name || '-') + '\n');
        eposPrinter.addText('Harap tunggu di ruang tunggu.\n\n\n\n');
        eposPrinter.addCut(eposPrinter.CUT_FEED);
        eposPrinter.send();
    }
</script>
@endpush
