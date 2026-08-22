<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('institution.name') }} - Antrian Legacy</title>
    
    <!-- Metronic 8 CSS (Bootstrap 5 based) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700&display=swap" />

    {{-- Lightweight critical CSS — avoid loading 1.85MB Metronic bundles when not needed --}}
    @if(request()->is('tv-legacy') || request()->is('tv-legacy/*'))
        <style>
            *{box-sizing:border-box}
            body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;overflow-x:hidden}
            .d-flex{display:flex}.d-none{display:none!important}.d-block{display:block}
            .align-items-center{align-items:center}.justify-content-center{justify-content:center}.justify-content-between{justify-content:space-between}
            .flex-column{flex-direction:column}.flex-wrap{flex-wrap:wrap}.flex-root{flex:1 1 auto}
            .flex-row-fluid{flex:1 1 0}.flex-column-fluid{flex:1 1 auto}
            .gap-2{gap:.5rem}.gap-3{gap:.75rem}.gap-3\.5{gap:.875rem}.gap-4{gap:1rem}.text-center{text-align:center}.text-end{text-align:right}
            .overflow-hidden{overflow:hidden}.w-100{width:100%}.min-w-0{min-width:0}
            .p-8{padding:2rem}.py-6{padding-top:1.5rem;padding-bottom:1.5rem}.py-10{padding-top:10px;padding-bottom:10px}
            .pt-10{padding-top:2.5rem}.pb-10{padding-bottom:2.5rem}
            .px-3{padding-left:.75rem;padding-right:.75rem}.px-4{padding-left:1rem;padding-right:1rem}
            .mb-1{margin-bottom:.25rem}.mb-3{margin-bottom:.75rem}.mb-4{margin-bottom:1rem}
            .mb-6{margin-bottom:1.5rem}.me-1{margin-right:.25rem}.me-2{margin-right:.5rem}.me-3{margin-right:.75rem}
            .mt-0.5{margin-top:.125rem}.mt-1{margin-top:.25rem}.mt-2{margin-top:.5rem}
            .mt-6{margin-top:1.5rem}.my-auto{margin-top:auto;margin-bottom:auto}.py-2{padding:.5rem 0}.py-12{padding:3rem 0}
            .px-3.5{padding-left:.875rem;padding-right:.875rem}.py-1\.5{padding-top:.375rem;padding-bottom:.375rem}
            .p-lg-12{padding:3rem}.fs-1{font-size:1.75rem}.fs-2{font-size:1.5rem}.fs-2x{font-size:2rem}
            .fs-3{font-size:1.35rem}.fs-4{font-size:1.15rem}.fs-5{font-size:1rem}.fs-6{font-size:.9rem}
            .fs-7{font-size:.85rem}.fs-8{font-size:.75rem}.fs-2hx{font-size:1.6rem}
            .fw-bold{font-weight:700}.fw-boldest{font-weight:800}.fw-semibold{font-weight:600}
            .text-uppercase{text-transform:uppercase}.text-muted{color:#6c757d}.text-white{color:#fff}.text-white-50{color:rgba(255,255,255,.7)}
            .text-primary{color:#009ef7}.bg-light-primary{background:#f1faff}
            .bg-light-success{background:#e8fff3}.text-success{color:#50cd89}
            .border{border:1px solid #e4e6ef}.border-2{border-width:2px}
            .border-bottom{border-bottom:1px solid #e4e6ef}
            .border-slate-200{border-color:#e2e8f0}.border-primary-subtle{border-color:#c2e9ff}
            .border-success-subtle{border-color:#c9f7d5}
            .rounded-3{border-radius:.625rem}.rounded-circle{border-radius:50%}.rounded-pill{border-radius:50rem}
            .shadow-sm{box-shadow:0 .125rem .25rem rgba(0,0,0,.075)}
            .card{border:1px solid #e4e6ef;border-radius:.625rem;background:#fff}
            .card-body{padding:1.5rem}.badge{display:inline-block;padding:.35em .65em;font-size:.75em;font-weight:700;line-height:1;text-align:center;white-space:nowrap;vertical-align:baseline;border-radius:50rem}
            .bg-light{background:#f5f8fa}.btn{display:inline-block;font-weight:500;text-align:center;vertical-align:middle;cursor:pointer;border:1px solid transparent;padding:.5rem 1rem;border-radius:.475rem}
            .btn-primary{color:#fff;background:#009ef7;border-color:#009ef7}
            .form-control{display:block;width:100%;padding:.5rem .75rem;font-size:1rem;line-height:1.5;color:#181c32;background:#fff;border:1px solid #e4e6ef;border-radius:.475rem}
            .container-xxl{width:100%;max-width:1320px;margin:0 auto;padding:0 1rem}
            .page{display:flex;flex-direction:row;flex:1}.wrapper{display:flex;flex-direction:column;flex:1}
        </style>
    @else
        <link href="{{ asset('metronic-assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('metronic-assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    @endif

    <style>
        :root {
            --bs-font-sans-serif: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }
        body { -webkit-font-smoothing: antialiased; letter-spacing: -0.01em; overflow-x: hidden; }
        .glass-card { background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: none !important; }
        .rounded-30px { border-radius: 30px !important; }
        .rounded-40px { border-radius: 40px !important; }
        .rounded-50px { border-radius: 50px !important; }
        .ls-n1 { letter-spacing: -1px !important; }
        .ls-n2 { letter-spacing: -2px !important; }
        .ls-n3 { letter-spacing: -3px !important; }
        .btn-active-scale:active { transform: scale(0.96); transition: transform 0.1s ease; }
        .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
    @stack('styles')
</head>
<body id="kt_body" class="bg-body">
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        @hasSection('full-screen')
            <!-- Full screen layout without containers -->
            @yield('content')
        @else
            <!--begin::Page-->
            <div class="page d-flex flex-row flex-column-fluid">
                <!--begin::Wrapper-->
                <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                    <!--begin::Container-->
                    <div class="container-xxl d-flex flex-column flex-column-fluid pt-10 pb-10">
                        @yield('content')
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Page-->
        @endif
    </div>
    <!--end::Root-->

    @if(request()->is('tv-legacy') || request()->is('tv-legacy/*'))
        {{-- Lightweight: fetch-based, no jQuery/Metronic for TV legacy --}}
        <script>window.TV_CSRF_TOKEN='{{ csrf_token() }}';</script>
    @else
        <script src="{{ asset('metronic-assets/plugins/global/plugins.bundle.js') }}"></script>
        <script src="{{ asset('metronic-assets/js/scripts.bundle.js') }}"></script>
        <script>
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
