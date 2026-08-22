<?php

use Illuminate\Support\Facades\Storage;

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

    $response->assertRedirect('/tv-legacy/login');
});

it('returns browser provider fallback when google tts is not configured', function () {
    $response = withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ])->getJson(route('tv-display.tts.announcement', ['text' => 'Nomor A-001 ke Loket 1']));

    $response->assertOk()
        ->assertJson([
            'provider' => 'browser',
        ]);
});

it('serves cached audio payload for authenticated tv legacy session', function () {
    Storage::fake('public');

    config([
        'services.google_tts.cache_disk' => 'public',
        'services.google_tts.cache_prefix' => 'tts/google',
    ]);

    $cacheKey = str_repeat('a', 40);
    $audioPayload = 'ID3FAKE-AUDIO-PAYLOAD';
    Storage::disk('public')->put('tts/google/'.$cacheKey.'.mp3', $audioPayload);

    $response = withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ])->get(route('tv-display.tts.audio', ['cacheKey' => $cacheKey]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'audio/mpeg')
        ->assertHeader('Content-Length', (string) strlen($audioPayload));

    expect($response->getContent())->toBe($audioPayload);
});

it('allows tv legacy video audio after samsung sound activation', function () {
    $response = withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ])->get(route('tv-display.legacy'));

    $response->assertOk()
        ->assertSee('tvDebugPanel', false)
        ->assertSee('function tvDebug', false)
        ->assertSee('tts audio play failed', false)
        ->assertSee('queuedAnnouncementCall', false)
        ->assertSee('announcement deferred until sound activation', false)
        ->assertSee('announcement replay after sound activation', false)
        ->assertSee('clearTtsAudioSource', false)
        ->assertSee('tts direct audio selected', false)
        ->assertSee('var isSamsungTv', false)
        ->assertSee('var isLgTv', false)
        ->assertSee('if (isSamsungTv)', false)
        ->assertSee('shouldIsolateTtsAudio', false)
        ->assertSee('return false;', false)
        ->assertSee('playTtsWithWebAudio', false)
        ->assertSee('tts web audio started', false)
        ->assertSee('audioPlayer.src = SILENT_AUDIO', false)
        ->assertSee('audioPlayer.volume = isLgTv ? 0 : TTS_VOLUME', false)
        ->assertDontSee("audioPlayer.src = ''", false)
        ->assertSee('beginTtsPlayback', false)
        ->assertSee('object-fit: contain', false)
        ->assertSee('tvPlayer.muted = !soundActivated', false)
        ->assertSee('tvPlayer.volume = soundActivated ? volume : 0', false)
        ->assertDontSee("tvPlayer.removeAttribute('src')", false);
});

// ── Legacy TV Login & PIN Authentication ────────────────────────────────────

it('renders tv display legacy login page with PIN and on-screen keypad', function () {
    $response = get(route('tv-display.legacy.login'));

    $response->assertOk()
        ->assertSee('Monitor Antrian')
        ->assertSee('PIN TV Display')
        ->assertSee('Masukkan PIN TV Display...')
        ->assertSee('Papan Tombol PIN')
        ->assertSee('tv-numpad-btn', false);
});

it('logs in to legacy tv display with correct PIN', function () {
    config(['kiosk.tv_display_password' => bcrypt('654321')]);

    $response = post(route('tv-display.legacy.authenticate'), [
        'password' => '654321',
    ]);

    $response->assertRedirect(route('tv-display.legacy'))
        ->assertSessionHas('tv_display_authenticated', true);
});

it('rejects wrong PIN on legacy tv display login', function () {
    config(['kiosk.tv_display_password' => bcrypt('654321')]);

    $response = from(route('tv-display.legacy.login'))
        ->post(route('tv-display.legacy.authenticate'), [
            'password' => '000000',
        ]);

    $response->assertRedirect(route('tv-display.legacy.login'))
        ->assertSessionHasErrors(['password']);
});
