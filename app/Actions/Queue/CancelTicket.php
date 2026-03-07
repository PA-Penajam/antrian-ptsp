<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class CancelTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {}

    public function handle(QueueTicket $queueTicket, Counter $counter, ?int $userId = null): QueueTicket
    {
        if (! in_array($queueTicket->status, [QueueStatus::Booked, QueueStatus::Waiting, QueueStatus::Called], true)) {
            throw new InvalidArgumentException('Ticket cannot be cancelled from its current status.');
        }

        $fromStatus = $queueTicket->status;

        $queueTicket->update([
            'status' => QueueStatus::Cancelled,
            'counter_id' => $counter->id,
            'cancelled_at' => CarbonImmutable::now(),
        ]);

        $this->logQueueActivity->handle(
            queueTicket: $queueTicket,
            action: 'ticket_cancelled',
            userId: $userId,
            counterId: $counter->id,
            meta: [
                'from_status' => $fromStatus->value,
                'to_status' => QueueStatus::Cancelled->value,
                'service_id' => $queueTicket->service_id,
                'queue_pool_id' => $queueTicket->queue_pool_id,
            ]
        );

        return $queueTicket->refresh();
    }
}
