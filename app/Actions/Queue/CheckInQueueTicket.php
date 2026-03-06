<?php

namespace App\Actions\Queue;

use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CheckInQueueTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {
    }

    public function handle(QueueTicket $queueTicket, ?int $userId = null): QueueTicket
    {
        if ($queueTicket->status !== 'booked') {
            throw new InvalidArgumentException('Only booked tickets can be checked in.');
        }

        return DB::transaction(function () use ($queueTicket, $userId): QueueTicket {
            $queueTicket->update([
                'status' => 'waiting',
                'checked_in_at' => CarbonImmutable::now(),
            ]);

            $this->logQueueActivity->handle(
                queueTicket: $queueTicket,
                action: 'ticket_checked_in',
                userId: $userId,
                counterId: null,
                meta: [
                    'from_status' => 'booked',
                    'to_status' => 'waiting',
                ]
            );

            return $queueTicket->refresh();
        });
    }
}
