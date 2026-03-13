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
    config(['kiosk.kiosk_password' => bcrypt('test-pass')]);

    $response = post(route('kiosk.authenticate'), [
        'password' => 'test-pass',
    ]);

    $response->assertRedirect(route('kiosk.index'))
        ->assertSessionHas('kiosk_authenticated', true);

    expect(session()->has('kiosk_authenticated_at'))->toBeTrue();
});

it('rejects wrong kiosk password', function () {
    config(['kiosk.kiosk_password' => bcrypt('correct-pass')]);

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

it('blocks access when timestamp exists but authenticated flag is false', function () {
    $response = withSession([
        'kiosk_authenticated' => false,
        'kiosk_authenticated_at' => now()->timestamp,
    ])->get('/kiosk');

    $response->assertRedirect('/kiosk/login');
});

it('middleware module password kiosk blocks unauthenticated access', function () {
    $response = get('/kiosk');

    $response->assertRedirect('/kiosk/login');
});
