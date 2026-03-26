<?php

use App\Services\Tts\MiniMaxTtsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function buildTarPayloadWithSingleFile(string $entryName, string $content): string
{
    $header = str_repeat("\0", 512);
    $normalizedEntryName = substr($entryName, 0, 100);
    $contentLength = strlen($content);

    $header = substr_replace($header, $normalizedEntryName, 0, strlen($normalizedEntryName));
    $header = substr_replace($header, str_pad('644', 7, '0', STR_PAD_LEFT)."\0", 100, 8);
    $header = substr_replace($header, str_pad('0', 7, '0', STR_PAD_LEFT)."\0", 108, 8);
    $header = substr_replace($header, str_pad('0', 7, '0', STR_PAD_LEFT)."\0", 116, 8);
    $header = substr_replace($header, str_pad(decoct($contentLength), 11, '0', STR_PAD_LEFT)."\0", 124, 12);
    $header = substr_replace($header, str_pad(decoct(time()), 11, '0', STR_PAD_LEFT)."\0", 136, 12);
    $header = substr_replace($header, str_repeat(' ', 8), 148, 8);
    $header = substr_replace($header, '0', 156, 1);
    $header = substr_replace($header, 'ustar', 257, 5);
    $header = substr_replace($header, '00', 263, 2);

    $checksum = 0;
    for ($index = 0; $index < 512; $index++) {
        $checksum += ord($header[$index]);
    }

    $header = substr_replace($header, str_pad(decoct($checksum), 6, '0', STR_PAD_LEFT)."\0 ", 148, 8);

    $paddingLength = (512 - ($contentLength % 512)) % 512;
    $padding = str_repeat("\0", $paddingLength);

    return $header.$content.$padding.str_repeat("\0", 1024);
}

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

    $service = new MiniMaxTtsService;
    $result = $service->getOrCreateAnnouncement($text);

    Http::assertNothingSent();

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
        'services.minimax.strategy' => 'sync',
        'services.minimax.language_boost' => 'auto',
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

    expect(Storage::disk('public')->exists($result['path']))->toBeTrue();
});

it('calls minimax async api and caches the audio', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => 'test-voice',
        'services.minimax.model' => 'speech-2.8-hd',
        'services.minimax.strategy' => 'async',
        'services.minimax.language_boost' => 'auto',
        'services.minimax.cache_disk' => 'public',
        'services.minimax.cache_prefix' => 'tts/minimax',
        'services.minimax.speed' => 1.0,
        'services.minimax.vol' => 1.0,
        'services.minimax.pitch' => 0,
        'services.minimax.async_poll_attempts' => 3,
        'services.minimax.async_poll_interval_ms' => 100,
    ]);

    $expectedAudio = 'fake-mp3-audio-binary-data';
    $tarPayload = buildTarPayloadWithSingleFile('archive/content.mp3', $expectedAudio);

    Http::fake([
        'api.minimax.io/v1/t2a_async_v2' => Http::response([
            'task_id' => 12345,
            'base_resp' => [
                'status_code' => 0,
                'status_msg' => 'success',
            ],
        ], 200),
        'api.minimax.io/v1/query/t2a_async_query_v2*' => Http::response([
            'status' => 'Success',
            'file_id' => 67890,
            'base_resp' => [
                'status_code' => 0,
                'status_msg' => 'success',
            ],
        ], 200),
        'api.minimax.io/v1/files/retrieve*' => Http::response([
            'file' => [
                'download_url' => 'https://files.minimax.test/audio.mp3',
            ],
            'base_resp' => [
                'status_code' => 0,
                'status_msg' => 'success',
            ],
        ], 200),
        'files.minimax.test/audio.mp3' => Http::response($tarPayload, 200),
    ]);

    $service = new MiniMaxTtsService;
    $text = 'Nomor A-001 ke Loket 1';
    $result = $service->getOrCreateAnnouncement($text);

    $cacheKey = sha1(implode('|', ['test-voice', 'speech-2.8-hd', mb_strtolower($text)]));

    expect($result)
        ->not->toBeNull()
        ->and($result['cache_key'])->toBe($cacheKey)
        ->and($result['path'])->toBe('tts/minimax/'.$cacheKey.'.mp3');

    expect(Storage::disk('public')->exists($result['path']))->toBeTrue();
    expect(Storage::disk('public')->get($result['path']))->toBe($expectedAudio);
});

it('refreshes cached tar payload before returning cached announcement', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => 'test-voice',
        'services.minimax.model' => 'speech-2.8-hd',
        'services.minimax.strategy' => 'sync',
        'services.minimax.language_boost' => 'auto',
        'services.minimax.cache_disk' => 'public',
        'services.minimax.cache_prefix' => 'tts/minimax',
    ]);

    $text = 'Nomor A-001 ke Loket 1';
    $cacheKey = sha1(implode('|', ['test-voice', 'speech-2.8-hd', mb_strtolower($text)]));
    $path = 'tts/minimax/'.$cacheKey.'.mp3';

    $staleTarPayload = buildTarPayloadWithSingleFile('archive/stale.mp3', 'old-audio-data');
    Storage::disk('public')->put($path, $staleTarPayload);

    $freshAudio = 'fresh-audio-data';
    Http::fake([
        'api.minimax.io/v1/t2a_v2' => Http::response([
            'data' => [
                'audio' => bin2hex($freshAudio),
            ],
            'base_resp' => [
                'status_code' => 0,
                'status_msg' => 'success',
            ],
        ], 200),
    ]);

    $service = new MiniMaxTtsService;
    $result = $service->getOrCreateAnnouncement($text);

    expect($result)
        ->not->toBeNull()
        ->and($result['cache_key'])->toBe($cacheKey)
        ->and($result['path'])->toBe($path);

    expect(Storage::disk('public')->get($path))->toBe($freshAudio);
    Http::assertSentCount(1);
});

it('falls back to sync when strategy is auto and async call fails', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => 'test-voice',
        'services.minimax.model' => 'speech-2.8-hd',
        'services.minimax.strategy' => 'auto',
        'services.minimax.language_boost' => 'auto',
        'services.minimax.cache_disk' => 'public',
        'services.minimax.cache_prefix' => 'tts/minimax',
        'services.minimax.speed' => 1.0,
        'services.minimax.vol' => 1.0,
        'services.minimax.pitch' => 0,
    ]);

    $fakeAudioHex = bin2hex('fallback-sync-audio-data');

    Http::fake([
        'api.minimax.io/v1/t2a_async_v2' => Http::response('Server Error', 500),
        'api.minimax.io/v1/t2a_v2' => Http::response([
            'data' => [
                'audio' => $fakeAudioHex,
            ],
            'base_resp' => [
                'status_code' => 0,
                'status_msg' => 'success',
            ],
        ], 200),
    ]);

    $service = new MiniMaxTtsService;
    $result = $service->getOrCreateAnnouncement('Nomor A-001 ke Loket 1');

    expect($result)->not->toBeNull();
    expect(Storage::disk('public')->exists($result['path']))->toBeTrue();
});

it('throws exception when api call fails', function () {
    config([
        'services.minimax.api_key' => 'test-key',
        'services.minimax.voice_id' => 'test-voice',
        'services.minimax.model' => 'speech-2.8-hd',
        'services.minimax.strategy' => 'sync',
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
