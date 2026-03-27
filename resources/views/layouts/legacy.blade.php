<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('institution.name') }} - Antrian Legacy</title>
    
    <!-- Metronic 8 CSS (Bootstrap 5 based) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('metronic-assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('metronic-assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    
    <style>
        /* System Font Priority - Lightweight & Modern */
        :root {
            --bs-font-sans-serif: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --modern-navy: #0f172a;
            --modern-blue: #1e293b;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
            overflow-x: hidden;
        }

        /* Modern Glass Card (RGBA Only - No Blur for Performance) */
        .glass-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: none !important; /* Force disable blur for legacy compatibility */
        }

        /* Chunky Rounding System */
        .rounded-30px { border-radius: 30px !important; }
        .rounded-40px { border-radius: 40px !important; }
        .rounded-50px { border-radius: 50px !important; }
        
        /* Modern Typography Utilities */
        .ls-n1 { letter-spacing: -1px !important; }
        .ls-n2 { letter-spacing: -2px !important; }
        .ls-n3 { letter-spacing: -3px !important; }
        
        /* Tactile Feedback for Touchscreens */
        .btn-active-scale:active {
            transform: scale(0.96);
            transition: transform 0.1s ease;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Custom Scrollbar for Legacy */
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

    <!-- Metronic 8 JS Bundle (includes jQuery, Bootstrap 5, Popper) -->
    <script src="{{ asset('metronic-assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('metronic-assets/js/scripts.bundle.js') }}"></script>

    <script>
        // Setup CSRF token for jQuery AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
