<?php

use App\Services\Tts\GoogleTtsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('returns null when text is empty', function () {
    config([
        'services.google_tts.api_key' => 'test-key',
        'services.google_tts.language_code' => 'id-ID',
        'services.google_tts.voice_name' => 'id-ID-Wavenet-A',
    ]);

    $service = new GoogleTtsService;

    expect($service->getOrCreateAnnouncement(''))->toBeNull()
        ->and($service->getOrCreateAnnouncement('   '))->toBeNull();
});

it('returns null when api key is missing', function () {
    config([
        'services.google_tts.api_key' => '',
        'services.google_tts.language_code' => 'id-ID',
        'services.google_tts.voice_name' => 'id-ID-Wavenet-A',
    ]);

    $service = new GoogleTtsService;

    expect($service->getOrCreateAnnouncement('Nomor A-001 ke Loket 1'))->toBeNull();
});

it('returns cached result when audio already exists', function () {
    config([
        'services.google_tts.api_key' => 'test-key',
        'services.google_tts.language_code' => 'id-ID',
        'services.google_tts.voice_name' => 'id-ID-Wavenet-A',
        'services.google_tts.cache_disk' => 'public',
        'services.google_tts.cache_prefix' => 'tts/google',
    ]);

    $text = 'Nomor A-001 ke Loket 1';
    $cacheKey = sha1(implode('|', ['id-ID', 'id-ID-Wavenet-A', mb_strtolower($text)]));
    $path = 'tts/google/'.$cacheKey.'.mp3';

    Storage::disk('public')->put($path, 'fake-audio-data');

    $service = new GoogleTtsService;
    $result = $service->getOrCreateAnnouncement($text);

    Http::assertNothingSent();

    expect($result)
        ->not->toBeNull()
        ->and($result['cache_key'])->toBe($cacheKey)
        ->and($result['path'])->toBe($path);
});

it('returns legacy voice cached result before calling api', function () {
    config([
        'services.google_tts.api_key' => 'test-key',
        'services.google_tts.language_code' => 'id-ID',
        'services.google_tts.voice_name' => 'id-ID-Wavenet-A',
        'services.google_tts.legacy_voice_names' => ['id-ID-Standard-D'],
        'services.google_tts.cache_disk' => 'public',
        'services.google_tts.cache_prefix' => 'tts/google',
    ]);

    $text = 'Nomor antrian A7, silakan menuju Loket Layanan Informasi dan Pengaduan.';
    $cacheKey = sha1(implode('|', ['id-ID', 'id-ID-Standard-D', mb_strtolower($text)]));
    $path = 'tts/google/'.$cacheKey.'.mp3';

    Storage::disk('public')->put($path, 'legacy-fake-audio-data');

    $service = new GoogleTtsService;
    $result = $service->getOrCreateAnnouncement($text);

    Http::assertNothingSent();

    expect($result)
        ->not->toBeNull()
        ->and($result['cache_key'])->toBe($cacheKey)
        ->and($result['path'])->toBe($path);
});

it('calls google tts api and caches the audio', function () {
    config([
        'services.google_tts.api_key' => 'test-key',
        'services.google_tts.language_code' => 'id-ID',
        'services.google_tts.voice_name' => 'id-ID-Wavenet-A',
        'services.google_tts.speaking_rate' => 1.0,
        'services.google_tts.pitch' => 0.0,
        'services.google_tts.volume_gain_db' => 0.0,
        'services.google_tts.cache_disk' => 'public',
        'services.google_tts.cache_prefix' => 'tts/google',
    ]);

    $fakeAudioBase64 = base64_encode('fake-mp3-audio-binary-data');

    Http::fake([
        'texttospeech.googleapis.com/*' => Http::response([
            'audioContent' => $fakeAudioBase64,
        ], 200),
    ]);

    $service = new GoogleTtsService;
    $text = 'Nomor A-001 ke Loket 1';
    $result = $service->getOrCreateAnnouncement($text);

    $cacheKey = sha1(implode('|', ['id-ID', 'id-ID-Wavenet-A', mb_strtolower($text)]));

    expect($result)
        ->not->toBeNull()
        ->and($result['cache_key'])->toBe($cacheKey)
        ->and($result['path'])->toBe('tts/google/'.$cacheKey.'.mp3');

    expect(Storage::disk('public')->exists($result['path']))->toBeTrue();
    expect(Storage::disk('public')->get($result['path']))->toBe('fake-mp3-audio-binary-data');
});

it('throws exception when api call fails', function () {
    config([
        'services.google_tts.api_key' => 'test-key',
        'services.google_tts.language_code' => 'id-ID',
        'services.google_tts.voice_name' => 'id-ID-Wavenet-A',
        'services.google_tts.cache_disk' => 'public',
        'services.google_tts.cache_prefix' => 'tts/google',
    ]);

    Http::fake([
        'texttospeech.googleapis.com/*' => Http::response('Server Error', 500),
    ]);

    $service = new GoogleTtsService;
    $service->getOrCreateAnnouncement('Nomor A-001 ke Loket 1');
})->throws(RuntimeException::class, 'Gagal membuat audio TTS dari Google TTS.');

it('builds correct cache path from key', function () {
    config(['services.google_tts.cache_prefix' => 'tts/google']);

    $service = new GoogleTtsService;
    $path = $service->cachePathFromKey('abc123def456');

    expect($path)->toBe('tts/google/abc123def456.mp3');
});
