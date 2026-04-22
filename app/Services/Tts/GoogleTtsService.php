<?php

namespace App\Services\Tts;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleTtsService
{
    /**
     * @return array{cache_key:string,path:string}|null
     */
    public function getOrCreateAnnouncement(string $text): ?array
    {
        $text = Str::squish($text);
        if ($text === '') {
            Log::debug('[TTS] Text kosong, skip');

            return null;
        }

        $apiKey = (string) config('services.google_tts.api_key');
        $languageCode = (string) config('services.google_tts.language_code', 'id-ID');
        $voiceName = (string) config('services.google_tts.voice_name', 'id-ID-Wavenet-A');

        if ($apiKey === '') {
            Log::warning('[TTS] Google TTS API key kosong');

            return null;
        }

        $disk = (string) config('services.google_tts.cache_disk', 'public');
        $prefix = trim((string) config('services.google_tts.cache_prefix', 'tts/google'), '/');
        $cacheKey = sha1(implode('|', [$languageCode, $voiceName, mb_strtolower($text)]));
        $path = $prefix.'/'.$cacheKey.'.mp3';

        Log::debug('[TTS] Processing', [
            'text' => $text,
            'cache_key' => $cacheKey,
            'language_code' => $languageCode,
            'voice_name' => $voiceName,
            'disk' => $disk,
        ]);

        if (! Storage::disk($disk)->exists($path)) {
            Log::info('[TTS] Cache miss, request Google TTS API', ['text' => $text]);
            $audio = $this->requestSpeech($apiKey, $languageCode, $voiceName, $text);
            Storage::disk($disk)->put($path, $audio);
            Log::info('[TTS] Audio saved', ['path' => $path, 'audio_size' => strlen($audio)]);
        } else {
            Log::debug('[TTS] Cache hit', ['path' => $path]);
        }

        return [
            'cache_key' => $cacheKey,
            'path' => $path,
        ];
    }

    public function cachePathFromKey(string $cacheKey): string
    {
        $prefix = trim((string) config('services.google_tts.cache_prefix', 'tts/google'), '/');

        return $prefix.'/'.$cacheKey.'.mp3';
    }

    private function requestSpeech(string $apiKey, string $languageCode, string $voiceName, string $text): string
    {
        $speakingRate = (float) config('services.google_tts.speaking_rate', 1.0);
        $pitch = (float) config('services.google_tts.pitch', 0.0);
        $volumeGainDb = (float) config('services.google_tts.volume_gain_db', 0.0);

        $response = Http::timeout(30)
            ->post('https://texttospeech.googleapis.com/v1/text:synthesize?key='.$apiKey, [
                'input' => [
                    'text' => $text,
                ],
                'voice' => [
                    'languageCode' => $languageCode,
                    'name' => $voiceName,
                ],
                'audioConfig' => [
                    'audioEncoding' => 'MP3',
                    'speakingRate' => $speakingRate,
                    'pitch' => $pitch,
                    'volumeGainDb' => $volumeGainDb,
                ],
            ]);

        if (! $response->successful()) {
            Log::error('[TTS] Google TTS HTTP request gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gagal membuat audio TTS dari Google TTS. ['.$response->status().']');
        }

        $audioContent = $response->json('audioContent');
        if (! is_string($audioContent) || $audioContent === '') {
            throw new RuntimeException('Respons audio Google TTS kosong.');
        }

        $binary = base64_decode($audioContent, true);
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Gagal decode base64 audio dari Google TTS.');
        }

        return $binary;
    }
}
