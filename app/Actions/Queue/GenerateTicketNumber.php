<?php

namespace App\Actions\Queue;

use App\Models\QueuePool;
use App\Models\QueueTicket;
use Carbon\CarbonInterface;

class GenerateTicketNumber
{
    /**
     * @return array{sequence_number:int,ticket_number:string}
     */
    public function handle(QueuePool $queuePool, CarbonInterface $serviceDate): array
    {
        $maxSequence = QueueTicket::query()
            ->where('queue_pool_id', $queuePool->id)
            ->whereDate('service_date', $serviceDate->toDateString())
            ->max('sequence_number');

        $nextSequence = ((int) $maxSequence) + 1;
        $ticketNumber = sprintf('%s-%04d', strtoupper($queuePool->code), $nextSequence);

        return [
            'sequence_number' => $nextSequence,
            'ticket_number' => $ticketNumber,
        ];
    }
}
