<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class SkipTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {}

    public function handle(QueueTicket $queueTicket, Counter $counter, ?int $userId = null): QueueTicket
    {
        if (! in_array($queueTicket->status, [QueueStatus::Called, QueueStatus::Waiting], true)) {
            throw new InvalidArgumentException('Only waiting or called tickets can be skipped.');
        }

        $queueTicket->update([
            'status' => QueueStatus::Skipped,
            'counter_id' => $counter->id,
            'cancelled_at' => CarbonImmutable::now(),
        ]);

        $this->logQueueActivity->handle(
            queueTicket: $queueTicket,
            action: 'ticket_skipped',
            userId: $userId,
            counterId: $counter->id
        );

        return $queueTicket->refresh();
    }
}
