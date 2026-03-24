<?php

use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;
use function Pest\Laravel\withSession;

it('redirects unauthenticated request to tv-display login', function () {
    $response = get(route('tv-display.index'));

    $response->assertRedirect(route('tv-display.login'));
});

it('shows tv-display login page', function () {
    $response = get(route('tv-display.login'));

    $response->assertOk()
        ->assertSee('Monitor Antrian PTSP')
        ->assertSee('Masuk untuk membuka tampilan TV Display antrian Pengadilan Agama Penajam.');
});

it('logs in with correct password', function () {
    config(['kiosk.tv_display_password' => bcrypt('test-tv-pass')]);

    $response = post(route('tv-display.authenticate'), [
        'password' => 'test-tv-pass',
    ]);

    $response->assertRedirect(route('tv-display.index'))
        ->assertSessionHas('tv_display_authenticated', true);

    expect(session()->has('tv_display_authenticated_at'))->toBeTrue();
});

it('rejects wrong tv-display password', function () {
    config(['kiosk.tv_display_password' => bcrypt('correct-pass')]);

    $response = from(route('tv-display.login'))->post(route('tv-display.authenticate'), [
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect(route('tv-display.login'))
        ->assertSessionHasErrors(['password']);

    expect(session()->has('tv_display_authenticated'))->toBeFalse();
});

it('logs out and clears tv-display session', function () {
    $response = withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ])->post(route('tv-display.logout'));

    $response->assertRedirect(route('tv-display.login'))
        ->assertSessionMissing('tv_display_authenticated')
        ->assertSessionMissing('tv_display_authenticated_at');
});

it('middleware module password tv-display blocks unauthenticated access', function () {
    $response = get('/tv-display');

    $response->assertRedirect('/tv-display/login');
});

it('blocks unauthenticated access to tv-display tts endpoint', function () {
    $response = getJson(route('tv-display.tts.announcement', ['text' => 'Nomor A-001 ke Loket 1']));

    $response->assertRedirect('/tv-display/login');
});

it('returns browser provider fallback when minimax is not configured', function () {
    $response = withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ])->getJson(route('tv-display.tts.announcement', ['text' => 'Nomor A-001 ke Loket 1']));

    $response->assertOk()
        ->assertJson([
            'provider' => 'browser',
        ]);
});
