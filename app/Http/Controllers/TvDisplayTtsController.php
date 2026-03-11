<?php

namespace App\Http\Controllers;

use App\Services\Tts\ElevenLabsTtsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TvDisplayTtsController extends Controller
{
    public function announcement(Request $request, ElevenLabsTtsService $ttsService): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:200'],
        ]);

        try {
            $announcement = $ttsService->getOrCreateAnnouncement($validated['text']);
        } catch (Throwable) {
            return response()->json([
                'provider' => 'browser',
            ], 200);
        }

        if (! $announcement) {
            return response()->json([
                'provider' => 'browser',
            ], 200);
        }

        return response()->json([
            'provider' => 'elevenlabs',
            'cache_key' => $announcement['cache_key'],
            'audio_url' => route('tv-display.tts.audio', ['cacheKey' => $announcement['cache_key']]),
        ]);
    }

    public function audio(string $cacheKey, ElevenLabsTtsService $ttsService): StreamedResponse
    {
        abort_unless(preg_match('/^[a-f0-9]{40}$/', $cacheKey) === 1, 404);

        $path = $ttsService->cachePathFromKey($cacheKey);
        $disk = (string) config('services.elevenlabs.cache_disk', 'public');

        abort_unless(Storage::disk($disk)->exists($path), 404);

        $stream = Storage::disk($disk)->readStream($path);
        abort_unless($stream !== false, 404);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
