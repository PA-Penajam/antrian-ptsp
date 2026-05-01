<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class CompleteTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {}

    public function handle(QueueTicket $queueTicket, Counter $counter, ?int $userId = null): QueueTicket
    {
        if ($queueTicket->status !== QueueStatus::Called) {
            throw new InvalidArgumentException('Only called tickets can be completed.');
        }

        $fromStatus = $queueTicket->status;

        $queueTicket->update([
            'status' => QueueStatus::Completed,
            'counter_id' => $counter->id,
            'started_at' => $queueTicket->started_at ?? CarbonImmutable::now(),
            'completed_at' => CarbonImmutable::now(),
        ]);

        $this->logQueueActivity->handle(
            queueTicket: $queueTicket,
            action: 'ticket_completed',
            userId: $userId,
            counterId: $counter->id,
            meta: [
                'from_status' => $fromStatus->value,
                'to_status' => QueueStatus::Completed->value,
                'service_id' => $queueTicket->service_id,
                'queue_pool_id' => $queueTicket->queue_pool_id,
                'visit_purpose' => $queueTicket->visit_purpose,
            ]
        );

        return $queueTicket->refresh();
    }
}
