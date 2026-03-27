<?php

namespace App\Http\Controllers;

use App\Actions\Queue\CheckPrinterConnectivity;
use App\Actions\Queue\CreateQueueTicket;
use App\Actions\Queue\PrintTicketToEposPrinter;
use App\Enums\ModuleSession;
use App\Models\AppSetting;
use App\Models\Service;
use App\Models\Wilayah;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
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

        $hashedPassword = (string) config('kiosk.kiosk_password');

        if (! $hashedPassword || ! Hash::check($request->input('password'), $hashedPassword)) {
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

    public function showLoginLegacy(): View
    {
        return view('pages.kiosk.login-legacy');
    }

    public function loginLegacy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $hashedPassword = (string) config('kiosk.kiosk_password');

        if (! $hashedPassword || ! Hash::check($request->input('password'), $hashedPassword)) {
            return back()->withErrors([
                'password' => 'Error: Password yang dimasukkan salah.',
            ]);
        }

        session([
            ModuleSession::KioskAuthenticated->value => true,
            ModuleSession::KioskAuthenticatedAt->value => now()->timestamp,
        ]);

        return redirect()->route('kiosk.legacy');
    }

    public function legacy(): View
    {
        $services = Service::active()
            ->where('walk_in_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedKabupatenKode = AppSetting::getValue('wilayah.scope.kabupaten_kode');

        // Jika kabupaten belum dipilih, return collection kosong
        // untuk mencegah loading semua desa/kelurahan di Indonesia
        if ($selectedKabupatenKode === null) {
            $wilayahOptions = collect();
        } else {
            $wilayahOptions = Wilayah::query()
                ->whereRaw('LENGTH(kode) = 13')
                ->where('kode', 'like', "{$selectedKabupatenKode}.%")
                ->orderBy('nama')
                ->get();
        }

        return view('pages.kiosk.legacy', compact('services', 'wilayahOptions'));
    }

    public function printLegacy(
        Request $request,
        CreateQueueTicket $createQueueTicket,
        PrintTicketToEposPrinter $printAction,
    ): JsonResponse {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'visitor_name' => ['required', 'string', 'min:3', 'max:255'],
            'visitor_identifier' => ['required', 'string', 'max:50'],
            'visitor_phone' => ['required', 'string', 'max:20'],
            'visitor_wilayah_kode' => ['required', 'string', 'exists:wilayah,kode'],
        ]);

        $ticket = $createQueueTicket->handle([
            'service_id' => $validated['service_id'],
            'channel' => 'walk_in_kiosk',
            'service_date' => CarbonImmutable::today(),
            'visitor_name' => $validated['visitor_name'],
            'visitor_identifier' => $validated['visitor_identifier'] ?: null,
            'visitor_phone' => $validated['visitor_phone'] ?: null,
            'visitor_wilayah_kode' => $validated['visitor_wilayah_kode'],
            'notes' => null,
            'created_by' => null,
        ]);

        $ticket->load('service');
        $printed = $printAction->handle($ticket);

        return response()->json([
            'success' => true,
            'ticket' => [
                'ticket_number' => $ticket->ticket_number,
                'service' => ['name' => $ticket->service?->name],
            ],
            'printed' => $printed,
        ]);
    }

    public function printerStatusLegacy(CheckPrinterConnectivity $checker): JsonResponse
    {
        $enabled = (bool) config('services.thermal_printer.enabled');
        $result = $checker->handle();

        if (! $enabled) {
            $status = 'disabled';
        } elseif ($result['connected']) {
            $status = 'connected';
        } else {
            $status = 'disconnected';
        }

        return response()->json([
            'status' => $status,
            'ip' => (string) config('services.thermal_printer.ip'),
            'port' => (int) config('services.thermal_printer.port'),
            'checked_at' => now()->toIso8601String(),
            'error' => $result['error'],
        ]);
    }
}
