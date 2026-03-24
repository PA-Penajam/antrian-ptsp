<?php

use App\Services\Tts\MiniMaxTtsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('returns null when text is empty', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => 'test-voice',
    ]);

    $service = new MiniMaxTtsService;

    expect($service->getOrCreateAnnouncement(''))->toBeNull()
        ->and($service->getOrCreateAnnouncement('   '))->toBeNull();
});

it('returns null when api key is missing', function () {
    config([
        'services.minimax.api_key' => '',
        'services.minimax.voice_id' => 'test-voice',
    ]);

    $service = new MiniMaxTtsService;

    expect($service->getOrCreateAnnouncement('Nomor A-001 ke Loket 1'))->toBeNull();
});

it('returns null when voice id is missing', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => '',
    ]);

    $service = new MiniMaxTtsService;

    expect($service->getOrCreateAnnouncement('Nomor A-001 ke Loket 1'))->toBeNull();
});

it('returns cached result when audio already exists', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => 'test-voice',
        'services.minimax.model' => 'speech-2.8-hd',
        'services.minimax.cache_disk' => 'public',
        'services.minimax.cache_prefix' => 'tts/minimax',
    ]);

    $text = 'Nomor A-001 ke Loket 1';
    $cacheKey = sha1(implode('|', ['test-voice', 'speech-2.8-hd', mb_strtolower($text)]));
    $path = 'tts/minimax/'.$cacheKey.'.mp3';

    Storage::disk('public')->put($path, 'fake-audio-data');

    Http::shouldNotHaveBeenDispatched();

    $service = new MiniMaxTtsService;
    $result = $service->getOrCreateAnnouncement($text);

    expect($result)
        ->not->toBeNull()
        ->and($result['cache_key'])->toBe($cacheKey)
        ->and($result['path'])->toBe($path);
});

it('calls minimax api and caches the audio', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => 'test-voice',
        'services.minimax.model' => 'speech-2.8-hd',
        'services.minimax.cache_disk' => 'public',
        'services.minimax.cache_prefix' => 'tts/minimax',
        'services.minimax.speed' => 1.0,
        'services.minimax.vol' => 1.0,
        'services.minimax.pitch' => 0,
    ]);

    $fakeAudioHex = bin2hex('fake-mp3-audio-binary-data');

    Http::fake([
        'api.minimax.io/v1/t2a_v2' => Http::response([
            'data' => [
                'audio' => $fakeAudioHex,
            ],
        ], 200),
    ]);

    $service = new MiniMaxTtsService;
    $text = 'Nomor A-001 ke Loket 1';
    $result = $service->getOrCreateAnnouncement($text);

    $cacheKey = sha1(implode('|', ['test-voice', 'speech-2.8-hd', mb_strtolower($text)]));

    expect($result)
        ->not->toBeNull()
        ->and($result['cache_key'])->toBe($cacheKey)
        ->and($result['path'])->toBe('tts/minimax/'.$cacheKey.'.mp3');

    Storage::disk('public')->assertExists($result['path']);
});

it('throws exception when api call fails', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => 'test-voice',
        'services.minimax.model' => 'speech-2.8-hd',
        'services.minimax.cache_disk' => 'public',
        'services.minimax.cache_prefix' => 'tts/minimax',
    ]);

    Http::fake([
        'api.minimax.io/v1/t2a_v2' => Http::response('Server Error', 500),
    ]);

    $service = new MiniMaxTtsService;
    $service->getOrCreateAnnouncement('Nomor A-001 ke Loket 1');
})->throws(RuntimeException::class, 'Gagal membuat audio TTS dari MiniMax.');

it('builds correct cache path from key', function () {
    config(['services.minimax.cache_prefix' => 'tts/minimax']);

    $service = new MiniMaxTtsService;
    $path = $service->cachePathFromKey('abc123def456');

    expect($path)->toBe('tts/minimax/abc123def456.mp3');
});
