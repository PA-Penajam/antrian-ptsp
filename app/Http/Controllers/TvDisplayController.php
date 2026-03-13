<?php

namespace App\Http\Controllers;

use App\Enums\ModuleSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
}
