<?php

namespace App\Actions\Queue;

use App\Models\Counter;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class CancelTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {
    }

    public function handle(QueueTicket $queueTicket, Counter $counter, ?int $userId = null): QueueTicket
    {
        if (! in_array($queueTicket->status, ['booked', 'waiting', 'called'], true)) {
            throw new InvalidArgumentException('Ticket cannot be cancelled from its current status.');
        }

        $queueTicket->update([
            'status' => 'cancelled',
            'counter_id' => $counter->id,
            'cancelled_at' => CarbonImmutable::now(),
        ]);

        $this->logQueueActivity->handle(
            queueTicket: $queueTicket,
            action: 'ticket_cancelled',
            userId: $userId,
            counterId: $counter->id
        );

        return $queueTicket->refresh();
    }
}
