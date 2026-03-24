<?php

namespace App\Services\Tts;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MiniMaxTtsService
{
    /**
     * @return array{cache_key:string,path:string}|null
     */
    public function getOrCreateAnnouncement(string $text): ?array
    {
        $text = Str::squish($text);
        if ($text === '') {
            return null;
        }

        $apiKey = (string) config('services.minimax.api_key');
        $voiceId = (string) config('services.minimax.voice_id');
        if ($apiKey === '' || $voiceId === '') {
            return null;
        }

        $model = (string) config('services.minimax.model', 'speech-2.8-hd');
        $disk = (string) config('services.minimax.cache_disk', 'public');
        $prefix = trim((string) config('services.minimax.cache_prefix', 'tts/minimax'), '/');
        $cacheKey = sha1(implode('|', [$voiceId, $model, mb_strtolower($text)]));
        $path = $prefix.'/'.$cacheKey.'.mp3';

        if (! Storage::disk($disk)->exists($path)) {
            $audio = $this->requestSpeech($apiKey, $voiceId, $model, $text);
            Storage::disk($disk)->put($path, $audio);
        }

        return [
            'cache_key' => $cacheKey,
            'path' => $path,
        ];
    }

    public function cachePathFromKey(string $cacheKey): string
    {
        $prefix = trim((string) config('services.minimax.cache_prefix', 'tts/minimax'), '/');

        return $prefix.'/'.$cacheKey.'.mp3';
    }

    private function requestSpeech(string $apiKey, string $voiceId, string $model, string $text): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
        ])
            ->asJson()
            ->timeout(30)
            ->post('https://api.minimax.io/v1/t2a_v2', [
                'model' => $model,
                'text' => $text,
                'stream' => false,
                'voice_setting' => [
                    'voice_id' => $voiceId,
                    'speed' => (float) config('services.minimax.speed', 1.0),
                    'vol' => (float) config('services.minimax.vol', 1.0),
                    'pitch' => (int) config('services.minimax.pitch', 0),
                ],
                'audio_setting' => [
                    'sample_rate' => 32000,
                    'bitrate' => 128000,
                    'format' => 'mp3',
                    'channel' => 1,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal membuat audio TTS dari MiniMax.');
        }

        $data = $response->json('data.audio');
        if (! is_string($data) || $data === '') {
            throw new RuntimeException('Respons audio MiniMax kosong.');
        }

        $binary = hex2bin($data);
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Gagal decode hex audio dari MiniMax.');
        }

        return $binary;
    }
}
