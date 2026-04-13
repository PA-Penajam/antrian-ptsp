<?php

namespace App\Services\Tts;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            Log::debug('[TTS] Text kosong, skip');

            return null;
        }

        $apiKey = (string) config('services.minimax.api_key');
        $voiceId = (string) config('services.minimax.voice_id');
        if ($apiKey === '' || $voiceId === '') {
            Log::warning('[TTS] API key atau voice ID kosong', [
                'api_key_set' => $apiKey !== '',
                'voice_id' => $voiceId,
            ]);

            return null;
        }

        $model = (string) config('services.minimax.model', 'speech-2.8-hd');
        $disk = (string) config('services.minimax.cache_disk', 'public');
        $prefix = trim((string) config('services.minimax.cache_prefix', 'tts/minimax'), '/');
        $cacheKey = sha1(implode('|', [$voiceId, $model, mb_strtolower($text)]));
        $path = $prefix.'/'.$cacheKey.'.mp3';

        Log::debug('[TTS] Processing', [
            'text' => $text,
            'cache_key' => $cacheKey,
            'model' => $model,
            'voice_id' => $voiceId,
            'disk' => $disk,
        ]);

        if ($this->cacheNeedsRefresh($disk, $path)) {
            Log::info('[TTS] Cache miss, request speech API', ['text' => $text, 'strategy' => config('services.minimax.strategy')]);
            $audio = $this->requestSpeech($apiKey, $voiceId, $model, $text);
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
        $prefix = trim((string) config('services.minimax.cache_prefix', 'tts/minimax'), '/');

        return $prefix.'/'.$cacheKey.'.mp3';
    }

    private function requestSpeech(string $apiKey, string $voiceId, string $model, string $text): string
    {
        $strategy = strtolower((string) config('services.minimax.strategy', 'async'));

        return match ($strategy) {
            'sync' => $this->requestSpeechSync($apiKey, $voiceId, $model, $text),
            'auto' => $this->requestSpeechAuto($apiKey, $voiceId, $model, $text),
            default => $this->requestSpeechAsync($apiKey, $voiceId, $model, $text),
        };
    }

    private function requestSpeechAuto(string $apiKey, string $voiceId, string $model, string $text): string
    {
        try {
            return $this->requestSpeechAsync($apiKey, $voiceId, $model, $text);
        } catch (RuntimeException $asyncException) {
            try {
                return $this->requestSpeechSync($apiKey, $voiceId, $model, $text);
            } catch (RuntimeException $syncException) {
                throw new RuntimeException(
                    'Gagal membuat audio TTS MiniMax (async gagal: '.$asyncException->getMessage().'; sync gagal: '.$syncException->getMessage().').',
                    0,
                    $syncException,
                );
            }
        }
    }

    private function requestSpeechSync(string $apiKey, string $voiceId, string $model, string $text): string
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
                'output_format' => 'hex',
                'language_boost' => (string) config('services.minimax.language_boost', 'auto'),
                'voice_setting' => $this->voiceSetting($voiceId),
                'audio_setting' => [
                    'sample_rate' => 44100,
                    'bitrate' => 128000,
                    'format' => 'mp3',
                    'channel' => 1,
                ],
            ]);

        $this->ensureSuccessfulApiResponse($response, 'Gagal membuat audio TTS dari MiniMax.');

        $data = $response->json('data.audio');
        if (! is_string($data) || $data === '') {
            throw new RuntimeException('Respons audio MiniMax (sync) kosong.');
        }

        $binary = hex2bin($data);
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Gagal decode hex audio dari MiniMax (sync).');
        }

        return $binary;
    }

    private function requestSpeechAsync(string $apiKey, string $voiceId, string $model, string $text): string
    {
        $createTaskResponse = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
        ])
            ->asJson()
            ->timeout(30)
            ->post('https://api.minimax.io/v1/t2a_async_v2', [
                'model' => $model,
                'text' => $text,
                'language_boost' => (string) config('services.minimax.language_boost', 'auto'),
                'voice_setting' => $this->voiceSetting($voiceId),
                'audio_setting' => [
                    'audio_sample_rate' => 44100,
                    'bitrate' => 128000,
                    'format' => 'mp3',
                    'channel' => 1,
                ],
            ]);

        $this->ensureSuccessfulApiResponse($createTaskResponse, 'Gagal membuat task TTS MiniMax (async).');

        $taskId = $createTaskResponse->json('task_id');
        if (! is_string($taskId) && ! is_int($taskId)) {
            throw new RuntimeException('Task ID TTS MiniMax (async) tidak ditemukan.');
        }

        $maxAttempts = max((int) config('services.minimax.async_poll_attempts', 12), 1);
        $pollIntervalMs = max((int) config('services.minimax.async_poll_interval_ms', 500), 100);

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $statusResponse = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
            ])
                ->asJson()
                ->timeout(30)
                ->get('https://api.minimax.io/v1/query/t2a_async_query_v2', [
                    'task_id' => (string) $taskId,
                ]);

            $this->ensureSuccessfulApiResponse($statusResponse, 'Gagal mengambil status task TTS MiniMax (async).');

            $status = strtolower((string) $statusResponse->json('status', ''));
            if ($status === 'success') {
                $fileId = $statusResponse->json('file_id');
                if (! is_string($fileId) && ! is_int($fileId)) {
                    throw new RuntimeException('File ID TTS MiniMax (async) tidak tersedia.');
                }

                return $this->downloadAsyncAudio($apiKey, (string) $fileId);
            }

            if ($status === 'failed' || $status === 'expired') {
                throw new RuntimeException('Task TTS MiniMax (async) gagal dengan status: '.$status.'.');
            }

            if ($attempt < $maxAttempts) {
                usleep($pollIntervalMs * 1000);
            }
        }

        throw new RuntimeException('Task TTS MiniMax (async) melewati batas waktu polling.');
    }

    private function downloadAsyncAudio(string $apiKey, string $fileId): string
    {
        $metadataResponse = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
        ])
            ->asJson()
            ->timeout(30)
            ->get('https://api.minimax.io/v1/files/retrieve', [
                'file_id' => $fileId,
            ]);

        if ($metadataResponse->successful() && (int) $metadataResponse->json('base_resp.status_code', 0) === 0) {
            $downloadUrl = $metadataResponse->json('file.download_url');

            if (is_string($downloadUrl) && $downloadUrl !== '') {
                $downloadResponse = Http::timeout(60)->get($downloadUrl);
                $body = $downloadResponse->body();

                if ($downloadResponse->successful() && $body !== '') {
                    return $this->extractAudioPayload($body);
                }
            }
        }

        $fallbackResponse = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
        ])
            ->timeout(60)
            ->get('https://api.minimax.io/v1/files/retrieve_content', [
                'file_id' => $fileId,
            ]);

        $fallbackBody = $fallbackResponse->body();
        if (! $fallbackResponse->successful() || $fallbackBody === '') {
            throw new RuntimeException('Gagal mengunduh audio dari MiniMax (async).');
        }

        return $this->extractAudioPayload($fallbackBody);
    }

    private function voiceSetting(string $voiceId): array
    {
        return [
            'voice_id' => $voiceId,
            'speed' => (float) config('services.minimax.speed', 1.0),
            'vol' => (float) config('services.minimax.vol', 1.0),
            'pitch' => (float) config('services.minimax.pitch', 0),
        ];
    }

    private function ensureSuccessfulApiResponse(Response $response, string $failureMessage): void
    {
        if (! $response->successful()) {
            Log::error('[TTS] HTTP request gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException($failureMessage);
        }

        $statusCode = $response->json('base_resp.status_code');
        if (is_numeric($statusCode) && (int) $statusCode !== 0) {
            $statusMessage = (string) $response->json('base_resp.status_msg', 'unknown_error');
            Log::error('[TTS] MiniMax API error', [
                'status_code' => $statusCode,
                'status_msg' => $statusMessage,
            ]);

            throw new RuntimeException($failureMessage.' ['.$statusCode.': '.$statusMessage.']');
        }
    }

    private function cacheNeedsRefresh(string $disk, string $path): bool
    {
        if (! Storage::disk($disk)->exists($path)) {
            return true;
        }

        $cachedPayload = Storage::disk($disk)->get($path);

        return $cachedPayload === '' || $this->isTarArchivePayload($cachedPayload);
    }

    private function extractAudioPayload(string $payload): string
    {
        if (! $this->isTarArchivePayload($payload)) {
            return $payload;
        }

        return $this->extractMp3FromTarPayload($payload);
    }

    private function isTarArchivePayload(string $payload): bool
    {
        if (strlen($payload) < 265) {
            return false;
        }

        return str_starts_with(substr($payload, 257, 5), 'ustar');
    }

    private function extractMp3FromTarPayload(string $payload): string
    {
        $offset = 0;
        $payloadLength = strlen($payload);
        $emptyBlock = str_repeat("\0", 512);

        while ($offset + 512 <= $payloadLength) {
            $header = substr($payload, $offset, 512);

            if ($header === $emptyBlock) {
                break;
            }

            $name = rtrim(substr($header, 0, 100), "\0 ");
            $sizeOctal = trim(substr($header, 124, 12));
            $size = $sizeOctal === '' ? 0 : octdec($sizeOctal);
            $dataOffset = $offset + 512;

            if ($size < 0 || ($dataOffset + $size) > $payloadLength) {
                break;
            }

            if ($name !== '' && str_ends_with(strtolower($name), '.mp3')) {
                $audio = substr($payload, $dataOffset, $size);

                if ($audio !== '') {
                    return $audio;
                }
            }

            $dataBlocks = (int) ceil($size / 512);
            $offset = $dataOffset + ($dataBlocks * 512);
        }

        throw new RuntimeException('Gagal mengekstrak file MP3 dari arsip MiniMax.');
    }
}
