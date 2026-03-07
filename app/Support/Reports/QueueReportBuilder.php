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
        $tickets = QueueTicket::query()
            ->with(['service', 'counter', 'creator'])
            ->whereDate('service_date', '>=', $from)
            ->whereDate('service_date', '<=', $to)
            ->get();

        return [
            'by_service' => $this->groupAndCount($tickets, fn (QueueTicket $ticket): string => $ticket->service?->name ?? '-'),
            'by_counter' => $this->groupAndCount($tickets, fn (QueueTicket $ticket): string => $ticket->counter?->name ?? '-'),
            'by_officer' => $this->groupAndCount($tickets, fn (QueueTicket $ticket): string => $ticket->creator?->name ?? '-'),
            'by_status' => $this->groupAndCount($tickets, fn (QueueTicket $ticket): string => $ticket->status->value),
            'officer_service_distribution' => $this->buildOfficerServiceDistribution($from, $to),
        ];
    }

    /**
     * @param  Collection<int,QueueTicket>  $tickets
     * @param  callable(QueueTicket):string  $groupBy
     * @return array<string,int>
     */
    private function groupAndCount(Collection $tickets, callable $groupBy): array
    {
        /** @var array<string,int> $result */
        $result = $tickets
            ->groupBy($groupBy)
            ->map(fn (Collection $group): int => $group->count())
            ->sortKeys()
            ->toArray();

        return $result;
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
