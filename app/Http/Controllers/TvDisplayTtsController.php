<?php

namespace App\Http\Controllers;

use App\Services\Tts\MiniMaxTtsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TvDisplayTtsController extends Controller
{
    public function announcement(Request $request, MiniMaxTtsService $ttsService): JsonResponse
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
            'provider' => 'minimax',
            'cache_key' => $announcement['cache_key'],
            'audio_url' => route('tv-display.tts.audio', ['cacheKey' => $announcement['cache_key']]),
        ]);
    }

    public function audio(string $cacheKey, MiniMaxTtsService $ttsService): Response
    {
        abort_unless(preg_match('/^[a-f0-9]{40}$/', $cacheKey) === 1, 404);

        $path = $ttsService->cachePathFromKey($cacheKey);
        $disk = (string) config('services.minimax.cache_disk', 'public');

        abort_unless(Storage::disk($disk)->exists($path), 404);

        $content = Storage::disk($disk)->get($path);
        abort_unless(is_string($content) && $content !== '', 404);

        return response($content, 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Length' => (string) strlen($content),
            'Content-Disposition' => 'inline; filename="announcement-'.$cacheKey.'.mp3"',
        ]);
    }
}
