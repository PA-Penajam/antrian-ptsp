<?php

namespace App\Support\Reports;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanBulananReportBuilder
{
    private const CHANNEL_LABELS = [
        'online_booking' => 'Online Booking',
        'walk_in_kiosk' => 'Kiosk Mandiri',
        'assisted_same_day' => 'Dibantu Petugas',
    ];

    /**
     * Membangun dataset laporan bulanan.
     *
     * @return array{
     *     judul_bulan: string,
     *     ringkasan: array{total: int, completed: int, waiting: int, cancelled: int},
     *     per_layanan: array<int, array{id: int, name: string, total: int, completed: int, cancelled: int}>,
     *     per_hari: array<int, array{date: string, hari: int, nama_hari: string, total: int, online: int, kiosk: int, assisted: int}>,
     *     per_channel: array<int, array{channel: string, total: int, persen: float}>,
     *     detail_pengunjung: array<int, array{no: int, nama: string, alamat: string, layanan: string}>,
     * }
     */
    public function build(int $bulan, int $tahun): array
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->toDateString();
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $baseQuery = QueueTicket::query()
            ->whereBetween('service_date', [$startDate, $endDate]);

        $cancelledStatuses = [QueueStatus::Cancelled->value, QueueStatus::Skipped->value];

        $ringkasan = [
            'total' => (clone $baseQuery)->count(),
            'completed' => (clone $baseQuery)
                ->where('status', QueueStatus::Completed)
                ->count(),
            'waiting' => (clone $baseQuery)
                ->whereIn('status', [
                    QueueStatus::Booked,
                    QueueStatus::Waiting,
                    QueueStatus::Called,
                ])
                ->count(),
            'cancelled' => (clone $baseQuery)
                ->whereIn('status', $cancelledStatuses)
                ->count(),
        ];

        $perLayanan = (clone $baseQuery)
            ->join('services', 'queue_tickets.service_id', '=', 'services.id')
            ->select([
                'services.id',
                'services.name',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN queue_tickets.status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN queue_tickets.status IN ('cancelled', 'skipped') THEN 1 ELSE 0 END) as cancelled"),
            ])
            ->groupBy('services.id', 'services.name')
            ->orderBy('services.name')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'total' => (int) $row->total,
                'completed' => (int) $row->completed,
                'cancelled' => (int) $row->cancelled,
            ])
            ->values()
            ->all();

        $perHariRaw = (clone $baseQuery)
            ->select([
                DB::raw('DATE(service_date) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN channel = 'online_booking' THEN 1 ELSE 0 END) as online"),
                DB::raw("SUM(CASE WHEN channel = 'walk_in_kiosk' THEN 1 ELSE 0 END) as kiosk"),
                DB::raw("SUM(CASE WHEN channel = 'assisted_same_day' THEN 1 ELSE 0 END) as assisted"),
            ])
            ->groupByRaw('DATE(service_date)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $perHari = [];
        $periodStart = Carbon::create($tahun, $bulan, 1);
        $periodEnd = $periodStart->copy()->addMonth(); // exclusive — first day of next month

        $period = new \DatePeriod(
            $periodStart,
            new \DateInterval('P1D'),
            $periodEnd,
        );

        foreach ($period as $day) {
            $date = $day->format('Y-m-d');
            /** @var object{total?:int, online?:int, kiosk?:int, assisted?:int}|null $dayData */
            $dayData = $perHariRaw->get($date);

            $perHari[] = [
                'date' => $date,
                'hari' => (int) $day->format('d'),
                'nama_hari' => Carbon::parse($date)->locale('id')->isoFormat('ddd'),
                'total' => $dayData ? (int) $dayData->total : 0,
                'online' => $dayData ? (int) $dayData->online : 0,
                'kiosk' => $dayData ? (int) $dayData->kiosk : 0,
                'assisted' => $dayData ? (int) $dayData->assisted : 0,
            ];
        }

        $perChannel = (clone $baseQuery)
            ->select([
                'channel',
                DB::raw('COUNT(*) as total'),
            ])
            ->whereIn('channel', array_keys(self::CHANNEL_LABELS))
            ->groupBy('channel')
            ->orderByDesc('total')
            ->get()
            ->keyBy('channel');

        $grandTotal = max((int) $ringkasan['total'], 1);
        $perChannelFormatted = [];
        foreach (self::CHANNEL_LABELS as $key => $label) {
            $jumlah = (int) ($perChannel[$key]->total ?? 0);
            $perChannelFormatted[] = [
                'channel' => $label,
                'total' => $jumlah,
                'persen' => round(($jumlah / $grandTotal) * 100, 1),
            ];
        }

        $detailPengunjung = (clone $baseQuery)
            ->with(['service', 'wilayah'])
            ->orderBy('service_date')
            ->orderBy('ticket_number')
            ->get()
            ->values()
            ->map(fn (QueueTicket $ticket, int $index): array => [
                'no' => $index + 1,
                'nama' => $ticket->visitor_name ?: '-',
                'alamat' => $ticket->wilayah?->nama ?: 'Tidak tersedia',
                'layanan' => $ticket->service?->name ?: 'Tidak tersedia',
            ])
            ->values()
            ->all();

        return [
            'judul_bulan' => Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y'),
            'ringkasan' => $ringkasan,
            'per_layanan' => $perLayanan,
            'per_hari' => $perHari,
            'per_channel' => $perChannelFormatted,
            'detail_pengunjung' => $detailPengunjung,
        ];
    }
}
