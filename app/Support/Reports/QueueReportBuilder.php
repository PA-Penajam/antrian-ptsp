<?php

namespace App\Support\Reports;

use App\Models\QueueTicket;
use Illuminate\Support\Collection;

class QueueReportBuilder
{
    /**
     * @return array{
     *     by_service:array<string,int>,
     *     by_counter:array<string,int>,
     *     by_officer:array<string,int>,
     *     by_status:array<string,int>
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
}
