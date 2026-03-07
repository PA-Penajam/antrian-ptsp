<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Register test routes with middleware
    Route::get('/test-kiosk-protected', fn () => 'ok')
        ->middleware('module.password:kiosk');

    Route::get('/test-tv-display-protected', fn () => 'ok')
        ->middleware('module.password:tv-display');
});

it('redirects unauthenticated kiosk request to kiosk login', function () {
    $response = $this->get('/test-kiosk-protected');

    $response->assertRedirect('/kiosk/login');
});

it('passes through with valid kiosk session', function () {
    $this->withSession([
        'kiosk_authenticated' => true,
        'kiosk_authenticated_at' => now()->timestamp,
    ]);

    $response = $this->get('/test-kiosk-protected');

    $response->assertOk();
});

it('redirects expired kiosk session to kiosk login', function () {
    $expiredTimestamp = now()->subMinutes(1441)->timestamp;

    $this->withSession([
        'kiosk_authenticated' => true,
        'kiosk_authenticated_at' => $expiredTimestamp,
    ]);

    $response = $this->get('/test-kiosk-protected');

    $response->assertRedirect('/kiosk/login');
});

it('redirects unauthenticated tv-display request to tv-display login', function () {
    $response = $this->get('/test-tv-display-protected');

    $response->assertRedirect('/tv-display/login');
});

it('passes through with valid tv-display session', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = $this->get('/test-tv-display-protected');

    $response->assertOk();
});

it('redirects expired tv-display session to tv-display login', function () {
    $expiredTimestamp = now()->subMinutes(1441)->timestamp;

    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => $expiredTimestamp,
    ]);

    $response = $this->get('/test-tv-display-protected');

    $response->assertRedirect('/tv-display/login');
});

it('uses configured session lifetime from config', function () {
    // Set custom session lifetime of 30 minutes
    config(['kiosk.session_lifetime' => 30]);

    // Session authenticated 29 minutes ago (should pass)
    $this->withSession([
        'kiosk_authenticated' => true,
        'kiosk_authenticated_at' => now()->subMinutes(29)->timestamp,
    ]);

    $response = $this->get('/test-kiosk-protected');
    $response->assertOk();

    // Session authenticated 31 minutes ago (should redirect)
    $this->withSession([
        'kiosk_authenticated' => true,
        'kiosk_authenticated_at' => now()->subMinutes(31)->timestamp,
    ]);

    $response = $this->get('/test-kiosk-protected');
    $response->assertRedirect('/kiosk/login');
});
