<?php

namespace App\Http\Controllers;

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
                'password' => 'Password salah.',
            ]);
        }

        session([
            'kiosk_authenticated' => true,
            'kiosk_authenticated_at' => now()->timestamp,
        ]);

        return redirect()->route('kiosk.index');
    }

    public function logout(): RedirectResponse
    {
        session()->forget(['kiosk_authenticated', 'kiosk_authenticated_at']);

        return redirect()->route('kiosk.login');
    }

    public function index(): View
    {
        return view('pages.kiosk.index');
    }
}
