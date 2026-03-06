<?php

namespace App\Actions\Queue;

use App\Models\Counter;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class CompleteTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {
    }

    public function handle(QueueTicket $queueTicket, Counter $counter, ?int $userId = null): QueueTicket
    {
        if ($queueTicket->status !== 'called') {
            throw new InvalidArgumentException('Only called tickets can be completed.');
        }

        $queueTicket->update([
            'status' => 'completed',
            'counter_id' => $counter->id,
            'started_at' => $queueTicket->started_at ?? CarbonImmutable::now(),
            'completed_at' => CarbonImmutable::now(),
        ]);

        $this->logQueueActivity->handle(
            queueTicket: $queueTicket,
            action: 'ticket_completed',
            userId: $userId,
            counterId: $counter->id
        );

        return $queueTicket->refresh();
    }
}
