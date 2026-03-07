<?php

namespace App\Support\Dashboard;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonitorStats
{
    /**
     * @return array{
     *     total_served_today:int,
     *     throughput_today:int,
     *     backlog_by_service:array<string,int>,
     *     served_by_officer:array<string,int>,
     *     officer_service_matrix:array<string,array<string,int>>
     * }
     */
    public function build(?string $date = null): array
    {
        $targetDate = $date ?? now()->toDateString();

        $totalServed = QueueTicket::query()
            ->whereDate('service_date', $targetDate)
            ->where('status', QueueStatus::Completed)
            ->count();

        /** @var Collection<int,object{name:string,total:int}> $backlogRows */
        $backlogRows = DB::table('queue_tickets')
            ->join('services', 'services.id', '=', 'queue_tickets.service_id')
            ->whereDate('queue_tickets.service_date', $targetDate)
            ->where('queue_tickets.status', QueueStatus::Waiting->value)
            ->selectRaw('services.name as name, COUNT(*) as total')
            ->groupBy('services.name')
            ->orderBy('services.name')
            ->get();

        /** @var Collection<int,object{name:string,total:int}> $officerRows */
        $officerRows = DB::table('queue_activities')
            ->join('users', 'users.id', '=', 'queue_activities.user_id')
            ->whereDate('queue_activities.created_at', $targetDate)
            ->where('queue_activities.action', 'ticket_completed')
            ->selectRaw('users.name as name, COUNT(*) as total')
            ->groupBy('users.name')
            ->orderBy('users.name')
            ->get();

        /** @var Collection<int,object{officer_name:string,service_name:string,total:int}> $matrixRows */
        $matrixRows = DB::table('queue_activities')
            ->join('users', 'users.id', '=', 'queue_activities.user_id')
            ->join('queue_tickets', 'queue_tickets.id', '=', 'queue_activities.queue_ticket_id')
            ->join('services', 'services.id', '=', 'queue_tickets.service_id')
            ->whereDate('queue_activities.created_at', $targetDate)
            ->where('queue_activities.action', 'ticket_completed')
            ->selectRaw('users.name as officer_name, services.name as service_name, COUNT(*) as total')
            ->groupBy('users.name', 'services.name')
            ->orderBy('users.name')
            ->orderBy('services.name')
            ->get();

        /** @var array<string,array<string,int>> $matrix */
        $matrix = [];
        foreach ($matrixRows as $row) {
            $matrix[$row->officer_name][$row->service_name] = (int) $row->total;
        }

        return [
            'total_served_today' => $totalServed,
            'throughput_today' => $totalServed,
            'backlog_by_service' => $backlogRows
                ->mapWithKeys(fn (object $row): array => [$row->name => (int) $row->total])
                ->toArray(),
            'served_by_officer' => $officerRows
                ->mapWithKeys(fn (object $row): array => [$row->name => (int) $row->total])
                ->toArray(),
            'officer_service_matrix' => $matrix,
        ];
    }
}
