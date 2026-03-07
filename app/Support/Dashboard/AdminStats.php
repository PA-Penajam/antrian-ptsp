<?php

namespace App\Support\Dashboard;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminStats
{
    /**
     * @return array{
     *     booking_success_today:int,
     *     booking_failed_today:int,
     *     tickets_created_today:int,
     *     tickets_cancelled_today:int,
     *     tickets_completed_today:int,
     *     failure_summary:array{cancelled:int,skipped:int},
     *     public_activity:array<string,int>
     * }
     */
    public function build(?string $date = null): array
    {
        $targetDate = $date ?? now()->toDateString();

        $bookingSuccess = QueueTicket::query()
            ->whereDate('service_date', $targetDate)
            ->where('channel', 'online_booking')
            ->where('status', '!=', QueueStatus::Cancelled)
            ->count();

        $bookingFailed = QueueTicket::query()
            ->whereDate('service_date', $targetDate)
            ->where('channel', 'online_booking')
            ->where('status', QueueStatus::Cancelled)
            ->count();

        $ticketsCreated = QueueTicket::query()
            ->whereDate('created_at', $targetDate)
            ->count();

        $ticketsCancelled = QueueTicket::query()
            ->whereDate('service_date', $targetDate)
            ->where('status', QueueStatus::Cancelled)
            ->count();

        $ticketsCompleted = QueueTicket::query()
            ->whereDate('service_date', $targetDate)
            ->where('status', QueueStatus::Completed)
            ->count();

        /** @var Collection<int,object{channel:string,total:int}> $publicRows */
        $publicRows = DB::table('queue_tickets')
            ->whereDate('service_date', $targetDate)
            ->selectRaw('channel, COUNT(*) as total')
            ->groupBy('channel')
            ->orderBy('channel')
            ->get();

        $cancelledActivities = DB::table('queue_activities')
            ->whereDate('created_at', $targetDate)
            ->where('action', 'ticket_cancelled')
            ->count();

        $skippedActivities = DB::table('queue_activities')
            ->whereDate('created_at', $targetDate)
            ->where('action', 'ticket_skipped')
            ->count();

        return [
            'booking_success_today' => $bookingSuccess,
            'booking_failed_today' => $bookingFailed,
            'tickets_created_today' => $ticketsCreated,
            'tickets_cancelled_today' => $ticketsCancelled,
            'tickets_completed_today' => $ticketsCompleted,
            'failure_summary' => [
                'cancelled' => $cancelledActivities,
                'skipped' => $skippedActivities,
            ],
            'public_activity' => $publicRows
                ->mapWithKeys(fn (object $row): array => [$row->channel => (int) $row->total])
                ->toArray(),
        ];
    }

    /**
     * Get trend data for the last 7 days.
     *
     * @return array<int, array{date: string, total: int, completed: int}>
     */
    public function getTrendData(?string $date = null): array
    {
        $endDate = $date ? \Carbon\Carbon::parse($date) : now();
        $startDate = $endDate->copy()->subDays(6)->startOfDay();
        $endDate = $endDate->endOfDay();

        /** @var Collection<int, object{date: string, total: int, completed: int}> $rows */
        $rows = DB::table('queue_tickets')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', [QueueStatus::Completed->value])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();

        // Build date range for 7 days
        $dateRange = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $endDate->copy()->subDays($i)->startOfDay();
            $dateKey = $day->format('Y-m-d');
            $dateRange[$dateKey] = ['date' => $day->format('d M'), 'total' => 0, 'completed' => 0];
        }

        // Fill with actual data
        foreach ($rows as $row) {
            if (isset($dateRange[$row->date])) {
                $dateRange[$row->date]['total'] = (int) $row->total;
                $dateRange[$row->date]['completed'] = (int) $row->completed;
            }
        }

        return array_values($dateRange);
    }

    /**
     * Get service distribution for completed tickets.
     *
     * @return array<int, array{name: string, count: int, percentage: float}>
     */
    public function getServiceDistribution(?string $date = null): array
    {
        $targetDate = $date ?? now()->toDateString();

        /** @var Collection<int, object{name: string, count: int}> $rows */
        $rows = DB::table('queue_tickets')
            ->join('services', 'queue_tickets.service_id', '=', 'services.id')
            ->whereDate('queue_tickets.service_date', $targetDate)
            ->where('queue_tickets.status', QueueStatus::Completed->value)
            ->select('services.name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $total = $rows->sum('count');
        $result = [];

        // Take top 5, bucket rest as "Lainnya" if more than 6
        $topRows = $rows->take(5);
        $otherCount = $rows->skip(5)->sum('count');

        foreach ($topRows as $row) {
            $result[] = [
                'name' => $row->name,
                'count' => (int) $row->count,
                'percentage' => round(($row->count / $total) * 100, 1),
            ];
        }

        if ($otherCount > 0) {
            $result[] = [
                'name' => 'Lainnya',
                'count' => (int) $otherCount,
                'percentage' => round(($otherCount / $total) * 100, 1),
            ];
        }

        return $result;
    }
}
