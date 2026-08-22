<?php

namespace App\Http\Controllers;

use App\Enums\ModuleSession;
use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TvDisplayController extends Controller
{
    public function showLogin(): View
    {
        return view('pages.tv-display.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $hashedPassword = (string) config('kiosk.tv_display_password');

        if (! $hashedPassword || ! Hash::check($request->input('password'), $hashedPassword)) {
            return back()->withErrors([
                'password' => 'Password salah.',
            ]);
        }

        session([
            ModuleSession::TvDisplayAuthenticated->value => true,
            ModuleSession::TvDisplayAuthenticatedAt->value => now()->timestamp,
        ]);

        return redirect()->route('tv-display.index');
    }

    public function logout(): RedirectResponse
    {
        session()->forget([ModuleSession::TvDisplayAuthenticated->value, ModuleSession::TvDisplayAuthenticatedAt->value]);

        return redirect()->route('tv-display.login');
    }

    public function index(): View
    {
        return view('pages.tv-display.index');
    }

    public function showLoginLegacy(): View
    {
        return view('pages.tv-display.login-legacy');
    }

    public function loginLegacy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $hashedPassword = (string) config('kiosk.tv_display_password');

        if (! $hashedPassword || ! Hash::check($request->input('password'), $hashedPassword)) {
            return back()->withErrors([
                'password' => 'PIN salah.',
            ]);
        }

        session([
            ModuleSession::TvDisplayAuthenticated->value => true,
            ModuleSession::TvDisplayAuthenticatedAt->value => now()->timestamp,
        ]);

        return redirect()->route('tv-display.legacy');
    }

    public function legacy(): View
    {
        return view('pages.tv-display.legacy');
    }

    public function apiState(): JsonResponse
    {
        try {
            $currentCalls = QueueTicket::query()
                ->with(['counter', 'service'])
                ->where('status', QueueStatus::Called)
                ->whereDate('service_date', today())
                ->orderByDesc('called_at')
                ->limit(6)
                ->get();

            $recentCalls = QueueTicket::query()
                ->with(['counter', 'service'])
                ->whereDate('service_date', today())
                ->whereNotNull('called_at')
                ->orderByDesc('called_at')
                ->limit(4)
                ->get();

            $videos = Cache::remember('tv-display:videos', 60, function (): array {
                $files = Storage::disk('public')->files('videos');
                $allowed = ['mp4', 'webm', 'ogg'];

                return collect($files)
                    ->filter(fn (string $file): bool => in_array(
                        strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                        $allowed,
                        true,
                    ))
                    ->map(fn (string $file): string => asset('storage/'.$file))
                    ->sort()
                    ->values()
                    ->all();
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'currentCalls' => $currentCalls->toArray(),
                    'recentCalls' => $recentCalls->toArray(),
                    'videos' => $videos,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'currentCalls' => [],
                    'recentCalls' => [],
                    'videos' => [],
                ],
            ]);
        }
    }
}
