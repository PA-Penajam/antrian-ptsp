<?php

namespace App\Actions\Queue;

use App\Models\QueueActivity;
use App\Models\QueueTicket;

class LogQueueActivity
{
    /**
     * @param  array<string,mixed>|null  $meta
     */
    public function handle(
        QueueTicket $queueTicket,
        string $action,
        ?int $userId = null,
        ?int $counterId = null,
        ?array $meta = null
    ): QueueActivity {
        return QueueActivity::query()->create([
            'queue_ticket_id' => $queueTicket->id,
            'user_id' => $userId,
            'counter_id' => $counterId,
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}
