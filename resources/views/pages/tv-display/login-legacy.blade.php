@extends('layouts.legacy')

@section('full-screen', true)

@push('styles')
<style>
    body {
        margin: 0;
        padding: 0;
        background: #0f172a;
    }
    .tv-login-root {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .tv-login-card {
        border-radius: 32px !important;
        border: 1px solid rgba(255,255,255,0.08) !important;
        background: #1e293b !important;
        box-shadow: 0 40px 80px rgba(0,0,0,0.5) !important;
        width: 100%;
        max-width: 520px;
    }
    .tv-input {
        background-color: rgba(255,255,255,0.06) !important;
        border: 2px solid rgba(255,255,255,0.12) !important;
        border-radius: 16px !important;
        font-size: 1.35rem !important;
        padding: 1.2rem 1.75rem !important;
        height: auto !important;
        font-weight: 600;
        color: #f1f5f9 !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .tv-input:focus {
        border-color: #3b82f6 !important;
        background-color: rgba(255,255,255,0.1) !important;
        box-shadow: 0 0 0 4px rgba(59,130,246,0.15) !important;
        outline: none;
    }
    .tv-input::placeholder { color: rgba(148,163,184,0.5) !important; }
</style>
@endpush

@section('content')
<div class="tv-login-root">
    <div class="tv-login-card card">
        <div class="card-body p-14">

            <div class="text-center mb-12">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-6"
                     style="width:88px;height:88px;background:linear-gradient(135deg,#1d4ed8,#2563eb);box-shadow:0 16px 40px rgba(37,99,235,0.45);">
                    <i class="fa-solid fa-display text-white" style="font-size:36px;"></i>
                </div>

                <h1 class="fw-boldest fs-2x mb-3 text-white" style="letter-spacing:-1px;">
                    Monitor Antrian
                </h1>
                <p class="fs-3 fw-semibold mb-0" style="color:rgba(148,163,184,0.8);">
                    TV Display &mdash; {{ config('institution.name') }}
                </p>
            </div>

            @if($errors->has('password'))
                <div class="rounded-3 fw-semibold fs-5 mb-8 p-5"
                     style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#fca5a5;">
                    <i class="fa-solid fa-circle-xmark me-2"></i>
                    {{ $errors->first('password') }}
                </div>
            @endif

            <form method="POST" action="{{ route('tv-display.legacy.authenticate') }}">
                @csrf
                <div class="mb-8">
                    <label class="fs-4 fw-bold mb-3 d-block text-uppercase"
                           style="color:rgba(148,163,184,0.9);letter-spacing:0.5px;">
                        Password TV Display
                    </label>
                    <input type="password"
                           name="password"
                           class="form-control tv-input"
                           placeholder="Masukkan password..."
                           autofocus
                           autocomplete="current-password">
                </div>

                <button type="submit"
                        class="btn btn-lg w-100 fs-2 fw-boldest py-5 rounded-pill"
                        style="background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;border:none;box-shadow:0 12px 32px rgba(37,99,235,0.4);">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Masuk ke TV Display
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
