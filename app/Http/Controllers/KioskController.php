<?php

namespace App\Http\Controllers;

use App\Enums\ModuleSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KioskController extends Controller
{
    public function showLogin(): View
    {
        return view('pages.kiosk.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if ($request->input('password') !== config('kiosk.password')) {
            return back()->withErrors([
                'password' => 'Error: Password yang dimasukkan salah.',
            ]);
        }

        session([
            ModuleSession::KioskAuthenticated->value => true,
            ModuleSession::KioskAuthenticatedAt->value => now()->timestamp,
        ]);

        return redirect()->route('kiosk.index');
    }

    public function logout(): RedirectResponse
    {
        session()->forget([ModuleSession::KioskAuthenticated->value, ModuleSession::KioskAuthenticatedAt->value]);

        return redirect()->route('kiosk.login');
    }

    public function index(): View
    {
        return view('pages.kiosk.index');
    }
}
