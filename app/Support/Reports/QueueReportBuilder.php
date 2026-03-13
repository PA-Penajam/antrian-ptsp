<?php

namespace App\Support\Reports;

use App\Models\QueueTicket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QueueReportBuilder
{
    /**
     * @return array{
     *     by_service:array<string,int>,
     *     by_counter:array<string,int>,
     *     by_officer:array<string,int>,
     *     by_status:array<string,int>,
     *     officer_service_distribution:array<string,array<string,int>>
     * }
     */
    public function build(string $from, string $to): array
    {
        $dateScope = fn ($query) => $query
            ->whereDate('service_date', '>=', $from)
            ->whereDate('service_date', '<=', $to);

        return [
            'by_service' => QueueTicket::query()
                ->tap($dateScope)
                ->join('services', 'queue_tickets.service_id', '=', 'services.id')
                ->selectRaw('services.name, COUNT(*) as count')
                ->groupBy('services.name')
                ->orderBy('services.name')
                ->pluck('count', 'services.name')
                ->toArray(),

            'by_counter' => QueueTicket::query()
                ->tap($dateScope)
                ->whereNotNull('counter_id')
                ->join('counters', 'queue_tickets.counter_id', '=', 'counters.id')
                ->selectRaw('counters.name, COUNT(*) as count')
                ->groupBy('counters.name')
                ->orderBy('counters.name')
                ->pluck('count', 'counters.name')
                ->toArray(),

            'by_officer' => QueueTicket::query()
                ->tap($dateScope)
                ->join('users', 'queue_tickets.created_by', '=', 'users.id')
                ->selectRaw('users.name, COUNT(*) as count')
                ->groupBy('users.name')
                ->orderBy('users.name')
                ->pluck('count', 'users.name')
                ->toArray(),

            'by_status' => QueueTicket::query()
                ->tap($dateScope)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),

            'officer_service_distribution' => $this->buildOfficerServiceDistribution($from, $to),
        ];
    }

    /**
     * @return array<string,array<string,int>>
     */
    private function buildOfficerServiceDistribution(string $from, string $to): array
    {
        /** @var Collection<int,object{officer_name:string,service_name:string,total:int}> $rows */
        $rows = DB::table('queue_activities')
            ->join('users', 'users.id', '=', 'queue_activities.user_id')
            ->join('queue_tickets', 'queue_tickets.id', '=', 'queue_activities.queue_ticket_id')
            ->join('services', 'services.id', '=', 'queue_tickets.service_id')
            ->where('queue_activities.action', 'ticket_completed')
            ->whereDate('queue_tickets.service_date', '>=', $from)
            ->whereDate('queue_tickets.service_date', '<=', $to)
            ->selectRaw('users.name as officer_name, services.name as service_name, COUNT(*) as total')
            ->groupBy('users.name', 'services.name')
            ->orderBy('users.name')
            ->orderBy('services.name')
            ->get();

        /** @var array<string,array<string,int>> $distribution */
        $distribution = [];

        foreach ($rows as $row) {
            $distribution[$row->officer_name][$row->service_name] = (int) $row->total;
        }

        ksort($distribution);

        return $distribution;
    }
}
