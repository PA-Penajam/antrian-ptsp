<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class RestoreSkippedTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {}

    public function handle(QueueTicket $queueTicket, Counter $counter, ?int $userId = null): QueueTicket
    {
        if ($queueTicket->status !== QueueStatus::Skipped) {
            throw new InvalidArgumentException('Only skipped tickets can be restored/recalled.');
        }

        $fromStatus = $queueTicket->status;

        $queueTicket->update([
            'status' => QueueStatus::Called,
            'counter_id' => $counter->id,
            'called_at' => CarbonImmutable::now(),
            'cancelled_at' => null, // Reset cancelled_at since it is no longer skipped
        ]);

        $this->logQueueActivity->handle(
            queueTicket: $queueTicket,
            action: 'ticket_recalled',
            userId: $userId,
            counterId: $counter->id,
            meta: [
                'from_status' => $fromStatus->value,
                'to_status' => QueueStatus::Called->value,
                'service_id' => $queueTicket->service_id,
                'queue_pool_id' => $queueTicket->queue_pool_id,
            ]
        );

        \App\Events\TicketCalled::dispatch($queueTicket->id);

        return $queueTicket->refresh();
    }
}
