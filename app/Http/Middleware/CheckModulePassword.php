<?php

namespace App\Http\Middleware;

use App\Enums\ModuleSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $sessionKey = self::resolveSessionKey($module);
        $timestampKey = self::resolveTimestampKey($module);

        $isAuthenticated = session($sessionKey, false);
        $authenticatedAt = session($timestampKey);
        $sessionLifetimeSeconds = config('kiosk.session_lifetime', 1440) * 60;

        if (! $isAuthenticated || ! $authenticatedAt || (now()->timestamp - $authenticatedAt) >= $sessionLifetimeSeconds) {
            session()->forget([$sessionKey, $timestampKey]);

            return redirect()->to(self::resolveLoginUrl($module));
        }

        return $next($request);
    }

    /**
     * Resolve session key autentikasi berdasarkan nama modul.
     */
    private static function resolveLoginUrl(string $module): string
    {
        return match ($module) {
            'kiosk-legacy' => '/kiosk-legacy/login',
            'tv-legacy' => '/tv-legacy/login',
            default => '/'.$module.'/login',
        };
    }

    private static function resolveSessionKey(string $module): string
    {
        return match ($module) {
            'kiosk', 'kiosk-legacy' => ModuleSession::KioskAuthenticated->value,
            'tv-display', 'tv-legacy' => ModuleSession::TvDisplayAuthenticated->value,
            default => str_replace('-', '_', $module).'_authenticated',
        };
    }

    /**
     * Resolve session key timestamp berdasarkan nama modul.
     */
    private static function resolveTimestampKey(string $module): string
    {
        return match ($module) {
            'kiosk', 'kiosk-legacy' => ModuleSession::KioskAuthenticatedAt->value,
            'tv-display', 'tv-legacy' => ModuleSession::TvDisplayAuthenticatedAt->value,
            default => str_replace('-', '_', $module).'_authenticated_at',
        };
    }
}
