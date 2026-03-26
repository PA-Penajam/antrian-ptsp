@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    body {
        margin: 0;
        padding: 0;
    }
    .login-root {
        min-height: 100vh;
        background: url('/metronic-assets/media/auth/bg10.jpeg') center/cover no-repeat fixed;
    }
    .login-overlay {
        min-height: 100vh;
        background: linear-gradient(to bottom, rgba(15,15,35,0.08) 0%, rgba(10,10,28,0.18) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .login-card {
        border-radius: 32px !important;
        border: none !important;
        box-shadow: 0 40px 80px rgba(0,0,0,0.35) !important;
        background: #fff;
        width: 100%;
        max-width: 520px;
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
</style>
@endpush

@section('content')
<div class="login-root">
    <div class="login-overlay">
        <div class="login-card card">
            <div class="card-body p-14">

                <div class="text-center mb-12">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-6"
                         style="width:88px;height:88px;background:linear-gradient(135deg,#009EF7,#0095E8);box-shadow:0 16px 40px rgba(0,158,247,0.35);">
                        <i class="fa-solid fa-lock text-white" style="font-size:36px;"></i>
                    </div>

                    @if(config('institution.logo_path'))
                        <img alt="Logo" src="{{ Storage::url(config('institution.logo_path')) }}"
                             style="height:48px;object-fit:contain;display:block;margin:0 auto 16px;">
                    @else
                        <img alt="Logo" src="{{ asset('metronic-assets/media/logos/logo-papenajam.webp') }}"
                             style="height:48px;object-fit:contain;display:block;margin:0 auto 16px;">
                    @endif

                    <h1 class="fw-boldest fs-2x text-gray-900 mb-3" style="letter-spacing:-1px;">
                        Selamat Datang
                    </h1>
                    <p class="text-gray-400 fs-3 fw-semibold mb-0">
                        {{ config('institution.name') }}
                    </p>
                </div>

                @if($errors->has('password'))
                    <div class="rounded-3 fw-semibold fs-5 mb-8 p-5"
                         style="background:#fff5f8;border:1px solid #f1416c;color:#f1416c;">
                        <i class="fa-solid fa-circle-xmark me-2"></i>
                        {{ $errors->first('password') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('kiosk.legacy.authenticate') }}">
                    @csrf
                    <div class="mb-8">
                        <label class="fs-4 fw-bold text-gray-700 mb-3 d-block text-uppercase"
                               style="letter-spacing:0.5px;">
                            Password Kiosk
                        </label>
                        <input type="password"
                               name="password"
                               class="form-control kiosk-input"
                               placeholder="Masukkan password..."
                               autofocus
                               autocomplete="current-password">
                    </div>

                    <button type="submit"
                            class="btn btn-primary btn-lg w-100 fs-2 fw-boldest py-5 rounded-pill shadow">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>
                        Masuk ke Kiosk
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
