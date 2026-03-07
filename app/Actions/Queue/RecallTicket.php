<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class RecallTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {}

    public function handle(QueueTicket $queueTicket, Counter $counter, ?int $userId = null): QueueTicket
    {
        if ($queueTicket->status !== QueueStatus::Called) {
            throw new InvalidArgumentException('Only called tickets can be recalled.');
        }

        $fromStatus = $queueTicket->status;

        $queueTicket->update([
            'counter_id' => $counter->id,
            'called_at' => CarbonImmutable::now(),
        ]);

        $this->logQueueActivity->handle(
            queueTicket: $queueTicket,
            action: 'ticket_recalled',
            userId: $userId,
            counterId: $counter->id,
            meta: [
                'from_status' => $fromStatus->value,
                'to_status' => $fromStatus->value,
                'service_id' => $queueTicket->service_id,
                'queue_pool_id' => $queueTicket->queue_pool_id,
            ]
        );

        return $queueTicket->refresh();
    }
}
