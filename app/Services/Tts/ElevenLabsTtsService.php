<?php

namespace App\Services\Tts;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ElevenLabsTtsService
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

        $apiKey = (string) config('services.elevenlabs.api_key');
        $voiceId = (string) config('services.elevenlabs.voice_id');
        if ($apiKey === '' || $voiceId === '') {
            return null;
        }

        $model = (string) config('services.elevenlabs.model', 'eleven_turbo_v2_5');
        $disk = (string) config('services.elevenlabs.cache_disk', 'public');
        $prefix = trim((string) config('services.elevenlabs.cache_prefix', 'tts/elevenlabs'), '/');
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
        $prefix = trim((string) config('services.elevenlabs.cache_prefix', 'tts/elevenlabs'), '/');

        return $prefix.'/'.$cacheKey.'.mp3';
    }

    private function requestSpeech(string $apiKey, string $voiceId, string $model, string $text): string
    {
        $response = Http::withHeaders([
            'xi-api-key' => $apiKey,
            'Accept' => 'audio/mpeg',
        ])
            ->asJson()
            ->timeout(30)
            ->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'text' => $text,
                'model_id' => $model,
                'voice_settings' => [
                    'stability' => (float) config('services.elevenlabs.stability', 0.45),
                    'similarity_boost' => (float) config('services.elevenlabs.similarity_boost', 0.8),
                    'style' => (float) config('services.elevenlabs.style', 0.2),
                    'use_speaker_boost' => (bool) config('services.elevenlabs.use_speaker_boost', true),
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal membuat audio TTS dari ElevenLabs.');
        }

        $body = $response->body();
        if ($body === '') {
            throw new RuntimeException('Respons ElevenLabs kosong.');
        }

        return $body;
    }
}
