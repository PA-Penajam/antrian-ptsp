<?php

use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\withSession;

it('redirects unauthenticated request to kiosk login', function () {
    $response = get(route('kiosk.index'));

    $response->assertRedirect(route('kiosk.login'));
});

it('shows kiosk login page', function () {
    $response = get(route('kiosk.login'));

    $response->assertOk()
        ->assertSee('Selamat Datang')
        ->assertSee('Antrian PTSP Pengadilan Agama Penajam');
});

it('logs in with correct password', function () {
    $response = post(route('kiosk.authenticate'), [
        'password' => config('kiosk.password'),
    ]);

    $response->assertRedirect(route('kiosk.index'))
        ->assertSessionHas('kiosk_authenticated', true);

    expect(session()->has('kiosk_authenticated_at'))->toBeTrue();
});

it('rejects wrong kiosk password', function () {
    $response = from(route('kiosk.login'))->post(route('kiosk.authenticate'), [
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('kiosk.login'))
        ->assertSessionHasErrors(['password']);

    expect(session()->has('kiosk_authenticated'))->toBeFalse();
});

it('logs out and clears kiosk session', function () {
    $response = withSession([
        'kiosk_authenticated' => true,
        'kiosk_authenticated_at' => now()->timestamp,
    ])->post(route('kiosk.logout'));

    $response->assertRedirect(route('kiosk.login'))
        ->assertSessionMissing('kiosk_authenticated')
        ->assertSessionMissing('kiosk_authenticated_at');
});

it('middleware module password kiosk blocks unauthenticated access', function () {
    $response = get('/kiosk');

    $response->assertRedirect('/kiosk/login');
});
