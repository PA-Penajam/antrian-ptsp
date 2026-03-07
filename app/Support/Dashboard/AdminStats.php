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
}
