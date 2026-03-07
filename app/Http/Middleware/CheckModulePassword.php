<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        // Convert module name for session key: tv-display → tv_display
        $sessionKey = str_replace('-', '_', $module).'_authenticated';
        $timestampKey = str_replace('-', '_', $module).'_authenticated_at';

        $authenticatedAt = session($timestampKey);
        $sessionLifetimeSeconds = config('kiosk.session_lifetime', 1440) * 60;

        if (! $authenticatedAt || (now()->timestamp - $authenticatedAt) >= $sessionLifetimeSeconds) {
            return redirect()->to('/'.$module.'/login');
        }

        return $next($request);
    }
}
