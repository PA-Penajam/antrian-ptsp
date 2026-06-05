<?php

namespace App\Http\Controllers\Api;

use App\Enums\QueueStatus;
use App\Http\Controllers\Controller;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Endpoint read-only untuk dashboard survei (aplikasi terpisah).
 * Mengembalikan jumlah tiket berstatus 'completed' (tamu selesai dilayani)
 * per tanggal layanan. Hanya agregat — tanpa data pribadi pengunjung.
 *
 * Autentikasi: shared secret via header X-Api-Key (dibaca dari env
 * SURVEY_DASHBOARD_API_KEY agar endpoint self-contained tanpa mengubah
 * file config lain).
 */
class ServedCountController extends Controller
{
    private const MAX_RANGE_DAYS = 92;

    public function index(Request $request): JsonResponse
    {
        $expected = (string) env('SURVEY_DASHBOARD_API_KEY', '');
        $provided = (string) $request->header('X-Api-Key', '');

        // Tolak bila kunci server belum dikonfigurasi atau tidak cocok.
        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'start' => ['nullable', 'date_format:Y-m-d'],
            'end' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $start = $validated['start'] ?? now()->toDateString();
        $end = $validated['end'] ?? now()->toDateString();

        if ($start > $end) {
            return response()->json(['message' => 'Rentang tanggal tidak valid'], 422);
        }

        if (Carbon::parse($start)->diffInDays(Carbon::parse($end)) > self::MAX_RANGE_DAYS) {
            return response()->json(['message' => 'Rentang tanggal maksimal 92 hari'], 422);
        }

        $rows = QueueTicket::query()
            ->where('status', QueueStatus::Completed->value)
            ->whereBetween('service_date', [$start, $end])
            ->selectRaw('service_date, COUNT(*) as served')
            ->groupBy('service_date')
            ->orderBy('service_date')
            ->get();

        $data = $rows->map(static fn ($row) => [
            'date' => Carbon::parse($row->service_date)->toDateString(),
            'served' => (int) $row->served,
        ])->all();

        return response()->json([
            'data' => $data,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
