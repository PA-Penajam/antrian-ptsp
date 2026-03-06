<?php

namespace App\Actions\Queue;

use App\Models\Counter;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CallNextTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {
    }

    public function handle(Counter $counter, ?int $userId = null): ?QueueTicket
    {
        return DB::transaction(function () use ($counter, $userId): ?QueueTicket {
            $queueTicket = QueueTicket::query()
                ->where('queue_pool_id', $counter->queue_pool_id)
                ->where('status', 'waiting')
                ->orderBy('service_date')
                ->orderBy('sequence_number')
                ->first();

            if (! $queueTicket) {
                return null;
            }

            $queueTicket->update([
                'status' => 'called',
                'counter_id' => $counter->id,
                'called_at' => CarbonImmutable::now(),
            ]);

            $this->logQueueActivity->handle(
                queueTicket: $queueTicket,
                action: 'ticket_called',
                userId: $userId,
                counterId: $counter->id
            );

            return $queueTicket->refresh();
        });
    }
}
